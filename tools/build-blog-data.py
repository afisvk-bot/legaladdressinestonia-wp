#!/usr/bin/env python3
"""build-blog-data.py <source_id> — собирает tools/data/blog-<id>.json из tools/data/b<id>/<lang>.html + meta.json"""
import json,sys,os
sid=sys.argv[1]; D=f'/home/claude/legaladdressinestonia-wp/tools/data/b{sid}/'
meta=json.load(open(D+'meta.json')); out={'source_id':int(sid),'langs':{}}
for l,m in meta.items():
    if not os.path.exists(D+l+'.html'): continue
    z=l=='zh'; rt,rd,rk=m['rm']
    assert len(rt)<=(32 if z else 60),(l,'title',len(rt)); assert (45 if z else 115)<=len(rd)<=(85 if z else 160),(l,'desc',len(rd)); assert rk.lower() in rt.lower() or rk in rt,(l,'kw')
    out['langs'][l]={'title':m['title'],'slug':m['slug'],'content':open(D+l+'.html').read(),'rm':{'title':rt,'desc':rd,'kw':rk}}
json.dump(out,open(f'/home/claude/legaladdressinestonia-wp/tools/data/blog-{sid}.json','w'),ensure_ascii=False)
print('built',sid,sorted(out['langs']))
