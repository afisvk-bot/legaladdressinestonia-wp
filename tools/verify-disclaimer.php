<?php
/** wp eval-file tools/verify-disclaimer.php — дисклеймер на страницах всех типов и языков. */
do_action( 'litespeed_purge_all' );
$texts = lai_disclaimer_texts();
$total = array( 'ok' => 0, 'fail' => 0 );
foreach ( lai_disclaimer_types() as $type ) {
	$en = get_posts( array( 'post_type' => $type, 'lang' => 'en', 'numberposts' => 1, 'post_status' => 'publish' ) );
	if ( ! $en ) { echo "$type: no EN posts\n"; continue; }
	$counts = array();
	foreach ( pll_languages_list( array( 'fields' => 'slug' ) ) as $l ) {
		$counts[ $l ] = count( get_posts( array( 'post_type' => $type, 'lang' => $l, 'numberposts' => -1, 'post_status' => 'publish', 'fields' => 'ids' ) ) );
	}
	echo "== $type (published per lang: " . wp_json_encode( $counts ) . ")\n";
	foreach ( pll_get_post_translations( $en[0]->ID ) as $l => $id ) {
		if ( 'publish' !== get_post_status( $id ) ) { continue; }
		$body = wp_remote_retrieve_body( wp_remote_get( add_query_arg( 'nc', microtime( true ), get_permalink( $id ) ), array( 'timeout' => 30, 'sslverify' => false ) ) );
		$has  = false !== strpos( $body, 'lai-disc--at-image' );
		$lbl  = $texts[ $l ][0] ?? '?';
		$ok   = $has && false !== strpos( $body, esc_html( $lbl ) );
		$img  = strpos( $body, 'single-image' ); $d = strpos( $body, 'lai-disc--at-image' ); $date = strpos( $body, 'class="data"' );
		$order = ( $img && $d && $date && $img < $d && $d < $date ) ? 'image<disc<date' : 'order?';
		$total[ $ok ? 'ok' : 'fail' ]++;
		echo sprintf( "  %-3s %-4s %s %s %s\n", $l, $ok ? 'OK' : 'FAIL', $order, $lbl, get_permalink( $id ) );
	}
}
echo 'TOTAL ', wp_json_encode( $total ), "\n";
