<?php
global $wpdb; $p = $wpdb->prefix;
echo "pipedrive feeds: ", $wpdb->get_var( "SELECT COUNT(*) FROM {$p}vxcf_pipedrive" ), " | accounts: ", $wpdb->get_var( "SELECT COUNT(*) FROM {$p}vxcf_pipedrive_accounts" ), "\n";
foreach ( $wpdb->get_results( "SELECT * FROM {$p}vxcf_pipedrive LIMIT 30", ARRAY_A ) as $r ) echo "  feed: ", json_encode( array_intersect_key( $r, array_flip( array( 'id', 'name', 'form_id', 'object', 'is_active', 'time' ) ) ) ), "\n";
echo "log total: ", $wpdb->get_var( "SELECT COUNT(*) FROM {$p}vxcf_pipedrive_log" ), " last: ", $wpdb->get_var( "SELECT MAX(time) FROM {$p}vxcf_pipedrive_log" ), "\n";
foreach ( $wpdb->get_results( "SELECT form_id, status, COUNT(*) c, MAX(time) t FROM {$p}vxcf_pipedrive_log GROUP BY form_id, status ORDER BY t DESC LIMIT 15", ARRAY_A ) as $r ) echo "  log: ", json_encode( $r ), "\n";
echo "meta with pipedrive: "; foreach ( $wpdb->get_results( "SELECT pm.post_id, pm.meta_key, LEFT(pm.meta_value,120) v FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID=pm.post_id AND p.post_status='publish' WHERE pm.meta_value LIKE '%pipedrive%' AND pm.meta_key NOT LIKE '\\_%' LIMIT 8" ) as $r ) echo "\n  #$r->post_id $r->meta_key: $r->v";
echo "\nplugins dir: ", implode( ', ', array_filter( scandir( WP_PLUGIN_DIR ), function ( $d ) { return preg_match( '/pipedrive|cf7|crm|vx/i', $d ); } ) ), "\n";
