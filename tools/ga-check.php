<?php
global $wpdb;
echo "active plugins: ", implode( ', ', (array) maybe_unserialize( $wpdb->get_var( "SELECT option_value FROM {$wpdb->options} WHERE option_name='active_plugins'" ) ) ), "\n\n";
echo "options mentioning GA/GTM ids:\n";
foreach ( $wpdb->get_results( "SELECT option_name, LENGTH(option_value) l, option_value v FROM {$wpdb->options} WHERE option_value REGEXP 'G-[A-Z0-9]{8,12}|GTM-[A-Z0-9]{5,8}|gtag\\\\(|googletagmanager|UA-[0-9]{5,}' LIMIT 40" ) as $r ) {
	preg_match_all( '/(G-[A-Z0-9]{8,12}|GTM-[A-Z0-9]{5,8}|UA-[0-9]{5,}-[0-9]+)/', $r->v, $m );
	echo "  {$r->option_name} (len {$r->l}): ", implode( ',', array_unique( $m[1] ) ), "\n";
}
echo "\npostmeta/posts with gtag/GTM: ";
echo $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status='publish' AND (post_content LIKE '%googletagmanager%' OR post_content LIKE '%gtag(%')" ), " posts, ";
echo $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE '%googletagmanager%' OR meta_value LIKE '%gtag(%'" ), " meta\n";
foreach ( $wpdb->get_results( "SELECT post_id, meta_key FROM {$wpdb->postmeta} WHERE meta_value LIKE '%googletagmanager%' OR meta_value LIKE '%gtag(%' LIMIT 10" ) as $r ) echo "  meta #{$r->post_id} {$r->meta_key} (", get_post_type( $r->post_id ), " ", get_post_status( $r->post_id ), ")\n";
foreach ( $wpdb->get_results( "SELECT ID, post_type, post_status, post_title, post_modified FROM {$wpdb->posts} WHERE (post_content LIKE '%googletagmanager%' OR post_content LIKE '%gtag(%') LIMIT 10" ) as $r ) echo "  post #{$r->ID} {$r->post_type} {$r->post_status} «{$r->post_title}» {$r->post_modified}\n";
echo "\nplugins dir: ", implode( ', ', array_diff( scandir( WP_PLUGIN_DIR ), array( '.', '..' ) ) ), "\n";
