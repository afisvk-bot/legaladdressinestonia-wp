<?php
/** Диагностика 3 новых RU-страниц и меню RU/EN. */
$slugs = array( 'likvidatsiya-kompanii-v-estonii-zakryt-prodat-ili-vosstanovit-o-pravilno', 'registratsiya-tovarnogo-znaka-i-patenta-v-estonii-i-es-zashhita-brenda-ot-razrabotki-strategii-do-polucheniya-svidetelstva', 'estonskaya-kompaniya-dlya-ukrainskih-predprinimatelej' );
global $wpdb;
$ids = array();
foreach ( $slugs as $s ) {
	foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT ID,post_type,post_status,post_parent,menu_order,post_date,post_modified FROM {$wpdb->posts} WHERE post_name=%s AND post_type NOT IN ('revision','nav_menu_item')", $s ) ) as $r ) {
		$ids[] = (int) $r->ID;
		$m = get_post_meta( $r->ID );
		$acf = array_filter( array_keys( $m ), function ( $k ) { return 0 === strpos( $k, 'content_blocks' ); } );
		echo "#{$r->ID} {$r->post_type} {$r->post_status} parent={$r->post_parent} lang=", pll_get_post_language( $r->ID ), " date={$r->post_date} mod={$r->post_modified}\n  url ", get_permalink( $r->ID ), "\n  title «", get_the_title( $r->ID ), "»\n  group ", wp_json_encode( pll_get_post_translations( $r->ID ) ), " tpl=", get_post_meta( $r->ID, '_wp_page_template', true ), " thumb=", get_post_thumbnail_id( $r->ID ), "\n  content_len=", mb_strlen( get_post_field( 'post_content', $r->ID ) ), " acf_keys=", count( $acf ), " layouts=", wp_json_encode( maybe_unserialize( $m['content_blocks'][0] ?? '' ) ), "\n  rm: ", get_post_meta( $r->ID, 'rank_math_title', true ), ' | ', get_post_meta( $r->ID, 'rank_math_description', true ), ' | ', get_post_meta( $r->ID, 'rank_math_focus_keyword', true ), "\n";
		$tx = array(); foreach ( get_object_taxonomies( $r->post_type ) as $t ) { $terms = wp_get_object_terms( $r->ID, $t, array( 'fields' => 'id=>name' ) ); if ( $terms && ! is_wp_error( $terms ) && 'language' !== $t && 'post_translations' !== $t ) $tx[ $t ] = $terms; } echo '  tax ', wp_json_encode( $tx, JSON_UNESCAPED_UNICODE ), "\n";
	}
}
$walk_menu = function ( $mid, $label ) use ( $ids ) {
	$o = wp_get_nav_menu_object( $mid ); echo "== $label menu $mid ", $o ? $o->name : '?', "\n";
	$items = (array) wp_get_nav_menu_items( $mid, array( 'post_status' => 'any' ) ); $by = array();
	foreach ( $items as $i ) $by[ (int) $i->menu_item_parent ][] = $i;
	$walk = function ( $p, $d ) use ( &$walk, $by, $ids ) { foreach ( $by[ $p ] ?? array() as $i ) { echo str_repeat( '  ', $d ), ( in_array( (int) $i->object_id, $ids, true ) ? '>>' : '' ), "#{$i->ID} o={$i->menu_order} {$i->type}:{$i->object}/{$i->object_id} \"{$i->title}\" ", get_post_meta( $i->ID, '_lai_synced_title', true ) ? '[synced]' : '', "\n"; $walk( $i->ID, $d + 1 ); } };
	$walk( 0, 0 );
};
foreach ( lai_menu_map() as $loc => $mid ) {
	$walk_menu( $mid, "EN master $loc" );
	$walk_menu( lai_sync_lang_menu_id( $loc, 'ru' ), "RU $loc" );
}
