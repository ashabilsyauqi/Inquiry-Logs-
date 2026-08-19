const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode');
const qrcodeTerminal = require('qrcode-terminal');
const axios = require('axios');
const express = require('express');
const cors = require('cors');

const app = express();
app.use(cors());
app.use(express.json());

const PORT = process.env.BRIDGE_PORT || 3001;
const BASE_URL = process.env.APP_URL || 'https://crm.difitech.id';
const WEBHOOK_URL = process.env.WEBHOOK_URL || (BASE_URL + '/api/wa-webhook');
const DISCONNECT_ALERT_URL = process.env.DISCONNECT_ALERT_URL || (BASE_URL + '/api/wa-disconnect-alert');

// Map to store sessions: sessionId -> { sessionId, client, status, qrDataUrl, phone }
const sessions = new Map();
const STATUS_UPDATE_URL = process.env.STATUS_UPDATE_URL || (BASE_URL + '/api/wa-status-update');

function sendDisconnectAlert(sessionId, reason) {
    axios.post(DISCONNECT_ALERT_URL, { sessionId, reason })
        .catch(err => console.error(`[WA Bridge] Failed sending disconnect alert for ${sessionId}:`, err.message));
}

function sendConnectStatusUpdate(sessionId, status, phone = null) {
    axios.post(STATUS_UPDATE_URL, { sessionId, status, phone })
        .then(() => console.log(`[WA Bridge] [${sessionId}] Status '${status}' synced to Laravel DB.`))
        .catch(err => console.error(`[WA Bridge] Failed syncing status for ${sessionId}:`, err.message));
}
const fs = require('fs');
const path = require('path');

function getCustomExecutablePath() {
    if (process.env.PUPPETEER_EXECUTABLE_PATH) return process.env.PUPPETEER_EXECUTABLE_PATH;
    const homeDir = process.env.HOME || '/home/sryyuqht';
    const cacheDir = path.join(homeDir, '.cache/puppeteer');
    if (fs.existsSync(cacheDir)) {
        const searchPath = (dir) => {
            try {
                const files = fs.readdirSync(dir);
                for (const file of files) {
                    const fullPath = path.join(dir, file);
                    const stat = fs.statSync(fullPath);
                    if (stat.isDirectory()) {
                        const res = searchPath(fullPath);
                        if (res) return res;
                    } else if (file === 'chrome' || file === 'chrome-headless-shell' || file === 'headless_shell' || file === 'chromium') {
                        if (stat.mode & 0o111) {
                            return fullPath;
                        }
                    }
                }
            } catch (e) {}
            return null;
        };
        const shellPath = searchPath(cacheDir);
        if (shellPath) {
            console.log(`[WA Bridge] Found executable at: ${shellPath}`);
            return shellPath;
        }
    }
    return undefined;
}

function createSession(sessionId = 'default') {
    let sessionData = sessions.get(sessionId);

    if (sessionData && sessionData.status !== 'DISCONNECTED' && sessionData.client) {
        return sessionData;
    }

    if (sessionData && sessionData.client) {
        try { sessionData.client.destroy(); } catch (e) {}
    }

    console.log(`[WA Bridge] Initializing session: ${sessionId}`);

    sessionData = {
        sessionId,
        status: 'INITIALIZING',
        qrDataUrl: null,
        phone: null,
        client: null
    };

    const execPath = getCustomExecutablePath();
    const client = new Client({
        authStrategy: new LocalAuth({ clientId: sessionId }),
        puppeteer: {
            headless: true,
            executablePath: execPath,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--no-first-run',
                '--no-zygote',
                '--single-process',
                '--disable-gpu',
                '--disable-software-rasterizer'
            ]
        }
    });

    sessionData.client = client;
    sessions.set(sessionId, sessionData);

    client.on('qr', async (qr) => {
        console.log(`[WA Bridge] [${sessionId}] New QR Code generated.`);
        sessionData.status = 'QR_READY';
        try {
            sessionData.qrDataUrl = await qrcode.toDataURL(qr);
        } catch (err) {
            console.error('Error generating QR Data URL:', err);
        }
        qrcodeTerminal.generate(qr, { small: true });
    });

    client.on('ready', async () => {
        sessionData.status = 'CONNECTED';
        sessionData.qrDataUrl = null;
        try {
            const info = client.info;
            sessionData.phone = info.wid.user;
            console.log(`[WA Bridge] [${sessionId}] Client Ready! Phone: ${sessionData.phone}`);
            
            // Instantly sync CONNECTED status & phone to Laravel MySQL DB
            sendConnectStatusUpdate(sessionId, 'CONNECTED', sessionData.phone);

            // Send welcome Control Panel menu to Self Chat ("Message Yourself")
            sendAdminControlPanelMenu(client, sessionData.phone);
            
            // Scan and backfill missed/unread chats during offline period
            syncRecentChats(client, sessionId);
        } catch (e) {
            console.log(`[WA Bridge] [${sessionId}] Client Ready!`);
            sendConnectStatusUpdate(sessionId, 'CONNECTED');
        }
    });

    client.on('authenticated', () => {
        sessionData.status = 'AUTHENTICATED';
        console.log(`[WA Bridge] [${sessionId}] Authenticated.`);
    });

    client.on('auth_failure', (msg) => {
        sessionData.status = 'DISCONNECTED';
        sessionData.qrDataUrl = null;
        console.error(`[WA Bridge] [${sessionId}] Auth failure:`, msg);
        sendConnectStatusUpdate(sessionId, 'DISCONNECTED');
        sendDisconnectAlert(sessionId, 'Auth failure: ' + msg);
    });

    client.on('change_state', (state) => {
        console.log(`[WA Bridge] [${sessionId}] Connection state changed: ${state}`);
        if (state === 'DISCONNECTED' || state === 'UNPAIRED' || state === 'UNLAUNCHED') {
            sessionData.status = 'DISCONNECTED';
            sessionData.qrDataUrl = null;
            sessionData.phone = null;
            sendConnectStatusUpdate(sessionId, 'DISCONNECTED');
            sendDisconnectAlert(sessionId, 'Perangkat WhatsApp terputus dari HP (State: ' + state + ')');
        }
    });

    client.on('disconnected', (reason) => {
        sessionData.status = 'DISCONNECTED';
        sessionData.qrDataUrl = null;
        sessionData.phone = null;
        console.log(`[WA Bridge] [${sessionId}] Disconnected:`, reason);
        sendConnectStatusUpdate(sessionId, 'DISCONNECTED');
        sendDisconnectAlert(sessionId, 'Disconnected: ' + reason);
    });

    client.on('message_create', async (msg) => {
        if (msg.from === 'status@broadcast' || msg.to === 'status@broadcast') return;
        if (msg.from.includes('@g.us') || msg.to.includes('@g.us')) return;

        try {
            let sender = msg.from;
            let receiver = msg.to;
            let senderName = null;

            try {
                const senderContact = await client.getContactById(msg.from);
                if (senderContact) {
                    if (senderContact.number) sender = senderContact.number;
                    senderName = senderContact.name || senderContact.pushname || null;
                }
                const receiverContact = await client.getContactById(msg.to);
                if (receiverContact && receiverContact.number) {
                    receiver = receiverContact.number;
                }
            } catch (e) {
                // Ignore contact fetching error
            }

            const cleanSender = sender.replace('@c.us', '').replace('@s.whatsapp.net', '').replace('@lid', '');
            const cleanReceiver = receiver.replace('@c.us', '').replace('@s.whatsapp.net', '').replace('@lid', '');
            const isSelfChat = (cleanSender === sessionData.phone && cleanReceiver === sessionData.phone) || (msg.from === msg.to);

            const isHashtagCommand = msg.body && msg.body.trim().startsWith('#');
            const isSlashCommand = msg.body && (msg.body.trim().startsWith('/') || msg.body.trim().startsWith('.'));

            // 1. SELF CHAT / DEDICATED ADMIN CONTROL PANEL (# COMMANDS IN MESSAGE YOURSELF)
            if (isSelfChat || (msg.fromMe && isHashtagCommand)) {
                if (isHashtagCommand || msg.body.trim() === '#help' || msg.body.trim() === '#menu') {
                    console.log(`[WA Bridge] Processing Admin Self-Chat Control Command: "${msg.body}"`);

                    if (msg.body.trim() === '#menu' || msg.body.trim() === '#help') {
                        await sendAdminControlPanelMenu(client, sessionData.phone);
                        return;
                    }

                    // Forward to Laravel Webhook as Admin Control Panel Command
                    const payload = {
                        sessionId,
                        sender: cleanSender,
                        receiver: cleanReceiver,
                        senderName,
                        message: msg.body.trim(),
                        isFromMe: true,
                        isAdminCommand: true,
                        isSelfChat: true
                    };

                    try {
                        const response = await axios.post(WEBHOOK_URL, payload);
                        if (response.data && response.data.replyMessage) {
                            // Reply back directly inside Self Chat
                            await client.sendMessage(msg.from, response.data.replyMessage);
                        }
                    } catch (e) {
                        console.error('[WA Bridge] Error posting self chat command:', e.message);
                    }
                    return;
                }
            }

            // 2. DYNAMIC OPERATOR STYLE TRIGGER MENU INTERCEPTOR IN CUSTOMER CHAT (/1, /2, /3, .1, .2, /deal)
            if (msg.fromMe && isSlashCommand && !isSelfChat) {
                console.log(`[WA Bridge] Intercepted Admin Operator Menu Trigger Command: "${msg.body}" to ${receiver}`);

                const payload = {
                    sessionId,
                    sender: cleanSender,
                    receiver: cleanReceiver,
                    senderName,
                    message: msg.body.trim(),
                    isFromMe: true,
                    isAdminCommand: true,
                    isSelfChat: false
                };

                await axios.post(WEBHOOK_URL, payload).catch(e => {});

                // Delete operator command message instantly so 0% messages are sent to customer!
                try {
                    await msg.delete(true);
                } catch (delErr) {
                    try { await msg.delete(false); } catch (e) {}
                }
                return;
            }

            const payload = {
                sessionId,
                sender: cleanSender,
                receiver: cleanReceiver,
                senderName,
                message: msg.body,
                isFromMe: msg.fromMe,
                isAdminCommand: false,
                isSelfChat
            };

            await axios.post(WEBHOOK_URL, payload);
        } catch (error) {
            console.error(`[WA Bridge] [${sessionId}] Error forwarding webhook:`, error.message);
        }
    });

    client.initialize().catch(err => {
        console.error(`[WA Bridge] [${sessionId}] Initialize error:`, err);
        sessionData.status = 'DISCONNECTED';
        sendDisconnectAlert(sessionId, 'Initialize error: ' + err.message);
    });

    return sessionData;
}

// Function to send Admin Control Panel Menu inside "Message Yourself" (Chat ke Nomor Sendiri)
async function sendAdminControlPanelMenu(client, adminPhone) {
    if (!adminPhone) return;
    try {
        const selfChatId = adminPhone.includes('@') ? adminPhone : `${adminPhone}@c.us`;
        const menuText = "🤖 *CRM ADMIN CONTROL PANEL (CHAT SENDIRI)* 🤖\n\n" +
            "Gunakan format ini di *Chat Sendiri (Message Yourself)* untuk update stage customer *TANPA MENGIRIM PESAN APAPUN KE CUSTOMER*:\n\n" +
            "📌 *FORMAT PERINTAH STAGE*:\n" +
            "• `#deal <no_hp>` ➔ Set Stage *Deal*\n" +
            "• `#meeting <no_hp>` ➔ Set Stage *Meeting*\n" +
            "• `#pitching <no_hp>` ➔ Set Stage *Pitching*\n" +
            "• `#stage <nomor/nama> <no_hp>` ➔ Set Stage khusus\n\n" +
            "💡 *CONTOH CONKRET*:\n" +
            "👉 `#deal 08123456789` \n" +
            "👉 `#meeting 628123456789` \n" +
            "👉 `#stage 1 08123456789` \n\n" +
            "Ketik `#menu` kapan saja untuk melihat menu ini kembali.";

        await client.sendMessage(selfChatId, menuText);
        console.log(`[WA Bridge] Sent Admin Control Panel menu to Self Chat (${selfChatId})`);
    } catch (err) {
        console.log('[WA Bridge] Could not send welcome menu to self chat:', err.message);
    }
}

// Function to scan and backfill recent missed chats when WA reconnects
async function syncRecentChats(client, sessionId) {
    try {
        console.log(`[WA Bridge] [${sessionId}] Scanning recent missed chats after reconnect...`);
        const chats = await client.getChats();
        const recentChats = chats.slice(0, 20); // Scan top 20 recent chats

        for (const chat of recentChats) {
            if (chat.isGroup) continue;
            const messages = await chat.fetchMessages({ limit: 5 });

            for (const msg of messages) {
                let sender = msg.from;
                let receiver = msg.to;
                let senderName = null;

                try {
                    const senderContact = await client.getContactById(msg.from);
                    if (senderContact) {
                        if (senderContact.number) sender = senderContact.number;
                        senderName = senderContact.name || senderContact.pushname || null;
                    }
                } catch (e) {}

                const payload = {
                    sessionId,
                    sender: sender.replace('@c.us', '').replace('@s.whatsapp.net', '').replace('@lid', ''),
                    receiver: receiver.replace('@c.us', '').replace('@s.whatsapp.net', '').replace('@lid', ''),
                    senderName,
                    message: msg.body,
                    isFromMe: msg.fromMe
                };

                await axios.post(WEBHOOK_URL, payload).catch(() => {});
            }
        }
        console.log(`[WA Bridge] [${sessionId}] Missed chats sync complete.`);
    } catch (err) {
        console.error(`[WA Bridge] [${sessionId}] Error syncing missed chats:`, err.message);
    }
}

// Global unhandled rejection catch to prevent node process crash on puppeteer context navigation
process.on('unhandledRejection', (reason, promise) => {
    console.log('[WA Bridge] Handled async promise rejection:', reason ? reason.message : reason);
});

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

    if (!session) {
        session = createSession(sessionId);
    } else if (session.client && session.status === 'CONNECTED') {
        try {
            const state = await session.client.getState();
            if (!state || state === 'DISCONNECTED' || state === 'UNPAIRED' || state === 'UNLAUNCHED') {
                session.status = 'DISCONNECTED';
                session.qrDataUrl = null;
                session.phone = null;
                sendConnectStatusUpdate(sessionId, 'DISCONNECTED');
                sendDisconnectAlert(sessionId, 'Device unlinked from phone app (state: ' + state + ')');
            }
        } catch (e) {
            session.status = 'DISCONNECTED';
            session.qrDataUrl = null;
            session.phone = null;
            sendConnectStatusUpdate(sessionId, 'DISCONNECTED');
            sendDisconnectAlert(sessionId, 'Connection check failed: ' + e.message);
        }
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
app.post('/api/connect', (req, res) => {
    const sessionId = req.body.session || 'default';
    let session = sessions.get(sessionId);

    if (!session || session.status === 'DISCONNECTED') {
        session = createSession(sessionId);
    }

    res.json({
        status: 'success',
        sessionId,
        sessionStatus: session.status
    });
});

// 4. Logout / Destroy a session
app.post('/api/logout', async (req, res) => {
    const sessionId = req.body.session || 'default';
    const session = sessions.get(sessionId);

    if (session && session.client) {
        try {
            await session.client.logout().catch(() => {});
            await session.client.destroy().catch(() => {});
        } catch (err) {
            console.error(`Error logging out session ${sessionId}:`, err);
        }
        sessions.delete(sessionId);
    }

    sendDisconnectAlert(sessionId, 'Manual disconnect / Logout triggered from Dashboard');

    res.json({ status: 'success', message: `Session ${sessionId} logged out.` });
});

app.listen(PORT, '0.0.0.0', () => {
    console.log(`🚀 WA Bridge Express API running on http://0.0.0.0:${PORT}`);
});
