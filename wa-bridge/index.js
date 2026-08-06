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
        const payload = {
            sender: msg.from.replace('@c.us', ''),
            receiver: msg.to.replace('@c.us', ''),
            message: msg.body,
            isFromMe: msg.fromMe
        };

        // Forward to our Laravel webhook locally!
        const response = await axios.post('http://127.0.0.1:8000/api/wa-webhook', payload);
        console.log(`Webhook sent! Status: ${response.status}. Sender: ${payload.sender}, Receiver: ${payload.receiver}, Message: ${payload.message}`);
    } catch (error) {
        console.error('Error sending webhook:', error.message);
    }
});

// Start the client
client.initialize();
