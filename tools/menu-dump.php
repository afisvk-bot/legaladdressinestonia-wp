<?php
$o = (array) get_option( 'polylang', array() );
$theme = (string) get_option( 'stylesheet' );
foreach ( (array) $o['nav_menus'][ $theme ]['primary-menu'] as $lang => $mid ) {
	$items = (array) wp_get_nav_menu_items( (int) $mid );
	$by = array(); foreach ( $items as $i ) $by[ $i->ID ] = $i;
	$out = array();
	foreach ( $items as $i ) {
		$obj = 'post_type' === $i->type ? (int) $i->object_id : 0;
		$src = $obj ? (int) pll_get_post( $obj, 'ru' ) : 0;
		if ( in_array( $src, array( 4842, 4843, 4844, 4845, 4846, 4847 ), true ) || ( 'custom' === $i->type && isset( $by[ $i->menu_item_parent ] ) && 0 === (int) $by[ $i->menu_item_parent ]->menu_item_parent && in_array( $src, array(), true ) ) ) {
			$p = $by[ $i->menu_item_parent ] ?? null;
			$out[] = ( $p ? $p->title . ' › ' : '' ) . $i->title . ' → ' . wp_parse_url( $i->url, PHP_URL_PATH );
		}
	}
	echo "== $lang (" . count( $items ) . "):\n  " . implode( "\n  ", $out ) . "\n";
}
