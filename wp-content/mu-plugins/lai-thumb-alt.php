<?php
/**
 * Plugin Name: LAI — alt изображений записи на языке страницы
 * Description: Переводы статей и услуг используют то же изображение, что и русский оригинал, поэтому alt у картинки остаётся русским. Если alt написан кириллицей, а запись на другом языке (не ru/uk), в alt подставляется заголовок самой записи на её языке. Медиатека не меняется.
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

add_action( 'begin_fetch_post_thumbnail_html', function ( $post_id ) { $GLOBALS['lai_thumb_post'] = (int) $post_id; } );
add_action( 'end_fetch_post_thumbnail_html', function () { $GLOBALS['lai_thumb_post'] = 0; } );

add_filter( 'wp_get_attachment_image_attributes', function ( $attr ) {
	$pid = (int) ( $GLOBALS['lai_thumb_post'] ?? 0 );
	if ( ! $pid || empty( $attr['alt'] ) || ! function_exists( 'pll_get_post_language' ) ) return $attr;
	if ( ! preg_match( '/\p{Cyrillic}/u', (string) $attr['alt'] ) ) return $attr;
	$lang = (string) pll_get_post_language( $pid );
	if ( '' === $lang || in_array( $lang, array( 'ru', 'uk' ), true ) ) return $attr;
	$attr['alt'] = wp_strip_all_tags( get_the_title( $pid ) );
	return $attr;
}, 20 );
