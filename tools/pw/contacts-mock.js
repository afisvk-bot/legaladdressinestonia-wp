const { chromium, devices } = require('playwright'); const fs = require('fs');
const URL = 'https://staging.dragonslots-gr.gr/';
const HIDE = '#lae-cookie-consent,[class*="lae-cookie"],[class*="cookie-consent"],[class*="cky-"],[class*="chat"],[id*="chat"],[class*="pipedrive"],iframe{display:none!important}';
const PHONE = '+372 5626 2629', TEL = 'tel:+37256262629', MAIL = 'info@legaladdressinestonia.com';
const IC = {
  tg: '<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M21.4 4.1 2.9 11.2c-1.1.4-1 1.9.1 2.2l4.6 1.4 1.8 5.5c.3.9 1.4 1.1 2 .5l2.6-2.4 4.8 3.5c.8.6 1.9.1 2.1-.8l3-14c.3-1.2-.9-2.2-2-1.7ZM9.9 14.6l-.6 3.4-1.2-3.9 9.6-6.3-7.8 6.8Z"/></svg>',
  wa: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.5 20.5 4.8 16A8.6 8.6 0 1 1 8 19.3Z"/><path d="M9 8.6c0 3.3 3.1 6.4 6.4 6.4l1.2-1.4-1.9-1-1 .9c-1.1-.5-2.1-1.5-2.6-2.6l.9-1-1-1.9Z" fill="currentColor" stroke-width="1"/></svg>',
  ph: '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2Z"/></svg>',
  mail: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 6.5 8.5 6.5 8.5-6.5"/></svg>',
};
const CSS = (G) => `
.lai-hc{display:flex;flex-direction:column;align-items:flex-end;gap:4px;margin-left:24px;font-family:inherit}
.lai-hc__tel{font-size:17px;font-weight:500;color:#141414;text-decoration:none;letter-spacing:.2px;white-space:nowrap}
.lai-hc__row{display:flex;align-items:center;gap:14px}
.lai-hc__book{font-size:15px;font-weight:500;color:${G};text-decoration:none;border-bottom:1.5px solid ${G};line-height:1.2;white-space:nowrap}
.lai-ic{color:${G};display:inline-flex;align-items:center;justify-content:center;line-height:0}
.lai-hm{display:none}
@media (max-width:991px){
 .lai-hc{display:none}
 .lai-hm{display:flex;align-items:center;gap:10px;margin-left:auto;margin-right:12px}
 .lai-hm--a .lai-hm__tel{font-size:15px;font-weight:500;color:${G};text-decoration:none;border-bottom:1.5px dotted ${G};white-space:nowrap}
 .lai-hm--b .lai-hm__btn{width:38px;height:38px;border-radius:50%;background:${G}1a;color:${G};display:inline-flex;align-items:center;justify-content:center}
}
.lai-fc{margin:0 0 22px;font-family:inherit}
.lai-fc__tel{display:block;font-size:24px;font-weight:500;color:#fff;text-decoration:none;letter-spacing:.3px;margin-bottom:6px}
.lai-fc__mail{display:inline-flex;align-items:center;gap:8px;font-size:16px;color:#fff;text-decoration:none;opacity:.9;margin-bottom:14px}
.lai-fc__mail svg{color:${G}}
.lai-fc__row{display:flex;align-items:center;gap:16px}
.lai-fc__book{font-size:16px;font-weight:500;color:${G};text-decoration:none;border-bottom:1.5px solid ${G};line-height:1.2}
.lai-fc .lai-ic svg{width:24px;height:24px}
.lai-fc__hours{font-size:14px;color:#fff;opacity:.6;margin-top:10px}
`;
const HEAD = `<div class="lai-hc"><a class="lai-hc__tel" href="${TEL}">${PHONE}</a><div class="lai-hc__row"><a class="lai-hc__book" href="#">Book a call</a><a class="lai-ic" href="#" title="Telegram">${IC.tg}</a><a class="lai-ic" href="#" title="WhatsApp">${IC.wa}</a></div></div>`;
const MOB_A = `<div class="lai-hm lai-hm--a"><a class="lai-hm__tel" href="${TEL}">${PHONE}</a></div>`;
const MOB_B = `<div class="lai-hm lai-hm--b"><a class="lai-hm__btn" href="${TEL}" title="Call">${IC.ph}</a><a class="lai-hm__btn" href="#" title="WhatsApp">${IC.wa}</a><a class="lai-hm__btn" href="#" title="Telegram">${IC.tg}</a></div>`;
const FOOT = `<div class="lai-fc"><a class="lai-fc__tel" href="${TEL}">${PHONE}</a><a class="lai-fc__mail" href="mailto:${MAIL}">${IC.mail}${MAIL}</a><div class="lai-fc__row"><a class="lai-fc__book" href="#">Book a call</a><a class="lai-ic" href="#" title="Telegram">${IC.tg}</a><a class="lai-ic" href="#" title="WhatsApp">${IC.wa}</a></div><div class="lai-fc__hours">Mon–Fri 9:00–17:00</div></div>`;
async function inject(p, mob) {
  await p.addStyleTag({ content: HIDE });
  const G = await p.evaluate(() => { const a = document.querySelector('.menu-social a'); const c = a && getComputedStyle(a).backgroundColor; return c && c !== 'rgba(0, 0, 0, 0)' ? c : '#34c759'; });
  const hex = await p.evaluate((c) => { const m = c.match(/\d+/g); return m ? '#' + m.slice(0, 3).map(x => (+x).toString(16).padStart(2, '0')).join('') : c; }, G);
  await p.addStyleTag({ content: CSS(hex) });
  await p.evaluate(([HEAD, MOB, FOOT]) => {
    const row = document.querySelector('header .row'); const kids = [...row.children]; const last = kids[kids.length - 1];
    // десктоп: после переключателя языка (последний элемент)
    last.insertAdjacentHTML('afterend', HEAD);
    // мобильная: перед бургером/языком — вставляем после логотипа
    row.querySelector('.logo').insertAdjacentHTML('afterend', MOB);
    const col = document.querySelector('footer .menu-social').parentElement; col.querySelector('.menu-social').insertAdjacentHTML('afterend', FOOT);
  }, [HEAD, mob, FOOT]);
  await p.waitForTimeout(400);
  return hex;
}
(async () => {
  const out = process.argv[2]; const b = await chromium.launch(); const rep = {};
  // desktop
  let ctx = await b.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 2 }); let p = await ctx.newPage();
  await p.goto(URL, { waitUntil: 'networkidle' }); await p.addStyleTag({ content: HIDE });
  await p.screenshot({ path: out + '/d-header-before.png', clip: { x: 0, y: 0, width: 1440, height: 110 } });
  rep.green = await inject(p, MOB_A);
  rep.headerHTML = await p.evaluate(() => [...document.querySelector('header .row').children].map(e => e.tagName + '.' + e.className).join(' | '));
  await p.screenshot({ path: out + '/d-header-after.png', clip: { x: 0, y: 0, width: 1440, height: 110 } });
  await p.evaluate(() => window.scrollTo(0, document.body.scrollHeight)); await p.waitForTimeout(800);
  await p.locator('footer').first().screenshot({ path: out + '/d-footer-after.png' });
  for (const w of [1280, 1100]) { await p.setViewportSize({ width: w, height: 900 }); await p.evaluate(() => window.scrollTo(0, 0)); await p.waitForTimeout(400); await p.screenshot({ path: out + `/d-header-${w}.png`, clip: { x: 0, y: 0, width: w, height: 110 } }); }
  await ctx.close();
  // mobile A and B
  for (const [n, MOB] of [['a', MOB_A], ['b', MOB_B]]) {
    ctx = await b.newContext({ ...devices['iPhone 13'] }); p = await ctx.newPage();
    await p.goto(URL, { waitUntil: 'networkidle' });
    if (n === 'a') { await p.addStyleTag({ content: HIDE }); await p.screenshot({ path: out + '/m-header-before.png', clip: { x: 0, y: 0, width: 390, height: 110 } }); }
    await inject(p, MOB);
    await p.screenshot({ path: out + `/m-header-${n}.png`, clip: { x: 0, y: 0, width: 390, height: 110 } });
    if (n === 'a') { await p.evaluate(() => window.scrollTo(0, document.body.scrollHeight)); await p.waitForTimeout(800); await p.locator('footer').first().screenshot({ path: out + '/m-footer-after.png' }); }
    await ctx.close();
  }
  fs.writeFileSync(out + '/rep.json', JSON.stringify(rep, null, 1)); await b.close();
})();
