import json, datetime
MID = 'G-QJR5RNWKWQ'
base = {'accountId': '0', 'containerId': '0'}
params = ['form_type', 'form_id', 'lead_source', 'lead_channel', 'contact_method', 'service', 'page_language', 'link_location']
variables, triggers, tags = [], [], []
vid = 9100
def var_dlv(name):
    global vid; vid += 1
    variables.append({**base, 'variableId': str(vid), 'name': f'LAI DLV - {name}', 'type': 'v', 'parameter': [
        {'type': 'INTEGER', 'key': 'dataLayerVersion', 'value': '2'},
        {'type': 'BOOLEAN', 'key': 'setDefaultValue', 'value': 'false'},
        {'type': 'TEMPLATE', 'key': 'name', 'value': name}]})
for p in params: var_dlv(p)
vid += 1
variables.append({**base, 'variableId': str(vid), 'name': 'LAI - GA4 Measurement ID', 'type': 'c', 'parameter': [{'type': 'TEMPLATE', 'key': 'value', 'value': MID}]})
events = {
    'generate_lead': ['form_type', 'form_id', 'lead_source', 'lead_channel', 'contact_method', 'service', 'page_language'],
    'calendly_booked': ['form_type', 'lead_channel', 'page_language'],
    'pipedrive_form_submit': ['form_type', 'lead_channel', 'page_language'],
    'click_phone': ['link_location', 'page_language'],
    'click_email': ['link_location', 'page_language'],
}
tid, gid = 9200, 9300
for ev, ps in events.items():
    tid += 1; gid += 1
    triggers.append({**base, 'triggerId': str(tid), 'name': f'LAI CE - {ev}', 'type': 'CUSTOM_EVENT', 'customEventFilter': [
        {'type': 'EQUALS', 'parameter': [{'type': 'TEMPLATE', 'key': 'arg0', 'value': '{{_event}}'}, {'type': 'TEMPLATE', 'key': 'arg1', 'value': ev}]}]})
    tags.append({**base, 'tagId': str(gid), 'name': f'LAI GA4 Event - {ev}', 'type': 'gaawe', 'parameter': [
        {'type': 'BOOLEAN', 'key': 'sendEcommerceData', 'value': 'false'},
        {'type': 'TEMPLATE', 'key': 'eventName', 'value': ev},
        {'type': 'TEMPLATE', 'key': 'measurementIdOverride', 'value': '{{LAI - GA4 Measurement ID}}'},
        {'type': 'LIST', 'key': 'eventSettingsTable', 'list': [
            {'type': 'MAP', 'map': [{'type': 'TEMPLATE', 'key': 'parameter', 'value': p}, {'type': 'TEMPLATE', 'key': 'parameterValue', 'value': '{{LAI DLV - %s}}' % p}]} for p in ps]}],
        'firingTriggerId': [str(tid)], 'tagFiringOption': 'ONCE_PER_EVENT', 'monitoringMetadata': {'type': 'MAP'},
        'consentSettings': {'consentStatus': 'NOT_SET'}})
out = {'exportFormatVersion': 2, 'exportTime': datetime.datetime.utcnow().strftime('%Y-%m-%d %H:%M:%S'),
       'containerVersion': {'path': 'accounts/0/containers/0/versions/0', **base, 'containerVersionId': '0',
           'container': {'path': 'accounts/0/containers/0', **base, 'name': 'LAI lead tracking', 'publicId': 'GTM-5Z4N9J7T', 'usageContext': ['WEB']},
           'tag': tags, 'trigger': triggers, 'variable': variables, 'fingerprint': '0'}}
json.dump(out, open('LAI-GTM-lead-tracking.json', 'w'), indent=2, ensure_ascii=False)
print(len(tags), 'tags', len(triggers), 'triggers', len(variables), 'variables')
