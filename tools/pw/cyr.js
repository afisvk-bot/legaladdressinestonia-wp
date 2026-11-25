const { chromium } = require('playwright'); const fs = require('fs');
const B = 'https://staging.dragonslots-gr.gr';
(async () => { const out = process.argv[2]; const b = await chromium.launch(); const rep = {};
 for (const u of ['/liquidation-einer-firma-in-estland/', '/target/estnische-firma-fuer-ukrainische-unternehmer/', '/company-liquidation-estonia/']) { const p = await b.newPage(); await p.goto(B + u, { waitUntil: 'load', timeout: 60000 }).catch(() => null);
  rep[u] = await p.evaluate(() => { const w = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT); const r = []; let n; while ((n = w.nextNode())) { const t = n.textContent.trim(); if (/[А-Яа-яЁё]/.test(t) && !n.parentElement.closest('script,style')) r.push((n.parentElement.closest('header')?'H ':n.parentElement.closest('footer')?'F ':'M ') + t.slice(0, 90)); } return [...new Set(r)]; });
  rep[u + ' alts'] = await p.evaluate(() => [...document.querySelectorAll('main img, .content img, img')].map(i => i.alt).filter(a => /[А-Яа-я]/.test(a)).slice(0, 5));
  await p.close(); }
 fs.writeFileSync(out + '/r.json', JSON.stringify(rep, null, 1)); await b.close(); })();
