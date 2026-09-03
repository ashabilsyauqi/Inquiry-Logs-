const { default: makeWASocket, useMultiFileAuthState, DisconnectReason, fetchLatestBaileysVersion } = require('@whiskeysockets/baileys');
const qrcode = require('qrcode');
const qrcodeTerminal = require('qrcode-terminal');
const axios = require('axios');
const express = require('express');
const cors = require('cors');
const pino = require('pino');
const fs = require('fs');
const path = require('path');

const app = express();
app.use(cors());
app.use(express.json());

// Load environment variables from parent Laravel .env if available
const envPath = path.join(__dirname, '../.env');
if (fs.existsSync(envPath)) {
    try {
        const envContent = fs.readFileSync(envPath, 'utf-8');
        envContent.split(/\r?\n/).forEach(line => {
            const trimmed = line.trim();
            if (trimmed && !trimmed.startsWith('#')) {
                const eqIdx = trimmed.indexOf('=');
                if (eqIdx > 0) {
                    const key = trimmed.substring(0, eqIdx).trim();
                    const val = trimmed.substring(eqIdx + 1).trim().replace(/^["']|["']$/g, '');
                    if (!process.env[key]) {
                        process.env[key] = val;
                    }
                }
            }
        });
        console.log(`[WA Bridge] Successfully loaded parent Laravel environment from ${envPath}`);
    } catch (e) {
        console.error('[WA Bridge] Error parsing ../.env:', e.message);
    }
}

const PORT = process.env.BRIDGE_PORT || 3001;
let BASE_URL = process.env.APP_URL || process.env.WEBHOOK_BASE_URL;
if (!BASE_URL) {
    BASE_URL = 'http://127.0.0.1:8000';
}
BASE_URL = BASE_URL.replace(/\/+$/, '');

const WEBHOOK_URL = process.env.WEBHOOK_URL || (BASE_URL + '/api/wa-webhook');
const DISCONNECT_ALERT_URL = process.env.DISCONNECT_ALERT_URL || (BASE_URL + '/api/wa-disconnect-alert');
const STATUS_UPDATE_URL = process.env.STATUS_UPDATE_URL || (BASE_URL + '/api/wa-status-update');

console.log(`[WA Bridge] Backend Base URL: ${BASE_URL}`);
console.log(`[WA Bridge] Webhook URL: ${WEBHOOK_URL}`);
console.log(`[WA Bridge] Status Update URL: ${STATUS_UPDATE_URL}`);

// Map to store sessions: sessionId -> { sessionId, sock, status, qrDataUrl, phone }
const sessions = new Map();
const lidToPhoneMap = new Map();

function sendDisconnectAlert(sessionId, reason) {
    console.log(`[WA Bridge] Sending disconnect alert for [${sessionId}]: ${reason}`);
    axios.post(DISCONNECT_ALERT_URL, { sessionId, reason })
        .then(() => console.log(`[WA Bridge] [${sessionId}] Disconnect alert sent.`))
        .catch(err => console.error(`[WA Bridge] Failed sending disconnect alert for ${sessionId}:`, err.message));
}

function sendConnectStatusUpdate(sessionId, status, phone = null) {
    console.log(`[WA Bridge] Sending status update for [${sessionId}]: ${status} (${phone || 'No phone'})`);
    axios.post(STATUS_UPDATE_URL, { sessionId, status, phone })
        .then(() => console.log(`[WA Bridge] [${sessionId}] Status '${status}' synced to Laravel DB.`))
        .catch(err => console.error(`[WA Bridge] Failed syncing status for ${sessionId} (${STATUS_UPDATE_URL}):`, err.message));
}

async function createSession(sessionId = 'default') {
    let sessionData = sessions.get(sessionId);

    if (sessionData && sessionData.status !== 'DISCONNECTED' && sessionData.sock) {
        return sessionData;
    }

    if (sessionData && sessionData.sock) {
        try { sessionData.sock.ev.removeAllListeners(); } catch (e) {}
        try { sessionData.sock.end(new Error('Session reset')); } catch (e) {}
    }

    console.log(`[WA Bridge] Initializing Baileys Socket Session: ${sessionId}`);

    sessionData = {
        sessionId,
        status: 'INITIALIZING',
        qrDataUrl: null,
        phone: null,
        sock: null
    };
    sessions.set(sessionId, sessionData);

    try {
        const authFolder = path.join(__dirname, `baileys_auth_${sessionId}`);
        const { state, saveCreds } = await useMultiFileAuthState(authFolder);
        const { version } = await fetchLatestBaileysVersion().catch(() => ({ version: [2, 3000, 1015901307] }));

        const sock = makeWASocket({
            version,
            auth: state,
            printQRInTerminal: false,
            logger: pino({ level: 'silent' }),
            browser: ['Difitech CRM', 'Chrome', '1.0.0'],
            connectTimeoutMs: 60000,
            defaultQueryTimeoutMs: 60000,
            keepAliveIntervalMs: 25000,
        });

        sessionData.sock = sock;

        sock.ev.on('creds.update', saveCreds);

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                console.log(`[WA Bridge] [${sessionId}] New QR Code generated.`);
                sessionData.status = 'QR_READY';
                try {
                    sessionData.qrDataUrl = await qrcode.toDataURL(qr);
                    qrcodeTerminal.generate(qr, { small: true });
                } catch (err) {
                    console.error(`[WA Bridge] [${sessionId}] Error generating QR Data URL:`, err.message);
                }
            }

            if (connection === 'open') {
                const rawUser = sock.user ? sock.user.id : null;
                const cleanPhone = rawUser ? rawUser.split(':')[0].replace(/[^0-9]/g, '') : null;
                sessionData.status = 'CONNECTED';
                sessionData.qrDataUrl = null;
                sessionData.phone = cleanPhone;
                console.log(`[WA Bridge] [${sessionId}] Socket Connected! Phone: ${cleanPhone}`);

                sendConnectStatusUpdate(sessionId, 'CONNECTED', cleanPhone);
            }

            if (connection === 'close') {
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
                console.log(`[WA Bridge] [${sessionId}] Connection closed (code ${statusCode}). Reconnecting: ${shouldReconnect}`);

                sessionData.status = 'DISCONNECTED';
                sessionData.qrDataUrl = null;

                if (statusCode === DisconnectReason.loggedOut) {
                    sendDisconnectAlert(sessionId, 'Log out dari aplikasi HP WhatsApp');
                    try { fs.rmSync(authFolder, { recursive: true, force: true }); } catch (e) {}
                } else if (shouldReconnect) {
                    setTimeout(() => createSession(sessionId), 3000);
                }
            }
        });

        // Track and map WhatsApp LID (Privacy ID) to Phone Numbers (PNJID)
        sock.ev.on('contacts.upsert', (contacts) => {
            for (const contact of contacts) {
                if (contact.id && contact.lid) {
                    const phone = contact.id.replace('@s.whatsapp.net', '').replace(/[^0-9]/g, '');
                    const lid = contact.lid.replace('@lid', '').replace(/[^0-9]/g, '');
                    if (phone && lid) {
                        lidToPhoneMap.set(lid, phone);
                        console.log(`[WA Bridge] [${sessionId}] Cached LID mapping: ${lid} -> ${phone}`);
                    }
                }
            }
        });

        sock.ev.on('contacts.update', (updates) => {
            for (const update of updates) {
                if (update.id && update.lid) {
                    const phone = update.id.replace('@s.whatsapp.net', '').replace(/[^0-9]/g, '');
                    const lid = update.lid.replace('@lid', '').replace(/[^0-9]/g, '');
                    if (phone && lid) {
                        lidToPhoneMap.set(lid, phone);
                        console.log(`[WA Bridge] [${sessionId}] Updated LID mapping: ${lid} -> ${phone}`);
                    }
                }
            }
        });

        sock.ev.on('messages.upsert', async (m) => {
            if (m.type !== 'notify') return;
            for (const msg of m.messages) {
                if (!msg.message) continue;
                const remoteJid = msg.key.remoteJid || '';
                if (remoteJid.endsWith('@g.us') || remoteJid.includes('status@broadcast')) continue;

                const isFromMe = Boolean(msg.key.fromMe);
                
                let remotePhone = '';
                if (remoteJid.endsWith('@s.whatsapp.net')) {
                    remotePhone = remoteJid.replace('@s.whatsapp.net', '').replace(/[^0-9]/g, '');
                } else if (msg.key.participant && msg.key.participant.endsWith('@s.whatsapp.net')) {
                    remotePhone = msg.key.participant.replace('@s.whatsapp.net', '').replace(/[^0-9]/g, '');
                } else if (msg.participant && msg.participant.endsWith('@s.whatsapp.net')) {
                    remotePhone = msg.participant.replace('@s.whatsapp.net', '').replace(/[^0-9]/g, '');
                } else if (remoteJid.endsWith('@lid')) {
                    const cleanLid = remoteJid.replace('@lid', '').replace(/[^0-9]/g, '');
                    let resolved = lidToPhoneMap.get(cleanLid);

                    if (!resolved && (msg.key.participant || msg.participant)) {
                        const participantPhone = (msg.key.participant || msg.participant).replace('@s.whatsapp.net', '').replace(/[^0-9]/g, '');
                        if (participantPhone && participantPhone.length <= 13) {
                            resolved = participantPhone;
                            lidToPhoneMap.set(cleanLid, resolved);
                        }
                    }

                    if (!resolved && sock.signalRepository?.lidMapping?.getPNForLID) {
                        try {
                            const pn = await sock.signalRepository.lidMapping.getPNForLID(remoteJid);
                            if (pn) {
                                resolved = pn.replace('@s.whatsapp.net', '').replace(/[^0-9]/g, '');
                                lidToPhoneMap.set(cleanLid, resolved);
                            }
                        } catch (e) {}
                    }

                    remotePhone = resolved || cleanLid;
                } else {
                    const cleanId = remoteJid.split('@')[0].replace(/[^0-9]/g, '');
                    remotePhone = lidToPhoneMap.get(cleanId) || cleanId;
                }

                const text = msg.message.conversation || msg.message.extendedTextMessage?.text || msg.message.imageMessage?.caption || '';
                if (!text.trim()) continue;

                const senderName = isFromMe ? 'Admin CS' : (msg.pushName || remotePhone);
                const sender = isFromMe ? (sessionData.phone || 'admin') : remotePhone;
                const receiver = isFromMe ? remotePhone : (sessionData.phone || sessionId || 'default');

                console.log(`[WA Bridge] [${sessionId}] ${isFromMe ? 'Outgoing (CS)' : 'Incoming'} message [${remotePhone}]: ${text}`);

                axios.post(WEBHOOK_URL, {
                    sessionId,
                    sender,
                    receiver,
                    senderName,
                    message: text,
                    isFromMe
                }).catch(err => console.error(`[WA Bridge] Webhook post error (${WEBHOOK_URL}):`, err.message));
            }
        });

    } catch (err) {
        console.error(`[WA Bridge] [${sessionId}] Baileys Init Error:`, err.message);
        sessionData.status = 'DISCONNECTED';
    }

    return sessionData;
}

// Automatically initialize 'default' session on startup
createSession('default');

// API ENDPOINTS

// 1. Get all active sessions
app.get('/api/sessions', (req, res) => {
    const list = [];
    for (const [id, data] of sessions.entries()) {
        list.push({
            sessionId: id,
            status: data.status,
            phone: data.phone,
            hasQr: !!data.qrDataUrl
        });
    }
    res.json({ status: 'success', sessions: list });
});

// 2. Get QR Data URL for a session
app.get('/api/qr', async (req, res) => {
    const sessionId = req.query.session || 'default';
    let session = sessions.get(sessionId);

    if (!session || session.status === 'DISCONNECTED') {
        session = await createSession(sessionId);
    }

    res.json({
        status: 'success',
        sessionId,
        sessionStatus: session.status,
        qrDataUrl: session.qrDataUrl,
        phone: session.phone
    });
});

// 3. Connect / Initialize a session
app.post('/api/connect', async (req, res) => {
    const sessionId = req.body.session || 'default';
    let session = sessions.get(sessionId);

    if (!session || session.status === 'DISCONNECTED') {
        session = await createSession(sessionId);
    }

    res.json({
        status: 'success',
        sessionId,
        sessionStatus: session.status
    });
});

// 4. Send direct WhatsApp message
app.post('/api/send-message', async (req, res) => {
    const { session: sessionId = 'default', to, message } = req.body;
    const session = sessions.get(sessionId);

    if (!session || !session.sock || session.status !== 'CONNECTED') {
        return res.status(400).json({ status: 'error', message: 'WA Session tidak aktif atau belum terhubung.' });
    }

    if (!to || !message) {
        return res.status(422).json({ status: 'error', message: 'Nomor tujuan (to) dan isi pesan (message) wajib diisi.' });
    }

    try {
        const cleanTo = to.replace(/[^0-9]/g, '');
        const jid = cleanTo + '@s.whatsapp.net';
        const sentMsg = await session.sock.sendMessage(jid, { text: message });

        res.json({
            status: 'success',
            message: 'Pesan WhatsApp berhasil dikirim!',
            messageId: sentMsg.key.id
        });
    } catch (err) {
        console.error(`[WA Bridge] Send message error:`, err.message);
        res.status(500).json({ status: 'error', message: err.message });
    }
});

// 5. Logout / Destroy a session
app.post('/api/logout', async (req, res) => {
    const sessionId = req.body.session || 'default';
    const session = sessions.get(sessionId);

    if (session && session.sock) {
        try {
            await session.sock.logout().catch(() => {});
            session.sock.end();
        } catch (err) {
            console.error(`Error logging out session ${sessionId}:`, err.message);
        }
        sessions.delete(sessionId);

        const authFolder = path.join(__dirname, `baileys_auth_${sessionId}`);
        try { fs.rmSync(authFolder, { recursive: true, force: true }); } catch (e) {}
    }

    sendDisconnectAlert(sessionId, 'Manual disconnect / Logout triggered from Dashboard');

    res.json({ status: 'success', message: `Session ${sessionId} logged out.` });
});

// Auto-resume all saved Baileys sessions on engine startup
function autoResumeExistingSessions() {
    try {
        const files = fs.readdirSync(__dirname);
        const authFolders = files.filter(f => f.startsWith('baileys_auth_') && fs.statSync(path.join(__dirname, f)).isDirectory());

        if (authFolders.length === 0) {
            console.log('[WA Bridge] No existing auth sessions found to auto-resume.');
            return;
        }

        console.log(`[WA Bridge] Found ${authFolders.length} saved session(s). Auto-resuming all WhatsApp connections...`);
        authFolders.forEach(folder => {
            const sessionId = folder.replace('baileys_auth_', '');
            if (sessionId) {
                console.log(`[WA Bridge] 🔄 Auto-resuming session: [${sessionId}]`);
                createSession(sessionId).catch(err => {
                    console.error(`[WA Bridge] Error auto-resuming session [${sessionId}]:`, err.message);
                });
            }
        });
    } catch (err) {
        console.error('[WA Bridge] Error checking auth folders for auto-resume:', err.message);
    }
}

app.listen(PORT, '0.0.0.0', () => {
    console.log(`🚀 WA Bridge Baileys Socket Engine running on http://0.0.0.0:${PORT}`);
    autoResumeExistingSessions();
});
