<?php
/** wp eval-file tools/dup-check2.php — объём ZH-страниц vs EN, текст отдельных страниц, похожесть пар дублей. */
function t2( $id ) {
	$t = get_post_field( 'post_content', $id );
	foreach ( (array) get_post_meta( $id ) as $k => $v ) {
		if ( 0 === strpos( $k, 'content_blocks_' ) && is_string( $v[0] ) && ! is_numeric( $v[0] ) && strlen( $v[0] ) > 20 && false === strpos( $v[0], 'field_' ) ) { $t .= ' ' . $v[0]; }
	}
	return trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $t ) ) );
}
echo "== ZH volume (CJK chars) vs EN (words)\n";
foreach ( array( 'service', 'licensing', 'industry', 'target', 'post' ) as $type ) {
	foreach ( get_posts( array( 'post_type' => $type, 'lang' => 'zh', 'numberposts' => -1, 'post_status' => 'publish', 'fields' => 'ids' ) ) as $id ) {
		$zh = t2( $id ); $cjk = preg_match_all( '/\p{Han}/u', $zh );
		$en = pll_get_post( $id, 'en' ); $ew = $en ? count( preg_split( '/\s+/u', t2( $en ), -1, PREG_SPLIT_NO_EMPTY ) ) : 0;
		$lat = preg_match_all( '/[a-zA-Z]{4,}/', $zh );
		printf( "#%d %s cjk=%d latin_words=%d | en#%d %dw | ratio=%.2f\n", $id, $type, $cjk, $lat, $en, $ew, $ew ? $cjk / $ew : 0 );
	}
}
foreach ( array( 1831, 3641, 3642 ) as $id ) {
	echo "\n== #$id ", get_post_type( $id ), ' ', pll_get_post_language( $id ), ' "', get_the_title( $id ), '"', "\n", mb_substr( t2( $id ), 0, 500 ), "\n";
}
echo "\n== duplicate pairs similarity\n";
foreach ( array( array( 4545, 4535 ), array( 4516, 4504 ), array( 4471, 4468 ), array( 2461, 2456 ), array( 2376, 2372 ), array( 2378, 2373 ), array( 2526, 2515 ), array( 3641, 3642 ) ) as $p ) {
	$a = t2( $p[0] ); $b = t2( $p[1] );
	similar_text( mb_substr( $a, 0, 6000 ), mb_substr( $b, 0, 6000 ), $pct );
	$ga = pll_get_post_translations( $p[0] ); $gb = pll_get_post_translations( $p[1] );
	$in_a = in_array( $p[0], $ga, true ); $in_b = in_array( $p[1], $gb, true );
	printf( "#%d (%s, %s, mod %s, in-group:%s) vs #%d (%s, %s, mod %s, in-group:%s) similarity=%.0f%%\n",
		$p[0], get_post_field( 'post_name', $p[0] ), get_post_status( $p[0] ), get_post_field( 'post_modified', $p[0] ), $in_a ? 'y' : 'n',
		$p[1], get_post_field( 'post_name', $p[1] ), get_post_status( $p[1] ), get_post_field( 'post_modified', $p[1] ), $in_b ? 'y' : 'n', $pct );
}
echo "\n== TSD posts\n";
foreach ( get_posts( array( 'post_type' => 'post', 'lang' => '', 'numberposts' => -1, 's' => 'TSD', 'fields' => 'ids' ) ) as $id ) {
	printf( "#%d %s %s %s tr=%s\n", $id, pll_get_post_language( $id ), get_post_status( $id ), get_the_title( $id ), wp_json_encode( pll_get_post_translations( $id ) ) );
}
