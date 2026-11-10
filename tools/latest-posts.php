<?php
$langs = pll_languages_list( array( 'fields' => 'slug' ) ); $seen = array();
foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 40, 'lang' => '', 'orderby' => 'date', 'order' => 'DESC' ) ) as $p ) {
	$g = pll_get_post_translations( $p->ID ); $key = min( $g ?: array( $p->ID ) );
	if ( isset( $seen[ $key ] ) ) continue; $seen[ $key ] = 1;
	$src = $g['en'] ?? $g['ru'] ?? $p->ID;
	$c = get_post_field( 'post_content', $src );
	echo "{$p->post_date} group=" . wp_json_encode( $g ) . ' missing=' . implode( ',', array_diff( $langs, array_keys( $g ) ) ) . ' | src#' . $src . ' "' . get_the_title( $src ) . '" chars=' . mb_strlen( wp_strip_all_tags( $c ) ) . ' faq=' . ( get_post_meta( $src, 'faq_items', true ) ? 'acf' : '-' ) . ' meta_keys=' . implode( ',', array_slice( array_filter( array_keys( get_post_meta( $src ) ), fn( $k ) => '_' !== $k[0] && 0 !== strpos( $k, 'rank_math' ) ), 0, 8 ) ) . "\n";
	if ( count( $seen ) >= 8 ) break;
}
