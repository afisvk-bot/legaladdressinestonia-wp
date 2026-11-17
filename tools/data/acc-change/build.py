import importlib, json, sys, re
sys.path.insert(0, '.')
from common import fields
out = {}
for lang in ['en', 'uk', 'de', 'es', 'fr', 'pt', 'tr', 'zh']:
    T = importlib.import_module(lang).T
    t, d, kw = T['rm']
    tl, dl = len(t), len(d)
    ok_t = tl <= (32 if lang == 'zh' else 60)
    ok_d = (45 <= dl <= 85) if lang == 'zh' else (115 <= dl <= 160)
    ok_k = kw.lower() in t.lower()
    print(f'{lang}: title {tl} {"OK" if ok_t else "BAD"} | desc {dl} {"OK" if ok_d else "BAD"} | kw {"OK" if ok_k else "BAD"} | {T["title"]}')
    f = fields(T)
    # sanity: no Cyrillic in non-uk, balanced tags
    blob = ' '.join(f.values())
    if lang not in ('uk',) and re.search(r'[А-Яа-яЁё]', blob): print('  !! cyrillic in', lang)
    for tag in ['table', 'ul', 'ol', 'blockquote', 'p']:
        if blob.count(f'<{tag}>') + blob.count(f'<{tag} ') != blob.count(f'</{tag}>'): print('  !! unbalanced', tag)
    out[lang] = {'source_id': 3627, 'lang': lang, 'title': T['title'], 'slug': T['slug'], 'fields': f}
    json.dump(out[lang], open(f'../acc-change-{lang}.json', 'w'), ensure_ascii=False, indent=1)
json.dump(out, open('../acc-change-all.json', 'w'), ensure_ascii=False)
print('fields per lang:', {k: len(v['fields']) for k, v in out.items()})
