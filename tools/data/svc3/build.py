import json, re, sys, os
B = 'https://staging.dragonslots-gr.gr'
C = json.load(open('common.json'))
EXP = json.load(open('/tmp/claude-0/-home-claude/a6ca971b-ac50-5087-abb7-98999e1ac54a/scratchpad/svc3/export.json'))
SKIP = {'content_blocks_5_provider_logos'}
def parse(path):
    d, k = {}, None
    for line in open(path, encoding='utf-8').read().split('\n'):
        m = re.match(r'^## (\S+)\s*$', line)
        if m: k = m.group(1); d[k] = []; continue
        if k: d[k].append(line)
    return {k: '\n'.join(v).strip('\n') for k, v in d.items()}
ok = True
for pid in sorted(os.listdir('.')):
    if not pid.isdigit(): continue
    need = set(EXP[pid]['fields']) - SKIP
    for fn in sorted(os.listdir(pid)):
        if not fn.endswith('.txt'): continue
        lang = fn[:-4]; p = parse(f'{pid}/{fn}')
        f = {k: v for k, v in C[lang].items()}
        for k, v in p.items():
            if k.startswith('_'): continue
            f[k] = re.sub(r'\{L:(\d+)\}', lambda m: f'{B}/?p={m.group(1)}', v)
        f['content_blocks_4_step_description'] = C['help'][lang] + ' ' + p['_step_desc']
        miss = need - set(f); extra = set(f) - need
        t, d, kw = f['rank_math_title'], f['rank_math_description'], f['rank_math_focus_keyword']
        tl = len(t.replace('%currentyear%', '2026')); dl = len(d)
        bad = []
        if tl > (32 if lang == 'zh' else 60): bad.append(f'title {tl}')
        if not ((45 <= dl <= 85) if lang == 'zh' else (115 <= dl <= 160)): bad.append(f'desc {dl}')
        if kw.lower() not in t.lower(): bad.append('kw')
        blob = ' '.join(f.values())
        if lang != 'uk' and re.search(r'[А-Яа-яЁёІіЇїЄє]', blob): bad.append('cyrillic')
        for tag in ['table', 'ul', 'ol', 'blockquote', 'h2', 'h3', 'b', 'a', 'tr', 'td']:
            if len(re.findall(f'<{tag}[ >]', blob)) != blob.count(f'</{tag}>'): bad.append('unbalanced ' + tag)
        if miss: bad.append('missing ' + ','.join(sorted(miss)))
        if extra: bad.append('extra ' + ','.join(sorted(extra)))
        if bad: ok = False
        print(pid, lang, 'OK' if not bad else bad, '|', p['_title'])
        json.dump({'source_id': int(pid), 'lang': lang, 'title': p['_title'], 'slug': p['_slug'], 'fields': f}, open(f'{pid}-{lang}.json', 'w'), ensure_ascii=False, indent=1)
print('ALL OK' if ok else 'ERRORS')
