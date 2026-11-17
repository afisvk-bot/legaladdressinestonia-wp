<?php
global $wpdb;
$c = get_option( 'aio_wp_security_configs' );
foreach ( array( 'aiowps_enable_rename_login_page', 'aiowps_login_page_slug', 'aiowps_enable_login_lockdown', 'aiowps_enable_brute_force_attack_prevention', 'aiowps_enable_whitelisting', 'aiowps_allowed_ip_addresses', 'aiowps_enable_6g_firewall', 'aiowps_prevent_users_enumeration', 'aiowps_enable_login_captcha', 'aiowps_default_captcha' ) as $k ) echo $k, ' = ', is_array( $c ) && isset( $c[ $k ] ) ? ( is_scalar( $c[ $k ] ) ? $c[ $k ] : json_encode( $c[ $k ] ) ) : '-', "\n";
echo "lockouts (last 10): ";
$t = $wpdb->prefix . 'aiowps_login_lockdown';
if ( $wpdb->get_var( "SHOW TABLES LIKE '$t'" ) ) { foreach ( $wpdb->get_results( "SELECT * FROM $t ORDER BY id DESC LIMIT 10", ARRAY_A ) as $r ) echo "\n  ", json_encode( $r ); } else echo 'no table';
echo "\nlatest posts:\n";
foreach ( $wpdb->get_results( "SELECT ID,post_type,post_date,post_modified,LEFT(post_title,50) t FROM {$wpdb->posts} WHERE post_status='publish' ORDER BY post_modified DESC LIMIT 5" ) as $p ) echo "  #$p->ID $p->post_type $p->post_modified $p->t\n";
echo "updraft last restore: ", var_export( get_option( 'updraft_last_restore' ) ?: get_site_option( 'updraft_restore_time' ), true ), "\n";
echo "files mtime functions.php: ", date( 'c', filemtime( get_template_directory() . '/functions.php' ) ), "\n";
