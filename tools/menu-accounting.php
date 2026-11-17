<?php
/** Пункты EN мастер-меню: дерево с подписями, чтобы найти Accounting & Tax. */
$map = function_exists( 'lai_menu_map' ) ? lai_menu_map() : array();
echo 'map: ', wp_json_encode( $map ), "\n";
foreach ( array_unique( array_map( 'intval', (array) $map ) ) as $mid ) {
	$o = wp_get_nav_menu_object( $mid ); echo "== menu $mid ", $o ? $o->name : '?', "\n";
	$items = (array) wp_get_nav_menu_items( $mid ); $by = array();
	foreach ( $items as $i ) $by[ (int) $i->menu_item_parent ][] = $i;
	$walk = function ( $p, $d ) use ( &$walk, $by ) { foreach ( $by[ $p ] ?? array() as $i ) { echo str_repeat( '  ', $d ), "#{$i->ID} o={$i->menu_order} {$i->type}:{$i->object}/{$i->object_id} cls=", implode( ' ', array_filter( (array) $i->classes ) ), " \"{$i->title}\" {$i->url}\n"; $walk( $i->ID, $d + 1 ); } };
	$walk( 0, 0 );
}
