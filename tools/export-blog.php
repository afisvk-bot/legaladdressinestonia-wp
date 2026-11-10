<?php
/** wp eval-file tools/export-blog.php <id> — пост для перевода: заголовок, slug, контент, FAQ (ACF), мета, рубрики. */
$id = (int) $args[0]; $p = get_post( $id );
$faq = array();
foreach ( get_post_meta( $id ) as $k => $v ) if ( 0 === strpos( $k, 'faqs_' ) ) $faq[ $k ] = $v[0];
echo wp_json_encode( array( 'id' => $id, 'lang' => pll_get_post_language( $id ), 'date' => $p->post_date, 'title' => $p->post_title, 'slug' => $p->post_name, 'excerpt' => $p->post_excerpt,
	'content' => $p->post_content, 'faq' => $faq, 'rm' => array( 'title' => get_post_meta( $id, 'rank_math_title', true ), 'desc' => get_post_meta( $id, 'rank_math_description', true ), 'kw' => get_post_meta( $id, 'rank_math_focus_keyword', true ) ),
	'cats' => array_map( fn( $c ) => array( $c, get_cat_name( $c ), pll_get_term_translations( $c ) ), wp_get_post_categories( $id ) ),
	'group' => pll_get_post_translations( $id ),
	'slug_samples' => array_map( fn( $l ) => get_post_field( 'post_name', pll_get_post( 2509, $l ) ?: 0 ), array( 'de' => 'de', 'uk' => 'uk', 'zh' => 'zh' ) ),
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), "\n";
