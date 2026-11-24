<?php
$p = get_page_by_path( 'kak-vesti-buhgalteriyu-o-samostoyatelno', OBJECT, 'post' );
if ( ! $p ) { echo "not found\n"; return; }
echo "#{$p->ID} {$p->post_status} ", pll_get_post_language( $p->ID ), " date={$p->post_date} author={$p->post_author} «{$p->post_title}»\n";
echo "group: ", wp_json_encode( pll_get_post_translations( $p->ID ) ), "\n";
echo "cats: ", implode( ',', wp_get_post_categories( $p->ID ) ), " thumb=", get_post_thumbnail_id( $p->ID ), " ", wp_get_attachment_url( get_post_thumbnail_id( $p->ID ) ), "\n";
echo "rm: ", get_post_meta( $p->ID, 'rank_math_title', true ), " | ", get_post_meta( $p->ID, 'rank_math_description', true ), " | ", get_post_meta( $p->ID, 'rank_math_focus_keyword', true ), "\n";
echo "len ", mb_strlen( $p->post_content ), "\n";
echo "crypto pages: "; foreach ( get_posts( array( 'post_type' => array( 'service', 'licensing', 'industry', 'target', 'post', 'page' ), 'lang' => 'en', 's' => 'crypto', 'numberposts' => 20 ) ) as $q ) echo "\n  #{$q->ID} {$q->post_type} ", get_permalink( $q ), " «{$q->post_title}»";
echo "\nlicensing en: "; foreach ( get_posts( array( 'post_type' => 'licensing', 'lang' => 'en', 'numberposts' => -1 ) ) as $q ) echo "\n  #{$q->ID} ", get_permalink( $q ), " «{$q->post_title}»";
echo "\n";
