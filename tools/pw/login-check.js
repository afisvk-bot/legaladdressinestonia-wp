const { chromium } = require('playwright'); const fs = require('fs');
(async () => { const out = process.argv[2]; const b = await chromium.launch(); const r = {};
 for (const u of ['/setting/', '/setting/?interim-login=1', '/wp-login.php?interim-login=1']) { const p = await b.newPage(); const x = await p.goto('https://staging.dragonslots-gr.gr' + u, { waitUntil: 'load' }).catch(e => null);
  r[u] = { status: x && x.status(), url: p.url(), recaptcha: await p.evaluate(() => !!document.querySelector('.g-recaptcha, iframe[src*=recaptcha], script[src*=recaptcha]')), form: await p.evaluate(() => !!document.querySelector('#loginform')) }; await p.close(); }
 fs.writeFileSync(out + '/r.json', JSON.stringify(r, null, 1)); await b.close(); })();
