<?php
/**
 * wp eval-file tools/fix-staging-access.php [check]
 * После восстановления бэкапа прода на staging: вернуть адреса staging, отключить
 * переименование входа (AIOS), сбросить кеш. С аргументом "check" — только отчёт.
 */
$check   = in_array( 'check', $args, true );
$staging = 'https://staging.dragonslots-gr.gr';
$report  = array();

$report['before'] = array(
	'siteurl'       => get_option( 'siteurl' ),
	'home'          => get_option( 'home' ),
	'table_prefix'  => $GLOBALS['wpdb']->prefix,
	'env'           => wp_get_environment_type(),
	'aios_active'   => is_plugin_active( 'all-in-one-wp-security-and-firewall/wp-security.php' ),
	'mu_plugins'    => array_keys( get_mu_plugins() ),
);
$aios = get_option( 'aio_wp_security_configs' );
if ( is_array( $aios ) ) {
	$report['before']['aios_rename_login'] = $aios['aiowps_enable_rename_login_page'] ?? null;
	$report['before']['aios_login_slug']   = $aios['aiowps_login_page_slug'] ?? null;
}
$report['before']['admins'] = array_map( function ( $u ) {
	return $u->user_login;
}, get_users( array( 'role' => 'administrator' ) ) );

if ( ! $check ) {
	$cur = untrailingslashit( (string) get_option( 'siteurl' ) );
	if ( $cur !== $staging ) {
		foreach ( array_unique( array( $cur, 'https://www.legaladdressinestonia.com', 'https://legaladdressinestonia.com', 'http://legaladdressinestonia.com', 'http://www.legaladdressinestonia.com' ) ) as $from ) {
			$r = WP_CLI::runcommand( "search-replace '$from' '$staging' --all-tables-with-prefix --skip-columns=guid --format=count", array( 'launch' => false, 'return' => true, 'exit_error' => false ) );
			$report['search_replace'][ $from ] = trim( (string) $r );
		}
	}
	update_option( 'siteurl', $staging );
	update_option( 'home', $staging );

	if ( is_array( $aios ) ) {
		$aios['aiowps_enable_rename_login_page'] = '';
		update_option( 'aio_wp_security_configs', $aios );
	}
	deactivate_plugins( 'all-in-one-wp-security-and-firewall/wp-security.php', true );

	// AIOS пишет правила в .htaccess и мог оставить firewall-загрузчик в mu-plugins.
	$ht = ABSPATH . '.htaccess';
	if ( is_writable( $ht ) ) {
		$c = (string) file_get_contents( $ht );
		$n = preg_replace( '/# BEGIN All In One WP Security.*?# END All In One WP Security\s*/s', '', $c );
		if ( $n !== $c ) {
			copy( $ht, $ht . '.bak-' . gmdate( 'YmdHis' ) );
			file_put_contents( $ht, $n );
			$report['htaccess'] = 'AIOS rules removed (backup saved)';
		}
	}
	foreach ( array( 'aios-firewall-loader.php' ) as $f ) {
		$p = WPMU_PLUGIN_DIR . '/' . $f;
		if ( file_exists( $p ) ) {
			@unlink( $p );
			$report['removed'][] = $f;
		}
	}
	$ui = ABSPATH . '.user.ini';
	if ( is_writable( $ui ) && false !== strpos( (string) file_get_contents( $ui ), 'aios-bootstrap' ) ) {
		file_put_contents( $ui, preg_replace( '/^.*aios-bootstrap.*$\n?/m', '', (string) file_get_contents( $ui ) ) );
		$report['user_ini'] = 'aios auto_prepend removed';
	}

	flush_rewrite_rules( true );
	wp_cache_flush();
	do_action( 'litespeed_purge_all' );

	$report['after'] = array(
		'siteurl'     => get_option( 'siteurl' ),
		'home'        => get_option( 'home' ),
		'aios_active' => is_plugin_active( 'all-in-one-wp-security-and-firewall/wp-security.php' ),
	);
	$res = wp_remote_get( $staging . '/wp-login.php', array( 'timeout' => 20, 'sslverify' => false, 'redirection' => 0 ) );
	$report['after']['wp_login_http'] = is_wp_error( $res ) ? $res->get_error_message() : wp_remote_retrieve_response_code( $res );
}

echo wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), "\n";
