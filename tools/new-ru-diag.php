<?php
/** Новые/изменённые RU-записи за последние дни + проверки. */
global $wpdb;
$v = get_option( 'lai_mig_20261127' ); echo 'mig 20261127: ', is_array( $v ) ? 'applied ' . $v['time'] : 'NOT applied', "\n";
$rows = $wpdb->get_results( "SELECT ID,post_type,post_status,post_name,post_title,post_parent,post_date,post_modified FROM {$wpdb->posts} WHERE post_type IN ('service','licensing','target','industry','page','post') AND post_status IN ('publish','draft','pending','future','private') AND post_modified >= '2026-11-25 00:00:00' ORDER BY post_date DESC" );
$menu_ru = lai_sync_lang_menu_id( 'primary-menu', 'ru' );
$mitems = array(); foreach ( (array) wp_get_nav_menu_items( $menu_ru, array( 'post_status' => 'any' ) ) as $i ) $mitems[ (int) $i->object_id ][] = $i;
$byid = array(); foreach ( (array) wp_get_nav_menu_items( $menu_ru, array( 'post_status' => 'any' ) ) as $i ) $byid[ $i->ID ] = $i;
foreach ( $rows as $r ) {
	$lang = pll_get_post_language( $r->ID );
	$m = get_post_meta( $r->ID );
	$n = 0; $len = 0; $html = '';
	foreach ( $m as $k => $vv ) if ( 0 === strpos( $k, 'content_blocks_' ) && ! is_serialized( $vv[0] ) && ! preg_match( '/^field_/', $vv[0] ) ) { $n++; $len += mb_strlen( $vv[0] ); $html .= $vv[0] . "\n"; }
	$html .= $r->post_type === 'post' ? get_post_field( 'post_content', $r->ID ) : '';
	$t = get_post_meta( $r->ID, 'rank_math_title', true ); $d = get_post_meta( $r->ID, 'rank_math_description', true ); $kw = get_post_meta( $r->ID, 'rank_math_focus_keyword', true );
	$th = (int) get_post_thumbnail_id( $r->ID );
	echo "\n#{$r->ID} {$r->post_type} {$r->post_status} lang=$lang date={$r->post_date} mod={$r->post_modified} parent={$r->post_parent}\n  «{$r->post_title}»\n  ", get_permalink( $r->ID ), "\n";
	echo '  group=', wp_json_encode( pll_get_post_translations( $r->ID ) ), " tpl=", get_page_template_slug( $r->ID ), " fields=$n chars=$len layouts=", wp_json_encode( maybe_unserialize( $m['content_blocks'][0] ?? '' ) ), "\n";
	echo '  RM title(' . mb_strlen( str_replace( '%currentyear%', '2026', $t ) ) . "): $t\n  RM desc(" . mb_strlen( $d ) . "): $d\n  RM kw: $kw\n";
	echo "  thumb=$th ", $th ? basename( (string) get_attached_file( $th ) ) . ' ' . ( $th ? round( filesize( get_attached_file( $th ) ) / 1024 ) . 'KB' : '' ) . ' alt=«' . get_post_meta( $th, '_wp_attachment_image_alt', true ) . '»' : '', "\n";
	$mi = $mitems[ $r->ID ] ?? array();
	foreach ( $mi as $i ) { $p = $byid[ (int) $i->menu_item_parent ] ?? null; echo "  menu RU #{$i->ID} «{$i->title}» parent=", $p ? '«' . $p->title . '»' : 'top', " order={$i->menu_order}\n"; }
	if ( ! $mi && 'post' !== $r->post_type ) echo "  menu RU: —\n";
	preg_match_all( '#href="([^"]+)"#', $html, $mm );
	foreach ( array_unique( $mm[1] ) as $u ) {
		$abs = 0 === strpos( $u, '/' ) ? home_url( $u ) : $u;
		if ( false === strpos( $abs, wp_parse_url( home_url(), PHP_URL_HOST ) ) ) { echo "  link ext: $u\n"; continue; }
		$pid = url_to_postid( $abs );
		echo '  link ', $pid ? 'OK ' . pll_get_post_language( $pid ) . ' #' . $pid : 'UNRESOLVED', " $u\n";
	}
	foreach ( array( 'nofollow', 'target="_blank"', 'style=', '<span', '&nbsp;', 'h1' ) as $smell ) { $c = substr_count( $html, $smell ); if ( $c ) echo "  markup: $smell ×$c\n"; }
}
// menu item for parent "Изменения в компании"
foreach ( $byid as $i ) if ( false !== mb_stripos( $i->title, 'Изменени' ) || false !== mb_stripos( $i->title, 'капитал' ) || false !== mb_stripos( $i->title, 'дол' ) || false !== mb_stripos( $i->title, 'правлен' ) ) echo "MENU #{$i->ID} «{$i->title}» {$i->type}:{$i->object}/{$i->object_id} parent={$i->menu_item_parent} url={$i->url}\n";
