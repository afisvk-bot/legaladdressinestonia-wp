<?php
foreach ( array( 4684, 4682, 4667, 4664, 4653, 4652, 4638, 4636 ) as $id ) {
	$l = pll_get_post_language( $id ); $tr = pll_get_post_translations( $id ); $own = $tr[ $l ];
	printf( "#%d %s slug=%s mod=%s | keeper #%d slug=%s mod=%s url=%s\n", $id, $l, get_post_field( 'post_name', $id ), get_post_field( 'post_modified', $id ), $own, get_post_field( 'post_name', $own ), get_post_field( 'post_modified', $own ), get_permalink( $own ) );
	$a = get_post_field( 'post_content', $id ); $b = get_post_field( 'post_content', $own ); similar_text( substr( $a, 0, 3000 ), substr( $b, 0, 3000 ), $pct ); echo "   content same: ", $a === $b ? 'identical' : round( $pct ) . '%', " len ", strlen( $a ), "/", strlen( $b ), "\n";
}
global $wpdb; $r = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}snippets WHERE id=13", ARRAY_A ); echo "==== #13\n", $r['code'], "\n";
