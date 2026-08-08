const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const axios = require('axios');

// Create a new client instance
const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    }
});

// When the client receives the QR Code
client.on('qr', (qr) => {
    console.log('SCAN THIS QR CODE DENGAN WHATSAPP ANDA:');
    qrcode.generate(qr, { small: true });
});

// When the client is ready
client.on('ready', () => {
    console.log('Client is ready! Mendengarkan semua pesan masuk dan keluar...');
});

// Listen to all messages, including the ones sent by the owner (message_create)
client.on('message_create', async (msg) => {
    // We only care about normal chats, not groups or statuses
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
            // Ignore contact fetching errors
        }

        const payload = {
            sender: sender.replace('@c.us', '').replace('@s.whatsapp.net', '').replace('@lid', ''),
            receiver: receiver.replace('@c.us', '').replace('@s.whatsapp.net', '').replace('@lid', ''),
            message: msg.body,
            isFromMe: msg.fromMe
        };

        // Forward to our Laravel webhook (live cPanel domain or local fallback)
        const webhookUrl = process.env.WEBHOOK_URL || 'https://crm.difitech.id/api/wa-webhook';
        const response = await axios.post(webhookUrl, payload);
        // Removed verbose console.log here to prevent terminal spam
    } catch (error) {
        console.error('Error sending webhook:', error.message);
    }
});

// Start the client
client.initialize();
