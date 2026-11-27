const { chromium } = require('playwright'); const fs = require('fs');
const B = 'https://staging.dragonslots-gr.gr';
const P = [['svc','/vat-deklaratsii-v-estonii/'],['blog','/blog-ru/kriptovalyutnaya-kompaniya-v-estonii-2026/'],['svcold','/buhgalterskoe-obsluzhivanie-v-estonii/']];
(async () => { const out = process.argv[2]; const b = await chromium.launch(); const rep = {};
 for (const [n, u] of P) for (const [v, q] of [['old', '?lai_old=1'], ['new', '']]) for (const w of [1440, 390]) {
  const ctx = await b.newContext({ viewport: { width: w, height: w > 500 ? 1000 : 844 }, deviceScaleFactor: 1 }); const p = await ctx.newPage();
  await p.goto(B + u + q, { waitUntil: 'networkidle', timeout: 90000 }).catch(() => {});
  await p.addStyleTag({ content: '[class*=cookie],[id*=cookie],.lai-chat,[class*=chat-widget]{display:none!important}' }).catch(() => {});
  await p.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 700) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 60)); } window.scrollTo(0, 0); });
  await p.waitForTimeout(700);
  const k = `${n}-${v}-${w}`;
  rep[k] = await p.evaluate(() => {
   const x = s => { const e = [...document.querySelectorAll(s)].find(e => e.offsetParent); if (!e) return null; const r = e.getBoundingClientRect(); const c = getComputedStyle(e); return { x: Math.round(r.left), w: Math.round(r.width), fs: c.fontSize, lh: c.lineHeight }; };
   return { h1: x('.head-title h1'), crumbs: x('.rank-math-breadcrumb'), img: x('.single-image img'), meta: x('.data'), toc: x('#ez-toc-container'), p: x('.article-content p, .block-cta p, .block-text p, .block-cta blockquote'), h2: x('.article-content h2, .block-text h2'), h3: x('.article-content h3, .block-text h3, .block-cta h3'), li: x('.article-content ul li, .block-text ul li'), table: x('.article-content table, .block-cta table, .block-text table'), faq: x('.service-faq'), card: x('.service-img-text'), ctext: x('.service-img-text p'), link: (() => { const a = [...document.querySelectorAll('.article-content a, .block-text a, .service-img-text a')].find(a => !a.classList.contains('btn') && !a.closest('#ez-toc-container') && a.offsetParent); if (!a) return null; const c = getComputedStyle(a); return { color: c.color, deco: c.textDecorationLine + ' ' + c.textDecorationColor + ' ' + c.textDecorationThickness }; })() };
  });
  await p.screenshot({ path: `${out}/${k}-top.png` });
  // фрагмент статьи: от первого H2 в тексте
  const y = await p.evaluate(() => { const e = [...document.querySelectorAll('.article-content h2, .block-text h2')].find(e => e.offsetParent); return e ? e.getBoundingClientRect().top + scrollY - 120 : 2000; });
  await p.screenshot({ path: `${out}/${k}-mid.png`, clip: { x: 0, y: Math.max(0, y), width: w, height: w > 500 ? 1000 : 844 }, fullPage: true });
  if (n === 'svc') { const y2 = await p.evaluate(() => { const e = document.querySelector('.service-img-text'); return e ? e.getBoundingClientRect().top + scrollY - 60 : 3000; }); await p.screenshot({ path: `${out}/${k}-card.png`, clip: { x: 0, y: y2, width: w, height: w > 500 ? 900 : 844 }, fullPage: true }); }
  await ctx.close(); }
 fs.writeFileSync(out + '/r.json', JSON.stringify(rep, null, 1)); await b.close(); })();
