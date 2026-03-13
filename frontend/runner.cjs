const puppeteer = require('puppeteer');
const { execSync } = require('child_process');

(async () => {
    const token = process.argv[2];
    if (!token) { console.error("Missing token"); process.exit(1); }

    const browser = await puppeteer.launch({
        headless: 'new', // Use new Headless mode
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--ignore-certificate-errors', '--disable-web-security']
    });

    // Create new context
    const context = await browser.createBrowserContext();
    const page = await context.newPage();

    page.on('console', msg => {
        console.log(`[Browser Console]: ${msg.text()}`);
    });

    const client = await page.target().createCDPSession();
    await client.send('Network.enable');

    // Track WebSocket Events
    client.on('Network.webSocketCreated', params => {
        console.log(`[Network.WS] URL: ${params.url}`);
    });

    client.on('Network.webSocketHandshakeResponseReceived', params => {
        console.log(`[Network.WS] Handshake Status: ${params.response.status} ${params.response.statusText}`);
    });

    // Intercept requests for fetching /broadcasting/auth
    page.setRequestInterception(true);
    page.on('request', req => {
        if (req.url().includes('/broadcasting/auth')) {
            console.log(`[Fetch XHR Req] URL: ${req.url()}, Headers:`, req.headers());
        }
        req.continue();
    });

    page.on('response', async res => {
        const url = res.url();
        if (url.includes('/broadcasting/auth')) {
            console.log(`[Fetch XHR] POST /broadcasting/auth Status: ${res.status()}`);
            try {
                const text = await res.text();
                console.log(`[Fetch XHR] Response: ${text}`);
            } catch (e) { }
        }
    });

    // 1. Visit App index exactly once to set Token
    await page.goto('http://localhost:5173/app/', { waitUntil: 'domcontentloaded' });

    await page.evaluate((token) => {
        localStorage.setItem('auth_token', token);
    }, token);

    console.log("[Test] Token injected. Reloading application to trigger AuthContext...");

    // 2. Reload page to trigger Echo initialization in fully authenticated state
    await page.goto('http://localhost:5173/app/', { waitUntil: 'load' });

    // Wait for websocket handshake and Echo Subscriptions
    await new Promise(r => setTimeout(r, 4000));

    // 3. We use PHP from the host to dispatch the Plan Update directly to test the system!
    console.log("[Test] Firing Admin Plan Update Event externally via PHP...");
    try {
        const out = execSync("cd ../backend && php artisan tinker --execute='event(new \\\\App\\\\Events\\\\DoctorPlanUpdated(19));'", { encoding: 'utf-8' });
        console.log(`[PHP Log]: ${out.trim()}`);
    } catch (e) {
        console.error(`[PHP Error]: ${e.message}`);
    }

    // 4. Wait for the Frontend to react to the WS message
    await new Promise(r => setTimeout(r, 5000));

    await browser.close();
    console.log("[Test] Sequence Complete.");
})();
