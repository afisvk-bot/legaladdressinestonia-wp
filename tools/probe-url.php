<?php
foreach ( $args as $u ) {
	$url = home_url( $u ); $pid = url_to_postid( $url );
	$p = $pid ? null : get_page_by_path( 'startups', OBJECT, array( 'industry' ) );
	echo "$u pid=$pid bypath=" . ( $p ? $p->ID . ' ' . $p->post_type : '-' ) . "\n";
	$id = $pid ?: ( $p ? $p->ID : 0 );
	if ( $id ) echo wp_json_encode( pll_get_post_translations( $id ) ), "\n";
	foreach ( get_posts( array( 'post_type' => 'industry', 'name' => 'startups', 'lang' => '', 'post_status' => 'any' ) ) as $q ) echo $q->ID, ' ', $q->post_status, ' ', get_permalink( $q ), "\n";
}
