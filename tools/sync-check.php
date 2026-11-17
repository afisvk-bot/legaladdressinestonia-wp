<?php
global $wpdb;
$n = 0;
foreach ( $wpdb->get_results( "SELECT t.term_id, tt.description FROM {$wpdb->term_taxonomy} tt JOIN {$wpdb->terms} t ON t.term_id=tt.term_id WHERE tt.taxonomy='post_translations'" ) as $r ) {
	$d = maybe_unserialize( $r->description );
	if ( is_array( $d ) && ! empty( $d['sync'] ) ) {
		$n++;
		$ids = array(); foreach ( $d as $l => $id ) if ( 'sync' !== $l ) $ids[] = "$l:$id";
		$first = (int) ( $d['en'] ?? $d['ru'] ?? reset( $d ) );
		echo "term {$r->term_id} sync=", wp_json_encode( $d['sync'] ), " | ", implode( ' ', $ids ), " | ", get_post_type( $first ), " \"", mb_substr( get_the_title( $first ), 0, 50 ), "\"\n";
	}
}
echo "groups with sync: $n\n";
