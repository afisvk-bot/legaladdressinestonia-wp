<?php
foreach ( $args as $u ) { $full = home_url( $u ); $id = url_to_postid( $full ); if ( ! $id ) { $p = get_page_by_path( basename( untrailingslashit( $u ) ), OBJECT, array( 'post', 'service', 'page', 'licensing', 'industry', 'target' ) ); $id = $p ? $p->ID : 0; }
 echo "$u => ", $id ? "#$id " . get_post_type( $id ) . ' ' . pll_get_post_language( $id ) . ' ' . get_permalink( $id ) . ' tr=' . implode( ',', array_keys( pll_get_post_translations( $id ) ) ) : 'NOT FOUND', "\n"; }
