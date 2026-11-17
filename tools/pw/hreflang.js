const { chromium } = require('playwright'); const fs = require('fs');
(async () => {
  const out = process.argv[2]; const b = await chromium.launch(); const ctx = await b.newContext(); const p = await ctx.newPage(); const rep = {};
  for (const u of ['https://staging.dragonslots-gr.gr/change-of-accountant-in-estonia/', 'https://staging.dragonslots-gr.gr/accounting-restoration-in-estonia/']) {
    await p.goto(u, { waitUntil: 'load' });
    rep[u] = await p.evaluate(() => [...document.querySelectorAll('link[hreflang]')].map(l => l.hreflang + ' ' + l.href));
  }
  fs.writeFileSync(out + '/r.json', JSON.stringify(rep, null, 1)); await b.close();
})();
