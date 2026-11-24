const { chromium } = require('playwright'); const fs = require('fs');
(async () => {
  const out = process.argv[2]; const b = await chromium.launch(); const ctx = await b.newContext(); const p = await ctx.newPage();
  const loader = await (await p.request.get('https://webforms.pipedrive.com/f/loader')).text();
  fs.writeFileSync(out + '/loader.js', loader);
  // сообщения от iframe Pipedrive и Calendly на странице контактов
  await p.addInitScript(() => { window.__msgs = []; window.addEventListener('message', e => { try { window.__msgs.push({ o: e.origin, d: typeof e.data === 'string' ? e.data.slice(0, 300) : JSON.stringify(e.data).slice(0, 300) }); } catch (x) {} }); });
  await p.goto('https://staging.dragonslots-gr.gr/contacts/', { waitUntil: 'networkidle' }); await p.waitForTimeout(4000);
  const msgs = await p.evaluate(() => window.__msgs);
  // исходник формы внутри iframe (для поиска postMessage при отправке)
  const fr = p.frames().find(f => /webforms\.pipedrive\.com/.test(f.url()));
  let frameScripts = [];
  if (fr) { frameScripts = await fr.evaluate(() => [...document.scripts].map(s => s.src).filter(Boolean)); for (const [i, src] of frameScripts.entries()) { try { fs.writeFileSync(`${out}/frame-${i}.js`, await (await p.request.get(src)).text()); } catch (e) {} } }
  fs.writeFileSync(out + '/rep.json', JSON.stringify({ msgs, frameUrl: fr && fr.url(), frameScripts, calendly: p.frames().filter(f => /calendly/.test(f.url())).map(f => f.url().slice(0, 120)) }, null, 1));
  await b.close();
})();
