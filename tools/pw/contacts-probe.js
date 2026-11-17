const { chromium, devices } = require('playwright'); const fs = require('fs');
const HIDE = '#lae-cookie-consent,[class*="lae-cookie"],[class*="cookie-consent"],[class*="cky-"],[id*="cookie"],[class*="cookie"],[class*="chat"],[id*="chat"],[class*="pipedrive"],iframe{display:none!important}';
(async () => {
  const out = process.argv[2]; const b = await chromium.launch(); const rep = {};
  for (const [name, opts] of [['d', { viewport: { width: 1440, height: 900 } }], ['m', { ...devices['iPhone 13'] }]]) {
    const ctx = await b.newContext(opts); const p = await ctx.newPage();
    await p.goto('https://eestifirma.ee/en/', { waitUntil: 'networkidle', timeout: 90000 }).catch(e => rep.err = e.message);
    await p.waitForTimeout(2000); await p.addStyleTag({ content: HIDE }).catch(() => {});
    await p.screenshot({ path: `${out}/ef-${name}-top.png` });
    await p.evaluate(() => window.scrollTo(0, document.body.scrollHeight)); await p.waitForTimeout(1500);
    const f = p.locator('footer').first();
    if (await f.count()) await f.screenshot({ path: `${out}/ef-${name}-footer.png` }).catch(() => {});
    if (name === 'd') rep.ef = await p.evaluate(() => ({ header: (document.querySelector('header') || {}).innerText, footer: (document.querySelector('footer') || {}).innerText, links: [...document.querySelectorAll('a[href^="tel:"],a[href^="mailto:"],a[href*="wa.me"],a[href*="whatsapp"],a[href*="t.me"]')].map(a => a.href + ' | ' + a.innerText.trim()).slice(0, 20) }));
    // mobile menu open
    if (name === 'm') { const burger = p.locator('header button, header [class*=burger], header [class*=toggle]').first(); if (await burger.count()) { await p.evaluate(() => window.scrollTo(0, 0)); await burger.click({ force: true }).catch(() => {}); await p.waitForTimeout(1200); await p.screenshot({ path: `${out}/ef-m-menu.png` }); } }
    // our site
    await p.goto('https://staging.dragonslots-gr.gr/', { waitUntil: 'networkidle' }); await p.addStyleTag({ content: HIDE });
    await p.screenshot({ path: `${out}/our-${name}-top.png` });
    await p.evaluate(() => window.scrollTo(0, document.body.scrollHeight)); await p.waitForTimeout(1000);
    const of = p.locator('footer').first(); if (await of.count()) await of.screenshot({ path: `${out}/our-${name}-footer.png` }).catch(() => {});
    if (name === 'd') rep.our = await p.evaluate(() => ({ header: (document.querySelector('header') || {}).outerHTML.slice(0, 4000), footer: (document.querySelector('footer') || {}).outerHTML.slice(0, 9000), links: [...document.querySelectorAll('a[href^="tel:"],a[href^="mailto:"],a[href*="wa.me"],a[href*="whatsapp"],a[href*="t.me"],a[href*="calendly"],a[href*="cal.com"]')].map(a => a.href + ' | ' + a.innerText.trim() + ' | ' + a.closest('header,footer,section,div').className).slice(0, 30), fonts: getComputedStyle(document.body).fontFamily }));
    await ctx.close();
  }
  // our contact page
  const ctx = await b.newContext(); const p = await ctx.newPage();
  for (const u of ['https://staging.dragonslots-gr.gr/contacts/', 'https://staging.dragonslots-gr.gr/contact/', 'https://staging.dragonslots-gr.gr/contact-us/']) { const r = await p.goto(u).catch(() => null); if (r && r.status() === 200) { rep.contact = { u, links: await p.evaluate(() => [...document.querySelectorAll('a[href^="tel:"],a[href^="mailto:"],a[href*="wa.me"],a[href*="t.me"]')].map(a => a.href + ' | ' + a.innerText.trim())), text: (await p.evaluate(() => document.body.innerText)).slice(0, 1500) }; break; } }
  fs.writeFileSync(out + '/rep.json', JSON.stringify(rep, null, 1)); await b.close();
})();
