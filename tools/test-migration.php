<?php
$uk = pll_get_post( 3624, 'uk' );
if ( $uk && (int) get_post_meta( $uk, '_lai_tr_source', true ) === 3624 ) { wp_delete_post( $uk, true ); echo "deleted test UK #$uk\n"; }
delete_option( 'lai_mig_20261106' );
wp_set_current_user( 1 );
echo implode( "\n", lai_mig_20261106_run() ), "\n";
$id = pll_get_post( 3624, 'uk' );
$r = wp_remote_get( get_permalink( $id ), array( 'timeout' => 30, 'sslverify' => false ) ); $b = wp_remote_retrieve_body( $r );
preg_match( '#<title>(.*?)</title>#s', $b, $t );
echo 'UK http ' . wp_remote_retrieve_response_code( $r ) . ' ' . get_permalink( $id ) . ' | ' . html_entity_decode( $t[1] ?? '' ) . ' | disc ' . ( false !== strpos( $b, 'lai-disc' ) ? 'y' : 'n' ) . ' | faq ' . substr_count( $b, 'Часті запитання' ) . ' | uk-links ' . preg_match_all( '#href="[^"]*(yurydychna-adresa|richnyy-zvit|podatkovyy-konsaltynh)#', $b ) . "\n";
echo 'group ' . wp_json_encode( pll_get_post_translations( $id ) ) . "\n";
foreach ( array( '/fur-start-ups-2/', '/registering-an-mt-in-estonia-2/' ) as $p ) { $x = wp_remote_get( home_url( $p ), array( 'redirection' => 0, 'sslverify' => false ) ); echo "$p " . wp_remote_retrieve_response_code( $x ) . ' ' . wp_remote_retrieve_header( $x, 'location' ) . "\n"; }
