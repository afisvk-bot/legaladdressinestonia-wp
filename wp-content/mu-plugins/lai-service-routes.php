<?php
/**
 * Plugin Name: LAI — Service routes (все языки)
 * Description: Исправляет правила адресов услуг темы: тема строит список адресов услуг (transient i10v_service_paths) через get_posts() без 'lang', и Polylang отдаёт услуги только текущего языка. Если правила пересобирались на запросе EN-страницы, все RU/UK/DE… адреса услуг отдавали 404. Здесь список строится сразу по всем языкам.
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

add_filter( 'pre_transient_i10v_service_paths', function ( $pre ) {
	$paths = get_transient( 'lai_service_paths_all' );
	if ( is_array( $paths ) && $paths ) return $paths;
	$ids = get_posts( array(
		'post_type' => 'service', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids',
		'lang' => '', 'suppress_filters' => false, 'no_found_rows' => true,
	) );
	$paths = array();
	foreach ( $ids as $id ) {
		$uri = get_page_uri( $id );
		if ( $uri ) $paths[] = $uri;
	}
	$paths = array_values( array_unique( $paths ) );
	if ( $paths ) set_transient( 'lai_service_paths_all', $paths, HOUR_IN_SECONDS );
	return $paths ?: $pre;
} );

// Тот же сброс, что у темы: при сохранении/удалении услуги.
foreach ( array( 'save_post_service', 'deleted_post', 'wp_trash_post', 'untrashed_post' ) as $hook ) {
	add_action( $hook, function ( $post_id = null ) {
		if ( $post_id && 'service' !== get_post_type( $post_id ) ) return;
		delete_transient( 'lai_service_paths_all' );
		set_transient( 'i10v_service_flush_needed', 1, HOUR_IN_SECONDS );
	} );
}

// Разовая пересборка правил после установки этого файла.
add_action( 'init', function () {
	if ( '1.0.0' !== get_option( 'lai_service_routes_ver' ) ) {
		update_option( 'lai_service_routes_ver', '1.0.0', false );
		delete_transient( 'lai_service_paths_all' );
		set_transient( 'i10v_service_flush_needed', 1, HOUR_IN_SECONDS );
	}
}, 5 );
