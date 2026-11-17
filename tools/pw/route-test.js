const { chromium } = require('playwright'); const fs = require('fs');
const B = 'https://staging.dragonslots-gr.gr';
const urls = ['/buchhalterwechsel-estland/', '/smena-buhgaltera-estonskoj-kompanii/', '/godovoj-otchet-v-estonii/', '/jahresbericht-f-r-estnische-unternehmen/', '/restablecimiento-de-la-contabilidad-en-estonia/', '/clean-up/', '/restablecimiento-de-la-contabilidad-en-estonia-2/'];
(async () => {
  const out = process.argv[2]; const b = await chromium.launch(); const rep = {};
  for (const u of urls) { const ctx = await b.newContext(); const p = await ctx.newPage(); const r = await p.goto(B + u, { waitUntil: 'load' }); rep['fresh ' + u] = (r && r.status()) + ' ' + p.url() + ' | ' + (await p.title()); await ctx.close(); }
  const ctx = await b.newContext(); const p = await ctx.newPage(); await p.goto(B + '/change-of-accountant-in-estonia/');
  rep.cookies_after_en = (await ctx.cookies()).map(c => c.name + '=' + c.value).join('; ');
  for (const u of urls) { const r = await p.goto(B + u, { waitUntil: 'load' }); rep['after-en ' + u] = (r && r.status()) + ' ' + p.url(); }
  fs.writeFileSync(out + '/r.json', JSON.stringify(rep, null, 1)); await b.close();
})();
