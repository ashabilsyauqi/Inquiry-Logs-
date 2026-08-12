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
const WEBHOOK_URL = process.env.WEBHOOK_URL || 'http://127.0.0.1:8000/api/wa-webhook';
const DISCONNECT_ALERT_URL = process.env.DISCONNECT_ALERT_URL || 'http://127.0.0.1:8000/api/wa-disconnect-alert';

// Map to store sessions: sessionId -> { sessionId, client, status, qrDataUrl, phone }
const sessions = new Map();

function sendDisconnectAlert(sessionId, reason) {
    axios.post(DISCONNECT_ALERT_URL, { sessionId, reason })
        .catch(err => console.error(`[WA Bridge] Failed sending disconnect alert for ${sessionId}:`, err.message));
}

function createSession(sessionId = 'default') {
    if (sessions.has(sessionId)) {
        return sessions.get(sessionId);
    }

    console.log(`[WA Bridge] Initializing session: ${sessionId}`);

    const sessionData = {
        sessionId,
        status: 'INITIALIZING',
        qrDataUrl: null,
        phone: null,
        client: null
    };

    const client = new Client({
        authStrategy: new LocalAuth({ clientId: sessionId }),
        puppeteer: {
            args: ['--no-sandbox', '--disable-setuid-sandbox']
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
            
            // Scan and backfill missed/unread chats during offline period
            syncRecentChats(client, sessionId);
        } catch (e) {
            console.log(`[WA Bridge] [${sessionId}] Client Ready!`);
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
        sendDisconnectAlert(sessionId, 'Auth failure: ' + msg);
    });

    client.on('disconnected', (reason) => {
        sessionData.status = 'DISCONNECTED';
        sessionData.qrDataUrl = null;
        sessionData.phone = null;
        console.log(`[WA Bridge] [${sessionId}] Disconnected:`, reason);
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

            const isSlashCommand = msg.body && msg.body.trim().startsWith('/');

            // INTERNAL ADMIN SLASH COMMAND INTERCEPTOR
            if (msg.fromMe && isSlashCommand) {
                console.log(`[WA Bridge] Intercepted Internal Admin Command: "${msg.body}" to ${receiver}`);

                // Send payload to Laravel webhook as Admin Command
                const payload = {
                    sessionId,
                    sender: sender.replace('@c.us', '').replace('@s.whatsapp.net', '').replace('@lid', ''),
                    receiver: receiver.replace('@c.us', '').replace('@s.whatsapp.net', '').replace('@lid', ''),
                    senderName,
                    message: msg.body.trim(),
                    isFromMe: true,
                    isAdminCommand: true
                };

                await axios.post(WEBHOOK_URL, payload).catch(e => {});

                // Delete command message for everyone so customer never sees the raw slash command!
                try {
                    await msg.delete(true);
                } catch (delErr) {
                    // Fallback to delete for me
                    try { await msg.delete(false); } catch (e) {}
                }
                return;
            }

            const payload = {
                sessionId,
                sender: sender.replace('@c.us', '').replace('@s.whatsapp.net', '').replace('@lid', ''),
                receiver: receiver.replace('@c.us', '').replace('@s.whatsapp.net', '').replace('@lid', ''),
                senderName,
                message: msg.body,
                isFromMe: msg.fromMe,
                isAdminCommand: false
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
app.get('/api/qr', (req, res) => {
    const sessionId = req.query.session || 'default';
    let session = sessions.get(sessionId);

    if (!session) {
        session = createSession(sessionId);
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

    res.json({ status: 'success', message: `Session ${sessionId} logged out.` });
});

app.listen(PORT, () => {
    console.log(`🚀 WA Bridge Express API running on http://localhost:${PORT}`);
});
