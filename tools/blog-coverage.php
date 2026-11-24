<?php
$langs = array( 'en', 'ru', 'uk', 'de', 'es', 'fr', 'pt', 'tr', 'zh' );
$seen = array(); $rows = array();
$all = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'lang' => '', 'orderby' => 'date', 'order' => 'DESC' ) );
echo "published posts total: ", count( $all ), "\n";
foreach ( $all as $p ) {
	if ( isset( $seen[ $p->ID ] ) ) continue;
	$tr = pll_get_post_translations( $p->ID );
	$pub = array();
	foreach ( $tr as $l => $id ) { $seen[ $id ] = 1; if ( 'publish' === get_post_status( $id ) ) $pub[ $l ] = $id; }
	$src = $pub['en'] ?? $pub['ru'] ?? reset( $pub );
	$miss = array_diff( $langs, array_keys( $pub ) );
	$rows[] = sprintf( "%s | %-3s langs | src #%d %s «%s» | missing: %s", get_the_date( 'Y-m-d', $src ), count( $pub ), $src, pll_get_post_language( $src ), mb_substr( get_the_title( $src ), 0, 60 ), $miss ? implode( ',', $miss ) : '—' );
}
echo "groups: ", count( $rows ), "\n", implode( "\n", $rows ), "\n";
echo "\ndrafts/pending posts:\n"; foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => array( 'draft', 'pending', 'future' ), 'numberposts' => -1, 'lang' => '' ) ) as $p ) echo "  #{$p->ID} {$p->post_status} ", pll_get_post_language( $p->ID ), " {$p->post_modified} «", mb_substr( $p->post_title, 0, 70 ), "»\n";
