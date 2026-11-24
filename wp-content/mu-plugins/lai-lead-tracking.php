<?php
/**
 * Plugin Name: LAI — Lead tracking (события лидов в dataLayer для GTM / GA4)
 * Description: Передаёт в dataLayer события: generate_lead (успешная отправка любой формы Contact Form 7), calendly_booked (запись в Calendly), pipedrive_form_submit (форма Pipedrive с переадресацией после отправки), click_phone, click_email. Параметры: form_type, form_id, lead_source, lead_channel, contact_method, page_language, link_location. Сами теги GA4 настраиваются в GTM (контейнер GTM-5Z4N9J7T); согласие на cookies учитывает GTM.
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

add_action( 'wp_footer', function () {
	if ( is_admin() ) return;
	$bc   = function_exists( 'lai_bc_form_id' ) ? lai_bc_form_id() : 0;
	$lang = function_exists( 'pll_current_language' ) ? (string) pll_current_language() : '';
	?>
<script id="lai-lead-tracking" data-no-optimize="1" data-no-defer="1">
(function(){
 var BC=<?php echo (int) $bc; ?>, LANG=<?php echo wp_json_encode( $lang ?: 'en' ); ?>;
 window.dataLayer=window.dataLayer||[];
 function push(ev,p){ var o={event:ev,page_language:LANG,page_path:location.pathname}; for(var k in p){ if(p[k]!==undefined&&p[k]!=='') o[k]=p[k]; } window.dataLayer.push(o); }
 function src(){ try{ return JSON.parse(sessionStorage.getItem('lai_src')||'{}'); }catch(e){ return {}; } }

 /* 1. Формы сайта (Contact Form 7): только успешная отправка */
 document.addEventListener('wpcf7mailsent',function(e){
  var d=e.detail||{}, v={}; (d.inputs||[]).forEach(function(i){ v[i.name]=i.value; });
  var id=parseInt(d.contactFormId,10)||0, s=src();
  push('generate_lead',{
   form_id:String(id),
   form_type: id===BC?'book_a_call':(document.querySelector('#'+(d.unitTag||'x'))&&document.querySelector('#'+d.unitTag).closest('.modal,[class*=modal]')?'consultation':'contact_form'),
   lead_source:v['lead-source']||'(not answered)',
   lead_channel:v['lead-channel']||s.c||'',
   contact_method:v['contact-method']||'',
   service:v['menu-212']||''
  });
 });

 /* 2. Calendly: запись на звонок */
 window.addEventListener('message',function(e){
  if(!/^https:\/\/([a-z0-9-]+\.)?calendly\.com$/.test(e.origin||'')) return;
  var ev=e.data&&e.data.event; if(!ev) return;
  if(ev==='calendly.event_scheduled') push('calendly_booked',{lead_channel:src().c||'',form_type:'calendly'});
 });

 /* 3. Формы Pipedrive: при включённой переадресации после отправки виджет присылает REDIRECT (type 6) */
 var PD_KEY='lai_pd_sent';
 window.addEventListener('message',function(e){
  if(!/^https:\/\/([a-z0-9-]+\.)*(pipedrive\.com|pipedrivewebforms\.com)$/.test(e.origin||'')) return;
  var d=e.data; if(!d||typeof d!=='object'||d.type!==6) return;
  try{ sessionStorage.setItem(PD_KEY,String(Date.now())); }catch(x){}
  push('pipedrive_form_submit',{form_type:'pipedrive',lead_channel:src().c||'',redirect_url:(d.payload&&d.payload.url||'').slice(0,200)});
 });
 // страница «спасибо» после формы Pipedrive: ?lai_lead=pd (если событие не ушло до переадресации)
 if(/[?&]lai_lead=pd\b/.test(location.search)){
  var t=0; try{ t=+sessionStorage.getItem(PD_KEY)||0; sessionStorage.removeItem(PD_KEY);}catch(x){}
  if(!t||Date.now()-t>60000) push('pipedrive_form_submit',{form_type:'pipedrive',lead_channel:src().c||'',via:'thank_you_url'});
 }

 /* 4. Клики по телефону и почте */
 document.addEventListener('click',function(e){
  var a=e.target.closest&&e.target.closest('a[href^="tel:"],a[href^="mailto:"]'); if(!a) return;
  var loc=a.closest('header')?'header':a.closest('footer')?'footer':a.closest('.mm-menu,.lai-mmc,[class*="mobile-menu"]')?'mobile_menu':a.closest('#book-a-call')?'book_a_call':'content';
  var tel=/^tel:/.test(a.getAttribute('href'));
  push(tel?'click_phone':'click_email',{link_location:loc,link_text:(a.textContent||'').trim().slice(0,60)});
 },true);
})();
</script>
	<?php
}, 70 );
