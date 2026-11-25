import json, re, sys, os
sys.path.insert(0, os.path.dirname(__file__))
os.chdir(os.path.dirname(os.path.abspath(__file__)))
import importlib.util
spec = importlib.util.spec_from_file_location('b', 'build.py')
EXP = json.load(open('/tmp/claude-0/-home-claude/a6ca971b-ac50-5087-abb7-98999e1ac54a/scratchpad/svc3/export.json'))
B = 'https://staging.dragonslots-gr.gr'
pages = {}
for pid, typ in (('4758', 'service'), ('4749', 'service'), ('4757', 'target')):
    langs = {}
    for lang in ['en', 'uk', 'de', 'es', 'fr', 'pt', 'tr', 'zh']:
        d = json.load(open(f'{pid}-{lang}.json'))
        f = {}
        for k, v in d['fields'].items():
            v = re.sub(re.escape(B) + r'/\?p=(\d+)', r'{L:\1}', v)
            v = v.replace(B + '/', '/')
            f[k] = v
        langs[lang] = {'title': d['title'], 'slug': d['slug'], 'fields': f}
    pages[pid] = {'source_id': int(pid), 'source_slug': EXP[pid]['slug'], 'type': typ, 'langs': langs}
pages['4757']['ru_fix'] = {'rank_math_title': [' (70 символов)', ''], 'content_blocks_6_tariff_items_2_package_items': ['VAT с услуг из Украины:', '<li>VAT с услуг из Украины:']}
L = {
 'Company Liquidation': {'ru': 'Ликвидация компаний', 'uk': 'Ліквідація компаній', 'de': 'Firmenliquidation', 'es': 'Liquidación de empresas', 'fr': 'Liquidation de société', 'pt': 'Liquidação de empresas', 'tr': 'Şirket tasfiyesi', 'zh': '公司清算'},
 'Trademark Registration': {'ru': 'Регистрация товарного знака', 'uk': 'Реєстрація торговельної марки', 'de': 'Markenanmeldung', 'es': 'Registro de marcas', 'fr': 'Dépôt de marque', 'pt': 'Registo de marcas', 'tr': 'Marka tescili', 'zh': '商标注册'},
 'Ukrainian Entrepreneurs': {'ru': 'Предприниматели из Украины', 'uk': 'Підприємці з України', 'de': 'Unternehmer aus der Ukraine', 'es': 'Emprendedores de Ucrania', 'fr': 'Entrepreneurs ukrainiens', 'pt': 'Empreendedores da Ucrânia', 'tr': 'Ukraynalı girişimciler', 'zh': '乌克兰企业家'},
}
labels = {}
for en, m in L.items():
    for lang, t in m.items(): labels.setdefault(lang, {})[en] = t
menu = {'items': [
  {'page': '4758', 'label': 'Company Liquidation', 'parent': {'title': 'Services', 'top': 1}, 'after': {'obj': 2263}},
  {'page': '4749', 'label': 'Trademark Registration', 'parent': {'title': 'Services', 'top': 1}, 'after': {'title': 'Registration Numbers'}},
  {'page': '4757', 'label': 'Ukrainian Entrepreneurs', 'parent': {'title': 'Who We Serve', 'top': 1}, 'after': {'obj': 2649}},
], 'labels': labels}
fallback = {'kak-poluchit-e-residency': 2247, 'korporativnyj-nalog': 2761, 'kak-otkryt-bankovskij': 2263, 'kompaniya-dlya-osnovatelej-iz-za-predelov-es': 2250}
out = {'pages': pages, 'menu': menu, 'fallback': fallback}
json.dump(out, open('../../../wp-content/mu-plugins/lai-data/migrate-20261127.json', 'w'), ensure_ascii=False)
blob = json.dumps(out, ensure_ascii=False)
print('size', len(blob), 'staging host left:', B in blob, 'L refs:', len(re.findall(r'\{L:\d+\}', blob)))
