<?php
/**
 * Мобильное меню (≤991px): нижняя «шторка» с вкладками разделов, кнопка «бургер»,
 * переключатель языка в том же стиле, нижняя панель с CTA.
 * Стили — CSS/mobile-menu.css, поведение — js/mobile-menu.js.
 */

defined( 'ABSPATH' ) || exit;

function lai_mm_label( string $key ): string {
	$l = array(
		'cta'  => array( 'en' => 'Get a consultation', 'ru' => 'Получить консультацию', 'uk' => 'Отримати консультацію', 'de' => 'Beratung anfragen', 'fr' => 'Demander une consultation', 'es' => 'Solicitar consulta', 'pt' => 'Pedir consulta', 'tr' => 'Danışmanlık alın', 'zh' => '获取咨询' ),
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
	$classes[] = 'mm--c';
	return $classes;
} );

add_action( 'wp_enqueue_scripts', function () {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();
	wp_enqueue_style( 'lai-mobile-menu', $uri . '/CSS/mobile-menu.css', array( 'style' ), filemtime( $dir . '/CSS/mobile-menu.css' ) );
	wp_enqueue_script( 'lai-mobile-menu', $uri . '/js/mobile-menu.js', array( 'jquery224', 'main' ), filemtime( $dir . '/js/mobile-menu.js' ), true );
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
