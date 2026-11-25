const { chromium } = require('playwright'); const fs = require('fs');
const B = 'https://staging.dragonslots-gr.gr';
const pages = ['/likvidatsiya-kompanii-v-estonii-zakryt-prodat-ili-vosstanovit-o-pravilno/','/company-liquidation-in-estonia/','/likvidatsiya-kompaniyi-v-estoniyi/','/liquidation-einer-firma-in-estland/','/liquidacion-de-empresas-en-estonia/','/liquidation-de-societe-en-estonie/','/liquidacao-de-empresas-na-estonia/','/estonyada-sirket-tasfiyesi/','/company-liquidation-estonia/',
'/registratsiya-tovarnogo-znaka-i-patenta-v-estonii-i-es-zashhita-brenda-ot-razrabotki-strategii-do-polucheniya-svidetelstva/','/trademark-registration-in-estonia/','/reyestratsiya-torhovelnoyi-marky-v-estoniyi/','/markenanmeldung-in-estland/','/registro-de-marcas-en-estonia/','/depot-de-marque-en-estonie/','/registo-de-marcas-na-estonia/','/estonyada-marka-tescili/','/trademark-registration-estonia/',
'/target/estonskaya-kompaniya-dlya-ukrainskih-predprinimatelej/','/target/estonian-company-for-ukrainian-entrepreneurs/','/target/estonska-kompaniya-dlya-ukrayintsiv/','/target/estnische-firma-fuer-ukrainische-unternehmer/','/target/empresa-estonia-para-emprendedores-ucranianos/','/target/societe-estonienne-pour-entrepreneurs-ukrainiens/','/target/empresa-estonia-para-empreendedores-ucranianos/','/target/ukraynali-girisimciler-icin-estonya-sirketi/','/target/estonian-company-for-ukrainians-zh/'];
(async () => { const out = process.argv[2]; const b = await chromium.launch(); const rep = {};
 for (const u of pages) { const p = await b.newPage(); const r = await p.goto(B + u, { waitUntil: 'load', timeout: 60000 }).catch(() => null);
  rep[u] = Object.assign({ status: r && r.status() }, await p.evaluate(async () => {
   const main = document.querySelector('main') || document.body;
   const links = [...new Set([...main.querySelectorAll('a[href]')].filter(a => !a.closest('header,footer,nav')).map(a => a.href.split('#')[0]).filter(h => h.startsWith(location.origin)))];
   const bad = []; for (const h of links) { try { const x = await fetch(h, { method: 'HEAD', redirect: 'manual' }); if (x.status >= 300) bad.push(x.status + ' ' + h); } catch (e) {} }
   const cyr = /[А-Яа-яЁё]/.test(main.innerText.replace(/Русский|Українська|RU|UA/g, ''));
   const menu = [...document.querySelectorAll('header a')].map(a => a.textContent.trim()).filter(Boolean);
   return { title: document.title, h1: (document.querySelector('h1') || {}).textContent, hreflang: document.querySelectorAll('link[hreflang]').length, links: links.length, bad, cyr, menuHas: menu.filter(t => /Liquid|Ліквід|Ликвид|tasfiye|清算|Trademark|Marke|marca|marque|Marka|商标|товарн|торговель|Ukrain|Ucran|ucran|Ukrayna|乌克兰|Украин|Україн/i.test(t)) };
  })); await p.close(); }
 for (const [n, u, w] of [['en-desk', '/company-liquidation-in-estonia/', 1440], ['de-tm', '/markenanmeldung-in-estland/', 1440], ['uk-mob', '/target/estonska-kompaniya-dlya-ukrayintsiv/', 390]]) {
  const ctx = await b.newContext({ viewport: { width: w, height: w < 500 ? 844 : 900 }, deviceScaleFactor: w < 500 ? 2 : 1 }); const p = await ctx.newPage();
  await p.goto(B + u, { waitUntil: 'networkidle', timeout: 60000 }).catch(() => {}); await p.waitForTimeout(800);
  await p.evaluate(() => document.querySelectorAll('#lae-cookie-banner,[id*=cookie],[class*=cookie]').forEach(e => e.style.display = 'none'));
  await p.screenshot({ path: out + '/' + n + '.png' });
  if (w > 1000) { const s = await p.$('header a:text-is("Services"), header a:text-is("Dienstleistungen")'); if (s) { await s.hover(); await p.waitForTimeout(700); await p.screenshot({ path: out + '/' + n + '-menu.png' }); } }
  await ctx.close(); }
 fs.writeFileSync(out + '/r.json', JSON.stringify(rep, null, 1)); await b.close(); })();
