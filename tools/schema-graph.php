<?php
$ids = array_filter( explode( ',', $args[0] ?? '3633,3629' ) );
if ( in_array( 'zh', $ids, true ) ) { $ids = array_diff( $ids, array( 'zh' ) ); $ids[] = pll_get_post( 3633, 'zh' ) ?: get_posts( array( 'post_type' => 'post', 'lang' => 'zh', 'numberposts' => 1, 'fields' => 'ids' ) )[0]; }
foreach ( $ids as $id ) {
	$u = is_numeric( $id ) ? get_permalink( (int) $id ) : home_url( $id );
	$b = wp_remote_retrieve_body( wp_remote_get( add_query_arg( 'r', time(), $u ), array( 'timeout' => 30, 'sslverify' => false ) ) );
	echo "\n===== #$id $u\n";
	preg_match_all( '#<script[^>]*application/ld\+json[^>]*>(.*?)</script>#s', $b, $m );
	foreach ( $m[1] as $i => $j ) {
		$d = json_decode( $j, true );
		$nodes = isset( $d['@graph'] ) ? $d['@graph'] : array( $d );
		foreach ( $nodes as $n ) {
			$t = is_array( $n['@type'] ?? '' ) ? implode( '+', $n['@type'] ) : ( $n['@type'] ?? '?' );
			if ( in_array( $t, array( 'Place', 'Organization', 'WebSite', 'ImageObject', 'FAQPage' ), true ) ) { echo "[$i] $t\n"; continue; }
			echo "[$i] $t: " . wp_json_encode( $n, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "\n";
		}
	}
	// visible breadcrumb + author box + dates in HTML
	if ( preg_match( '#<[^>]+class="[^"]*breadcrumb[^"]*"[^>]*>.*?</(div|nav|ul|p)>#s', $b, $bc ) ) echo 'BC HTML: ' . substr( preg_replace( '/\s+/', ' ', strip_tags( $bc[0], '<a>' ) ), 0, 400 ) . "\n";
	preg_match_all( '#itemtype="([^"]+)"#', $b, $mi ); echo 'microdata: ' . implode( ',', array_unique( $mi[1] ) ) . "\n";
	if ( preg_match( '#<aside class="lai-ab".*?</aside>#s', $b, $ab ) ) echo 'AUTHOR BOX: ' . preg_replace( '/\s+/', ' ', strip_tags( $ab[0], '<a><img>' ) ) . "\n";
	if ( preg_match( '#<div class="lai-updated">.*?</div>#s', $b, $up ) ) echo 'UPDATED: ' . strip_tags( $up[0] ) . "\n";
}
