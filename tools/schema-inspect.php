<?php
$ids = array_map( 'intval', array_filter( explode( ',', $args[0] ?? '' ) ) );
if ( ! $ids ) { $ids = array( (int) get_posts( array( 'post_type' => 'post', 'lang' => 'en', 'numberposts' => 1, 'fields' => 'ids' ) )[0] ); $ids[] = pll_get_post( $ids[0], 'ru' ); $ids[] = (int) get_posts( array( 'post_type' => 'service', 'lang' => 'en', 'numberposts' => 1, 'fields' => 'ids' ) )[0]; }
$opt = get_option( 'rank-math-options-titles' ); $gen = get_option( 'rank-math-options-general' );
echo "RM titles: " . wp_json_encode( array_intersect_key( (array) $opt, array_flip( array( 'knowledgegraph_type', 'knowledgegraph_name', 'knowledgegraph_logo', 'pt_post_default_rich_snippet', 'pt_post_default_article_type', 'pt_service_default_rich_snippet', 'disable_author_archives', 'author_custom_robots', 'pt_post_add_meta_box' ) ) ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "\n";
echo "RM general breadcrumbs: " . wp_json_encode( array_intersect_key( (array) $gen, array_flip( array( 'breadcrumbs', 'breadcrumbs_home', 'breadcrumbs_home_label', 'breadcrumbs_separator' ) ) ), JSON_UNESCAPED_UNICODE ) . "\n";
echo "RM modules: " . wp_json_encode( get_option( 'rank_math_modules' ) ) . "\n";
foreach ( $ids as $id ) {
	$u = get_permalink( $id );
	$b = wp_remote_retrieve_body( wp_remote_get( add_query_arg( 'r', time(), $u ), array( 'timeout' => 30, 'sslverify' => false ) ) );
	echo "\n===== #$id " . get_post_type( $id ) . ' ' . pll_get_post_language( $id ) . " $u\n";
	echo 'author ' . get_post_field( 'post_author', $id ) . ' ' . get_the_author_meta( 'display_name', get_post_field( 'post_author', $id ) ) . ' url=' . get_author_posts_url( get_post_field( 'post_author', $id ) ) . "\n";
	echo 'rm schema meta: ' . implode( ',', array_filter( array_keys( get_post_meta( $id ) ), fn( $k ) => 0 === strpos( $k, 'rank_math_schema' ) || 'rank_math_rich_snippet' === $k ) ) . "\n";
	preg_match_all( '#<script[^>]*application/ld\+json[^>]*>(.*?)</script>#s', $b, $m );
	foreach ( $m[1] as $j ) echo "LDJSON: " . substr( preg_replace( '/\s+/', ' ', $j ), 0, 3000 ) . "\n";
	preg_match_all( '#class="[^"]*(breadcrumb|rank-math-breadcrumb)[^"]*"#', $b, $bc ); echo 'breadcrumb html: ' . count( $bc[0] ) . ' ' . implode( ' ', array_unique( $bc[1] ) ) . "\n";
	preg_match_all( '#itemtype="([^"]+)"#', $b, $mi ); echo 'microdata: ' . implode( ',', array_unique( $mi[1] ) ) . "\n";
}
$a = get_users( array( 'has_published_posts' => array( 'post' ), 'fields' => array( 'ID', 'display_name', 'user_nicename' ) ) );
foreach ( $a as $u ) { echo "\nUSER {$u->ID} {$u->display_name} posts=" . count_user_posts( $u->ID, 'post' ) . ' desc=' . mb_substr( get_the_author_meta( 'description', $u->ID ), 0, 200 ) . ' url=' . get_the_author_meta( 'user_url', $u->ID ) . ' meta=' . wp_json_encode( array_filter( array_intersect_key( array_map( fn( $v ) => mb_substr( $v[0], 0, 120 ), get_user_meta( $u->ID ) ), array_flip( array( 'facebook', 'twitter', 'linkedin', 'additional_profile_urls', 'rank_math_permalinks', 'job_title', 'description_ru', 'description_de' ) ) ) ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "\n"; }
