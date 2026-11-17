<?php
global $wpdb;
echo "polylang sync option: ", wp_json_encode( ( get_option( 'polylang' ) )['sync'] ?? null ), "\n";
foreach ( $wpdb->get_results( "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key LIKE '%pll%sync%' OR meta_key = '_pll_sync_post' ORDER BY post_id" ) as $r ) {
	$p = get_post( $r->post_id );
	echo "#{$r->post_id} {$p->post_type} {$p->post_status} ", pll_get_post_language( $r->post_id ), " \"", mb_substr( $p->post_title, 0, 50 ), "\" {$r->meta_key}={$r->meta_value}\n";
}
echo "pll meta keys on 4578: ", implode( ',', array_filter( array_keys( get_post_meta( 4578 ) ), function ( $k ) { return false !== stripos( $k, 'pll' ); } ) ), "\n";
