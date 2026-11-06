<?php
foreach ( array_map( 'intval', explode( ',', $args[0] ) ) as $id ) {
	preg_match_all( '#<h[23][^>]*>(.*?)</h[23]>#s', get_post_field( 'post_content', $id ), $m );
	echo "#$id " . get_the_title( $id ) . "\n   " . implode( ' | ', array_map( fn( $h ) => trim( wp_strip_all_tags( $h ) ), array_slice( $m[1], 0, 18 ) ) ) . "\n";
}
