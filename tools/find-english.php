<?php
$id = (int) $args[0];
foreach ( get_post_meta( $id ) as $k => $v ) {
	if ( ! is_string( $v[0] ) || is_serialized( $v[0] ) ) continue;
	$t = wp_strip_all_tags( $v[0] );
	if ( preg_match_all( '/\b(the|and|with|which|your|for|of|to)\b/i', $t ) >= 1 && ! preg_match( '/^(field_|rank_math)/', $k . $v[0] ) ) echo "$k: " . mb_substr( preg_replace( '/\s+/', ' ', $t ), 0, 140 ) . "\n";
}
$b = wp_remote_retrieve_body( wp_remote_get( add_query_arg( 'r', time(), get_permalink( $id ) ), array( 'sslverify' => false ) ) );
$b = preg_replace( '#<(script|style|header|footer|nav)\b.*?</\1>#si', '', $b );
preg_match_all( '/[^.>]{0,50}\b(the|and|with|which)\b[^.<]{0,50}/i', wp_strip_all_tags( $b ), $m ); echo "--- in page body:\n" . implode( "\n", array_slice( array_unique( array_map( 'trim', $m[0] ) ), 0, 25 ) ) . "\n";
