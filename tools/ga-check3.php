<?php
global $wpdb; $r = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}snippets WHERE id=10", ARRAY_A ); echo $r['code'], "\n==== #12\n";
$r = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}snippets WHERE id=12", ARRAY_A ); echo substr( $r['code'], 0, 1500 ), "\n==== #8\n";
$r = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}snippets WHERE id=8", ARRAY_A ); echo substr( $r['code'], 0, 2500 ), "\n";
