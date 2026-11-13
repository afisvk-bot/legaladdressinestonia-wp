const { chromium } = require('playwright');
const fs = require('fs');
const BASE = 'https://staging.dragonslots-gr.gr';
const pages = [['home', '/'], ['ru', '/ru/'], ['service', '/contact-person-in-estonia/'], ['blog', '/blog/visas-and-residence-permits-for-founders-of-estonian-companies/']];
const HIDE = '#lae-cookie-consent,[class*="lae-cookie"],[class*="cookie-consent"],[class*="cky-"],[class*="chat"],[id*="chat"],[class*="pipedrive"],iframe{display:none!important}';
async function swap(p) {
  await p.addStyleTag({ url: 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap' });
  const main = await p.evaluate(() => getComputedStyle(document.body).fontFamily.split(',')[0].replace(/["']/g, '').trim());
  await p.evaluate(m => { for (const e of document.querySelectorAll('body, body *')) { const f = getComputedStyle(e).fontFamily; if (f.replace(/["']/g, '').toLowerCase().includes(m.toLowerCase())) e.style.setProperty('font-family', 'Inter, sans-serif', 'important'); } }, main);
  await p.evaluate(() => document.fonts.ready); await p.waitForTimeout(1500);
  return main;
}
(async () => {
  const out = process.argv[2];
  const b = await chromium.launch();
  const report = {};
  for (const [w, h, sfx] of [[1440, 1000, 'd'], [390, 844, 'm']]) {
    const ctx = await b.newContext({ viewport: { width: w, height: h }, deviceScaleFactor: 1, isMobile: w < 768, hasTouch: w < 768 });
    const p = await ctx.newPage();
    for (const [name, path] of pages) {
      if (sfx === 'm' && !['home', 'blog'].includes(name)) continue;
      await p.goto(BASE + path, { waitUntil: 'networkidle', timeout: 90000 }).catch(e => console.log('goto', e.message));
      await p.addStyleTag({ content: HIDE }); await p.waitForTimeout(1500);
      // прокрутка, чтобы подгрузились lazy-картинки
      await p.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 700) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 120)); } window.scrollTo(0, 0); });
      await p.waitForTimeout(1000);
      if (!report[name]) report[name] = await p.evaluate(() => ['body', 'h1', 'h2', 'h3', 'p', 'header a', '.btn, button, .button'].map(s => { const e = document.querySelector(s); if (!e) return s + ': -'; const c = getComputedStyle(e); return s + ': ' + c.fontFamily + ' ' + c.fontSize + ' ' + c.fontWeight; }));
      await p.screenshot({ path: `${out}/${name}-${sfx}-before.png`, fullPage: true });
      const main = await swap(p);
      report[name + '-main'] = main;
      await p.screenshot({ path: `${out}/${name}-${sfx}-after.png`, fullPage: true });
      console.log('ok', name, sfx);
    }
    await ctx.close();
  }
  fs.writeFileSync(out + '/report.json', JSON.stringify(report, null, 1));
  await b.close();
})();
