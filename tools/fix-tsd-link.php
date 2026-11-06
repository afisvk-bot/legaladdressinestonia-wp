<?php
foreach ( array( 3629, 3633 ) as $id ) echo "#$id " . get_post_type( $id ) . ' ' . get_post_status( $id ) . ' ' . pll_get_post_language( $id ) . ' "' . get_the_title( $id ) . '" ' . wp_json_encode( pll_get_post_translations( $id ) ) . "\n";
// any other RU post already sitting in EN group?
$g = pll_get_post_translations( 3633 );
if ( isset( $g['ru'] ) && 3629 !== (int) $g['ru'] ) echo 'EN group ru slot taken by #' . $g['ru'] . ' ' . get_post_status( $g['ru'] ) . ' "' . get_the_title( $g['ru'] ) . "\"\n";
$g2 = pll_get_post_translations( 3629 );
$merged = array_merge( $g2, $g, array( 'ru' => 3629 ) );
$merged = array_filter( $merged, fn( $p ) => 'publish' === get_post_status( $p ) );
pll_set_post_language( 3629, 'ru' );
pll_save_post_translations( $merged );
clean_post_cache( 3629 ); clean_post_cache( 3633 );
echo "after:\n";
foreach ( array( 3629, 3633 ) as $id ) echo "#$id " . wp_json_encode( pll_get_post_translations( $id ) ) . "\n";
do_action( 'litespeed_purge_all' );
