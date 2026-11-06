<?php
/** wp eval-file tools/dup-inventory.php — дубли title, язык Polylang vs фактический язык текста, связи переводов. */
$types = array( 'page', 'post', 'service', 'licensing', 'industry', 'target' );

function lai_inv_text( $id ) {
	$t = get_post_field( 'post_content', $id );
	foreach ( (array) get_post_meta( $id ) as $k => $v ) {
		if ( 0 === strpos( $k, 'content_blocks_' ) && is_string( $v[0] ) && ! is_numeric( $v[0] ) && strlen( $v[0] ) > 20 && false === strpos( $v[0], 'field_' ) ) {
			$t .= ' ' . $v[0];
		}
	}
	return trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $t ) ) );
}
function lai_inv_guess( $s ) {
	$s = mb_substr( $s, 0, 4000 );
	$len = max( 1, preg_match_all( '/\p{L}/u', $s ) );
	$cjk = preg_match_all( '/\p{Han}/u', $s ) / $len;
	$cyr = preg_match_all( '/\p{Cyrillic}/u', $s ) / $len;
	if ( $cjk > 0.2 ) return 'zh';
	if ( $cyr > 0.3 ) return preg_match_all( '/[іїєґ]/u', mb_strtolower( $s ) ) > 3 ? 'uk' : 'ru';
	$w = ' ' . mb_strtolower( preg_replace( '/[^\p{L}\s]/u', ' ', $s ) ) . ' ';
	$sc = array(
		'en' => array( ' the ', ' and ', ' of ', ' to ', ' is ', ' for ', ' with ' ),
		'de' => array( ' der ', ' die ', ' und ', ' das ', ' ist ', ' für ', ' mit ', ' nicht ' ),
		'fr' => array( ' le ', ' la ', ' les ', ' et ', ' des ', ' pour ', ' est ', ' une ' ),
		'es' => array( ' el ', ' los ', ' las ', ' y ', ' para ', ' una ', ' con ', ' del ' ),
		'pt' => array( ' os ', ' as ', ' e ', ' para ', ' uma ', ' com ', ' não ', ' do ', ' da ' ),
		'tr' => array( ' ve ', ' bir ', ' için ', ' bu ', ' ile ', ' olarak ', ' da ', ' de ' ),
	);
	$best = '?'; $bv = 0;
	foreach ( $sc as $l => $words ) {
		$n = 0;
		foreach ( $words as $x ) { $n += substr_count( $w, $x ); }
		if ( $n > $bv ) { $bv = $n; $best = $l; }
	}
	return $best;
}
function lai_inv_row( $id ) {
	$txt = lai_inv_text( $id );
	$tr  = array();
	foreach ( pll_get_post_translations( $id ) as $l => $tid ) { $tr[ $l ] = $tid; }
	return array(
		'id' => $id, 'type' => get_post_type( $id ), 'status' => get_post_status( $id ), 'lang' => pll_get_post_language( $id ),
		'text_lang' => lai_inv_guess( get_the_title( $id ) . ' ' . $txt ), 'slug' => get_post_field( 'post_name', $id ),
		'title' => get_the_title( $id ), 'rm_title' => get_post_meta( $id, 'rank_math_title', true ),
		'words' => count( preg_split( '/\s+/u', $txt, -1, PREG_SPLIT_NO_EMPTY ) ), 'date' => get_post_field( 'post_date', $id ),
		'modified' => get_post_field( 'post_modified', $id ), 'tr' => $tr, 'url' => get_permalink( $id ),
		'tpl' => get_page_template_slug( $id ), 'layouts' => (array) get_post_meta( $id, 'content_blocks', true ),
	);
}

$all = array();
foreach ( $types as $t ) {
	foreach ( get_posts( array( 'post_type' => $t, 'post_status' => 'publish', 'numberposts' => -1, 'lang' => '', 'fields' => 'ids' ) ) as $id ) {
		$all[ $id ] = lai_inv_row( $id );
	}
}

$out = array();
// 1. одинаковые title
$by = array();
foreach ( $all as $r ) { $by[ mb_strtolower( trim( $r['rm_title'] ?: $r['title'] ) ) ][] = $r['id']; }
foreach ( $by as $k => $ids ) {
	if ( count( $ids ) > 1 ) {
		$out['dup_titles'][] = array_map( fn( $i ) => array_diff_key( $all[ $i ], array( 'layouts' => 1 ) ), $ids );
	}
}
// 2. язык текста не совпадает с языком Polylang
foreach ( $all as $r ) {
	if ( '?' !== $r['text_lang'] && $r['text_lang'] !== $r['lang'] ) {
		$out['lang_mismatch'][] = sprintf( '#%d %s %s pll=%s text=%s %dw tr=%s "%s" %s', $r['id'], $r['type'], $r['status'], $r['lang'], $r['text_lang'], $r['words'], wp_json_encode( $r['tr'] ), $r['title'], $r['slug'] );
	}
}
// 3. записи без связи (нет ни одного перевода) — по типам
foreach ( $all as $r ) {
	if ( count( $r['tr'] ) <= 1 && 'page' !== $r['type'] ) {
		$out['unlinked'][] = sprintf( '#%d %s %s text=%s %dw "%s" %s', $r['id'], $r['type'], $r['lang'], $r['text_lang'], $r['words'], $r['title'], $r['slug'] );
	}
}
// 4. короткие (<300 слов) не-страницы
foreach ( $all as $r ) {
	if ( $r['words'] < 300 && 'page' !== $r['type'] ) {
		$out['thin'][] = sprintf( '#%d %s %s text=%s %dw layouts=%s tr=%s "%s" %s', $r['id'], $r['type'], $r['lang'], $r['text_lang'], $r['words'], implode( ',', array_slice( $r['layouts'], 0, 8 ) ), wp_json_encode( $r['tr'] ), $r['title'], $r['slug'] );
	}
}
// 5. группы переводов, где у языка 2+ записи одного типа с похожим slug (кандидаты в дубли внутри языка)
$counts = array();
foreach ( $all as $r ) { $counts[ $r['type'] ][ $r['lang'] ] = ( $counts[ $r['type'] ][ $r['lang'] ] ?? 0 ) + 1; }
$out['counts'] = $counts;

echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), "\n";
