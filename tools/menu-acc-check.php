<?php
$o = get_option( 'polylang' ); $theme = get_option( 'stylesheet' );
echo 'locations: ', wp_json_encode( $o['nav_menus'][ $theme ] ?? null ), "\n";
foreach ( wp_get_nav_menus() as $menu ) {
	$items = (array) wp_get_nav_menu_items( $menu->term_id );
	$acc = null; foreach ( $items as $i ) if ( 'post_type' === $i->type && (int) pll_get_post( (int) $i->object_id, 'en' ) === 2746 ) $acc = $i;
	if ( ! $acc ) { echo "== {$menu->term_id} {$menu->name}: no accounting item\n"; continue; }
	$kids = array_filter( $items, function ( $i ) use ( $acc ) { return (int) $i->menu_item_parent === (int) $acc->ID; } );
	usort( $kids, function ( $a, $b ) { return $a->menu_order <=> $b->menu_order; } );
	echo "== {$menu->term_id} {$menu->name}: «{$acc->title}» → ", implode( ' | ', array_map( function ( $i ) { return $i->title; }, $kids ) ), "\n";
}
