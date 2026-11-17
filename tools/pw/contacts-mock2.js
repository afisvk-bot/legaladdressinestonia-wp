const { chromium, devices } = require('playwright'); const fs = require('fs');
const URL = 'https://staging.dragonslots-gr.gr/';
const HIDE = '#lae-cookie-consent,[class*="lae-cookie"],[class*="cookie-consent"],[class*="cky-"],[class*="chat"],[id*="chat"],[class*="pipedrive"],iframe{display:none!important}';
const G = '#31d44b', PHONE = '+372 5626 2629', TEL = 'tel:+37256262629', MAIL = 'info@legaladdressinestonia.com';
const IC = {
  tg: '<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M21.4 4.1 2.9 11.2c-1.1.4-1 1.9.1 2.2l4.6 1.4 1.8 5.5c.3.9 1.4 1.1 2 .5l2.6-2.4 4.8 3.5c.8.6 1.9.1 2.1-.8l3-14c.3-1.2-.9-2.2-2-1.7ZM9.9 14.6l-.6 3.4-1.2-3.9 9.6-6.3-7.8 6.8Z"/></svg>',
  wa: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 20.5 4.8 16A8.6 8.6 0 1 1 8 19.3Z"/><path d="M9 8.6c0 3.3 3.1 6.4 6.4 6.4l1.2-1.4-1.9-1-1 .9c-1.1-.5-2.1-1.5-2.6-2.6l.9-1-1-1.9Z" fill="currentColor" stroke-width="1"/></svg>',
  ph: '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2Z"/></svg>',
  mail: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 6.5 8.5 6.5 8.5-6.5"/></svg>',
};
const CSS = `
.lai-mb{display:inline-flex;gap:8px;margin-right:8px;vertical-align:middle}
.lai-mb a{width:44px;height:44px;border-radius:14px;border:1px solid #e3e5ea;box-shadow:0 2px 6px rgba(20,30,50,.06);background:#fff;color:${G};display:inline-flex;align-items:center;justify-content:center}
.lai-mm{margin:24px 0 8px;padding:20px 0 0;border-top:1px solid #e9ebef;font-family:inherit}
.lai-mm__tel{display:flex;align-items:center;gap:10px;font-size:20px;font-weight:500;color:#141414;text-decoration:none;margin-bottom:10px}
.lai-mm__tel svg,.lai-mm__mail svg{color:${G}}
.lai-mm__mail{display:flex;align-items:center;gap:10px;font-size:15px;color:#141414;text-decoration:none;margin-bottom:16px}
.lai-mm__row{display:flex;gap:10px}
.lai-mm__row a{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:8px;height:46px;border-radius:12px;font-size:15px;font-weight:500;text-decoration:none}
.lai-mm__book{background:${G};color:#fff}
.lai-mm__chan{background:${G}1a;color:${G};flex:0 0 46px!important}
`;
const MB = (n) => `<span class="lai-mb"><a href="${TEL}" title="Call">${IC.ph}</a>${n > 1 ? `<a href="#" title="WhatsApp">${IC.wa}</a>` : ''}</span>`;
const MM = `<div class="lai-mm"><a class="lai-mm__tel" href="${TEL}">${IC.ph}${PHONE}</a><a class="lai-mm__mail" href="mailto:${MAIL}">${IC.mail}${MAIL}</a><div class="lai-mm__row"><a class="lai-mm__book" href="#">Book a call</a><a class="lai-mm__chan" href="#" title="WhatsApp">${IC.wa}</a><a class="lai-mm__chan" href="#" title="Telegram">${IC.tg}</a></div></div>`;
(async () => {
  const out = process.argv[2]; const b = await chromium.launch(); const rep = {};
  for (const n of [1, 2]) {
    const ctx = await b.newContext({ ...devices['iPhone 13'] }); const p = await ctx.newPage();
    await p.goto(URL, { waitUntil: 'networkidle' }); await p.addStyleTag({ content: HIDE }); await p.addStyleTag({ content: CSS });
    rep.mob = await p.evaluate(() => document.querySelector('header .mob_ready').outerHTML.replace(/<ul[\s\S]*<\/ul>/, '<UL/>').slice(0, 1500));
    await p.evaluate((h) => { const m = document.querySelector('header .mob_ready'); m.insertAdjacentHTML('afterbegin', h); }, MB(n));
    await p.waitForTimeout(300);
    await p.screenshot({ path: `${out}/m-header-${n}.png`, clip: { x: 0, y: 0, width: 390, height: 100 } });
    if (n === 1) {
      // открыть мобильное меню
      const cand = await p.evaluate(() => [...document.querySelectorAll('header .mob_ready *')].filter(e => /burger|toggl|menu-btn|hamb|navbar-toggler/i.test(e.className + ' ' + e.id)).map(e => e.tagName + '.' + e.className + '#' + e.id).slice(0, 8));
      rep.cand = cand;
      const sel = await p.evaluate(() => { const e = [...document.querySelectorAll('header .mob_ready button, header .mob_ready a, header .mob_ready div')].reverse().find(x => x.offsetWidth > 30 && x.offsetWidth < 70 && !x.closest('.lai-mb')); if (!e) return null; e.setAttribute('data-burger', '1'); return e.tagName + '.' + e.className; });
      rep.burger = sel;
      await p.locator('[data-burger="1"]').click({ force: true }).catch(e => rep.clickErr = e.message);
      await p.waitForTimeout(1200);
      const panel = await p.evaluate((h) => { const els = [...document.querySelectorAll('body *')].filter(e => { const r = e.getBoundingClientRect(); const s = getComputedStyle(e); return r.height > 450 && r.width > 300 && s.visibility !== 'hidden' && s.display !== 'none' && e.querySelector('a') && !['MAIN', 'BODY', 'HTML'].includes(e.tagName) && (s.position === 'fixed' || s.position === 'absolute'); }); const e = els[0]; if (!e) return null; const list = e.querySelector('ul') ? e.querySelector('ul').parentElement : e; list.insertAdjacentHTML('beforeend', h); return e.tagName + '.' + e.className; }, MM);
      rep.panel = panel;
      await p.waitForTimeout(500);
      await p.screenshot({ path: `${out}/m-menu.png` });
      await p.evaluate(() => { const m = document.querySelector('.lai-mm'); if (m) m.scrollIntoView({ block: 'end' }); });
      await p.waitForTimeout(400);
      await p.screenshot({ path: `${out}/m-menu-bottom.png` });
    }
    await ctx.close();
  }
  fs.writeFileSync(out + '/rep.json', JSON.stringify(rep, null, 1)); await b.close();
})();
