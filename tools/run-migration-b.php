<?php
wp_set_current_user( 1 );
delete_option( 'lai_mig_20261106b' );
$r = lai_mig_20261106b_run();
update_option( 'lai_mig_20261106b', $r + array( 'time' => current_time( 'mysql' ) ), false );
echo $r['summary'], "\n", implode( "\n", array_slice( $r['log'], 0, 40 ) ), "\n";
foreach ( array( 1415, 1401, 2574 ) as $id ) { $b = wp_remote_retrieve_body( wp_remote_get( add_query_arg( 'r', time(), get_permalink( $id ) ), array( 'sslverify' => false ) ) ); preg_match( '#<title>(.*?)</title>#s', $b, $t ); echo "#$id " . html_entity_decode( $t[1] ?? '' ) . "\n"; }
foreach ( array( 1308, 1304, 1317 ) as $id ) { $b = wp_remote_retrieve_body( wp_remote_get( add_query_arg( 'r', time(), get_permalink( $id ) ), array( 'sslverify' => false ) ) ); preg_match( '#<meta name="robots" content="([^"]*)"#', $b, $m ); echo "#$id robots: " . ( $m[1] ?? '?' ) . "\n"; }
