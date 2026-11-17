const { chromium } = require('playwright');
const fs = require('fs');
const B = 'https://staging.dragonslots-gr.gr';
const pages = [['en', '/change-of-accountant-in-estonia/'], ['de', '/buchhalterwechsel-estland/'], ['zh', '/change-of-accountant-zh/'], ['uk', '/zmina-bukhhaltera-estonskoyi-kompaniyi/'], ['ru', '/smena-buhgaltera-estonskoj-kompanii/'], ['restore-en', '/accounting-restoration-in-estonia/'], ['restore-es', '/restablecimiento-de-la-contabilidad-en-estonia/']];
const HIDE = '#lae-cookie-consent,[class*="lae-cookie"],[class*="cookie-consent"],[class*="cky-"],[class*="chat"],[id*="chat"],[class*="pipedrive"],iframe{display:none!important}';
(async () => {
  const out = process.argv[2];
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 1440, height: 1000 } });
  const rep = {};
  for (const [n, path] of pages) {
    const r = await p.goto(B + path, { waitUntil: 'networkidle', timeout: 90000 }).catch(e => null);
    await p.addStyleTag({ content: HIDE });
    await p.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 700) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 100)); } window.scrollTo(0, 0); });
    await p.waitForTimeout(800);
    rep[n] = await p.evaluate(() => ({ title: document.title, h1: (document.querySelector('h1') || {}).innerText, canonical: (document.querySelector('link[rel=canonical]') || {}).href, hreflang: [...document.querySelectorAll('link[hreflang]')].map(l => l.hreflang).join(','), btns: [...document.querySelectorAll('.tariff a, [class*=tariff] a, [class*=package] a')].map(a => a.innerText.trim()).filter(Boolean).join(' | '), related: [...document.querySelectorAll('a')].filter(a => /accounting|buchhalt|contab|compta|muhaseb|облік|бухгалт|会计|restor|interim|annual|jahres/i.test(a.href)).length, lang: document.documentElement.lang }));
    rep[n].status = r ? r.status() : 'ERR';
    await p.screenshot({ path: `${out}/${n}.png`, fullPage: true });
  }
  // menu
  await p.goto(B + '/', { waitUntil: 'networkidle' }); await p.addStyleTag({ content: HIDE });
  const svc = p.locator('header nav >> text=Services').first(); await svc.hover().catch(() => {}); await p.waitForTimeout(600);
  const acc = p.locator('header >> text=Accounting & Tax').first(); await acc.hover().catch(() => {}); await p.waitForTimeout(800);
  await p.screenshot({ path: `${out}/menu-en.png` });
  await p.goto(B + '/ru/', { waitUntil: 'networkidle' }); await p.addStyleTag({ content: HIDE });
  await p.locator('header nav >> text=Услуги').first().hover().catch(() => {}); await p.waitForTimeout(600);
  await p.locator('header >> text=Бухучет и налоги').first().hover().catch(() => {}); await p.waitForTimeout(800);
  await p.screenshot({ path: `${out}/menu-ru.png` });
  for (const old of ['/clean-up/', '/restablecimiento-de-la-contabilidad-en-estonia-2/', '/industry/startups/']) { const r = await p.goto(B + old, { waitUntil: 'load' }); rep['redir ' + old] = p.url() + ' ' + (r && r.status()); }
  fs.writeFileSync(out + '/report.json', JSON.stringify(rep, null, 1));
  await b.close();
})();
