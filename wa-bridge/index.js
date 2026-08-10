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

// Map to store sessions: sessionId -> { sessionId, client, status, qrDataUrl, phone }
const sessions = new Map();

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
    });

    client.on('disconnected', (reason) => {
        sessionData.status = 'DISCONNECTED';
        sessionData.qrDataUrl = null;
        sessionData.phone = null;
        console.log(`[WA Bridge] [${sessionId}] Disconnected:`, reason);
    });

    client.on('message_create', async (msg) => {
        if (msg.from === 'status@broadcast' || msg.to === 'status@broadcast') return;
        if (msg.from.includes('@g.us') || msg.to.includes('@g.us')) return;

        try {
            let sender = msg.from;
            let receiver = msg.to;

            try {
                const senderContact = await client.getContactById(msg.from);
                if (senderContact && senderContact.number) {
                    sender = senderContact.number;
                }
                const receiverContact = await client.getContactById(msg.to);
                if (receiverContact && receiverContact.number) {
                    receiver = receiverContact.number;
                }
            } catch (e) {
                // Ignore contact fetching error
            }

            const payload = {
                sessionId,
                sender: sender.replace('@c.us', '').replace('@s.whatsapp.net', '').replace('@lid', ''),
                receiver: receiver.replace('@c.us', '').replace('@s.whatsapp.net', '').replace('@lid', ''),
                message: msg.body,
                isFromMe: msg.fromMe
            };

            await axios.post(WEBHOOK_URL, payload);
        } catch (error) {
            console.error(`[WA Bridge] [${sessionId}] Error forwarding webhook:`, error.message);
        }
    });

    client.initialize().catch(err => {
        console.error(`[WA Bridge] [${sessionId}] Initialize error:`, err);
        sessionData.status = 'DISCONNECTED';
    });

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
            await session.client.logout();
            await session.client.destroy();
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
