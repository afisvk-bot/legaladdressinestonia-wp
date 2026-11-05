<?php
/**
 * Мобильное меню: варианты оформления, кнопка «бургер», нижняя панель с CTA.
 *
 * Вариант задаётся в wp-config.php: define( 'LAI_MOBILE_MENU', 'a' );  // a | b | c
 *   a — полноэкранный аккордеон (под шапкой, раскрытие на месте)
 *   b — боковая панель справа с переходом по уровням (как в приложениях)
 *   c — нижняя «шторка» с вкладками разделов
 * Предпросмотр на staging: ?mm=a|b|c (запоминается в cookie на сутки).
 */

defined( 'ABSPATH' ) || exit;

function lai_mm_variant(): string {
	$v       = defined( 'LAI_MOBILE_MENU' ) ? (string) LAI_MOBILE_MENU : 'a';
	$preview = 'production' !== wp_get_environment_type() || current_user_can( 'edit_theme_options' );
	if ( $preview ) {
		if ( isset( $_GET['mm'] ) && in_array( $_GET['mm'], array( 'a', 'b', 'c' ), true ) ) {
			$v = $_GET['mm'];
			if ( ! headers_sent() ) {
				setcookie( 'lai_mm', $v, time() + DAY_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true );
			}
		} elseif ( isset( $_COOKIE['lai_mm'] ) && in_array( $_COOKIE['lai_mm'], array( 'a', 'b', 'c' ), true ) ) {
			$v = $_COOKIE['lai_mm'];
		}
	}
	return in_array( $v, array( 'a', 'b', 'c' ), true ) ? $v : 'a';
}

function lai_mm_label( string $key ): string {
	$l = array(
		'cta'  => array( 'en' => 'Get a consultation', 'ru' => 'Получить консультацию', 'uk' => 'Отримати консультацію', 'de' => 'Beratung anfragen', 'fr' => 'Demander une consultation', 'es' => 'Solicitar consulta', 'pt' => 'Pedir consulta', 'tr' => 'Danışmanlık alın', 'zh' => '获取咨询' ),
		'back' => array( 'en' => 'Back', 'ru' => 'Назад', 'uk' => 'Назад', 'de' => 'Zurück', 'fr' => 'Retour', 'es' => 'Atrás', 'pt' => 'Voltar', 'tr' => 'Geri', 'zh' => '返回' ),
		'menu' => array( 'en' => 'Menu', 'ru' => 'Меню', 'uk' => 'Меню', 'de' => 'Menü', 'fr' => 'Menu', 'es' => 'Menú', 'pt' => 'Menu', 'tr' => 'Menü', 'zh' => '菜单' ),
	);
	$lang = function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'slug' ) : 'en';
	return $l[ $key ][ $lang ] ?? $l[ $key ]['en'];
}

/** Страница контактов на текущем языке (шаблон page-contacts.php). */
function lai_mm_contacts_url(): string {
	$pages = get_posts( array( 'post_type' => 'page', 'meta_key' => '_wp_page_template', 'meta_value' => 'page-contacts.php', 'numberposts' => 1, 'lang' => '', 'fields' => 'ids' ) );
	if ( ! $pages ) {
		return home_url( '/' );
	}
	$id = $pages[0];
	if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_current_language' ) ) {
		$id = pll_get_post( $id, pll_current_language() ) ?: $id;
	}
	return get_permalink( $id );
}

add_filter( 'body_class', function ( $classes ) {
	$classes[] = 'mm';
	$classes[] = 'mm--' . lai_mm_variant();
	return $classes;
} );

add_action( 'wp_enqueue_scripts', function () {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();
	wp_enqueue_style( 'lai-mobile-menu', $uri . '/CSS/mobile-menu.css', array( 'style' ), filemtime( $dir . '/CSS/mobile-menu.css' ) );
	wp_enqueue_script( 'lai-mobile-menu', $uri . '/js/mobile-menu.js', array( 'jquery224', 'main' ), filemtime( $dir . '/js/mobile-menu.js' ), true );
	wp_localize_script( 'lai-mobile-menu', 'laiMM', array(
		'variant' => lai_mm_variant(),
		'back'    => lai_mm_label( 'back' ),
	) );
}, 20 );

/** Нижняя панель меню: CTA. Если на странице есть модальная форма — откроется она. */
add_action( 'lai_mobile_menu_after', function () {
	$modal = function_exists( 'get_localized_cf7_form' ) ? '.form_' . get_localized_cf7_form()['modal_class'] : '';
	printf(
		'<div class="mm-foot"><a class="mm-cta" href="%s" data-mm-modal="%s">%s</a></div>',
		esc_url( lai_mm_contacts_url() ),
		esc_attr( $modal ),
		esc_html( lai_mm_label( 'cta' ) )
	);
} );
