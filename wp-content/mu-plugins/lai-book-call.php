<?php
/**
 * Plugin Name: LAI — Book a call (контакты в шапке/футере + форма заявки на звонок)
 * Description: Телефон и почта в шапке, мобильной шапке, мобильном меню и футере; ссылка «Book a call» открывает окно с формой (Contact Form 7 «Book a call»): имя, способ связи (Phone / Email / Telegram / WhatsApp), контакт, сообщение. Одна форма на все 9 языков: подписи переводятся при выводе. Форма создаётся автоматически при первом заходе в админку. Заявки: письмо на адрес из form_EN + Contact Form Entries (+ Pipedrive, если настроить связь для формы «Book a call»).
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

const LAI_BC_PHONE      = '+372 5626 2629';
const LAI_BC_TEL        = '+37256262629';
const LAI_BC_MAIL       = 'info@legaladdressinestonia.com';
const LAI_BC_FORM_TITLE = 'Book a call';
const LAI_BC_METHODS    = array( 'Phone', 'Email', 'Telegram', 'WhatsApp' );

function lai_bc_lang(): string {
	$l = function_exists( 'pll_current_language' ) ? (string) pll_current_language() : '';
	return $l ?: 'en';
}

function lai_bc_t( string $key ): string {
	static $T = array(
		'book'  => array( 'en' => 'Book a call', 'ru' => 'Заказать звонок', 'uk' => 'Замовити дзвінок', 'de' => 'Rückruf anfordern', 'es' => 'Solicitar llamada', 'fr' => 'Être rappelé', 'pt' => 'Pedir chamada', 'tr' => 'Arama talep et', 'zh' => '预约通话' ),
		'hours' => array( 'en' => 'Mon–Fri 9:00–17:00', 'ru' => 'Пн–Пт 9:00–17:00', 'uk' => 'Пн–Пт 9:00–17:00', 'de' => 'Mo–Fr 9:00–17:00', 'es' => 'Lun–Vie 9:00–17:00', 'fr' => 'Lun–Ven 9h00–17h00', 'pt' => 'Seg–Sex 9:00–17:00', 'tr' => 'Pzt–Cum 9:00–17:00', 'zh' => '周一至周五 9:00–17:00' ),
		'sub'   => array( 'en' => 'We will contact you within 1 working day · Mon–Fri 9:00–17:00 (Tallinn time)', 'ru' => 'Свяжемся с вами в течение рабочего дня · Пн–Пт 9:00–17:00 (по Таллину)', 'uk' => 'Зв’яжемося з вами протягом робочого дня · Пн–Пт 9:00–17:00 (за Таллінном)', 'de' => 'Wir melden uns innerhalb eines Werktages · Mo–Fr 9:00–17:00 (Tallinner Zeit)', 'es' => 'Le contactaremos en un día laborable · Lun–Vie 9:00–17:00 (hora de Tallin)', 'fr' => 'Nous vous contactons sous un jour ouvré · Lun–Ven 9h00–17h00 (heure de Tallinn)', 'pt' => 'Contactamos num dia útil · Seg–Sex 9:00–17:00 (hora de Tallinn)', 'tr' => 'Bir iş günü içinde dönüş yapıyoruz · Pzt–Cum 9:00–17:00 (Tallinn saati)', 'zh' => '我们将在一个工作日内与您联系 · 周一至周五 9:00–17:00（塔林时间）' ),
		'name'    => array( 'en' => 'Your name', 'ru' => 'Ваше имя', 'uk' => 'Ваше ім’я', 'de' => 'Ihr Name', 'es' => 'Su nombre', 'fr' => 'Votre nom', 'pt' => 'O seu nome', 'tr' => 'Adınız', 'zh' => '您的姓名' ),
		'name_ph' => array( 'en' => 'or company name', 'ru' => 'или название компании', 'uk' => 'або назва компанії', 'de' => 'oder Firmenname', 'es' => 'o nombre de la empresa', 'fr' => 'ou nom de la société', 'pt' => 'ou nome da empresa', 'tr' => 'veya şirket adı', 'zh' => '或公司名称' ),
		'method'  => array( 'en' => 'Contact method', 'ru' => 'Способ связи', 'uk' => 'Спосіб зв’язку', 'de' => 'Kontaktweg', 'es' => 'Forma de contacto', 'fr' => 'Moyen de contact', 'pt' => 'Forma de contacto', 'tr' => 'İletişim yöntemi', 'zh' => '联系方式' ),
		'Phone'   => array( 'en' => 'Phone', 'ru' => 'Телефон', 'uk' => 'Телефон', 'de' => 'Telefon', 'es' => 'Teléfono', 'fr' => 'Téléphone', 'pt' => 'Telefone', 'tr' => 'Telefon', 'zh' => '电话' ),
		'Email'   => array( 'en' => 'Email', 'ru' => 'Email', 'uk' => 'Email', 'de' => 'E-Mail', 'es' => 'Email', 'fr' => 'E-mail', 'pt' => 'E-mail', 'tr' => 'E-posta', 'zh' => '邮箱' ),
		'Telegram' => array( 'en' => 'Telegram' ),
		'WhatsApp' => array( 'en' => 'WhatsApp' ),
		'ph_tg'   => array( 'en' => '@username or phone', 'ru' => '@username или телефон', 'uk' => '@username або телефон', 'de' => '@Benutzername oder Telefon', 'es' => '@usuario o teléfono', 'fr' => '@identifiant ou téléphone', 'pt' => '@utilizador ou telefone', 'tr' => '@kullanıcı adı veya telefon', 'zh' => '@用户名或电话' ),
		'msg'     => array( 'en' => 'Message', 'ru' => 'Сообщение', 'uk' => 'Повідомлення', 'de' => 'Nachricht', 'es' => 'Mensaje', 'fr' => 'Message', 'pt' => 'Mensagem', 'tr' => 'Mesaj', 'zh' => '留言' ),
		'msg_ph'  => array( 'en' => 'How can we help you?', 'ru' => 'Чем мы можем помочь?', 'uk' => 'Чим ми можемо допомогти?', 'de' => 'Wie können wir helfen?', 'es' => '¿En qué podemos ayudarle?', 'fr' => 'Comment pouvons-nous vous aider ?', 'pt' => 'Como podemos ajudar?', 'tr' => 'Size nasıl yardımcı olabiliriz?', 'zh' => '我们能为您做些什么？' ),
		'send'    => array( 'en' => 'Send request', 'ru' => 'Отправить заявку', 'uk' => 'Надіслати заявку', 'de' => 'Anfrage senden', 'es' => 'Enviar solicitud', 'fr' => 'Envoyer la demande', 'pt' => 'Enviar pedido', 'tr' => 'Talep gönder', 'zh' => '提交请求' ),
		'note'    => array( 'en' => 'By clicking the button, I confirm that I have read the %s and consent to the processing of my personal data in accordance with the GDPR.', 'ru' => 'Нажимая кнопку, я подтверждаю, что ознакомился с %s и даю согласие на обработку персональных данных в соответствии с GDPR.', 'uk' => 'Натискаючи кнопку, я підтверджую, що ознайомився з %s і даю згоду на обробку персональних даних відповідно до GDPR.', 'de' => 'Mit dem Klick auf die Schaltfläche bestätige ich, dass ich die %s gelesen habe, und willige in die Verarbeitung meiner personenbezogenen Daten gemäß DSGVO ein.', 'es' => 'Al pulsar el botón, confirmo que he leído la %s y consiento el tratamiento de mis datos personales conforme al RGPD.', 'fr' => 'En cliquant sur le bouton, je confirme avoir lu la %s et consens au traitement de mes données personnelles conformément au RGPD.', 'pt' => 'Ao clicar no botão, confirmo que li a %s e autorizo o tratamento dos meus dados pessoais nos termos do RGPD.', 'tr' => 'Butona tıklayarak %s metnini okuduğumu ve kişisel verilerimin GDPR kapsamında işlenmesine onay verdiğimi beyan ederim.', 'zh' => '点击按钮即表示我已阅读%s，并同意根据GDPR处理我的个人数据。' ),
		'privacy' => array( 'en' => 'Privacy Policy', 'ru' => 'Политикой конфиденциальности', 'uk' => 'Політикою конфіденційності', 'de' => 'Datenschutzerklärung', 'es' => 'Política de privacidad', 'fr' => 'Politique de confidentialité', 'pt' => 'Política de Privacidade', 'tr' => 'Gizlilik Politikası', 'zh' => '隐私政策' ),
		'ok_t'    => array( 'en' => 'Request sent', 'ru' => 'Заявка отправлена', 'uk' => 'Заявку надіслано', 'de' => 'Anfrage gesendet', 'es' => 'Solicitud enviada', 'fr' => 'Demande envoyée', 'pt' => 'Pedido enviado', 'tr' => 'Talep gönderildi', 'zh' => '请求已发送' ),
		'ok'      => array( 'en' => 'Thank you! We will contact you within 1 working day.', 'ru' => 'Спасибо! Мы свяжемся с вами в течение рабочего дня.', 'uk' => 'Дякуємо! Ми зв’яжемося з вами протягом робочого дня.', 'de' => 'Vielen Dank! Wir melden uns innerhalb eines Werktages.', 'es' => '¡Gracias! Le contactaremos en un día laborable.', 'fr' => 'Merci ! Nous vous contacterons sous un jour ouvré.', 'pt' => 'Obrigado! Entraremos em contacto num dia útil.', 'tr' => 'Teşekkürler! Bir iş günü içinde sizinle iletişime geçeceğiz.', 'zh' => '谢谢！我们将在一个工作日内与您联系。' ),
		'req'     => array( 'en' => 'Please fill in this field.', 'ru' => 'Заполните это поле.', 'uk' => 'Заповніть це поле.', 'de' => 'Bitte füllen Sie dieses Feld aus.', 'es' => 'Rellene este campo.', 'fr' => 'Veuillez remplir ce champ.', 'pt' => 'Preencha este campo.', 'tr' => 'Lütfen bu alanı doldurun.', 'zh' => '请填写此字段。' ),
		'check'   => array( 'en' => 'Please check the highlighted fields.', 'ru' => 'Проверьте выделенные поля.', 'uk' => 'Перевірте виділені поля.', 'de' => 'Bitte prüfen Sie die markierten Felder.', 'es' => 'Revise los campos marcados.', 'fr' => 'Veuillez vérifier les champs indiqués.', 'pt' => 'Verifique os campos assinalados.', 'tr' => 'Lütfen işaretli alanları kontrol edin.', 'zh' => '请检查标出的字段。' ),
		'e_email' => array( 'en' => 'Please enter a valid email.', 'ru' => 'Введите корректный email.', 'uk' => 'Введіть коректний email.', 'de' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.', 'es' => 'Introduzca un email válido.', 'fr' => 'Veuillez saisir un e-mail valide.', 'pt' => 'Introduza um e-mail válido.', 'tr' => 'Lütfen geçerli bir e-posta girin.', 'zh' => '请输入有效的电子邮件。' ),
		'e_phone' => array( 'en' => 'Please enter the number in international format, e.g. +372…', 'ru' => 'Введите номер в международном формате, например +372…', 'uk' => 'Введіть номер у міжнародному форматі, наприклад +372…', 'de' => 'Bitte die Nummer im internationalen Format eingeben, z. B. +372…', 'es' => 'Introduzca el número en formato internacional, p. ej. +372…', 'fr' => 'Saisissez le numéro au format international, ex. +372…', 'pt' => 'Introduza o número em formato internacional, ex. +372…', 'tr' => 'Numarayı uluslararası formatta girin, örn. +372…', 'zh' => '请输入国际格式的电话号码，例如 +372…' ),
		'e_tg'    => array( 'en' => 'Enter your Telegram @username or phone number.', 'ru' => 'Укажите @username в Telegram или номер телефона.', 'uk' => 'Вкажіть @username у Telegram або номер телефону.', 'de' => 'Geben Sie Ihren Telegram-@Benutzernamen oder Ihre Telefonnummer ein.', 'es' => 'Indique su @usuario de Telegram o su teléfono.', 'fr' => 'Indiquez votre @identifiant Telegram ou votre numéro.', 'pt' => 'Indique o seu @utilizador do Telegram ou telefone.', 'tr' => 'Telegram @kullanıcı adınızı veya telefon numaranızı girin.', 'zh' => '请输入您的Telegram @用户名或电话号码。' ),
		'e_send'  => array( 'en' => 'Sorry, the request could not be sent. Please call %1$s or write to %2$s.', 'ru' => 'Не удалось отправить заявку. Позвоните по номеру %1$s или напишите на %2$s.', 'uk' => 'Не вдалося надіслати заявку. Зателефонуйте за номером %1$s або напишіть на %2$s.', 'de' => 'Die Anfrage konnte nicht gesendet werden. Rufen Sie uns unter %1$s an oder schreiben Sie an %2$s.', 'es' => 'No se pudo enviar la solicitud. Llámenos al %1$s o escriba a %2$s.', 'fr' => 'La demande n’a pas pu être envoyée. Appelez le %1$s ou écrivez à %2$s.', 'pt' => 'Não foi possível enviar o pedido. Ligue para %1$s ou escreva para %2$s.', 'tr' => 'Talep gönderilemedi. Lütfen %1$s numarasını arayın veya %2$s adresine yazın.', 'zh' => '请求未能发送。请致电 %1$s 或发送邮件至 %2$s。' ),
		'call'    => array( 'en' => 'Call us', 'ru' => 'Позвонить', 'uk' => 'Зателефонувати', 'de' => 'Anrufen', 'es' => 'Llamar', 'fr' => 'Appeler', 'pt' => 'Ligar', 'tr' => 'Ara', 'zh' => '致电' ),
		'close'   => array( 'en' => 'Close', 'ru' => 'Закрыть', 'uk' => 'Закрити', 'de' => 'Schließen', 'es' => 'Cerrar', 'fr' => 'Fermer', 'pt' => 'Fechar', 'tr' => 'Kapat', 'zh' => '关闭' ),
	);
	$l = lai_bc_lang();
	return $T[ $key ][ $l ] ?? $T[ $key ]['en'] ?? $key;
}

/* ---------- Форма Contact Form 7 ---------- */

function lai_bc_form_id(): int {
	$id = (int) get_option( 'lai_bc_form_id' );
	if ( $id && 'wpcf7_contact_form' === get_post_type( $id ) ) return $id;
	$p = get_posts( array( 'post_type' => 'wpcf7_contact_form', 'title' => LAI_BC_FORM_TITLE, 'numberposts' => 1, 'fields' => 'ids', 'lang' => '', 'post_status' => 'publish' ) );
	if ( $p ) { update_option( 'lai_bc_form_id', (int) $p[0], false ); return (int) $p[0]; }
	return 0;
}

function lai_bc_form_template(): string {
	return '<div class="lcf__r"><label for="lcf-name">{{name}}</label>[text* your-name id:lcf-name autocomplete:name placeholder "{{name_ph}}"]</div>
<div class="lcf__r"><span class="lcf__lbl">{{method}}</span>[radio contact-method use_label_element default:1 "Phone" "Email" "Telegram" "WhatsApp"]</div>
<div class="lcf__r"><label for="lcf-contact" class="lcf-contact-label">{{Phone}}</label>[text* contact-value id:lcf-contact class:lcf-contact autocomplete:tel placeholder "+372 5xxx xxxx"]</div>
<div class="lcf__r lcf__r--top"><label for="lcf-msg">{{msg}}</label>[textarea your-message id:lcf-msg x4 placeholder "{{msg_ph}}"]</div>
<div class="lcf__hp" aria-hidden="true">[text lcf-website tabindex:-1 autocomplete:off]</div>
<div class="lcf__r"><span></span><div>[submit class:lcf__btn "{{send}}"]<p class="lcf__note">{{note}}</p></div></div>';
}

/** Создаёт форму один раз (в админке). Получатель и отправитель — как у form_EN. */
function lai_bc_install_form(): int {
	if ( $id = lai_bc_form_id() ) return $id;
	if ( ! class_exists( 'WPCF7_ContactForm' ) ) return 0;
	$src  = get_posts( array( 'post_type' => 'wpcf7_contact_form', 'title' => 'form_EN', 'numberposts' => 1, 'fields' => 'ids', 'lang' => '' ) );
	$mail = $src ? (array) get_post_meta( (int) $src[0], '_mail', true ) : array();
	$form = WPCF7_ContactForm::get_template( array( 'title' => LAI_BC_FORM_TITLE, 'locale' => 'en_US' ) );
	$form->set_properties( array(
		'form' => lai_bc_form_template(),
		'mail' => array(
			'active'             => true,
			'subject'            => 'Book a call — [contact-method] — [your-name]',
			'sender'             => $mail['sender'] ?? '[_site_title] <wordpress@' . wp_parse_url( home_url(), PHP_URL_HOST ) . '>',
			'recipient'          => $mail['recipient'] ?? LAI_BC_MAIL,
			'body'               => "Name: [your-name]\nContact method: [contact-method]\nContact: [contact-value]\n\nMessage:\n[your-message]\n\n--\nLanguage: [_lai_lang]\nPage: [_url]\nDate: [_date] [_time]\nIP: [_remote_ip]",
			'additional_headers' => '',
			'attachments'        => '',
			'use_html'           => false,
			'exclude_blank'      => false,
		),
		'mail_2'   => array( 'active' => false ),
		'messages' => array(
			'mail_sent_ok'     => 'Thank you! We will contact you within 1 working day.',
			'mail_sent_ng'     => 'Sorry, the request could not be sent.',
			'validation_error' => 'Please check the highlighted fields.',
			'spam'             => 'Sorry, the request could not be sent.',
			'invalid_required' => 'Please fill in this field.',
		),
	) );
	$form->save();
	$id = (int) $form->id();
	if ( $id ) update_option( 'lai_bc_form_id', $id, false );
	return $id;
}
add_action( 'admin_init', function () { if ( ! lai_bc_form_id() ) lai_bc_install_form(); } );

function lai_bc_is_ours( $form = null ): bool {
	$form = $form ?: ( class_exists( 'WPCF7_ContactForm' ) ? WPCF7_ContactForm::get_current() : null );
	return $form && (int) $form->id() === lai_bc_form_id();
}

// Без автоматических <p>/<br> в нашей разметке.
add_filter( 'wpcf7_autop_or_not', function ( $autop ) { return lai_bc_is_ours() ? false : $autop; } );

// Подписи на языке страницы.
add_filter( 'wpcf7_form_elements', function ( $html ) {
	if ( ! lai_bc_is_ours() ) return $html;
	$pp   = (int) get_option( 'wp_page_for_privacy_policy' );
	$pp   = $pp && function_exists( 'pll_get_post' ) ? ( (int) pll_get_post( $pp, lai_bc_lang() ) ?: $pp ) : $pp;
	$link = '<a href="' . esc_url( $pp ? get_permalink( $pp ) : home_url( '/privacy-policy/' ) ) . '" target="_blank">' . esc_html( lai_bc_t( 'privacy' ) ) . '</a>';
	$map  = array( '{{note}}' => sprintf( esc_html( lai_bc_t( 'note' ) ), $link ) );
	foreach ( array( 'name', 'name_ph', 'method', 'Phone', 'msg', 'msg_ph', 'send' ) as $k ) $map[ '{{' . $k . '}}' ] = esc_html( lai_bc_t( $k ) );
	$html = strtr( $html, $map );
	foreach ( LAI_BC_METHODS as $m ) {
		$html = str_replace( '<span class="wpcf7-list-item-label">' . $m . '</span>', '<span class="wpcf7-list-item-label">' . esc_html( lai_bc_t( $m ) ) . '</span>', $html );
	}
	return $html;
} );

add_filter( 'wpcf7_form_hidden_fields', function ( $fields ) {
	if ( lai_bc_is_ours() ) { $fields['_lai_lang'] = lai_bc_lang(); $fields['_lai_ts'] = (string) time(); }
	return $fields;
} );

add_filter( 'wpcf7_special_mail_tags', function ( $out, $name ) {
	if ( '_lai_lang' === $name ) return sanitize_key( $_POST['_lai_lang'] ?? '' );
	return $out;
}, 10, 2 );

// Сообщения формы на языке посетителя (язык берём из скрытого поля при отправке).
add_filter( 'wpcf7_display_message', function ( $message, $status ) {
	if ( ! lai_bc_is_ours() ) return $message;
	if ( ! empty( $_POST['_lai_lang'] ) && function_exists( 'PLL' ) && ( $lang = PLL()->model->get_language( sanitize_key( $_POST['_lai_lang'] ) ) ) ) PLL()->curlang = $lang;
	switch ( $status ) {
		case 'mail_sent_ok': return lai_bc_t( 'ok' );
		case 'validation_error': return lai_bc_t( 'check' );
		case 'invalid_required': return lai_bc_t( 'req' );
		case 'mail_sent_ng':
		case 'spam': return sprintf( lai_bc_t( 'e_send' ), LAI_BC_PHONE, LAI_BC_MAIL );
	}
	return $message;
}, 10, 2 );

// Проверка контакта по выбранному способу связи.
add_filter( 'wpcf7_validate_text*', function ( $result, $tag ) {
	if ( 'contact-value' !== $tag->name || ! lai_bc_is_ours() ) return $result;
	$v = trim( (string) ( $_POST['contact-value'] ?? '' ) );
	if ( '' === $v ) return $result;
	$m     = sanitize_text_field( $_POST['contact-method'] ?? 'Phone' );
	$phone = (bool) preg_match( '/^\+?[0-9][0-9\s\-().]{6,20}$/', $v );
	$err   = '';
	if ( 'Email' === $m && ! is_email( $v ) ) $err = 'e_email';
	if ( in_array( $m, array( 'Phone', 'WhatsApp' ), true ) && ! $phone ) $err = 'e_phone';
	if ( 'Telegram' === $m && ! $phone && ! preg_match( '/^@?[A-Za-z0-9_]{4,32}$/', $v ) ) $err = 'e_tg';
	if ( $err ) {
		if ( ! empty( $_POST['_lai_lang'] ) && function_exists( 'PLL' ) && ( $lang = PLL()->model->get_language( sanitize_key( $_POST['_lai_lang'] ) ) ) ) PLL()->curlang = $lang;
		$result->invalidate( $tag, lai_bc_t( $err ) );
	}
	return $result;
}, 20, 2 );

// Антиспам: скрытое поле-ловушка и слишком быстрая отправка.
add_filter( 'wpcf7_spam', function ( $spam ) {
	if ( $spam || ! lai_bc_is_ours() ) return $spam;
	if ( ! empty( $_POST['lcf-website'] ) ) return true;
	$ts = (int) ( $_POST['_lai_ts'] ?? 0 );
	return $ts && ( time() - $ts ) < 3;
} );

// Reply-To, если клиент выбрал Email; пометка тестового сайта.
add_filter( 'wpcf7_mail_components', function ( $c, $form ) {
	if ( ! lai_bc_is_ours( $form ) ) return $c;
	$v = trim( (string) ( $_POST['contact-value'] ?? '' ) );
	if ( 'Email' === ( $_POST['contact-method'] ?? '' ) && is_email( $v ) ) $c['additional_headers'] = trim( ( $c['additional_headers'] ?? '' ) . "\nReply-To: " . $v );
	if ( false !== strpos( home_url(), 'staging.' ) ) $c['subject'] = '[STAGING TEST] ' . $c['subject'];
	return $c;
}, 10, 2 );

// На staging письма заблокированы защитой; разрешаем только письмо этой формы (для проверки).
add_action( 'wpcf7_before_send_mail', function ( $form ) {
	if ( lai_bc_is_ours( $form ) ) $GLOBALS['lai_allow_staging_mail'] = true;
} );

/* ---------- Вывод: шапка, мобильное меню, футер ---------- */

function lai_bc_svg( string $k ): string {
	$s = array(
		'ph'   => '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2Z"/></svg>',
		'mail' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 6.5 8.5 6.5 8.5-6.5"/></svg>',
	);
	return $s[ $k ];
}
function lai_bc_book_link( string $class ): string {
	return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( home_url( '/contacts/' ) ) . '#book-a-call" data-lai-book>' . esc_html( lai_bc_t( 'book' ) ) . '</a>';
}

add_action( 'lai_header_contacts', function () {
	echo '<div class="lai-hc"><a class="lai-hc__tel" href="tel:' . LAI_BC_TEL . '">' . esc_html( LAI_BC_PHONE ) . '</a>' . lai_bc_book_link( 'lai-hc__book' ) . '</div>';
} );
add_action( 'lai_header_mobile_contacts', function () {
	echo '<a class="lai-hm" href="tel:' . LAI_BC_TEL . '" aria-label="' . esc_attr( lai_bc_t( 'call' ) . ' ' . LAI_BC_PHONE ) . '">' . lai_bc_svg( 'ph' ) . '</a>';
} );
add_action( 'lai_mobile_menu_after', function () {
	echo '<div class="lai-mmc"><a class="lai-mmc__tel" href="tel:' . LAI_BC_TEL . '">' . lai_bc_svg( 'ph' ) . esc_html( LAI_BC_PHONE ) . '</a><a class="lai-mmc__mail" href="mailto:' . LAI_BC_MAIL . '">' . lai_bc_svg( 'mail' ) . LAI_BC_MAIL . '</a></div>';
}, 20 );
add_action( 'lai_footer_contacts', function () {
	echo '<div class="lai-fc"><a class="lai-fc__tel" href="tel:' . LAI_BC_TEL . '">' . esc_html( LAI_BC_PHONE ) . '</a><a class="lai-fc__mail" href="mailto:' . LAI_BC_MAIL . '">' . lai_bc_svg( 'mail' ) . LAI_BC_MAIL . '</a>' . lai_bc_book_link( 'lai-fc__book' ) . '<div class="lai-fc__hours">' . esc_html( lai_bc_t( 'hours' ) ) . '</div></div>';
} );

/* ---------- Окно с формой, стили, скрипт ---------- */

add_action( 'wp_footer', function () {
	$fid = lai_bc_form_id();
	if ( ! $fid ) return;
	$cfg = array();
	foreach ( LAI_BC_METHODS as $m ) {
		$cfg[ $m ] = array(
			'label' => lai_bc_t( 'Email' === $m ? 'Email' : ( 'Telegram' === $m ? 'Telegram' : 'Phone' ) ),
			'ph'    => 'Email' === $m ? 'name@company.com' : ( 'Telegram' === $m ? lai_bc_t( 'ph_tg' ) : '+372 5xxx xxxx' ),
			'type'  => 'Email' === $m ? 'email' : ( 'Telegram' === $m ? 'text' : 'tel' ),
			'ac'    => 'Email' === $m ? 'email' : ( 'Telegram' === $m ? 'off' : 'tel' ),
		);
	}
	$cfg['WhatsApp']['label'] = 'WhatsApp';
	?>
<div class="lcf-ov" id="book-a-call" role="dialog" aria-modal="true" aria-labelledby="lcf-title" hidden>
 <div class="lcf">
  <button type="button" class="lcf__x" data-lcf-close aria-label="<?php echo esc_attr( lai_bc_t( 'close' ) ); ?>">&times;</button>
  <div class="lcf__body">
   <p class="lcf__h" id="lcf-title"><?php echo esc_html( lai_bc_t( 'book' ) ); ?></p>
   <p class="lcf__sub"><?php echo esc_html( lai_bc_t( 'sub' ) ); ?></p>
   <?php echo do_shortcode( '[contact-form-7 id="' . $fid . '" html_class="lcf-form"]' ); ?>
  </div>
  <div class="lcf__done" hidden><div class="lcf__check"><?php echo '<svg viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>'; ?></div><p class="lcf__h"><?php echo esc_html( lai_bc_t( 'ok_t' ) ); ?></p><p class="lcf__sub"><?php echo esc_html( lai_bc_t( 'ok' ) ); ?></p></div>
 </div>
</div>
<style id="lai-book-call-css">
:root{--lcf-g:#31d44b;--lcf-ink:#151a2d}
/* шапка: десктоп */
.lai-hc{display:none}
@media (min-width:1400px){.lai-hc{display:flex;flex-direction:column;align-items:flex-end;gap:2px;margin-left:20px;line-height:1.25}
.lai-hc__tel{font-size:17px;font-weight:500;color:var(--lcf-ink)!important;text-decoration:none!important;white-space:nowrap}
.lai-hc__book{font-size:15px;font-weight:500;color:var(--lcf-g)!important;text-decoration:none!important;border-bottom:1.5px solid currentColor;white-space:nowrap}}
/* шапка: мобильная */
.lai-hm{display:none}
@media (max-width:991px),(min-width:1280px) and (max-width:1399px){.mob_ready{display:flex;align-items:center}.lai-hm{display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;width:44px;height:44px;margin-right:10px;border-radius:14px;border:1px solid #e3e5ea;background:#fff;box-shadow:0 2px 8px rgba(20,30,50,.06);color:var(--lcf-g)!important}}
@media (min-width:1280px) and (max-width:1399px){.lai-hm{width:38px;height:38px;border-radius:8px;box-shadow:none;border-color:#c9ccd3}}
/* мобильное меню */
.lai-mmc{margin:20px 30px 10px;padding-top:18px;border-top:1px solid #e9ebef}
.lai-mmc a{display:flex;align-items:center;gap:10px;color:var(--lcf-ink)!important;text-decoration:none!important;margin-bottom:12px}
.lai-mmc svg{color:var(--lcf-g);flex:0 0 auto}
.lai-mmc__tel{font-size:20px;font-weight:500}.lai-mmc__mail{font-size:15px;word-break:break-all}
/* футер */
.lai-fc{margin:0 0 22px;line-height:1.3}
.lai-fc__tel{display:block;font-size:24px;font-weight:500;color:#fff!important;text-decoration:none!important;margin-bottom:8px}
.lai-fc__mail{display:inline-flex;align-items:center;gap:8px;font-size:16px;color:#fff!important;text-decoration:none!important;opacity:.92;margin-bottom:12px;word-break:break-all}
.lai-fc__mail svg{color:var(--lcf-g);flex:0 0 auto}
.lai-fc__book{display:table;font-size:16px;font-weight:500;color:var(--lcf-g)!important;text-decoration:none!important;border-bottom:1.5px solid currentColor}
.lai-fc__hours{font-size:14px;color:#fff;opacity:.6;margin-top:10px}
/* окно */
.lcf-ov{position:fixed;inset:0;z-index:100000;background:rgba(15,22,38,.55);backdrop-filter:blur(3px);display:flex;align-items:center;justify-content:center;padding:16px;overflow:auto}
.lcf-ov[hidden]{display:none}
body.lcf-open{overflow:hidden}
body.lcf-open #LeadboosterContainer,body.lcf-open [id^="Leadbooster"],body.lcf-open [class*="proactiveChat"]{visibility:hidden!important}
.lcf{position:relative;background:#fff;border-radius:24px;width:100%;max-width:720px;margin:auto;padding:46px 56px 36px;color:var(--lcf-ink);box-shadow:0 30px 80px rgba(10,20,40,.25)}
.lcf__x{position:absolute;top:20px;right:22px;width:40px;height:40px;border-radius:12px;border:1px solid #e3e5ea;background:#fff;font-size:26px;line-height:1;color:var(--lcf-ink);cursor:pointer;padding:0}
.lcf__h{font-size:34px;font-weight:500;line-height:1.15;margin:0 0 8px;letter-spacing:-.4px}
.lcf__sub{font-size:15px;color:#6b7183;margin:0 0 26px}
.lcf .wpcf7 form{margin:0}
.lcf__r{display:grid;grid-template-columns:170px 1fr;align-items:center;gap:16px;margin-bottom:14px}
.lcf__r--top{align-items:start}.lcf__r--top>label{padding-top:13px}
.lcf__r>label,.lcf__lbl{font-size:16px;color:var(--lcf-ink);margin:0}
.lcf .wpcf7-form-control-wrap{display:block}
.lcf input.wpcf7-text,.lcf input.wpcf7-email,.lcf textarea.wpcf7-textarea{width:100%!important;height:auto;border:1px solid #dfe2e8!important;border-radius:14px!important;padding:13px 18px!important;font:inherit;font-size:16px!important;color:var(--lcf-ink);background:#fff!important;box-shadow:none!important;outline:none;margin:0!important}
.lcf textarea.wpcf7-textarea{height:110px!important;resize:vertical}
.lcf input.wpcf7-text:focus,.lcf textarea.wpcf7-textarea:focus{border-color:var(--lcf-g)!important}
.lcf .wpcf7-not-valid{border-color:#e5484d!important}
.lcf .wpcf7-not-valid-tip{font-size:13px;color:#e5484d;margin-top:4px}
.lcf .wpcf7-radio{display:flex;background:#f3f4f6;border-radius:14px;padding:4px;gap:2px}
.lcf .wpcf7-radio .wpcf7-list-item{flex:1;margin:0}
.lcf .wpcf7-radio label{display:block;margin:0;cursor:pointer;position:relative}
.lcf .wpcf7-radio input{position:absolute;opacity:0;pointer-events:none}
.lcf .wpcf7-radio .wpcf7-list-item-label{display:block;text-align:center;padding:10px 4px;border-radius:11px;font-size:15px;color:var(--lcf-ink);white-space:nowrap}
.lcf .wpcf7-radio input:checked+.wpcf7-list-item-label{background:#fff;font-weight:600;box-shadow:0 1px 3px rgba(0,0,0,.08),0 0 0 1.5px var(--lcf-g)}
.lcf .wpcf7-radio input:focus-visible+.wpcf7-list-item-label{outline:2px solid var(--lcf-g)}
.lcf__hp{position:absolute!important;left:-9999px!important;width:1px;height:1px;overflow:hidden}
.lcf input.lcf__btn{width:100%!important;height:56px;border:0!important;border-radius:14px!important;background:var(--lcf-g)!important;color:#fff!important;font:inherit;font-size:17px!important;font-weight:600;letter-spacing:.2px;cursor:pointer;margin:6px 0 0!important;padding:0!important;text-transform:none!important}
.lcf input.lcf__btn:hover{filter:brightness(.95)}
.lcf .wpcf7-spinner{display:block;margin:10px auto 0}
.lcf__note{font-size:13px;color:#8a90a0;line-height:1.55;margin:12px 0 0}
.lcf__note a{color:#6b7183;text-decoration:underline}
.lcf .wpcf7-response-output{margin:14px 0 0!important;border-radius:12px;font-size:14px;padding:10px 14px!important}
.lcf__done{text-align:center;padding:30px 0 16px}
.lcf__check{width:64px;height:64px;border-radius:50%;background:rgba(49,212,75,.12);color:var(--lcf-g);display:flex;align-items:center;justify-content:center;margin:0 auto 18px}
@media (max-width:640px){
.lcf-ov{align-items:flex-end;padding:0}
.lcf{border-radius:20px 20px 0 0;padding:58px 20px 24px;max-height:94vh;overflow:auto;margin:0}
.lcf__h{font-size:28px}.lcf__sub{margin-bottom:20px}
.lcf__r{grid-template-columns:1fr;gap:6px;margin-bottom:14px}.lcf__r--top>label{padding-top:0}.lcf__r>span:empty{display:none}
.lcf .wpcf7-radio .wpcf7-list-item-label{font-size:13px;padding:10px 2px}}
</style>
<script id="lai-book-call-js">
(function(){
 var ov=document.getElementById('book-a-call'); if(!ov) return;
 var cfg=<?php echo wp_json_encode( $cfg ); ?>, last=null;
 function open(e){ if(e) e.preventDefault(); last=document.activeElement; ov.hidden=false; document.body.classList.add('lcf-open'); setTimeout(function(){var i=ov.querySelector('#lcf-name'); if(i) i.focus();},50); }
 function close(){ ov.hidden=true; document.body.classList.remove('lcf-open'); var b=ov.querySelector('.lcf__body'), d=ov.querySelector('.lcf__done'); if(!d.hidden){d.hidden=true;b.hidden=false;} if(last&&last.focus) last.focus(); }
 document.addEventListener('click',function(e){ var a=e.target.closest('[data-lai-book],a[href$="#book-a-call"]'); if(a){ open(e); var mm=document.querySelector('.mm-toggle[aria-expanded="true"]'); if(mm) mm.click(); return; } if(e.target===ov||e.target.closest('[data-lcf-close]')) close(); });
 document.addEventListener('keydown',function(e){ if(e.key==='Escape'&&!ov.hidden) close(); });
 function setMethod(m){ var c=cfg[m]||cfg.Phone, i=ov.querySelector('#lcf-contact'), l=ov.querySelector('.lcf-contact-label'); if(!i) return; l.textContent=c.label; i.placeholder=c.ph; i.type=c.type; i.setAttribute('autocomplete',c.ac); i.setAttribute('inputmode',c.type==='tel'?'tel':(c.type==='email'?'email':'text')); }
 ov.addEventListener('change',function(e){ if(e.target.name==='contact-method') setMethod(e.target.value); });
 document.addEventListener('wpcf7mailsent',function(e){ if(!ov.contains(e.target)) return; ov.querySelector('.lcf__body').hidden=true; ov.querySelector('.lcf__done').hidden=false; try{ if(window.gtag) gtag('event','generate_lead',{form_name:'book_a_call'}); if(window.clarity) clarity('event','book_a_call'); }catch(x){} });
 if(location.hash==='#book-a-call') open();
})();
</script>
	<?php
}, 50 );
