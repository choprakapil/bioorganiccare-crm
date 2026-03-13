const puppeteer = require('puppeteer');
const { execSync } = require('child_process');

(async () => {
    // get token from backend script
    console.log("Getting token...");
    const rawOut = execSync("cd ../backend && php getToken.php", { encoding: 'utf-8' });
    const token = rawOut.trim().split(',')[2];

    if (!token) {
        console.error("Token missing!");
        process.exit(1);
    }

    const browser = await puppeteer.launch({ headless: 'new', args: ['--no-sandbox', '--disable-setuid-sandbox', '--ignore-certificate-errors', '--disable-web-security'] });
    const context = await browser.createBrowserContext();
    const page = await context.newPage();

    let eventFired = false;
    let authSucceeded = false;

    page.on('console', msg => {
        const text = msg.text();
        console.log(`[Browser Console]: ${text}`);
        if (text.includes('Plan Updated Event Received')) {
            eventFired = true;
        }
    });

    page.on('response', async res => {
        const url = res.url();
        if (url.includes('/broadcasting/auth')) {
            console.log(`[Fetch XHR] POST /broadcasting/auth Status: ${res.status()}`);
            if (res.status() === 200) authSucceeded = true;
        }
    });

    await page.goto('http://localhost:5173/app/', { waitUntil: 'domcontentloaded' });
    await page.evaluate((token) => { localStorage.setItem('auth_token', token); }, token);
    await page.goto('http://localhost:5173/app/', { waitUntil: 'load' });

    // give time for websocket connect
    await new Promise(r => setTimeout(r, 4000));

    console.log("[Test] Firing Admin Plan Update Event externally via PHP...");
    try {
        execSync("php ../backend/run_diagnostics3.php", { encoding: 'utf-8' });
        console.log("[PHP] Trigerred");
    } catch (e) { }

    await new Promise(r => setTimeout(r, 5000));
    await browser.close();

    console.log(`--- RESULTS ---`);
    console.log(`Auth Succeeded: ${authSucceeded}`);
    console.log(`Websocket Event Received: ${eventFired}`);
})();
