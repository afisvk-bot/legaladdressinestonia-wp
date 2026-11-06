<?php
foreach ( array( 6, 7 ) as $u ) {
	echo "== USER $u " . get_the_author_meta( 'display_name', $u ) . " slug=" . get_the_author_meta( 'user_nicename', $u ) . "\n";
	foreach ( get_user_meta( $u ) as $k => $v ) {
		if ( preg_match( '/^(description|rank_math|facebook|twitter|linkedin|additional|job|simple_local_avatar|wp_user_avatar|avatar|.*avatar.*|.*photo.*|first_name|last_name|url|.*social.*)/i', $k ) ) echo "  $k = " . mb_substr( preg_replace( '/\s+/', ' ', $v[0] ), 0, 300 ) . "\n";
	}
	echo '  avatar_url(256)=' . get_avatar_url( $u, array( 'size' => 256 ) ) . "\n";
	echo '  posts by lang: ' . wp_json_encode( array_count_values( array_map( 'pll_get_post_language', get_posts( array( 'author' => $u, 'post_type' => 'post', 'numberposts' => -1, 'lang' => '', 'fields' => 'ids' ) ) ) ) ) . "\n";
}
foreach ( pll_languages_list( array( 'fields' => 'slug' ) ) as $l ) {
	$p = pll_get_post( 1199, $l );
	echo "expert page $l #$p: " . wp_json_encode( array( get_field( 'expert-name', $p ), get_field( 'expert-jobtitle', $p ), mb_substr( (string) get_field( 'expert-desc', $p ), 0, 150 ), get_field( 'expert-label', $p ), get_field( 'expert-linkedin', $p ) ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "\n";
}
echo 'post authors: ' . wp_json_encode( array_count_values( array_map( fn( $i ) => get_post_field( 'post_author', $i ), get_posts( array( 'post_type' => 'post', 'numberposts' => -1, 'lang' => '', 'fields' => 'ids' ) ) ) ) ) . "\n";
