<?php
$ids = array_map( 'intval', array_filter( explode( ',', $args[0] ?? '3633,3629' ) ) );
foreach ( $ids as $id ) {
	$u = get_permalink( $id );
	$b = wp_remote_retrieve_body( wp_remote_get( add_query_arg( 'r', time(), $u ), array( 'timeout' => 30, 'sslverify' => false ) ) );
	echo "\n===== #$id $u\n";
	preg_match_all( '#<script[^>]*application/ld\+json[^>]*>(.*?)</script>#s', $b, $m );
	foreach ( $m[1] as $i => $j ) {
		$d = json_decode( $j, true );
		$nodes = isset( $d['@graph'] ) ? $d['@graph'] : array( $d );
		foreach ( $nodes as $n ) {
			$t = is_array( $n['@type'] ?? '' ) ? implode( '+', $n['@type'] ) : ( $n['@type'] ?? '?' );
			if ( in_array( $t, array( 'Place', 'Organization', 'WebSite', 'ImageObject' ), true ) ) { echo "[$i] $t\n"; continue; }
			echo "[$i] $t: " . wp_json_encode( $n, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "\n";
		}
	}
	// visible breadcrumb + author box + dates in HTML
	if ( preg_match( '#<[^>]+class="[^"]*breadcrumb[^"]*"[^>]*>.*?</(div|nav|ul|p)>#s', $b, $bc ) ) echo 'BC HTML: ' . substr( preg_replace( '/\s+/', ' ', strip_tags( $bc[0], '<a>' ) ), 0, 400 ) . "\n";
	if ( preg_match( '#itemtype="http://schema.org/Article".{0,1500}#s', $b, $ar ) ) echo 'ARTICLE MICRODATA: ' . substr( preg_replace( '/\s+/', ' ', $ar[0] ), 0, 1500 ) . "\n";
}
