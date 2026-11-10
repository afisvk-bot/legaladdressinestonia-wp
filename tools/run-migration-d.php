<?php
delete_option( 'lai_mig_20261110' ); $l = lai_mig_20261110_run(); update_option( 'lai_mig_20261110', array( 'log' => $l, 'time' => current_time( 'mysql' ) ), false ); echo implode( "\n", $l ), "\n";
$b = wp_remote_retrieve_body( wp_remote_get( add_query_arg( 'r', time(), get_permalink( 4597 ) ), array( 'sslverify' => false ) ) );
preg_match( '#<title>(.*?)</title>#s', $b, $t ); echo 'ES title: ' . html_entity_decode( $t[1] ?? '' ) . ' | english words left: ' . preg_match_all( '/\b(the|and|with|which|report)\b/', wp_strip_all_tags( $b ) ) . "\n";
$o = wp_remote_get( home_url( '/clean-up-2/' ), array( 'redirection' => 0, 'sslverify' => false ) ); echo 'old URL: ' . wp_remote_retrieve_response_code( $o ) . ' ' . wp_remote_retrieve_header( $o, 'location' ) . "\n";
