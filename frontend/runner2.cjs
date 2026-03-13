const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: 'new', args: ['--no-sandbox', '--disable-setuid-sandbox', '--ignore-certificate-errors', '--disable-web-security'] });
    const context = await browser.createBrowserContext();
    const page = await context.newPage();

    page.on('response', async res => {
        if (res.status() === 403) {
            console.log(`[HTTP 403] ${res.url()}`);
        }
    });

    await page.goto('http://localhost:5173/app/', { waitUntil: 'domcontentloaded' });
    await page.evaluate((token) => { localStorage.setItem('auth_token', token); }, process.argv[2]);
    await page.goto('http://localhost:5173/app/', { waitUntil: 'load' });
    
    await new Promise(r => setTimeout(r, 4000));
    await browser.close();
})();
