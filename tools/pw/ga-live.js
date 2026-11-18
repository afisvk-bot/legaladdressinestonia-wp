const { chromium } = require('playwright'); const fs = require('fs');
(async () => {
  const out = process.argv[2]; const b = await chromium.launch(); const rep = {};
  for (const site of ['https://legaladdressinestonia.com/', 'https://legaladdressinestonia.com/contact-person-in-estonia/', 'https://staging.dragonslots-gr.gr/']) {
    const ctx = await b.newContext({ viewport: { width: 1366, height: 900 } }); const p = await ctx.newPage(); const reqs = [];
    p.on('request', r => { const u = r.url(); if (/googletagmanager|google-analytics|analytics\.google|\/g\/collect|gtag\/js|clarity/.test(u)) reqs.push(u.slice(0, 220)); });
    const resp = await p.goto(site, { waitUntil: 'networkidle', timeout: 90000 }).catch(e => null);
    const html = resp ? await resp.text() : '';
    const r = { status: resp && resp.status(), ids: [...new Set((html.match(/(GTM-[A-Z0-9]{5,9}|G-[A-Z0-9]{8,12}|UA-\d{5,}-\d+)/g) || []))], hasConsentDefault: html.includes("gtag('consent', 'default'"), hasGtmSnippet: html.includes('googletagmanager.com/gtm.js'), litespeed: (resp && resp.headers()['x-litespeed-cache']) || '', reqsBeforeConsent: reqs.slice() };
    r.gtmLoaded = await p.evaluate(() => !!(window.google_tag_manager && Object.keys(window.google_tag_manager)));
    r.gtmKeys = await p.evaluate(() => window.google_tag_manager ? Object.keys(window.google_tag_manager).filter(k => /^(G-|GTM-|AW-)/.test(k)) : []);
    r.dl = await p.evaluate(() => (window.dataLayer || []).slice(0, 8).map(x => JSON.stringify(x).slice(0, 120)));
    reqs.length = 0;
    const acc = await p.$('#lae-cookie-accept'); r.banner = !!acc;
    if (acc) { await p.evaluate(() => { const s = document.getElementById('lae-cookie-consent'); if (s) s.hidden = false; }); await acc.click({ force: true }).catch(() => {}); await p.waitForTimeout(4000); }
    r.reqsAfterAccept = reqs.slice(0, 12);
    rep[site] = r; await ctx.close();
  }
  // Wayback: история кода на главной
  const ctx = await b.newContext(); const p = await ctx.newPage();
  try {
    const cdx = await (await p.request.get('https://web.archive.org/cdx/search/cdx?url=legaladdressinestonia.com/&from=2025&output=json&fl=timestamp,statuscode&filter=statuscode:200&collapse=timestamp:8', { timeout: 60000 })).json();
    rep.waybackCount = cdx.length - 1;
    const snaps = cdx.slice(1).map(x => x[0]);
    const pick = snaps.length > 14 ? snaps.filter((_, i) => i % Math.ceil(snaps.length / 14) === 0).concat(snaps.slice(-3)) : snaps;
    rep.wayback = {};
    for (const ts of [...new Set(pick)]) {
      try { const t = await (await p.request.get(`https://web.archive.org/web/${ts}id_/https://legaladdressinestonia.com/`, { timeout: 60000 })).text();
        rep.wayback[ts] = [...new Set((t.match(/(GTM-[A-Z0-9]{5,9}|G-[A-Z0-9]{8,12}|UA-\d{5,}-\d+)/g) || []))].join(',') || 'none';
      } catch (e) { rep.wayback[ts] = 'ERR ' + e.message.slice(0, 60); }
    }
  } catch (e) { rep.waybackErr = e.message.slice(0, 200); }
  fs.writeFileSync(out + '/rep.json', JSON.stringify(rep, null, 1)); await b.close();
})();
