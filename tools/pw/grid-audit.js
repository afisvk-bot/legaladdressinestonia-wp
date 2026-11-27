const { chromium } = require('playwright'); const fs = require('fs');
const B = 'https://staging.dragonslots-gr.gr';
const P = [['home-ru','/'],['home-en','/en/'],['svc-ru','/vat-deklaratsii-v-estonii/'],['svc-en','/vat-returns-in-estonia/'],['svc-old-ru','/buhgalterskoe-obsluzhivanie-v-estonii/'],['blog-ru','/blog-ru/kriptovalyutnaya-kompaniya-v-estonii-2026/'],['bloglist-ru','/blog-ru/']];
(async () => { const out = process.argv[2]; const b = await chromium.launch(); const rep = {};
 for (const [n, u] of P) { for (const w of [1440, 390]) {
  const ctx = await b.newContext({ viewport: { width: w, height: 900 } }); const p = await ctx.newPage();
  const r = await p.goto(B + u, { waitUntil: 'networkidle', timeout: 60000 }).catch(() => null);
  await p.addStyleTag({ content: '#cookie-notice,.cky-consent-container,[class*=cookie]{display:none!important}' }).catch(()=>{});
  await p.waitForTimeout(600);
  rep[n + '-' + w] = Object.assign({ url: u, status: r && r.status() }, await p.evaluate(() => {
   const cs = (e, props) => { const s = getComputedStyle(e); const o = {}; for (const k of props) o[k] = s[k]; return o; };
   const T = ['fontFamily','fontSize','lineHeight','fontWeight','color','letterSpacing','marginTop','marginBottom','textTransform'];
   const pick = (sel, extra = []) => { const e = [...document.querySelectorAll(sel)].find(x => !x.closest('header,footer,nav,.modal') && x.offsetParent); return e ? Object.assign({ cls: e.className.toString().slice(0, 60), text: e.textContent.trim().slice(0, 40) }, cs(e, T.concat(extra))) : null; };
   const secs = [...document.querySelectorAll('body section, main > div, .container, .wrapper, [class*=container]')].filter(e => e.offsetParent && !e.closest('header,footer')).map(e => { const r = e.getBoundingClientRect(); const s = getComputedStyle(e); return { tag: e.tagName, cls: e.className.toString().slice(0, 50), x: Math.round(r.left), w: Math.round(r.width), pl: s.paddingLeft, mw: s.maxWidth }; });
   const widths = {}; secs.forEach(s => { const k = s.x + '/' + s.w; widths[k] = (widths[k] || 0) + 1; });
   const a = [...document.querySelectorAll('a')].find(x => !x.closest('header,footer,nav,.btn,button') && x.closest('p,li,td') && x.offsetParent);
   return { title: document.title, bodyFont: cs(document.body, ['fontFamily','fontSize','lineHeight','color']), h1: pick('h1'), h2: pick('h2'), h3: pick('h3'), p: pick('p'), li: pick('li', ['listStyleType','paddingLeft']), td: pick('td'), a: a ? Object.assign({ text: a.textContent.trim().slice(0, 30), cls: a.className }, cs(a, ['color','textDecorationLine','textDecorationColor','textUnderlineOffset','fontWeight','borderBottom'])) : null, btn: pick('.btn, a[class*=btn], button[class*=btn]', ['backgroundColor','borderRadius','padding']), widths: Object.entries(widths).sort((x, y) => y[1] - x[1]).slice(0, 8), secs: secs.slice(0, 40), bodyClass: document.body.className.slice(0, 200) };
  }));
  await p.screenshot({ path: `${out}/${n}-${w}.png`, fullPage: true }); await ctx.close(); } }
 fs.writeFileSync(out + '/r.json', JSON.stringify(rep, null, 1)); await b.close(); })();
