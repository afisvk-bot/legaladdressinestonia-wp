<?php
$g = pll_get_post_translations( 4572 );
echo 'restoration group: ' . wp_json_encode( $g ) . "\n";
foreach ( $g as $l => $id ) {
	$txt = get_post_field( 'post_content', $id ); foreach ( get_post_meta( $id ) as $k => $v ) if ( preg_match( '/^content_blocks_\d+_(content|text_left|text_right)$/', $k ) ) $txt .= ' ' . $v[0];
	$txt = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $txt ) ) );
	echo "$l #$id " . get_post_status( $id ) . ' ' . get_post_field( 'post_date', $id ) . ' slug=' . get_post_field( 'post_name', $id ) . ' | "' . get_the_title( $id ) . '" | rmT=' . get_post_meta( $id, 'rank_math_title', true ) . ' | rmD=' . mb_strlen( get_post_meta( $id, 'rank_math_description', true ) ) . ' | kw=' . get_post_meta( $id, 'rank_math_focus_keyword', true ) . ' | chars=' . mb_strlen( $txt ) . ' | ' . mb_substr( $txt, 0, 90 ) . "\n";
}
foreach ( array( 4610, 4614, 4575 ) as $id ) {
	$c = get_post_field( 'post_content', $id );
	echo "#$id " . pll_get_post_language( $id ) . ' rmT=' . get_post_meta( $id, 'rank_math_title', true ) . ' | rmD=' . get_post_meta( $id, 'rank_math_description', true ) . ' | kw=' . get_post_meta( $id, 'rank_math_focus_keyword', true ) . ' | len=' . mb_strlen( $c ) . ' | start: ' . mb_substr( trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $c ) ) ), 0, 300 ) . "\n";
}
