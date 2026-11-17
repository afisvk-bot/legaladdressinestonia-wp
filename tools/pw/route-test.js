const { chromium } = require('playwright'); const fs = require('fs');
const B = 'https://staging.dragonslots-gr.gr';
const urls = ['/ru/godovoj-otchet-v-estonii/', '/de/jahresbericht-f-r-estnische-unternehmen/', '/de/buchhalterwechsel-estland/', '/ru/smena-buhgaltera-estonskoj-kompanii/', '/annual-report-for-estonian-companies/', '/buchhalterwechsel-estland/', '/smena-buhgaltera-estonskoj-kompanii/', '/godovoj-otchet-v-estonii/'];
(async () => {
  const out = process.argv[2]; const b = await chromium.launch(); const rep = {};
  for (const u of urls) { const ctx = await b.newContext(); const p = await ctx.newPage(); const r = await p.goto(B + u, { waitUntil: 'load' }); rep['fresh ' + u] = (r && r.status()) + ' ' + p.url() + ' | ' + (await p.title()); await ctx.close(); }
  fs.writeFileSync(out + '/r.json', JSON.stringify(rep, null, 1)); await b.close();
})();
