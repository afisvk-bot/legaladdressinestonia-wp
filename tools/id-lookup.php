<?php
foreach ( array( 844, 1037, 1269, 1401, 2156, 2183, 2189, 2190, 2254, 2342, 2343, 2344, 2345, 2346, 2776, 4490, 4529, 4578, 4699, 4724, 4757, 4758 ) as $id ) {
	$g = pll_get_post_translations( $id );
	$miss = array_diff( pll_languages_list( array( 'fields' => 'slug' ) ), array_keys( array_filter( $g, function ( $p ) { return 'publish' === get_post_status( $p ); } ) ) );
	echo "#$id ", get_post_type( $id ), " missing: ", implode( ',', $miss ), "\n";
}
foreach ( get_posts( array( 'post_type' => 'post', 'lang' => 'de', 'numberposts' => 60, 's' => 'Steuer' ) ) as $p ) echo "de post #{$p->ID} {$p->post_name}\n";
