import json, re, os
os.chdir(os.path.dirname(os.path.abspath(__file__)))
EXP = json.load(open('/tmp/claude-0/-home-claude/a6ca971b-ac50-5087-abb7-98999e1ac54a/scratchpad/svc6/export.json'))
B = 'https://staging.dragonslots-gr.gr'
LANGS = ['en', 'uk', 'de', 'es', 'fr', 'pt', 'tr', 'zh']
PIDS = ['4842', '4843', '4844', '4845', '4846', '4847']

pages = {}
for pid in PIDS:
    langs = {}
    for lang in LANGS:
        d = json.load(open(f'{pid}-{lang}.json'))
        langs[lang] = {'title': d['title'], 'slug': d['slug'], 'fields': d['fields']}
    pages[pid] = {'source_id': int(pid), 'source_slug': EXP[pid]['slug'], 'type': EXP[pid]['type'],
                  'thumb_name': langs['en']['slug'], 'langs': langs}

# ---- RU fixes (applied at runtime, conditionally, so later manual edits survive) ----
# broken paths in RU content -> page id (any language; resolved to RU at runtime); None = unlink, keep text
link_map = {
    '/services/corporate-changes/share-transfer/': 4845,
    '/services/corporate-changes/board-member-change/': 4844,
    '/services/corporate-changes/share-capital-increase/': 4846,
    '/services/corporate-changes/share-capital-decrease/': 4847,
    '/services/corporate-changes/articles-amendment/': None,
    '/services/corporate-changes/ubo-update/': None,
    '/services/liquidation/': 4758,
    '/target/ukrainian-entrepreneurs/': 4757,
    '/industry/startups/': 4529,
    '/accounting/payroll/': 4843,
    '/accounting/switch-accountant/': 4724,
    '/accounting/clean-up/': 4578,
    '/accounting/interim-reports/': 4699,
    '/vnzh-dlya-osnovatelya-kompanii-v-estonii/': 2776,
}
# unlinked list items -> link the leading text
li_links = {
    '4842': [['content_blocks_9_text_right', 'Расчёт зарплаты (payroll)', 4843],
             ['content_blocks_9_text_right', 'Восстановление учёта', 4578]],
    '4843': [['content_blocks_9_text_right', 'VAT-декларации (KMD)', 4842],
             ['content_blocks_9_text_right', 'Бухгалтерия для e-резидентов', 4490],
             ['content_blocks_9_text_right', 'Компания для стартапа', 4529],
             ['content_blocks_9_text_right', 'Восстановление учёта', 4578]],
}
# [field, regex, replacement] — applied only if regex matches
regex_fix = {
    '4842': [['content_blocks_3_content', r'^\s*конфигурацию мы проверяем индивидуально\.\s*', '']],
    '4845': [['content_blocks_6_tariff_items_1_package_items', r'\n(?!<li>)(Уведомления для налоговой)', '\n<li>\\1']],
    '4847': [['content_blocks_0_content', r'(минимальный капитал эстонской OÜ)[\x{00A0} ]+(€0,01)', '\\1 — \\2']],
}
# [field, expected current value or '*' (any, only if empty), new value]
exact_fix = {
    '4843': [
        ['content_blocks_11_faq_items_0_question', 'Часто задаваемые вопросы', 'Какие налоги платятся с зарплаты в Эстонии?'],
        ['rank_math_title', 'Расчёт зарплаты и зарплатных налогов в Эстонии (TSD) для компаний OÜ', 'Расчёт зарплаты в Эстонии: налоги и TSD для OÜ %currentyear%'],
        ['rank_math_focus_keyword', 'Расчёт зарплаты и зарплатных налогов в Эстонии', 'Расчёт зарплаты в Эстонии'],
        ['rank_math_description', EXP['4843']['fields']['rank_math_description'], 'Расчёт зарплаты в Эстонии под ключ: брутто и нетто, налоги работодателя, выплаты члену правления и ежемесячная декларация TSD в EMTA.'],
    ],
    '4844': [['rank_math_description', EXP['4844']['fields']['rank_math_description'], 'Сопровождение смены члена правления OÜ в Эстонии: решение участников, заявление через e-Residency или по доверенности и регистрация в Äriregister.']],
    '4845': [['content_blocks_6_tariff_items_2_package_anchor', '', 'Заказать']],
}
for pid in PIDS:
    pg = pages[pid]
    pg['ru'] = {'li_links': li_links.get(pid, []), 'regex': regex_fix.get(pid, []), 'exact': exact_fix.get(pid, [])}

# ---- menu ----
L = {
 'Company Changes': {'ru': 'Изменения в компании', 'uk': 'Зміни в компанії', 'de': 'Unternehmensänderungen', 'es': 'Cambios en la empresa', 'fr': 'Modifications de société', 'pt': 'Alterações na empresa', 'tr': 'Şirket değişiklikleri', 'zh': '公司变更'},
 'Share Sale and Transfer': {'ru': 'Продажа и передача долей', 'uk': 'Продаж і передача часток', 'de': 'Anteilsverkauf und -übertragung', 'es': 'Venta y transmisión de participaciones', 'fr': 'Vente et cession de parts', 'pt': 'Venda e transmissão de quotas', 'tr': 'Pay satışı ve devri', 'zh': '股权出售与转让'},
 'Change of Board Member': {'ru': 'Смена члена правления', 'uk': 'Зміна члена правління', 'de': 'Wechsel des Vorstands', 'es': 'Cambio de administrador', 'fr': 'Changement de dirigeant', 'pt': 'Mudança de gerente', 'tr': 'Yönetim kurulu değişikliği', 'zh': '董事会成员变更'},
 'Capital Increase': {'ru': 'Увеличение капитала', 'uk': 'Збільшення капіталу', 'de': 'Kapitalerhöhung', 'es': 'Aumento de capital', 'fr': 'Augmentation de capital', 'pt': 'Aumento de capital', 'tr': 'Sermaye artırımı', 'zh': '增资'},
 'Capital Reduction': {'ru': 'Уменьшение капитала', 'uk': 'Зменшення капіталу', 'de': 'Kapitalherabsetzung', 'es': 'Reducción de capital', 'fr': 'Réduction de capital', 'pt': 'Redução de capital', 'tr': 'Sermaye azaltımı', 'zh': '减资'},
 'VAT Returns': {'ru': 'VAT-декларации', 'uk': 'VAT-декларації', 'de': 'Umsatzsteuererklärungen', 'es': 'Declaraciones de IVA', 'fr': 'Déclarations de TVA', 'pt': 'Declarações de IVA', 'tr': 'KDV beyannameleri', 'zh': '增值税申报'},
 'Payroll': {'ru': 'Расчёт зарплаты', 'uk': 'Розрахунок зарплати', 'de': 'Lohnabrechnung', 'es': 'Nóminas', 'fr': 'Paie', 'pt': 'Processamento salarial', 'tr': 'Bordro', 'zh': '工资核算'},
}
labels = {}
for en, m in L.items():
    for lang, t in m.items(): labels.setdefault(lang, {})[en] = t
menu = {'items': [
  {'custom': 'Company Changes', 'label': 'Company Changes', 'parent': {'title': 'Services', 'top': 1}, 'after': {'obj': 4792}},
  {'page': '4845', 'label': 'Share Sale and Transfer', 'parent': {'title': 'Company Changes'}},
  {'page': '4844', 'label': 'Change of Board Member', 'parent': {'title': 'Company Changes'}, 'after': {'page': '4845'}},
  {'page': '4846', 'label': 'Capital Increase', 'parent': {'title': 'Company Changes'}, 'after': {'page': '4844'}},
  {'page': '4847', 'label': 'Capital Reduction', 'parent': {'title': 'Company Changes'}, 'after': {'page': '4846'}},
  {'page': '4842', 'label': 'VAT Returns', 'parent': {'obj': 2746}, 'after': {'obj': 1398}},
  {'page': '4843', 'label': 'Payroll', 'parent': {'obj': 2746}, 'after': {'page': '4842'}},
], 'labels': labels}

out = {'pages': pages, 'link_map': link_map, 'menu': menu, 'fallback': {}, 'alt': {'1037': 844, '844': 1037}}
blob = json.dumps(out, ensure_ascii=False)
assert B not in blob and 'staging' not in blob
json.dump(out, open('../../../wp-content/mu-plugins/lai-data/migrate-20261128.json', 'w'), ensure_ascii=False)
print('size', len(blob), 'L refs:', len(re.findall(r'\{L:\d+\}', blob)))

# menu strings file: append in one-line-per-key style
p = '../../../wp-content/mu-plugins/lai-data/lai-menu-strings.json'
txt = open(p, encoding='utf-8').read()
cur = json.loads(txt)
add = []
for en, m in L.items():
    if en in cur: continue
    row = {k: m[k] for k in ['de', 'es', 'fr', 'pt', 'tr', 'uk', 'zh']}; row['ru'] = m['ru']
    add.append('  ' + json.dumps(en, ensure_ascii=False) + ': ' + json.dumps(row, ensure_ascii=False))
if add:
    txt = txt.rstrip().rstrip('}').rstrip() + ',\n' + ',\n'.join(add) + '\n}\n'
    json.loads(txt)
    open(p, 'w', encoding='utf-8').write(txt)
print('menu strings added', len(add))
