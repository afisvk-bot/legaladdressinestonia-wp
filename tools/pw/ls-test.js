const { chromium, devices } = require('playwright'); const fs = require('fs');
const B = 'https://staging.dragonslots-gr.gr';
const HIDE = '#lae-cookie-consent,[class*="lae-cookie"],[class*="cookie-consent"],[class*="cky-"],#LeadboosterContainer{display:none!important}';
(async () => {
  const out = process.argv[2]; const b = await chromium.launch(); const rep = {};
  const ctx = await b.newContext({ viewport: { width: 1440, height: 950 } }); const p = await ctx.newPage();
  // заход «из ChatGPT» на страницу услуги, затем переход на другую страницу
  await p.goto(B + '/annual-report-for-estonian-companies/?utm_source=chatgpt.com', { waitUntil: 'networkidle', referer: 'https://chatgpt.com/' });
  await p.goto(B + '/contact-person-in-estonia/', { waitUntil: 'networkidle' }); await p.addStyleTag({ content: HIDE });
  rep.session = await p.evaluate(() => sessionStorage.getItem('lai_src'));
  // окно консультации form_EN
  await p.evaluate(async () => { const a = [...document.querySelectorAll('[data-mm-modal],[data-toggle=modal]')].find(x => x.offsetParent !== null && !x.closest('header')); a.scrollIntoView({ block: 'center' }); await new Promise(r => setTimeout(r, 300)); a.click(); });
  await p.waitForTimeout(1500);
  rep.consult = await p.evaluate(() => { const f = [...document.querySelectorAll('form.wpcf7-form')].find(x => x.getBoundingClientRect().height > 0 && !x.closest('#book-a-call')); if (!f) return null; return { id: f.querySelector('input[name=_wpcf7]').value, options: [...f.querySelectorAll('select[name=lead-source] option')].map(o => o.textContent + '=' + o.value), hidden: ['lead-channel', 'lead-referrer', 'lead-utm', 'lead-landing'].map(n => n + ':' + (f.querySelector(`input[name="${n}"]`) || {}).value) }; });
  await p.evaluate(() => { const s = [...document.querySelectorAll('select[name=lead-source]')].find(x => x.getBoundingClientRect().height > 0); if (s) s.focus(); });
  await p.screenshot({ path: out + '/consult-en.png' });
  await p.keyboard.press('Escape'); await p.evaluate(() => { document.querySelectorAll('.modal.show .close, .modal.show [data-dismiss]').forEach(b => b.click()); }); await p.waitForTimeout(800);
  // Book a call
  await p.evaluate(() => window.scrollTo(0, 0)); await p.click('.lai-hc__book'); await p.waitForTimeout(700);
  await p.screenshot({ path: out + '/book-en.png' });
  rep.book = await p.evaluate(() => { const f = document.querySelector('#book-a-call form'); return { options: [...f.querySelectorAll('select[name=lead-source] option')].map(o => o.textContent + '=' + o.value), hidden: ['lead-channel', 'lead-referrer', 'lead-utm', 'lead-landing'].map(n => n + ':' + (f.querySelector(`input[name="${n}"]`) || {}).value) }; });
  // тестовая заявка Book a call
  await p.fill('#lcf-name', 'TEST Claude — lead source check'); await p.fill('#lcf-contact', '+372 5555 0000');
  await p.selectOption('#lcf-src', 'ChatGPT / AI assistant'); await p.fill('#lcf-msg', 'Test request from staging. Please ignore.');
  await p.waitForTimeout(3500); await p.click('#book-a-call input.lcf__btn'); await p.waitForTimeout(5000);
  rep.submit = await p.evaluate(() => ({ done: !document.querySelector('.lcf__done').hidden, resp: (document.querySelector('#book-a-call .wpcf7-response-output') || {}).innerText }));
  await ctx.close();
  // RU мобильный: Book a call с переводом и form_RU
  const c2 = await b.newContext({ ...devices['iPhone 13'] }); const q = await c2.newPage();
  await q.goto(B + '/ru/', { waitUntil: 'networkidle', referer: 'https://www.google.com/' }); await q.addStyleTag({ content: HIDE });
  await q.evaluate(() => { document.querySelector('[data-lai-book]').click(); }); await q.waitForTimeout(700);
  await q.evaluate(() => document.querySelector('#lcf-src').scrollIntoView({ block: 'center' })); await q.waitForTimeout(300);
  await q.screenshot({ path: out + '/book-ru-m.png' });
  rep.ruBook = await q.evaluate(() => ({ label: document.querySelector('label[for=lcf-src]').innerText, opts: [...document.querySelectorAll('#lcf-src option')].map(o => o.textContent).join(' | '), ch: document.querySelector('#book-a-call input[name=lead-channel]').value }));
  fs.writeFileSync(out + '/rep.json', JSON.stringify(rep, null, 1)); await b.close();
})();
