const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    page.on('console', msg => console.log('BROWSER CONSOLE:', msg.type(), msg.text()));
    page.on('pageerror', err => console.log('BROWSER PAGE ERROR:', err.message));
    
    // go to base url
    await page.goto('http://localhost:5173', { waitUntil: 'networkidle2' });
    
    // inject super admin local storage auth details if possible, or just login
    await page.evaluate(() => {
        // usually token is stored in localStorage
        localStorage.setItem('auth_token', 'fake-token-do-not-validate-here');
        localStorage.setItem('user', JSON.stringify({role: 'super_admin'}));
    });
    
    // go to admin/pharmacy
    await page.goto('http://localhost:5173/admin/pharmacy', { waitUntil: 'networkidle2' });
    
    // give it a second to render and crash
    await new Promise(r => setTimeout(r, 2000));
    await browser.close();
})();
