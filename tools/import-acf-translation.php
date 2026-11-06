<?php
/**
 * wp eval-file tools/import-acf-translation.php <data/file.json> [dry]
 * Перевод страницы на ACF flexible content: копирует всю мету исходника (структуру блоков, цены, изображения),
 * подменяет текстовые поля переводом, локализует внутренние ссылки, ставит язык и связывает в группу Polylang.
 * Повторный запуск обновляет созданную этим скриптом запись (_lai_tr_source).
 */
$dry  = in_array( 'dry', $args, true );
$file = $args[0] ?? '';
$path = is_readable( $file ) ? $file : __DIR__ . '/' . $file;
$d    = json_decode( (string) file_get_contents( $path ), true );
if ( ! $d ) WP_CLI::error( "bad file $path" );
$src  = get_post( (int) $d['source_id'] );
$lang = $d['lang'];
$group = pll_get_post_translations( $src->ID );
$existing = $group[ $lang ] ?? 0;
if ( $existing && (int) get_post_meta( $existing, '_lai_tr_source', true ) !== $src->ID ) WP_CLI::error( "#$existing already is the $lang translation" );
wp_set_current_user( (int) ( get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) )[0] ?? 1 ) );
$host = wp_parse_url( home_url(), PHP_URL_HOST );
$log  = array();
$loc  = function ( $html ) use ( $lang, $host, &$log ) {
	return preg_replace_callback( '#href="([^"]+)"#', function ( $m ) use ( $lang, $host, &$log ) {
		$u = html_entity_decode( $m[1] );
		if ( wp_parse_url( $u, PHP_URL_HOST ) !== $host ) return $m[0];
		$pid = url_to_postid( $u );
		$tid = $pid ? pll_get_post( $pid, $lang ) : 0;
		if ( $tid && 'publish' === get_post_status( $tid ) ) { $log[] = "link $u -> " . get_permalink( $tid ); return 'href="' . esc_url( get_permalink( $tid ) ) . '"'; }
		$log[] = "link $u -> (no $lang, kept)"; return $m[0];
	}, $html );
};
$skip = array( '_edit_lock', '_edit_last', '_wp_old_slug', '_lai_tr_source', '_wp_trash_meta_status', '_wp_trash_meta_time', '_wp_desired_post_slug' );
if ( $dry ) { echo "dry: would " . ( $existing ? "update #$existing" : 'create' ) . "\n"; exit; }
$id = wp_insert_post( wp_slash( array(
	'ID' => $existing, 'post_type' => $src->post_type, 'post_status' => $src->post_status, 'post_title' => $d['title'],
	'post_name' => sanitize_title( $d['slug'] ), 'post_content' => $src->post_content, 'post_author' => $src->post_author,
	'menu_order' => $src->menu_order, 'post_parent' => $src->post_parent ? (int) pll_get_post( $src->post_parent, $lang ) : 0,
) ), true );
if ( is_wp_error( $id ) ) WP_CLI::error( $id->get_error_message() );
foreach ( get_post_meta( $src->ID ) as $k => $vals ) {
	if ( in_array( $k, $skip, true ) || 0 === strpos( $k, 'rank_math_internal' ) ) continue;
	delete_post_meta( $id, $k );
	foreach ( $vals as $v ) add_post_meta( $id, $k, wp_slash( maybe_unserialize( $v ) ) );
}
foreach ( $d['fields'] as $k => $v ) update_post_meta( $id, $k, wp_slash( $loc( $v ) ) );
update_post_meta( $id, '_lai_tr_source', $src->ID );
$tpl = get_page_template_slug( $src->ID ); if ( $tpl ) update_post_meta( $id, '_wp_page_template', $tpl );
pll_set_post_language( $id, $lang );
foreach ( get_object_taxonomies( $src->post_type ) as $tax ) {
	if ( in_array( $tax, array( 'language', 'post_translations' ), true ) ) continue;
	$terms = array();
	foreach ( wp_get_object_terms( $src->ID, $tax, array( 'fields' => 'ids' ) ) as $t ) { $terms[] = pll_is_translated_taxonomy( $tax ) ? (int) pll_get_term( $t, $lang ) : $t; }
	wp_set_object_terms( $id, array_filter( $terms ), $tax );
}
$group[ $lang ] = $id;
pll_save_post_translations( $group );
clean_post_cache( $id );
do_action( 'litespeed_purge_all' );
if ( function_exists( 'lai_sync_all_menus' ) ) lai_sync_all_menus();
echo "post #$id $lang " . get_permalink( $id ) . "\n";
echo 'group ' . wp_json_encode( pll_get_post_translations( $id ) ) . "\n";
echo implode( "\n", $log ), "\n";
$r = wp_remote_get( get_permalink( $id ), array( 'timeout' => 30, 'sslverify' => false ) );
$b = wp_remote_retrieve_body( $r );
preg_match( '#<title>(.*?)</title>#s', $b, $t ); preg_match_all( '#hreflang="([^"]+)"#', $b, $h );
echo 'http ' . wp_remote_retrieve_response_code( $r ) . ' title: ' . html_entity_decode( $t[1] ?? '' ) . ' hreflang: ' . implode( ',', array_unique( $h[1] ) ) . ' kb ' . round( strlen( $b ) / 1024 ) . "\n";
echo 'disclaimer ' . ( false !== strpos( $b, 'lai-disc' ) ? 'yes' : 'no' ) . ', faq ' . substr_count( $b, 'Часті запитання' ) . "\n";
