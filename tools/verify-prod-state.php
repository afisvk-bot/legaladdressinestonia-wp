<?php
/** Состояние после восстановления: файлы, миграции, ключевые изменения (только чтение). */
$mu = WPMU_PLUGIN_DIR; $th = get_template_directory();
$files = array( 'mu/staging-guard.php', 'mu/lai-disclaimer.php', 'mu/lai-redirects.php', 'mu/lai-article-schema.php', 'mu/lai-interlinking.php', 'mu/lai-migrate-20261106.php', 'mu/lai-migrate-20261106b.php', 'mu/lai-migrate-20261106c.php', 'mu/lai-data/lai-link-matrix.json', 'mu/lai-data/meta-20261106.json', 'th/functions.php', 'th/single.php', 'th/template-parts/content-flex.php', 'th/inc/mobile-menu.php' );
foreach ( $files as $f ) {
	$p = str_replace( array( 'mu/', 'th/' ), array( $mu . '/', $th . '/' ), $f );
	echo str_pad( $f, 42 ) . ( file_exists( $p ) ? 'md5 ' . md5_file( $p ) : 'MISSING' ) . "\n";
}
foreach ( array( 'lai_mig_20261106', 'lai_mig_20261106b', 'lai_mig_20261106c' ) as $o ) { $v = get_option( $o ); echo "$o: " . ( is_array( $v ) ? ( $v['time'] ?? '' ) . ' ' . ( $v['summary'] ?? '' ) : 'not run' ) . "\n"; }
echo 'blog_public=' . get_option( 'blog_public' ) . ' home=' . home_url() . "\n";
$uk = pll_get_post( 3624, 'uk' ); echo 'UK accounting page: ' . ( $uk ? "#$uk " . get_post_status( $uk ) . ' ' . get_permalink( $uk ) : 'none' ) . "\n";
echo '3641 zh group: ' . wp_json_encode( pll_get_post_translations( 3639 ) ) . "\n";
echo 'stray 4535 status: ' . get_post_status( 4535 ) . ', 3642: ' . get_post_status( 3642 ) . "\n";
echo '1269 rm_title: ' . get_post_meta( 1269, 'rank_math_title', true ) . "\n";
echo '4529 rm_title: ' . get_post_meta( 4529, 'rank_math_title', true ) . "\n";
echo 'bank article group 1629: ' . wp_json_encode( pll_get_post_translations( 1629 ) ) . "\n";
echo 'uk legal robots 1308: ' . wp_json_encode( get_post_meta( 1308, 'rank_math_robots', true ) ) . "\n";
global $wpdb; echo 'en privacy links in content (xlang sample): ' . (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key='_form' AND meta_value LIKE '%/privacy-policy%'" ) . " cf7 forms still link /privacy-policy\n";
echo 'posts count: ' . wp_json_encode( array_map( fn( $l ) => count( get_posts( array( 'post_type' => 'post', 'lang' => $l, 'numberposts' => -1, 'fields' => 'ids' ) ) ), array_combine( pll_languages_list( array( 'fields' => 'slug' ) ), pll_languages_list( array( 'fields' => 'slug' ) ) ) ) ) . "\n";
echo 'last modified posts: '; foreach ( get_posts( array( 'post_type' => 'any', 'numberposts' => 5, 'orderby' => 'modified', 'lang' => '' ) ) as $p ) echo "#{$p->ID} {$p->post_type} {$p->post_modified} | "; echo "\n";
