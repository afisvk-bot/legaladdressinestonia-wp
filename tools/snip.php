<?php
global $wpdb; foreach ( array_map( 'intval', $args ) as $id ) { $r = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}snippets WHERE id=%d", $id ), ARRAY_A ); echo "===== #$id {$r['name']} scope={$r['scope']}\n{$r['code']}\n"; }
