<?php
foreach ( get_posts( array( 'post_type' => array( 'industry', 'target', 'page', 'service' ), 'lang' => 'en', 'numberposts' => -1, 's' => 'startup' ) ) as $q ) echo $q->ID, ' ', $q->post_type, ' ', get_permalink( $q ), ' | ', $q->post_title, "\n";
echo "--- industry en\n";
foreach ( get_posts( array( 'post_type' => 'industry', 'lang' => 'en', 'numberposts' => -1 ) ) as $q ) echo $q->ID, ' ', get_permalink( $q ), "\n";
