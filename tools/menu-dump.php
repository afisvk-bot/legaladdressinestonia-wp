<?php
$o = (array) get_option( 'polylang', array() );
$theme = (string) get_option( 'stylesheet' );
$ru = (int) ( $o['nav_menus'][ $theme ]['primary-menu']['ru'] ?? 0 );
foreach ( array( 'EN' => (int) lai_menu_map()['primary-menu'], 'RU' => $ru ) as $n => $mid ) {
	$items = (array) wp_get_nav_menu_items( $mid, array( 'post_status' => 'any' ) );
	echo "== $n menu $mid (" . count( $items ) . ")\n";
	$kids = array();
	foreach ( $items as $i ) $kids[ (int) $i->menu_item_parent ][] = $i;
	$walk = function ( $p, $d ) use ( &$walk, $kids ) {
		foreach ( $kids[ $p ] ?? array() as $i ) {
			echo str_repeat( '  ', $d ) . "#{$i->ID} [{$i->type}" . ( 'post_type' === $i->type ? ":{$i->object_id}" : '' ) . "] «{$i->title}» stored«{$i->post_title}» o={$i->menu_order}\n";
			$walk( (int) $i->ID, $d + 1 );
		}
	};
	$walk( 0, 0 );
}
foreach ( array( 4842, 4843, 4844, 4845, 4846, 4847, 4758 ) as $id ) {
	echo "#$id ", wp_json_encode( pll_get_post_translations( $id ) ), " thumb=", get_post_thumbnail_id( $id ), " ", get_post_meta( get_post_thumbnail_id( $id ), '_wp_attached_file', true ), "\n";
}
