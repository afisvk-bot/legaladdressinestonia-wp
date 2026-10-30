<?php
/**
 * Plugin Name: Staging Guard
 * Description: На staging-хостах: noindex, robots Disallow, без писем, без Pipedrive, UpdraftPlus без расписания и удалённого хранилища, плашка STAGING. На продакшене ничего не делает.
 * Version: 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Staging = хост начинается с "staging." ИЛИ в wp-config.php задано
 * define( 'WP_ENVIRONMENT_TYPE', 'staging' );
 */
function lae_is_staging() {
	$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( $_SERVER['HTTP_HOST'] ) : '';
	if ( 0 === strpos( $host, 'staging.' ) ) {
		return true;
	}
	return function_exists( 'wp_get_environment_type' )
		&& in_array( wp_get_environment_type(), array( 'staging', 'development', 'local' ), true );
}

if ( ! lae_is_staging() ) {
	return;
}

// 1. Заголовок X-Robots-Tag на всех ответах (включая файлы через PHP).
add_action( 'send_headers', function () {
	header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
} );

// 2. <meta name="robots"> — перекрывает настройки SEO-плагинов.
add_filter( 'wp_robots', function () {
	return array( 'noindex' => true, 'nofollow' => true, 'noarchive' => true );
}, PHP_INT_MAX );
add_filter( 'pre_option_blog_public', '__return_zero' );

// 3. robots.txt: Disallow всё.
add_filter( 'robots_txt', function () {
	return "User-agent: *\nDisallow: /\n";
}, PHP_INT_MAX );

// 4. Sitemap отключаем (ядро + Yoast + Rank Math).
add_filter( 'wp_sitemaps_enabled', '__return_false' );
add_filter( 'wpseo_enable_xml_sitemap', '__return_false' );
add_filter( 'rank_math/sitemap/enable', '__return_false' );

// 5. Письма с staging никуда не уходят (формы заявок, уведомления).
add_filter( 'pre_wp_mail', function ( $return, $atts ) {
	error_log( '[staging-guard] mail blocked: ' . ( is_array( $atts['to'] ) ? implode( ',', $atts['to'] ) : $atts['to'] ) );
	return true;
}, 10, 2 );

// 6. Интеграции с внешними сервисами на staging выключены:
//    заявки с тестовых форм не должны создавать сделки/контакты в Pipedrive.
add_filter( 'option_active_plugins', function ( $plugins ) {
	$blocked = array( 'cf7-pipedrive-pro/', 'integration-for-contact-form-7-and-pipedrive/', 'pipedrive-chatbot/' );
	return array_values( array_filter( (array) $plugins, function ( $p ) use ( $blocked ) {
		foreach ( $blocked as $prefix ) {
			if ( 0 === strpos( $p, $prefix ) ) {
				return false;
			}
		}
		return true;
	} ) );
} );

// 7. UpdraftPlus на staging: без расписания и без удалённого хранилища,
//    чтобы копии staging не смешивались с бэкапами продакшена (Drive/Dropbox/S3).
//    Ручной бэкап/восстановление из загруженных файлов работает.
add_filter( 'pre_option_updraft_interval', function () { return 'manual'; } );
add_filter( 'pre_option_updraft_interval_database', function () { return 'manual'; } );
add_filter( 'pre_option_updraft_service', function () { return 'none'; } );

// 8. Плашка STAGING в админ-баре и на фронте для залогиненных.
add_action( 'admin_bar_menu', function ( $bar ) {
	$bar->add_node( array(
		'id'    => 'lae-staging',
		'title' => '⚠ STAGING',
		'meta'  => array( 'class' => 'lae-staging-flag' ),
	) );
}, 1 );
add_action( 'wp_head', 'lae_staging_css' );
add_action( 'admin_head', 'lae_staging_css' );
function lae_staging_css() {
	echo '<style>#wpadminbar{background:#b45309!important}#wp-admin-bar-lae-staging>.ab-item{font-weight:700}</style>';
}
