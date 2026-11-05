<?php
/**
 * wp eval-file tools/seo-audit.php — сводный технический SEO-аудит (только чтение).
 */
global $wpdb;
@set_time_limit( 900 );
$types = array( 'page', 'post', 'service', 'licensing', 'industry', 'target' );
$langs = pll_languages_list( array( 'fields' => 'slug' ) );
$out   = array();

/* ---------- 1. По базе: все опубликованные записи ---------- */
$rows = array();
foreach ( $types as $t ) {
	foreach ( get_posts( array( 'post_type' => $t, 'post_status' => 'publish', 'numberposts' => -1, 'lang' => '', 'fields' => 'ids' ) ) as $id ) {
		$lang  = pll_get_post_language( $id );
		$title = get_post_meta( $id, 'rank_math_title', true );
		$desc  = get_post_meta( $id, 'rank_math_description', true );
		$kw    = get_post_meta( $id, 'rank_math_focus_keyword', true );
		$robots = (array) get_post_meta( $id, 'rank_math_robots', true );
		// текст: контент + текстовые поля ACF flexible content
		$text = get_post_field( 'post_content', $id );
		foreach ( (array) get_post_meta( $id ) as $k => $v ) {
			if ( preg_match( '/^content_blocks_\d+_(content|text_left|text_right|final_cta_text|provider_text|faq_items_\d+_answer)$/', $k ) ) {
				$text .= ' ' . $v[0];
			}
		}
		$words = count( preg_split( '/\s+/u', trim( wp_strip_all_tags( $text ) ), -1, PREG_SPLIT_NO_EMPTY ) );
		$tr    = pll_get_post_translations( $id );
		$rows[] = array(
			'id' => $id, 'type' => $t, 'lang' => $lang, 'url' => get_permalink( $id ), 'post_title' => get_the_title( $id ),
			'rm_title' => $title, 'rm_desc' => $desc, 'kw' => $kw, 'noindex' => in_array( 'noindex', $robots, true ),
			'words' => $words, 'translations' => count( $tr ), 'thumb' => (bool) get_post_thumbnail_id( $id ),
		);
	}
}
$out['counts'] = array();
foreach ( $rows as $r ) {
	$out['counts'][ $r['type'] ][ $r['lang'] ] = ( $out['counts'][ $r['type'] ][ $r['lang'] ] ?? 0 ) + 1;
}
$total = count( $rows );
$f = function ( $cb ) use ( $rows ) { return array_values( array_filter( $rows, $cb ) ); };
$no_desc  = $f( fn( $r ) => '' === trim( (string) $r['rm_desc'] ) );
$no_kw    = $f( fn( $r ) => '' === trim( (string) $r['kw'] ) );
$noindex  = $f( fn( $r ) => $r['noindex'] );
$thin     = $f( fn( $r ) => $r['words'] < 300 && in_array( $r['type'], array( 'service', 'licensing', 'industry', 'target', 'post' ), true ) );
$titles   = array();
foreach ( $rows as $r ) {
	$k = mb_strtolower( trim( $r['rm_title'] ?: $r['post_title'] ) );
	$titles[ $k ][] = $r['lang'] . ':' . $r['id'];
}
$dup_titles = array_filter( $titles, fn( $v ) => count( $v ) > 1 );
$untranslated = array();
foreach ( $rows as $r ) {
	if ( 'en' === $r['lang'] && $r['translations'] < count( $langs ) && 'page' !== $r['type'] ) {
		$untranslated[] = $r['type'] . ' ' . $r['url'] . ' (' . $r['translations'] . '/' . count( $langs ) . ')';
	}
}
$out['db'] = array(
	'total_published' => $total,
	'no_meta_description' => count( $no_desc ), 'no_meta_description_by_lang' => array_count_values( array_column( $no_desc, 'lang' ) ),
	'no_focus_keyword' => count( $no_kw ),
	'noindex' => array_map( fn( $r ) => $r['url'], $noindex ),
	'thin_lt300_words' => count( $thin ), 'thin_examples' => array_slice( array_map( fn( $r ) => $r['words'] . 'w ' . $r['url'], $thin ), 0, 25 ),
	'duplicate_titles' => count( $dup_titles ), 'duplicate_title_examples' => array_slice( $dup_titles, 0, 10, true ),
	'en_not_fully_translated' => array_slice( $untranslated, 0, 30 ), 'en_not_fully_translated_count' => count( $untranslated ),
	'no_thumbnail' => count( $f( fn( $r ) => ! $r['thumb'] && 'page' !== $r['type'] ) ),
	'words_median_by_type' => ( function () use ( $rows ) {
		$m = array();
		foreach ( $rows as $r ) { $m[ $r['type'] ][] = $r['words']; }
		foreach ( $m as $t => $w ) { sort( $w ); $m[ $t ] = $w[ intdiv( count( $w ), 2 ) ]; }
		return $m;
	} )(),
);

/* ---------- 2. Внутренние ссылки (таблица Rank Math) ---------- */
$tbl = $wpdb->prefix . 'rank_math_internal_meta';
if ( $wpdb->get_var( "SHOW TABLES LIKE '$tbl'" ) === $tbl ) {
	$orph = array();
	foreach ( $rows as $r ) {
		if ( in_array( $r['type'], array( 'service', 'licensing', 'industry', 'target', 'post' ), true ) ) {
			$in = (int) $wpdb->get_var( $wpdb->prepare( "SELECT incoming_link_count FROM $tbl WHERE object_id=%d", $r['id'] ) );
			if ( $in === 0 ) { $orph[] = $r['lang'] . ' ' . $r['url']; }
		}
	}
	$out['links'] = array( 'zero_incoming_internal_links' => count( $orph ), 'examples' => array_slice( $orph, 0, 25 ) );
}

/* ---------- 3. Изображения ---------- */
$att = $wpdb->get_results( "SELECT ID FROM {$wpdb->posts} WHERE post_type='attachment' AND post_mime_type LIKE 'image/%'" );
$no_alt = 0; $big = array(); $fmt = array();
foreach ( $att as $a ) {
	if ( '' === trim( (string) get_post_meta( $a->ID, '_wp_attachment_image_alt', true ) ) ) { $no_alt++; }
	$file = get_attached_file( $a->ID );
	if ( $file && file_exists( $file ) ) {
		$sz  = filesize( $file );
		$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
		$fmt[ $ext ] = ( $fmt[ $ext ] ?? 0 ) + 1;
		if ( $sz > 300 * 1024 ) { $big[] = round( $sz / 1024 ) . 'KB ' . basename( $file ); }
	}
}
rsort( $big, SORT_NATURAL );
$out['images'] = array( 'total' => count( $att ), 'no_alt' => $no_alt, 'formats' => $fmt, 'over_300kb' => count( $big ), 'largest' => array_slice( $big, 0, 12 ) );

/* ---------- 4. Рендер ключевых страниц ---------- */
$sample = array();
foreach ( array( 'service', 'licensing', 'industry', 'target', 'post' ) as $t ) {
	$en = get_posts( array( 'post_type' => $t, 'lang' => 'en', 'numberposts' => 2, 'post_status' => 'publish', 'fields' => 'ids' ) );
	foreach ( $en as $id ) {
		$sample[] = $id;
		foreach ( array( 'ru', 'de' ) as $l ) { if ( $x = pll_get_post( $id, $l ) ) { $sample[] = $x; } }
	}
}
foreach ( $langs as $l ) { $sample[] = 'home:' . $l; }
$render = array();
foreach ( $sample as $s ) {
	$url = 0 === strpos( (string) $s, 'home:' ) ? pll_home_url( substr( $s, 5 ) ) : get_permalink( $s );
	$t0  = microtime( true );
	$res = wp_remote_get( add_query_arg( 'audit', 1, $url ), array( 'timeout' => 40, 'sslverify' => false ) );
	$ms  = round( ( microtime( true ) - $t0 ) * 1000 );
	$b   = wp_remote_retrieve_body( $res );
	preg_match( '#<title>(.*?)</title>#s', $b, $tt );
	preg_match( '#<meta name="description" content="([^"]*)"#', $b, $dd );
	preg_match( '#<link rel="canonical" href="([^"]+)"#', $b, $cc );
	preg_match_all( '#<h1[\s>]#i', $b, $h1 );
	preg_match_all( '#hreflang="([^"]+)"#', $b, $hl );
	preg_match_all( '#"@type"\s*:\s*"([^"]+)"#', $b, $st );
	preg_match_all( '#<img\b(?![^>]*\balt="[^"]+")[^>]*>#i', $b, $noalt );
	preg_match_all( '#<script\b#i', $b, $sc );
	preg_match( '#<meta name="robots" content="([^"]*)"#', $b, $rb );
	$render[] = array(
		'url' => $url, 'http' => wp_remote_retrieve_response_code( $res ), 'ms' => $ms, 'kb' => round( strlen( $b ) / 1024 ),
		'title' => html_entity_decode( trim( $tt[1] ?? '' ) ), 'title_len' => mb_strlen( html_entity_decode( trim( $tt[1] ?? '' ) ) ),
		'desc_len' => mb_strlen( html_entity_decode( $dd[1] ?? '' ) ), 'h1' => count( $h1[0] ),
		'canonical_self' => isset( $cc[1] ) ? ( untrailingslashit( strtok( $cc[1], '?' ) ) === untrailingslashit( strtok( $url, '?' ) ) ) : null,
		'hreflang' => count( array_unique( $hl[1] ) ), 'x_default' => in_array( 'x-default', $hl[1], true ),
		'schema' => array_values( array_unique( $st[1] ) ), 'img_no_alt' => count( $noalt[0] ), 'scripts' => count( $sc[0] ),
		'robots' => $rb[1] ?? '',
	);
}
$out['render'] = $render;

/* ---------- 5. Прочее ---------- */
$out['rank_math_404_last30d'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}rank_math_404_logs WHERE accessed > DATE_SUB(NOW(), INTERVAL 30 DAY)" );
$out['rank_math_404_top'] = $wpdb->get_results( "SELECT uri, times_accessed FROM {$wpdb->prefix}rank_math_404_logs ORDER BY times_accessed DESC LIMIT 15", ARRAY_A );
$out['plugins_active'] = get_option( 'active_plugins' );
$out['yoast_tables_present'] = (bool) $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}yoast_indexable'" );
$out['permalink'] = get_option( 'permalink_structure' );
$out['blog_public'] = get_option( 'blog_public' );

echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), "\n";
