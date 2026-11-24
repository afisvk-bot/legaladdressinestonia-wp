const { chromium } = require('playwright'); const fs = require('fs');
const B = 'https://staging.dragonslots-gr.gr';
(async () => {
  const out = process.argv[2]; const b = await chromium.launch(); const rep = {};
  const ctx = await b.newContext({ viewport: { width: 1440, height: 900 } }); const p = await ctx.newPage();
  const errs = []; p.on('pageerror', e => errs.push(e.message));
  await p.goto(B + '/annual-report-for-estonian-companies/?utm_source=chatgpt.com', { waitUntil: 'networkidle', referer: 'https://chatgpt.com/' });
  rep.trackerPresent = await p.evaluate(() => !!document.getElementById('lai-lead-tracking'));
  const evs = () => p.evaluate(() => (window.dataLayer || []).filter(x => x && x.event && /lead|calendly|pipedrive|click_/.test(x.event)));
  // клик по телефону в шапке
  await p.evaluate(() => { const a = document.querySelector('header a[href^="tel:"]'); a.addEventListener('click', e => e.preventDefault(), { once: true }); a.click(); });
  // футер почта
  await p.evaluate(() => { const a = document.querySelector('footer a[href^="mailto:"]'); a.addEventListener('click', e => e.preventDefault(), { once: true }); a.click(); });
  // CF7 mailsent (имитация события Book a call)
  await p.evaluate(() => { const f = document.querySelector('#book-a-call form'); const wrap = f.closest('.wpcf7'); f.dispatchEvent(new CustomEvent('wpcf7mailsent', { bubbles: true, detail: { contactFormId: 4741, unitTag: wrap.id, inputs: [{ name: 'lead-source', value: 'ChatGPT / AI assistant' }, { name: 'lead-channel', value: 'AI: ChatGPT' }, { name: 'contact-method', value: 'WhatsApp' }] } })); });
  // CF7 mailsent: консультация (form_EN 456) в окне
  await p.evaluate(() => { const f = [...document.querySelectorAll('form.wpcf7-form')].find(x => !x.closest('#book-a-call')); if (!f) return; const wrap = f.closest('.wpcf7'); f.dispatchEvent(new CustomEvent('wpcf7mailsent', { bubbles: true, detail: { contactFormId: 456, unitTag: wrap.id, inputs: [{ name: 'menu-212', value: 'Legal address' }, { name: 'lead-source', value: '' }, { name: 'lead-channel', value: 'AI: ChatGPT' }] } })); });
  // Calendly и Pipedrive (имитация сообщений)
  await p.evaluate(() => { window.dispatchEvent(new MessageEvent('message', { origin: 'https://calendly.com', data: { event: 'calendly.event_scheduled', payload: {} } })); window.dispatchEvent(new MessageEvent('message', { origin: 'https://evil.example', data: { event: 'calendly.event_scheduled' } })); window.dispatchEvent(new MessageEvent('message', { origin: 'https://webforms.pipedrive.com', data: { type: 6, payload: { url: 'https://legaladdressinestonia.com/contacts/?lai_lead=pd', uuid: 'x', source: 'pipedriveWebForms' } } })); window.dispatchEvent(new MessageEvent('message', { origin: 'https://webforms.pipedrive.com', data: { type: 0, payload: { height: 600 } } })); });
  rep.events = await evs();
  // переход на «спасибо» сразу после type 6 — дубль не должен уйти
  await p.goto(B + '/contacts/?lai_lead=pd', { waitUntil: 'networkidle' });
  rep.afterRedirect = await evs();
  // новый визит без предшествующего сообщения — событие по URL
  const c2 = await b.newContext({ locale: 'ru-RU' }); const q = await c2.newPage(); await q.goto(B + '/ru/kontakty/?lai_lead=pd', { waitUntil: 'networkidle' });
  await q.screenshot({ path: out + '/thanks.png' }); rep.url = q.url(); rep.directThankYou = await q.evaluate(() => (window.dataLayer || []).filter(x => x && x.event === 'pipedrive_form_submit'));
  rep.errors = errs;
  fs.writeFileSync(out + '/rep.json', JSON.stringify(rep, null, 1)); await b.close();
})();
