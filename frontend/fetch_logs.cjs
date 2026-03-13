const puppeteer = require('puppeteer');

(async () => {
    console.log("Starting Puppeteer...");
    const browser = await puppeteer.launch({ headless: "new", args: ['--no-sandbox'] });
    const page = await browser.newPage();

    let logs = [];
    page.on('console', msg => {
        const text = msg.text();
        console.log(`[CONSOLE] ${msg.type()}:`, text);
        logs.push(`[CONSOLE] ${msg.type()}: ${text}`);
    });

    page.on('pageerror', err => {
        console.log(`[PAGE ERROR]`, err.toString());
        logs.push(`[PAGE ERROR] ${err.toString()}`);
    });

    page.on('response', async res => {
        if (res.url().includes('/api/')) {
            try {
                const json = await res.json();
                console.log(`[NETWORK] ${res.status()} ${res.url()} =>`, JSON.stringify(json).substring(0, 500));
            } catch (e) {
                console.log(`[NETWORK] ${res.status()} ${res.url()} => Cannot parse body`);
            }
        }
    });

    console.log("Setting localStorage auth_token...");
    await page.goto('http://localhost:5173', { waitUntil: 'networkidle2' });
    await page.evaluate(() => {
        localStorage.setItem('auth_token', '157|7AXiuDLPvkbTEPNBhseBSdRkCyHBobNxFPZZmP7Gdf3b1421');
    });

    console.log("Navigating to /admin/governance...");
    await page.goto('http://localhost:5173/admin/governance', { waitUntil: 'networkidle2' });

    console.log("Waiting for components to render...");
    await new Promise(r => setTimeout(r, 3000));

    console.log("\n--- SCRIPT FINISHED ---");
    await browser.close();
})();
