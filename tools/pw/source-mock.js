const { chromium, devices } = require('playwright'); const fs = require('fs');
const B = 'https://staging.dragonslots-gr.gr';
const HIDE = '#lae-cookie-consent,[class*="lae-cookie"],[class*="cookie-consent"],[class*="cky-"],#LeadboosterContainer{display:none!important}';
const OPTS = ['Google search', 'ChatGPT / AI assistant', 'Recommendation', 'LinkedIn / Facebook / Instagram', 'YouTube / TikTok', 'e-Residency Marketplace', 'Trustpilot / reviews', 'Other'];
const CSS = `
.lsrc select{width:100%;appearance:none;-webkit-appearance:none;border:1px solid #dfe2e8;border-radius:14px;padding:13px 44px 13px 18px;font:inherit;font-size:16px;color:#151a2d;background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23151a2d' stroke-width='2.4'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right 18px center}
.lsrc .opt{font-size:12px;color:#8a90a0;margin-left:6px}
.lchips{display:flex;flex-wrap:wrap;gap:8px}
.lchips span{border:1px solid #dfe2e8;border-radius:999px;padding:8px 14px;font-size:14px;color:#151a2d;background:#fff}
.lchips span.on{border-color:#31d44b;background:rgba(49,212,75,.1);font-weight:600}
.lsrc-open{position:relative}.lsrc-list{position:absolute;left:0;right:0;top:100%;margin-top:6px;background:#fff;border:1px solid #dfe2e8;border-radius:14px;box-shadow:0 12px 30px rgba(10,20,40,.12);padding:6px;z-index:5}
.lsrc-list div{padding:10px 14px;border-radius:10px;font-size:15px}.lsrc-list div.h{background:#f3f4f6}
`;
(async () => {
  const out = process.argv[2]; const b = await chromium.launch(); const rep = {};
  for (const [name, opts] of [['d', { viewport: { width: 1440, height: 1050 } }], ['m', { ...devices['iPhone 13'] }]]) {
    const ctx = await b.newContext(opts); const p = await ctx.newPage();
    await p.goto(B + '/', { waitUntil: 'networkidle' }); await p.addStyleTag({ content: HIDE }); await p.addStyleTag({ content: CSS });
    // Вариант A: выпадающий список в форме Book a call (открыт)
    await p.evaluate(() => { document.getElementById('book-a-call').hidden = false; document.body.classList.add('lcf-open'); });
    await p.evaluate((OPTS) => {
      const msg = document.querySelector('.lcf .lcf__r--top');
      msg.insertAdjacentHTML('beforebegin', `<div class="lcf__r lsrc"><label>How did you hear about us?<span class="opt">(optional)</span></label><div class="lsrc-open"><select><option>Select an option</option>${OPTS.map(o => `<option>${o}</option>`).join('')}</select><div class="lsrc-list">${OPTS.map((o, i) => `<div class="${i === 1 ? 'h' : ''}">${o}</div>`).join('')}</div></div></div>`);
    }, OPTS);
    await p.waitForTimeout(300);
    await p.screenshot({ path: `${out}/${name}-A-select.png`, fullPage: false });
    // Вариант B: «чипсы»
    await p.evaluate((OPTS) => {
      const r = document.querySelector('.lsrc'); r.outerHTML = `<div class="lcf__r lsrc lcf__r--top"><label style="padding-top:8px">How did you hear about us?<span class="opt">(optional)</span></label><div class="lchips">${['Google', 'ChatGPT / AI', 'Recommendation', 'Social media', 'YouTube', 'e-Residency Marketplace', 'Other'].map((o, i) => `<span class="${i === 1 ? 'on' : ''}">${o}</span>`).join('')}</div></div>`;
    }, OPTS);
    await p.waitForTimeout(300);
    await p.screenshot({ path: `${out}/${name}-B-chips.png`, fullPage: false });
    // Основная форма «Get a consultation»
    await p.evaluate(() => { document.getElementById('book-a-call').hidden = true; document.body.classList.remove('lcf-open'); });
    const btn = p.locator('[data-mm-modal]').first();
    rep[name + '-btn'] = await btn.count();
    if (await btn.count()) {
      await btn.click({ force: true }).catch(e => rep.err = e.message); await p.waitForTimeout(1200);
      rep[name + '-form'] = await p.evaluate((OPTS) => {
        const f = [...document.querySelectorAll('.wpcf7 form')].find(x => x.offsetParent !== null && !x.closest('#book-a-call'));
        if (!f) return 'no visible form';
        const ta = f.querySelector('textarea') || f.querySelector('input[type=submit]');
        const wrap = ta.closest('p,div,label') || ta;
        wrap.insertAdjacentHTML('beforebegin', `<div class="lsrc" style="margin:0 0 14px"><select style="border-radius:12px"><option>How did you hear about us? (optional)</option>${OPTS.map(o => `<option>${o}</option>`).join('')}</select></div>`);
        return f.innerText.slice(0, 300);
      }, OPTS);
      await p.waitForTimeout(400);
      await p.screenshot({ path: `${out}/${name}-C-consult.png` });
    }
    await ctx.close();
  }
  fs.writeFileSync(out + '/rep.json', JSON.stringify(rep, null, 1)); await b.close();
})();
