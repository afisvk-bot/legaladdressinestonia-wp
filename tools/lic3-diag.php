<?php
foreach ( array( 'lai_mig_20261127' ) as $o ) { $v = get_option( $o ); echo $o, ': ', is_array( $v ) ? 'applied ' . $v['time'] : 'NOT applied', "\n"; }
foreach ( array( 'creditor-licence', 'fund-manager', 'gambling-licence' ) as $s ) {
	$p = get_page_by_path( $s, OBJECT, 'licensing' );
	if ( ! $p ) { echo "$s: NOT FOUND\n"; continue; }
	echo "== $s #{$p->ID} {$p->post_status} lang=", pll_get_post_language( $p->ID ), " title «{$p->post_title}» mod={$p->post_modified}\n";
	foreach ( pll_get_post_translations( $p->ID ) as $l => $id ) {
		$m = get_post_meta( $id ); $n = 0; $len = 0;
		foreach ( $m as $k => $v ) if ( 0 === strpos( $k, 'content_blocks_' ) && ! is_serialized( $v[0] ) && ! preg_match( '/^field_/', $v[0] ) ) { $n++; $len += mb_strlen( $v[0] ); }
		echo "  $l #$id ", get_post_status( $id ), ' ', get_permalink( $id ), " fields=$n chars=$len layouts=", wp_json_encode( maybe_unserialize( $m['content_blocks'][0] ?? '' ) ), ' content=', mb_strlen( get_post_field( 'post_content', $id ) ), ' rm=', get_post_meta( $id, 'rank_math_title', true ), "\n";
	}
}
// other licensing pages for reference
foreach ( get_posts( array( 'post_type' => 'licensing', 'lang' => 'ru', 'numberposts' => -1, 'post_status' => 'publish' ) ) as $p ) {
	$m = get_post_meta( $p->ID ); $len = 0; foreach ( $m as $k => $v ) if ( 0 === strpos( $k, 'content_blocks_' ) ) $len += mb_strlen( $v[0] );
	echo "RU #{$p->ID} {$p->post_name} chars=$len layouts=", wp_json_encode( maybe_unserialize( $m['content_blocks'][0] ?? '' ) ), "\n";
}
