const { chromium } = require('playwright');
const fs = require('fs');
(async () => {
  const out = process.argv[2];
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 1440, height: 1000 } });
  const fontReqs = [];
  p.on('response', r => { const u = r.url(); if (/\.(woff2?|ttf|otf)(\?|$)/i.test(u) || /fonts\.(googleapis|gstatic)|use\.typekit|fonts\.bunny/.test(u)) fontReqs.push(r.status() + ' ' + u); });
  await p.goto('https://staging.skin/', { waitUntil: 'networkidle', timeout: 90000 }).catch(e => console.log('goto', e.message));
  await p.waitForTimeout(3000);
  await p.screenshot({ path: out + '/skin-home.png' });
  await p.screenshot({ path: out + '/skin-full.png', fullPage: true });
  const info = await p.evaluate(() => {
    const pick = s => { const e = document.querySelector(s); if (!e) return null; const c = getComputedStyle(e); return { sel: s, text: (e.textContent || '').trim().slice(0, 60), ff: c.fontFamily, fs: c.fontSize, fw: c.fontWeight, lh: c.lineHeight, ls: c.letterSpacing, tt: c.textTransform }; };
    const els = ['body','h1','h2','h3','h4','p','nav a','header a','button','.btn','a','li','input','footer'].map(pick).filter(Boolean);
    const faces = [];
    for (const sh of document.styleSheets) { let rules; try { rules = sh.cssRules; } catch (e) { faces.push('CROSS-ORIGIN ' + sh.href); continue; }
      for (const r of rules) if (r.type === 5) faces.push(r.cssText.slice(0, 400)); }
    const loaded = []; document.fonts.forEach(f => loaded.push(f.family + ' ' + f.weight + ' ' + f.style + ' ' + f.status));
    const links = [...document.querySelectorAll('link[rel=stylesheet],link[rel=preload]')].map(l => l.href);
    return { title: document.title, els, faces, loaded, links };
  });
  fs.writeFileSync(out + '/info.json', JSON.stringify({ info, fontReqs }, null, 1));
  console.log(JSON.stringify(info.els, null, 1));
  await b.close();
})();
