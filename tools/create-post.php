<?php
/**
 * wp eval-file tools/create-post.php <data/dir/post-xx.json>
 * Создаёт (или обновляет созданную ранее этим скриптом) статью блога: язык Polylang, рубрика,
 * дата, автор, контент (внутренние ссылки /en-path/ локализуются), мета Rank Math, Easy TOC,
 * изображение записи (загрузка в медиатеку с alt), excerpt. Ключ идемпотентности — meta _lai_post_key.
 */
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$file = $args[0] ?? '';
$path = is_readable( __DIR__ . '/' . $file ) ? __DIR__ . '/' . $file : $file;
$d    = json_decode( (string) file_get_contents( $path ), true );
if ( ! is_array( $d ) || empty( $d['key'] ) ) WP_CLI::error( "bad data: $path" );
$dir  = dirname( $path ) . '/';
$lang = $d['lang'];

$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
wp_set_current_user( (int) $admins[0] );

$log = array();
$content = preg_replace_callback( '#href="(/[^"]*)"#', function ( $m ) use ( $lang, &$log ) {
	$url = home_url( $m[1] );
	$pid = url_to_postid( $url );
	if ( ! $pid ) {
		$p = get_page_by_path( basename( trim( $m[1], '/' ) ), OBJECT, array( 'page', 'service', 'licensing', 'industry', 'target', 'post' ) );
		$pid = $p ? $p->ID : 0;
	}
	$t = $pid ? pll_get_post( $pid, $lang ) : 0;
	if ( $t && 'publish' === get_post_status( $t ) ) {
		$log[] = $m[1] . ' -> ' . get_permalink( $t );
		return 'href="' . esc_url( get_permalink( $t ) ) . '"';
	}
	$log[] = $m[1] . ' -> NOT RESOLVED (pid ' . $pid . ')';
	return $m[0];
}, (string) file_get_contents( $dir . $d['content_file'] ) );

$existing = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'meta_key' => '_lai_post_key', 'meta_value' => $d['key'], 'lang' => '', 'fields' => 'ids', 'numberposts' => 1 ) );
$cat = (int) pll_get_term( (int) $d['cat_en'], $lang );
$arr = array(
	'post_type'     => 'post',
	'post_status'   => $d['status'] ?? 'publish',
	'post_title'    => $d['title'],
	'post_name'     => $d['slug'],
	'post_content'  => $content,
	'post_excerpt'  => $d['excerpt'] ?? '',
	'post_author'   => (int) $d['author'],
	'post_date'     => $d['date'],
	'post_date_gmt' => get_gmt_from_date( $d['date'] ),
	'post_category' => $cat ? array( $cat ) : array(),
);
if ( $existing ) $arr['ID'] = (int) $existing[0];
$id = wp_insert_post( wp_slash( $arr ), true );
if ( is_wp_error( $id ) ) WP_CLI::error( $id->get_error_message() );
pll_set_post_language( $id, $lang );
if ( $cat ) wp_set_post_categories( $id, array( $cat ) );
update_post_meta( $id, '_lai_post_key', $d['key'] );
foreach ( array( 'title' => 'rank_math_title', 'desc' => 'rank_math_description', 'kw' => 'rank_math_focus_keyword' ) as $k => $mk ) {
	if ( ! empty( $d['rm'][ $k ] ) ) update_post_meta( $id, $mk, wp_slash( $d['rm'][ $k ] ) );
}
if ( ! empty( $d['toc_from'] ) ) {
	foreach ( get_post_meta( (int) $d['toc_from'] ) as $k => $v ) {
		if ( 0 === strpos( $k, '_ez-toc-' ) ) update_post_meta( $id, $k, maybe_unserialize( $v[0] ) );
	}
}

// изображение записи
$att = 0;
if ( ! empty( $d['image'] ) ) {
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_lai_img_key', 'meta_value' => $d['image'], 'lang' => '', 'fields' => 'ids', 'numberposts' => 1 ) );
	if ( $found ) {
		$att = (int) $found[0];
	} else {
		$tmp = wp_tempnam( $d['image'] );
		copy( $dir . $d['image'], $tmp );
		$att = media_handle_sideload( array( 'name' => $d['image'], 'tmp_name' => $tmp ), $id, $d['image_title'] ?? $d['title'] );
		if ( is_wp_error( $att ) ) WP_CLI::error( 'image: ' . $att->get_error_message() );
		update_post_meta( $att, '_lai_img_key', $d['image'] );
	}
	update_post_meta( $att, '_wp_attachment_image_alt', wp_slash( $d['alt'] ?? '' ) );
	if ( function_exists( 'pll_set_post_language' ) && pll_is_translated_post_type( 'attachment' ) ) pll_set_post_language( $att, $lang );
	set_post_thumbnail( $id, $att );
}
clean_post_cache( $id );
echo wp_json_encode( array( 'id' => $id, 'status' => $existing ? 'updated' : 'created', 'url' => get_permalink( $id ), 'cat' => $cat, 'thumb' => $att, 'thumb_url' => $att ? wp_get_attachment_url( $att ) : '', 'sizes' => $att ? array_keys( (array) ( wp_get_attachment_metadata( $att )['sizes'] ?? array() ) ) : array(), 'links' => $log ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), "\n";
