const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    page.on('console', msg => console.log('BROWSER CONSOLE:', msg.type(), msg.text()));
    page.on('pageerror', err => console.log('BROWSER PAGE ERROR:', err.message));
    
    // go to login
    await page.goto('http://localhost:5173/login', { waitUntil: 'networkidle2' });
    
    // login as admin
    await page.type('input[type="email"]', 'john@example.com');
    await page.type('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
    
    // wait for redirect
    await page.waitForNavigation({ waitUntil: 'networkidle2' });
    
    // go to admin/pharmacy
    await page.goto('http://localhost:5173/admin/pharmacy', { waitUntil: 'networkidle2' });
    await browser.close();
})();
