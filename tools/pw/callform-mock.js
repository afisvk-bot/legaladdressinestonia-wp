const { chromium, devices } = require('playwright'); const fs = require('fs');
const URL = 'https://staging.dragonslots-gr.gr/';
const HIDE = '#lae-cookie-consent,[class*="lae-cookie"],[class*="cookie-consent"],[class*="cky-"],[class*="chat"],[id*="chat"],[class*="pipedrive"],iframe{display:none!important}';
const G = '#31d44b', GD = '#1fae3a';
const CSS = `
.lcf-ov{position:fixed;inset:0;background:rgba(15,22,38,.55);backdrop-filter:blur(3px);z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px}
.lcf{position:relative;background:#fff;border-radius:24px;width:100%;max-width:720px;padding:48px 56px 40px;font-family:Poppins,sans-serif;color:#151a2d;box-shadow:0 30px 80px rgba(10,20,40,.25)}
.lcf__x{position:absolute;top:22px;right:24px;width:40px;height:40px;border-radius:12px;border:1px solid #e3e5ea;display:flex;align-items:center;justify-content:center;font-size:22px;line-height:1;color:#151a2d;background:#fff}
.lcf h3{font-size:36px;font-weight:500;margin:0 0 6px;letter-spacing:-.5px}
.lcf__sub{font-size:15px;color:#6b7183;margin:0 0 30px}
.lcf__r{display:grid;grid-template-columns:170px 1fr;align-items:center;gap:16px;margin-bottom:16px}
.lcf__r.top{align-items:start}.lcf__r.top label{padding-top:14px}
.lcf label{font-size:16px;color:#151a2d}
.lcf input,.lcf textarea{width:100%;border:1px solid #dfe2e8;border-radius:14px;padding:14px 18px;font:inherit;font-size:16px;color:#151a2d;background:#fff;outline:none}
.lcf input::placeholder,.lcf textarea::placeholder{color:#9aa0ae}
.lcf textarea{height:120px;resize:none}
.lcf__seg{display:flex;background:#f3f4f6;border-radius:14px;padding:4px;gap:2px}
.lcf__seg span{flex:1;text-align:center;padding:10px 6px;border-radius:11px;font-size:15px;color:#151a2d;white-space:nowrap}
.lcf__seg span.on{background:#fff;font-weight:600;box-shadow:0 1px 3px rgba(0,0,0,.08),0 0 0 1.5px ${G}}
.lcf__btn{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;height:56px;border:0;border-radius:14px;background:${G};color:#fff;font:inherit;font-size:17px;font-weight:600;letter-spacing:.3px}
.lcf__note{font-size:13px;color:#8a90a0;line-height:1.55;margin:14px 0 0}
.lcf__note a{color:#6b7183;text-decoration:underline}
.lcf__cap{display:flex;align-items:center;gap:10px;border:1px solid #dfe2e8;border-radius:6px;padding:10px 12px;width:300px;font-size:14px;color:#555;background:#f9f9f9;margin-bottom:16px}
.lcf__cap i{width:24px;height:24px;border:2px solid #c1c1c1;border-radius:3px;background:#fff;display:inline-block}
@media (max-width:640px){.lcf{padding:56px 20px 24px;border-radius:20px}.lcf h3{font-size:28px}.lcf__r{grid-template-columns:1fr;gap:6px;margin-bottom:14px}.lcf__r.top label{padding-top:0}.lcf__seg span{font-size:13px;padding:10px 2px}.lcf__cap{width:100%}.lcf__sub{margin-bottom:20px}.lcf-ov{align-items:flex-end;padding:0}.lcf{border-radius:20px 20px 0 0;max-height:94vh;overflow:auto}}
`;
const FORM = (m) => {
  const conf = { Phone: ['Phone', '+372 5xxx xxxx', 'tel'], Email: ['Email', 'name@company.com', 'email'], Telegram: ['Telegram', '@username or phone', 'text'], WhatsApp: ['WhatsApp', '+372 5xxx xxxx', 'tel'] }[m];
  const tabs = ['Phone', 'Email', 'Telegram', 'WhatsApp'].map(t => `<span class="${t === m ? 'on' : ''}">${t}</span>`).join('');
  return `<div class="lcf-ov"><div class="lcf"><div class="lcf__x">×</div><h3>Book a call</h3><p class="lcf__sub">We will contact you within 1 working day — Mon–Fri 9:00–17:00 (Tallinn)</p>
<div class="lcf__r"><label>Your name</label><input placeholder="or company name"></div>
<div class="lcf__r"><label>Contact method</label><div class="lcf__seg">${tabs}</div></div>
<div class="lcf__r"><label>${conf[0]}</label><input type="${conf[2]}" placeholder="${conf[1]}"></div>
<div class="lcf__r top"><label>Message</label><textarea placeholder="How can we help you?"></textarea></div>
<div class="lcf__r"><span></span><div><div class="lcf__cap"><i></i>I'm not a robot</div><button class="lcf__btn">Send request <span>›</span></button>
<p class="lcf__note">By clicking the button, I confirm that I have read the <a href="#">Privacy Policy</a> and consent to the processing of my personal data in accordance with the GDPR.</p></div></div></div></div>`;
};
(async () => {
  const out = process.argv[2]; const b = await chromium.launch();
  for (const [name, opts, modes] of [['d', { viewport: { width: 1440, height: 1000 }, deviceScaleFactor: 1 }, ['Phone', 'Telegram']], ['m', { ...devices['iPhone 13'] }, ['Phone', 'Email']]]) {
    const ctx = await b.newContext(opts); const p = await ctx.newPage();
    await p.goto(URL, { waitUntil: 'networkidle' }); await p.addStyleTag({ content: HIDE }); await p.addStyleTag({ content: CSS });
    for (const m of modes) {
      await p.evaluate((h) => { document.querySelectorAll('.lcf-ov').forEach(e => e.remove()); document.body.insertAdjacentHTML('beforeend', h); }, FORM(m));
      await p.waitForTimeout(300);
      await p.screenshot({ path: `${out}/${name}-${m}.png` });
    }
    await ctx.close();
  }
  await b.close();
})();
