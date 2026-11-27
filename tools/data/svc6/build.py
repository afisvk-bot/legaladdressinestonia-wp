import json, re, os
os.chdir(os.path.dirname(os.path.abspath(__file__)))
B = 'https://staging.dragonslots-gr.gr'
C = json.load(open('common.json'))
EXP = json.load(open('/tmp/claude-0/-home-claude/a6ca971b-ac50-5087-abb7-98999e1ac54a/scratchpad/svc6/export.json'))
def parse(path):
    d, k = {}, None
    for line in open(path, encoding='utf-8').read().split('\n'):
        m = re.match(r'^## (\S+)\s*$', line)
        if m: k = m.group(1); d[k] = []; continue
        if k: d[k].append(line)
    return {k: '\n'.join(v).strip('\n') for k, v in d.items()}
def common(lang, key):
    c = C[lang]
    if key == 'content_blocks_1_anchor': return c['content_blocks_1_anchor']
    if key == 'content_blocks_2_anchor': return c['content_blocks_2_anchor']
    for suf, src in (('_package_anchor', 'content_blocks_6_tariff_items_0_package_anchor'), ('_provider_title', 'content_blocks_5_provider_title'), ('_provider_text', 'content_blocks_5_provider_text'), ('_faq_title', 'content_blocks_12_faq_title'), ('_final_cta_ancor', 'content_blocks_13_final_cta_ancor')):
        if key.endswith(suf): return c[src]
    return None
ok = True
for pid in sorted(x for x in os.listdir('.') if x.isdigit()):
    keys = [k for k in EXP[pid]['fields'] if not k.endswith('_provider_logos')]
    if pid == '4845' and 'content_blocks_6_tariff_items_2_package_anchor' not in keys: keys.append('content_blocks_6_tariff_items_2_package_anchor')
    for fn in sorted(os.listdir(pid)):
        if not fn.endswith('.txt'): continue
        lang = fn[:-4]; p = parse(f'{pid}/{fn}'); f = {}
        for k in keys:
            cv = common(lang, k)
            if cv is not None: f[k] = cv
        for k, v in p.items():
            if not k.startswith('_'): f[k] = v
        sd = [k for k in keys if k.endswith('step_description')][0]
        f[sd] = C['help'][lang] + ' ' + p['_step_desc']
        miss = set(keys) - set(f); extra = set(f) - set(keys)
        t, d, kw = f['rank_math_title'], f['rank_math_description'], f['rank_math_focus_keyword']
        tl = len(t.replace('%currentyear%', '2026')); dl = len(d); bad = []
        if tl > (32 if lang == 'zh' else 60): bad.append(f'title {tl}')
        if not ((45 <= dl <= 85) if lang == 'zh' else (115 <= dl <= 160)): bad.append(f'desc {dl}')
        if kw.lower() not in t.lower(): bad.append('kw')
        blob = ' '.join(f.values())
        if lang != 'uk' and re.search(r'[А-Яа-яЁёІіЇїЄє]', blob): bad.append('cyrillic')
        if B in blob or 'staging' in blob: bad.append('absolute url')
        for tag in ['table', 'ul', 'ol', 'blockquote', 'h2', 'h3', 'b', 'a', 'tr', 'td']:
            if len(re.findall(f'<{tag}[ >]', blob)) != blob.count(f'</{tag}>'): bad.append('unbalanced ' + tag)
        if miss: bad.append('missing ' + ','.join(sorted(miss)))
        if extra: bad.append('extra ' + ','.join(sorted(extra)))
        if bad: ok = False
        print(pid, lang, 'OK' if not bad else bad, '|', p['_title'])
        json.dump({'title': p['_title'], 'slug': p['_slug'], 'fields': f}, open(f'{pid}-{lang}.json', 'w'), ensure_ascii=False, indent=1)
print('ALL OK' if ok else 'ERRORS')
