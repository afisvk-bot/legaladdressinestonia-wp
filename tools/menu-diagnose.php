<?php
/** wp eval-file tools/menu-diagnose.php [slug1,slug2] — состояние меню всех языков и синхронизации. */
$slugs = array_filter( explode( ',', $args[0] ?? 'registering-an-mt-in-estonia,as-registration-in-estonia' ) );
$out   = array();

$tree = function ( $menu_id ) {
	$rows = array();
	foreach ( (array) wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) ) as $i ) {
		$rows[] = sprintf( '#%d p=%d o=%d %s:%s/%d st=%s m=%s "%s"',
			$i->ID, $i->menu_item_parent, $i->menu_order, $i->type, $i->object, $i->object_id, $i->post_status,
			get_post_meta( $i->ID, '_lai_master_item', true ) ?: '-', $i->title );
	}
	return $rows;
};

$out['map']   = function_exists( 'lai_menu_map' ) ? lai_menu_map() : 'lai_menu_map missing';
$out['langs'] = pll_languages_list( array( 'fields' => 'slug' ) );
$o            = get_option( 'polylang' );
$theme        = get_option( 'stylesheet' );
$out['pll_nav_menus'] = $o['nav_menus'][ $theme ] ?? null;

foreach ( array_unique( (array) $out['map'] ) as $mid ) {
	$obj = wp_get_nav_menu_object( $mid );
	$out['master'][ $mid ] = array( 'name' => $obj ? $obj->name : null, 'items' => $tree( $mid ) );
}
foreach ( (array) ( $out['pll_nav_menus']['primary-menu'] ?? array() ) as $lang => $mid ) {
	if ( in_array( (int) $mid, array_map( 'intval', (array) $out['map'] ), true ) ) {
		continue;
	}
	$obj = wp_get_nav_menu_object( $mid );
	$out['lang_menus'][ $lang ] = array( 'id' => $mid, 'name' => $obj ? $obj->name : null, 'items' => $tree( $mid ) );
}

foreach ( $slugs as $s ) {
	$p = get_posts( array( 'name' => $s, 'post_type' => 'any', 'post_status' => 'any', 'lang' => '', 'numberposts' => 1 ) );
	if ( ! $p ) {
		$out['posts'][ $s ] = 'not found';
		continue;
	}
	$tr = array();
	foreach ( pll_get_post_translations( $p[0]->ID ) as $l => $id ) {
		$tr[ $l ] = array( 'id' => $id, 'status' => get_post_status( $id ), 'title' => get_the_title( $id ), 'slug' => get_post_field( 'post_name', $id ) );
	}
	$out['posts'][ $s ] = array( 'id' => $p[0]->ID, 'type' => $p[0]->post_type, 'lang' => pll_get_post_language( $p[0]->ID ), 'translations' => $tr );
}

$last = get_option( 'lai_menu_sync_last' );
$out['last_sync'] = $last ? array( 'time' => wp_date( 'Y-m-d H:i:s', $last['time'] ), 'stats' => $last['stats'] ) : null;
$out['dry_run']   = function_exists( 'lai_sync_all_menus' ) ? lai_sync_all_menus( true ) : 'missing';

// Логи ошибок
$logs = array( WP_CONTENT_DIR . '/debug.log', ABSPATH . 'error_log', dirname( ABSPATH ) . '/logs/staging.dragonslots-gr.gr.error_log', '/usr/local/lsws/logs/error.log' );
foreach ( glob( dirname( ABSPATH ) . '/logs/*' ) ?: array() as $g ) {
	$logs[] = $g;
}
foreach ( array_unique( $logs ) as $f ) {
	if ( is_readable( $f ) && is_file( $f ) ) {
		$lines = array_slice( file( $f ), -400 );
		$hits  = array_values( array_filter( $lines, function ( $l ) {
			return preg_match( '/fatal|memory|exhausted|timeout|maximum execution|lai-|PHP (Warning|Error)/i', $l );
		} ) );
		$out['logs'][ $f ] = array_slice( $hits, -25 );
	} else {
		$out['logs'][ $f ] = file_exists( $f ) ? 'not readable' : 'missing';
	}
}
$out['php'] = array( 'memory_limit' => ini_get( 'memory_limit' ), 'max_execution_time' => ini_get( 'max_execution_time' ), 'WP_MEMORY_LIMIT' => WP_MEMORY_LIMIT );

echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), "\n";
