const { chromium } = require('playwright'); const fs = require('fs');
const B = 'https://staging.dragonslots-gr.gr';
(async () => {
  const out = process.argv[2]; const b = await chromium.launch(); const rep = {};
  for (const u of ['/', '/uk/', '/richnyy-zvit-v-estoniyi/', '/ru/', '/contacts/', '/kontakti/']) {
    const ctx = await b.newContext({ viewport: { width: 1440, height: 900 } }); const p = await ctx.newPage();
    await p.goto(B + u, { waitUntil: 'networkidle' }); await p.waitForTimeout(1000);
    const res = await p.evaluate(async () => {
      const btns = [...document.querySelectorAll('[data-mm-modal],[data-toggle=modal]')].filter(a => a.offsetParent !== null && !a.closest('header'));
      const a = btns[0]; if (!a) return { err: 'no visible button' };
      a.scrollIntoView({ block: 'center' }); await new Promise(r => setTimeout(r, 400)); a.click(); await new Promise(r => setTimeout(r, 1500));
      const vis = [...document.querySelectorAll('form.wpcf7-form')].filter(f => { const r = f.getBoundingClientRect(); return r.width > 0 && r.height > 0 && getComputedStyle(f).visibility !== 'hidden'; }).map(f => (f.querySelector('input[name=_wpcf7]') || {}).value);
      const modals = [...document.querySelectorAll('.modal, [class*="mm-modal"], [class*=modal]')].filter(m => { const s = getComputedStyle(m); const r = m.getBoundingClientRect(); return s.display !== 'none' && s.visibility !== 'hidden' && r.height > 200; }).map(m => m.className.slice(0, 60));
      return { clicked: a.innerText.trim().slice(0, 30) + ' ' + (a.getAttribute('data-mm-modal') || a.getAttribute('data-target')), visibleForms: vis, openModals: modals };
    });
    rep[u] = res; await p.screenshot({ path: out + '/' + (u.replace(/\W+/g, '_') || 'home') + '.png' });
    // pipedrive webform visible height after scroll
    rep[u].pd = await p.evaluate(async () => { const d = document.querySelector('.pipedriveWebForms'); if (!d) return null; d.scrollIntoView(); await new Promise(r => setTimeout(r, 2500)); const i = d.querySelector('iframe'); const s = getComputedStyle(d); return { h: Math.round(d.getBoundingClientRect().height), ih: i ? Math.round(i.getBoundingClientRect().height) : null, display: s.display, parentHidden: !!d.closest('[style*="display:none"],[style*="display: none"],[hidden]') }; });
    await ctx.close();
  }
  fs.writeFileSync(out + '/rep.json', JSON.stringify(rep, null, 1)); await b.close();
})();
