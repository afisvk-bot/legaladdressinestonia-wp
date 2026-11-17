const { chromium } = require('playwright'); const fs = require('fs');
const B = 'https://staging.dragonslots-gr.gr';
const pages = ['/', '/uk/', '/ru/', '/de/', '/contacts/', '/uk/kontakty/', '/ru/kontakty/', '/contact-person-in-estonia/', '/annual-report-for-estonian-companies/', '/uk/richnyj-zvit-dlya-estonskyh-kompanij/', '/blog/visas-and-residence-permits-for-founders-of-estonian-companies/', '/our-team/'];
(async () => {
  const out = process.argv[2]; const b = await chromium.launch(); const rep = {};
  const ctx = await b.newContext({ viewport: { width: 1440, height: 900 } }); const p = await ctx.newPage();
  for (const u of pages) {
    const r = await p.goto(B + u, { waitUntil: 'networkidle', timeout: 60000 }).catch(e => null);
    await p.waitForTimeout(1500);
    rep[u] = { status: r && r.status(), ...(await p.evaluate(() => {
      const forms = [...document.querySelectorAll('form.wpcf7-form')].map(f => { const id = (f.querySelector('input[name=_wpcf7]') || {}).value; const box = f.closest('.modal, .lcf-ov, [class*=modal], section, main') ; return id + (f.closest('#book-a-call') ? '(book-a-call)' : f.closest('.modal,[class*=modal]') ? '(modal ' + (f.closest('.modal,[class*=modal]').className.split(' ').slice(0, 3).join('.')) + ')' : '(inline)'); });
      return {
        cf7: forms,
        mmBtns: [...document.querySelectorAll('[data-mm-modal],[data-toggle=modal],[data-bs-toggle=modal]')].map(a => (a.getAttribute('data-mm-modal') || a.getAttribute('data-target') || a.getAttribute('data-bs-target')) + ':' + a.innerText.trim().slice(0, 25)).slice(0, 6),
        pdWebForm: [...document.querySelectorAll('.pipedriveWebForms, iframe[src*="pipedrive"]')].map(e => e.tagName + ' ' + (e.getAttribute('data-pd-webforms') || e.src || '').slice(0, 60) + ' h=' + Math.round(e.getBoundingClientRect().height)),
        otherIframes: [...document.querySelectorAll('iframe')].map(i => (i.id || '') + ' ' + (i.src || '').slice(0, 60)).filter(s => !/pipedrive|recaptcha|google/i.test(s)).slice(0, 5),
        leadbooster: !!document.querySelector('#LeadboosterContainer'),
      };
    })) };
  }
  // открыть «Get a consultation» на UK-странице услуги и проверить форму
  await p.goto(B + '/uk/richnyj-zvit-dlya-estonskyh-kompanij/', { waitUntil: 'networkidle' }).catch(() => {});
  const btn = p.locator('main [data-mm-modal], .content [data-mm-modal], [data-toggle=modal]').first();
  rep.ukModal = { btns: await btn.count() };
  if (await btn.count()) { await btn.scrollIntoViewIfNeeded().catch(() => {}); await btn.click({ force: true }).catch(e => rep.ukModal.err = e.message); await p.waitForTimeout(1200);
    rep.ukModal.visibleForms = await p.evaluate(() => [...document.querySelectorAll('form.wpcf7-form')].filter(f => f.offsetParent !== null).map(f => (f.querySelector('input[name=_wpcf7]') || {}).value + ' ' + f.innerText.replace(/\s+/g, ' ').slice(0, 120)));
    await p.screenshot({ path: out + '/uk-modal.png' }); }
  fs.writeFileSync(out + '/rep.json', JSON.stringify(rep, null, 1)); await b.close();
})();
