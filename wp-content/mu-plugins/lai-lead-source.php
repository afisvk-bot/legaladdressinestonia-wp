<?php
/**
 * Plugin Name: LAI — Lead source («Откуда вы о нас узнали?» + автоматические метки)
 * Description: В формы консультации form_EN…form_CN добавляется необязательный список «Откуда вы о нас узнали?» и скрытые поля: канал (Google, ChatGPT, LinkedIn…), реферер, UTM-метки, первая страница визита. Метки собираются скриптом в sessionStorage (только на время визита, без cookies) и попадают в письмо и Contact Form Entries. Формы обновляются автоматически при заходе в админку (один раз, повторно не меняются). Форма «Book a call» получает те же поля из lai-book-call.php.
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

const LAI_LS_VER = '1';

/** Варианты ответа: значение (уходит в письмо, на английском) => подписи по языкам. */
function lai_ls_options(): array {
	return array(
		'Google search'                   => array( 'ru' => 'Поиск Google', 'uk' => 'Пошук Google', 'de' => 'Google-Suche', 'es' => 'Búsqueda en Google', 'fr' => 'Recherche Google', 'pt' => 'Pesquisa no Google', 'tr' => 'Google araması', 'zh' => 'Google搜索' ),
		'ChatGPT / AI assistant'          => array( 'ru' => 'ChatGPT / ИИ-ассистент', 'uk' => 'ChatGPT / ШІ-асистент', 'de' => 'ChatGPT / KI-Assistent', 'es' => 'ChatGPT / asistente de IA', 'fr' => 'ChatGPT / assistant IA', 'pt' => 'ChatGPT / assistente de IA', 'tr' => 'ChatGPT / yapay zekâ asistanı', 'zh' => 'ChatGPT / AI助手' ),
		'Recommendation'                  => array( 'ru' => 'Рекомендация', 'uk' => 'Рекомендація', 'de' => 'Empfehlung', 'es' => 'Recomendación', 'fr' => 'Recommandation', 'pt' => 'Recomendação', 'tr' => 'Tavsiye', 'zh' => '他人推荐' ),
		'LinkedIn / Facebook / Instagram' => array(),
		'YouTube / TikTok'                => array(),
		'e-Residency Marketplace'         => array( 'ru' => 'Маркетплейс e-Residency', 'uk' => 'Маркетплейс e-Residency', 'es' => 'Marketplace de e-Residency', 'fr' => 'Marketplace e-Residency', 'pt' => 'Marketplace e-Residency', 'tr' => 'e-Residency Pazaryeri', 'zh' => 'e-Residency市场' ),
		'Trustpilot / reviews'            => array( 'ru' => 'Trustpilot / отзывы', 'uk' => 'Trustpilot / відгуки', 'de' => 'Trustpilot / Bewertungen', 'es' => 'Trustpilot / reseñas', 'fr' => 'Trustpilot / avis', 'pt' => 'Trustpilot / avaliações', 'tr' => 'Trustpilot / yorumlar', 'zh' => 'Trustpilot / 评价' ),
		'Other'                           => array( 'ru' => 'Другое', 'uk' => 'Інше', 'de' => 'Sonstiges', 'es' => 'Otro', 'fr' => 'Autre', 'pt' => 'Outro', 'tr' => 'Diğer', 'zh' => '其他' ),
	);
}

function lai_ls_text( string $key, string $lang ): string {
	$T = array(
		'q'   => array( 'en' => 'How did you hear about us?', 'ru' => 'Откуда вы о нас узнали?', 'uk' => 'Звідки ви про нас дізналися?', 'de' => 'Wie haben Sie von uns erfahren?', 'es' => '¿Cómo nos conoció?', 'fr' => 'Comment nous avez-vous connus ?', 'pt' => 'Como nos conheceu?', 'tr' => 'Bizi nereden duydunuz?', 'zh' => '您是如何得知我们的？' ),
		'opt' => array( 'en' => '(optional)', 'ru' => '(необязательно)', 'uk' => '(необов’язково)', 'de' => '(optional)', 'es' => '(opcional)', 'fr' => '(facultatif)', 'pt' => '(opcional)', 'tr' => '(isteğe bağlı)', 'zh' => '（选填）' ),
		'sel' => array( 'en' => 'Select an option', 'ru' => 'Выберите вариант', 'uk' => 'Оберіть варіант', 'de' => 'Bitte auswählen', 'es' => 'Seleccione una opción', 'fr' => 'Sélectionnez une option', 'pt' => 'Selecione uma opção', 'tr' => 'Bir seçenek belirleyin', 'zh' => '请选择' ),
	);
	return $T[ $key ][ $lang ] ?? $T[ $key ]['en'];
}

/** CF7-теги для формы на конкретном языке (подпись|значение — в письмо уходит английское значение). */
function lai_ls_select_tag( string $lang, string $first_label ): string {
	$opts = array( '"' . str_replace( '"', '', $first_label ) . '"' );
	foreach ( lai_ls_options() as $val => $tr ) {
		$label  = $tr[ $lang ] ?? $val;
		$opts[] = $label === $val ? '"' . $val . '"' : '"' . $label . '|' . $val . '"';
	}
	return '[select lead-source first_as_label ' . implode( ' ', $opts ) . ']';
}
function lai_ls_hidden_tags(): string {
	return '[hidden lead-channel class:lai-ls-channel][hidden lead-referrer class:lai-ls-referrer][hidden lead-utm class:lai-ls-utm][hidden lead-landing class:lai-ls-landing]';
}
function lai_ls_mail_block( string $lang_of_body = 'ru' ): string {
	if ( 'en' === $lang_of_body ) return "\nHow did you hear about us: [lead-source]\nChannel (auto): [lead-channel]\nReferrer: [lead-referrer]\nUTM: [lead-utm]\nLanding page: [lead-landing]\nForm page: [_url]\n";
	return "\nОткуда узнали (ответ клиента): [lead-source]\nКанал (авто): [lead-channel]\nРеферер: [lead-referrer]\nUTM: [lead-utm]\nПервая страница визита: [lead-landing]\nСтраница заявки: [_url]\n";
}

/** Один раз добавить поля в формы консультации form_XX. */
function lai_ls_update_forms(): array {
	$langs = array( 'form_EN' => 'en', 'form_RU' => 'ru', 'form_UK' => 'uk', 'form_DE' => 'de', 'form_ES' => 'es', 'form_FR' => 'fr', 'form_PT' => 'pt', 'form_TR' => 'tr', 'form_CN' => 'zh' );
	$log   = array();
	foreach ( $langs as $title => $lang ) {
		$p = get_posts( array( 'post_type' => 'wpcf7_contact_form', 'title' => $title, 'numberposts' => 1, 'lang' => '', 'post_status' => 'publish' ) );
		if ( ! $p ) { $log[] = "$title: not found"; continue; }
		$id   = $p[0]->ID;
		$form = (string) get_post_meta( $id, '_form', true );
		if ( false !== strpos( $form, 'lead-source' ) ) { $log[] = "$title #$id: already has fields"; continue; }
		$add = lai_ls_select_tag( $lang, lai_ls_text( 'q', $lang ) . ' ' . lai_ls_text( 'opt', $lang ) ) . "\n" . lai_ls_hidden_tags() . "\n";
		// после списка услуг (menu-212); если его нет — перед блоком согласия / кнопкой
		if ( preg_match( '/\[select\*? menu-212[^\]]*\]\s*\n?/u', $form, $m, PREG_OFFSET_CAPTURE ) ) {
			$pos  = $m[0][1] + strlen( $m[0][0] );
			$form = substr( $form, 0, $pos ) . ( "\n" === substr( $m[0][0], -1 ) ? '' : "\n" ) . $add . substr( $form, $pos );
		} else {
			$pos  = strpos( $form, '<div class="politika' );
			$pos  = false === $pos ? strpos( $form, '[submit' ) : $pos;
			$form = substr( $form, 0, (int) $pos ) . $add . substr( $form, (int) $pos );
		}
		update_post_meta( $id, '_form', $form );
		$mail = (array) get_post_meta( $id, '_mail', true );
		if ( isset( $mail['body'] ) && false === strpos( $mail['body'], '[lead-source]' ) ) {
			$mail['body'] = preg_replace( '/\n--\s*\n/u', lai_ls_mail_block( 'ru' ) . "\n-- \n", $mail['body'], 1, $n );
			if ( ! $n ) $mail['body'] .= "\n" . lai_ls_mail_block( 'ru' );
			update_post_meta( $id, '_mail', $mail );
		}
		$log[] = "$title #$id: fields added";
	}
	return $log;
}
add_action( 'admin_init', function () {
	if ( LAI_LS_VER === get_option( 'lai_ls_forms_ver' ) || ! class_exists( 'WPCF7_ContactForm' ) ) return;
	update_option( 'lai_ls_forms_log', lai_ls_update_forms(), false );
	update_option( 'lai_ls_forms_ver', LAI_LS_VER, false );
} );

/* ---------- Скрипт: определение источника визита ---------- */
add_action( 'wp_footer', function () {
	?>
<script id="lai-lead-source">
(function(){
 var K='lai_src', host=location.hostname.replace(/^www\./,'');
 function chan(ref,q){
  var s=(q.get('utm_source')||'').toLowerCase(), m=(q.get('utm_medium')||'').toLowerCase();
  var ai=[[/chatgpt|openai/,'AI: ChatGPT'],[/perplexity/,'AI: Perplexity'],[/gemini|bard/,'AI: Gemini'],[/copilot/,'AI: Copilot'],[/claude|anthropic/,'AI: Claude'],[/deepseek/,'AI: DeepSeek'],[/grok/,'AI: Grok'],[/you\.com|phind/,'AI: other']];
  var i; for(i=0;i<ai.length;i++){ if(ai[i][0].test(s)||ai[i][0].test(ref)) return ai[i][1]; }
  if(q.get('gclid')||q.get('gbraid')||q.get('wbraid')) return 'Ads: Google';
  if(q.get('fbclid')&&/cpc|paid|ads/.test(m)) return 'Ads: Meta';
  if(s) return 'UTM: '+s+(m?' / '+m:'');
  if(!ref) return 'Direct';
  var r=[[/(^|\.)google\./,'Search: Google'],[/bing\.com/,'Search: Bing'],[/yandex\./,'Search: Yandex'],[/duckduckgo/,'Search: DuckDuckGo'],[/yahoo\./,'Search: Yahoo'],[/ecosia|brave\.com|startpage/,'Search: other'],
         [/linkedin\.com|lnkd\.in/,'Social: LinkedIn'],[/facebook\.com|fb\.com|m\.facebook/,'Social: Facebook'],[/instagram\.com/,'Social: Instagram'],[/(^|\.)t\.co$|twitter\.com|(^|\.)x\.com/,'Social: X'],[/youtube\.com|youtu\.be/,'Social: YouTube'],[/tiktok\.com/,'Social: TikTok'],[/reddit\.com/,'Social: Reddit'],[/t\.me|telegram/,'Social: Telegram'],
         [/e-resident\.gov\.ee|eresident|e-residency/,'e-Residency'],[/trustpilot/,'Reviews: Trustpilot']];
  for(i=0;i<r.length;i++){ if(r[i][0].test(ref)) return r[i][1]; }
  return 'Referral: '+ref;
 }
 var d=null; try{ d=JSON.parse(sessionStorage.getItem(K)||'null'); }catch(e){}
 if(!d){
  var q=new URLSearchParams(location.search), ref='';
  try{ ref=document.referrer?new URL(document.referrer).hostname.replace(/^www\./,''):''; }catch(e){}
  var internal=ref&&(ref===host||ref.indexOf('dragonslots')>-1||ref.indexOf('legaladdressinestonia')>-1);
  var utm=['utm_source','utm_medium','utm_campaign','utm_term','utm_content'].map(function(k){return q.get(k)?k.replace('utm_','')+'='+q.get(k):'';}).filter(Boolean).join('; ');
  d={c: internal?'Internal (new session)':chan(ref,q), r: internal?'':(document.referrer||'').slice(0,300), u: utm.slice(0,300), l: (location.pathname+location.search).slice(0,300)};
  try{ sessionStorage.setItem(K,JSON.stringify(d)); }catch(e){}
 }
 function fill(root){ (root||document).querySelectorAll('form.wpcf7-form').forEach(function(f){
  var set=function(n,v){ var i=f.querySelector('input[name="'+n+'"]'); if(i) i.value=v||''; };
  set('lead-channel',d.c); set('lead-referrer',d.r); set('lead-utm',d.u); set('lead-landing',d.l); }); }
 fill(); document.addEventListener('submit',function(){ fill(); },true);
 document.addEventListener('wpcf7submit',function(){ setTimeout(fill,50); });
})();
</script>
	<?php
}, 60 );
