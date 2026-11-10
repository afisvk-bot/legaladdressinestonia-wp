<?php
$r = wp_remote_get( home_url( '/?pr=' . time() ), array( 'sslverify' => false ) ); $b = wp_remote_retrieve_body( $r );
preg_match( '#<meta name="robots" content="([^"]*)"#', $b, $m );
echo 'home robots meta: ' . ( $m[1] ?? '-' ) . ' | X-Robots-Tag: ' . wp_remote_retrieve_header( $r, 'x-robots-tag' ) . "\n";
$rb = wp_remote_retrieve_body( wp_remote_get( home_url( '/robots.txt' ), array( 'sslverify' => false ) ) ); echo 'robots.txt: ' . str_replace( "\n", ' / ', trim( $rb ) ) . "\n";
echo 'mail blocked: ' . ( has_filter( 'pre_wp_mail' ) ? 'yes' : 'no' ) . "\n";
$st = get_option( 'lai_mig_20261106b' ); echo 'mig b skipped: ' . implode( ' | ', array_filter( (array) ( $st['log'] ?? array() ), fn( $l ) => 0 === strpos( $l, 'SKIP' ) || 0 === strpos( $l, 'KEPT' ) ) ) . "\n";
foreach ( array( 4610, 4614 ) as $id ) echo "#$id " . pll_get_post_language( $id ) . ' ' . get_post_status( $id ) . ' "' . get_the_title( $id ) . '" ' . get_permalink( $id ) . ' group=' . wp_json_encode( pll_get_post_translations( $id ) ) . ' words=' . str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $id ) ) ) . "\n";
foreach ( array( 4572, 4597, 4599 ) as $id ) echo "#$id " . get_post_type( $id ) . ' ' . pll_get_post_language( $id ) . ' ' . get_post_status( $id ) . ' "' . get_the_title( $id ) . "\"\n";
