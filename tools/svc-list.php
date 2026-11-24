<?php
foreach ( get_posts( array( 'post_type' => 'service', 'lang' => 'en', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) ) as $q ) echo "#{$q->ID} ", str_replace( home_url(), '', get_permalink( $q ) ), " «{$q->post_title}» langs=", count( pll_get_post_translations( $q->ID ) ), "\n";
foreach ( array( 'industry', 'target' ) as $pt ) foreach ( get_posts( array( 'post_type' => $pt, 'lang' => 'en', 'numberposts' => -1 ) ) as $q ) echo "#{$q->ID} ", str_replace( home_url(), '', get_permalink( $q ) ), " «{$q->post_title}»\n";
