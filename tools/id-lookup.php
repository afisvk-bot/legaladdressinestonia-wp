<?php
foreach ( array( 2696, 2746, 4490, 4529, 4842, 4843, 4724, 4699, 4758, 4757, 2776 ) as $id ) {
	$p = get_post( $id );
	if ( ! $p ) { echo "#$id missing\n"; continue; }
	echo "#$id {$p->post_type} {$p->post_status} ", pll_get_post_language( $id ), " ", get_permalink( $id ), " «{$p->post_title}»\n";
}
foreach ( array( 'rabochaya-viza-i-vnzh-v-estonii', 'korporativnyj-nalog-estoniya-2026', 'buhgalteriya-dlya-e-rezidentov' ) as $s ) {
	$q = get_posts( array( 'name' => $s, 'post_type' => 'any', 'post_status' => 'any', 'lang' => '', 'numberposts' => 3 ) );
	foreach ( $q as $p ) echo "$s => #{$p->ID} {$p->post_type} ", pll_get_post_language( $p->ID ), " ", get_permalink( $p ), "\n";
	if ( ! $q ) echo "$s => none\n";
}
