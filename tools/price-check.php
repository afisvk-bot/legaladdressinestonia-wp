<?php
foreach ( pll_get_post_translations( 1415 ) as $l => $id ) {
	$hits = array();
	$all = array( 'post_content' => get_post_field( 'post_content', $id ), 'post_title' => get_the_title( $id ) );
	foreach ( get_post_meta( $id ) as $k => $v ) if ( is_string( $v[0] ) ) $all[ $k ] = $v[0];
	foreach ( $all as $k => $v ) if ( preg_match_all( '/.{0,40}(1\s?[24]00|1[.,][24]00)\s?(€|EUR|евро|євро)?.{0,30}/u', $v, $m ) ) $hits[] = "$k: " . implode( ' ‖ ', array_map( fn( $x ) => preg_replace( '/\s+/', ' ', wp_strip_all_tags( $x ) ), $m[0] ) );
	echo "== $l #$id\n  " . implode( "\n  ", $hits ) . "\n";
}
foreach ( array( 118, 1308, 3, 1304, 113, 1317 ) as $id ) echo "#$id " . pll_get_post_language( $id ) . ' robots=' . wp_json_encode( get_post_meta( $id, 'rank_math_robots', true ) ) . "\n";
