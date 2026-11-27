const { chromium } = require('playwright'); const fs = require('fs');
const B = 'https://staging.dragonslots-gr.gr';
const pages = ['/vat-deklaratsii-v-estonii/','/prodazha-i-peredacha-dolej-estonskoj-ou/','/raschyot-zarplaty-v-estonii/','/smena-chlena-pravleniya-estonskoj-kompanii/','/uvelichenie-kapitala-estonskoj-ou/','/umenshenie-kapitala-estonskoj-kompanii/'];
(async () => { const out = process.argv[2]; const b = await chromium.launch(); const rep = {};
 for (const u of pages) { const p = await b.newPage(); const r = await p.goto(B + u, { waitUntil: 'load', timeout: 60000 }).catch(() => null);
  rep[u] = Object.assign({ status: r && r.status() }, await p.evaluate(async () => {
   const main = document.querySelector('main') || document.body;
   const links = [...new Set([...main.querySelectorAll('a[href]')].filter(a => !a.closest('header,footer,nav')).map(a => a.href.split('#')[0]).filter(h => h.startsWith(location.origin)))];
   const bad = []; for (const h of links) { try { const x = await fetch(h, { method: 'HEAD', redirect: 'manual' }); if (x.status >= 300) bad.push(x.status + ' ' + h.replace(location.origin, '')); } catch (e) {} }
   const h = [...document.querySelectorAll('h1,h2')].map(e => e.tagName + ':' + e.textContent.trim().slice(0, 60));
   const imgs = [...main.querySelectorAll('img')].slice(0, 3).map(i => i.currentSrc.split('/').pop() + ' ' + i.naturalWidth + 'x' + i.naturalHeight + ' alt=' + i.alt);
   return { title: document.title, desc: (document.querySelector('meta[name=description]') || {}).content, canonical: (document.querySelector('link[rel=canonical]') || {}).href, robots: (document.querySelector('meta[name=robots]') || {}).content, h1count: document.querySelectorAll('h1').length, h: h.slice(0, 14), bad, imgs, schema: [...document.querySelectorAll('script[type="application/ld+json"]')].map(s => { try { const j = JSON.parse(s.textContent); return (j['@graph'] || [j]).map(x => x['@type']).flat().join(','); } catch (e) { return 'err'; } }) };
  })); await p.close(); }
 for (const [n, u, w] of [['desk', '/uvelichenie-kapitala-estonskoj-ou/', 1440], ['mob', '/raschyot-zarplaty-v-estonii/', 390]]) { const ctx = await b.newContext({ viewport: { width: w, height: w < 500 ? 844 : 900 }, deviceScaleFactor: w < 500 ? 2 : 1 }); const p = await ctx.newPage(); await p.goto(B + u, { waitUntil: 'networkidle', timeout: 60000 }).catch(() => {}); await p.waitForTimeout(800); await p.screenshot({ path: out + '/' + n + '.png', fullPage: true }); await ctx.close(); }
 fs.writeFileSync(out + '/r.json', JSON.stringify(rep, null, 1)); await b.close(); })();
