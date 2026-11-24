<?php foreach ( array_map( 'intval', $args ) as $id ) { $e = pll_get_post( $id, 'en' ); echo "$id => en #$e ", str_replace( home_url(), '', (string) get_permalink( $e ) ), "\n"; }
