const { chromium } = require('playwright'); const fs = require('fs'); const path = require('path');
const B = 'https://staging.dragonslots-gr.gr';
const urls = JSON.parse(fs.readFileSync(path.join(__dirname, 'svc6-urls.json')));
const MENU = { ru: ['Изменения в компании','Продажа и передача долей','VAT-декларации','Расчёт зарплаты'], en: ['Company Changes','Share Sale and Transfer','VAT Returns','Payroll'], uk: ['Зміни в компанії','VAT-декларації'], de: ['Unternehmensänderungen','Lohnabrechnung'], es: ['Cambios en la empresa','Nóminas'], fr: ['Modifications de société','Paie'], pt: ['Alterações na empresa','Processamento salarial'], tr: ['Şirket değişiklikleri','Bordro'], zh: ['公司变更','工资核算'] };
(async () => { const out = process.argv[2]; const b = await chromium.launch(); const rep = []; const seen = {};
 for (const [lang, u] of urls) { const p = await b.newPage(); const r = await p.goto(B + u, { waitUntil: 'load', timeout: 60000 }).catch(() => null);
  const res = await p.evaluate(async ({ lang, menu }) => {
   const main = document.querySelector('main') || document.body;
   const links = [...new Set([...main.querySelectorAll('a[href]')].filter(a => !a.closest('header,footer,nav')).map(a => a.href.split('#')[0]).filter(h => h.startsWith(location.origin) && !h.includes('/wp-content/')))];
   const bad = []; const wrongLang = [];
   for (const h of links) { try { const x = await fetch(h, { method: 'HEAD', redirect: 'manual' }); if (x.status >= 300) bad.push(x.status + ' ' + h.replace(location.origin, '')); } catch (e) {} }
   const txt = main.innerText;
   const cyr = (lang === 'ru' || lang === 'uk') ? [] : (txt.match(/[^\n]{0,30}[А-Яа-яЁёІіЇїЄє]+[^\n]{0,30}/g) || []).slice(0, 5);
   const nav = (document.querySelector('header') || document.body).innerText;
   const menuMiss = (menu[lang] || []).filter(s => !nav.includes(s));
   const img = document.querySelector('main img, .wp-post-image, img');
   const spans = (main.innerHTML.match(/font-weight: 400/g) || []).length;
   return { status: 0, title: document.title, tlen: document.title.length, desc: (document.querySelector('meta[name=description]') || {}).content || '', h1: [...document.querySelectorAll('h1')].map(e => e.textContent.trim()), hreflang: document.querySelectorAll('link[rel=alternate][hreflang]').length, bad, cyr, menuMiss, spans, links: links.length, buttons: [...main.querySelectorAll('a,button')].filter(a => /Заказать|Order|Bestellen|Solicitar|Commander|Encomendar|Sipariş|订购|Замовити/.test(a.textContent)).length,
     ogimg: (document.querySelector('meta[property="og:image"]') || {}).content || '' };
  }, { lang, menu: MENU });
  res.status = r && r.status(); res.lang = lang; res.url = u; rep.push(res); await p.close(); }
 for (const [n, u, w] of [['en-desk', '/share-capital-increase-estonia/', 1440], ['de-mob', '/lohnabrechnung-in-estland/', 390]]) { const ctx = await b.newContext({ viewport: { width: w, height: w < 500 ? 844 : 900 } }); const p = await ctx.newPage(); await p.goto(B + u, { waitUntil: 'networkidle', timeout: 60000 }).catch(() => {}); await p.waitForTimeout(800); await p.screenshot({ path: out + '/' + n + '.png', fullPage: false }); await ctx.close(); }
 // menu open screenshot EN desktop
 { const ctx = await b.newContext({ viewport: { width: 1440, height: 900 } }); const p = await ctx.newPage(); await p.goto(B + '/vat-returns-in-estonia/', { waitUntil: 'networkidle', timeout: 60000 }).catch(() => {}); const s = p.locator('header a, header span', { hasText: 'Services' }).first(); await s.hover().catch(() => {}); await p.waitForTimeout(700); const c = p.locator('header a', { hasText: 'Company Changes' }).first(); await c.hover().catch(() => {}); await p.waitForTimeout(700); await p.screenshot({ path: out + '/menu-en.png' }); await ctx.close(); }
 fs.writeFileSync(out + '/r.json', JSON.stringify(rep, null, 1)); await b.close(); })();
