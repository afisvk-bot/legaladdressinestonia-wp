<?php
$all = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'lang' => '' ) );
foreach ( $all as $p ) {
	$l = pll_get_post_language( $p->ID ); $tr = pll_get_post_translations( $p->ID );
	if ( ( $tr[ $l ] ?? 0 ) !== $p->ID ) {
		$owner = $tr[ $l ] ?? 0;
		echo "#{$p->ID} $l «", mb_substr( $p->post_title, 0, 55 ), "» {$p->post_date} slug={$p->post_name} src_meta=", get_post_meta( $p->ID, '_lai_tr_source', true ), " | group says $l=#$owner «", $owner ? mb_substr( get_the_title( $owner ), 0, 40 ) : '-', "» ", $owner ? get_post_status( $owner ) . ' src_meta=' . get_post_meta( $owner, '_lai_tr_source', true ) . ' date=' . get_post_field( 'post_date', $owner ) : '', "\n";
	}
}
