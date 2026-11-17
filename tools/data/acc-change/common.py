"""Helpers for the 'Change of accountant' (RU #3627) translations → import-acf-translation JSON."""
B = 'https://staging.dragonslots-gr.gr'
LINKS = {
    'monthly': B + '/monthly-accounting-in-estonia/',
    'eres': B + '/accounting-e-residents/',
    'restore': B + '/accounting-restoration-in-estonia/',
    'interim': B + '/interim-reports/',
    'annual': B + '/annual-report-for-estonian-companies/',
    'tax': B + '/tax-consulting-in-estonia/',
    'blog': B + '/blog/how-to-choose-an-accountant-in-estonia/',
}


def table(h3, head, rows):
    out = [f'<h3>{h3}</h3>', '<table>', '<tbody>', f'<tr>\n<td><b>{head[0]}</b></td>\n<td><b>{head[1]}</b></td>\n</tr>']
    for a, b in rows:
        out.append(f'<tr>\n<td>{a}</td>\n<td>{b}</td>\n</tr>')
    out += ['</tbody>', '</table>']
    return '\n'.join(out)


def bullets(intro, items, outro=None):
    s = f'<p>{intro}</p>\n<ul>\n' + '\n'.join(f' \t<li><b>{a}</b> {b}</li>' for a, b in items) + '\n</ul>'
    if outro:
        s += f'\n<p>{outro}</p>'
    return s


def related(items, art_text, art_anchor):
    lis = '\n'.join(f' \t<li><a href="{LINKS[k]}"><b>{name}</b></a> — {why}</li>' for k, name, why in items)
    return f'<ul>\n{lis}\n</ul>\n<p class="lai-art">{art_text} <a href="{LINKS["blog"]}">{art_anchor}</a>.</p>'


def practice(h2, h_check, check_head, check, h_access, access, h_when, when, h_noreply, noreply, h_dur, dur_head, dur):
    t1 = table(h_check, check_head, check)
    t2 = table(h_dur, dur_head, dur)
    acc = '<ol>\n' + '\n'.join(f' \t<li>{a}</li>' for a in access) + '\n</ol>'
    return '\n'.join([f'<h2>{h2}</h2>', t1, f'<h3>{h_access}</h3>', acc, f'<h3>{h_when}</h3>', f'<p>{when}</p>',
                      f'<h3>{h_noreply}</h3>', f'<p>{noreply}</p>', t2])


def li(items):
    return '\n'.join(f'<li>{x}</li>' for x in items)


def fields(T):
    p = 'content_blocks_'
    f = {
        p + '0_content': T['c0'], p + '1_anchor': T['a1'], p + '2_anchor': T['a2'], p + '3_content': T['c3'],
        p + '4_steps_title': T['steps_title'], p + '4_step_1': T['s1'], p + '4_step_2': T['s2'], p + '4_step_3': T['s3'],
        p + '4_step_4': T['s4'], p + '4_step_description': T['s_desc'],
        p + '5_provider_title': T['prov_t'], p + '5_provider_text': T['prov_x'],
        p + '6_tariff_title': T['tariff_title'],
        p + '6_tariff_items_2_package_star': T['star'],
        p + '7_title_right': T['b7_t'], p + '7_text_right': T['b7_x'],
        p + '8_title_left': T['b8_t'], p + '8_text_left': T['b8_x'],
        p + '9_title_right': T['b9_t'], p + '9_text_right': T['b9_x'],
        p + '10_title_left': T['b10_t'], p + '10_text_left': T['b10_x'],
        p + '11_content': T['c11'], p + '12_faq_title': T['faq_title'],
        p + '13_final_cta_title': T['cta_t'], p + '13_final_cta_text': T['cta_x'], p + '13_final_cta_ancor': T['order'],
        'rank_math_title': T['rm'][0], 'rank_math_description': T['rm'][1], 'rank_math_focus_keyword': T['rm'][2],
    }
    for i in range(3):
        f[p + f'6_tariff_items_{i}_name'] = T[f't{i}']
        f[p + f'6_tariff_items_{i}_price_from'] = T['from_']
        f[p + f'6_tariff_items_{i}_package_items'] = li(T[f't{i}_items'])
        f[p + f'6_tariff_items_{i}_package_anchor'] = T['order']
    for i, (q, a) in enumerate(T['faq']):
        f[p + f'12_faq_items_{i}_question'] = q
        f[p + f'12_faq_items_{i}_answer'] = a
    assert len(T['faq']) == 9
    return f
