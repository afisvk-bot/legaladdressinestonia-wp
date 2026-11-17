<?php
global $wpdb;
echo "prefix: {$wpdb->prefix}\n";
echo "siteurl: ", get_option( 'siteurl' ), " | home: ", get_option( 'home' ), "\n";
$roles = get_option( $wpdb->prefix . 'user_roles' );
echo "user_roles option: ", is_array( $roles ) ? implode( ',', array_keys( $roles ) ) : var_export( $roles, true ), "\n";
if ( is_array( $roles ) && isset( $roles['administrator'] ) ) echo "admin caps: ", count( $roles['administrator']['capabilities'] ), " manage_options=", var_export( $roles['administrator']['capabilities']['manage_options'] ?? null, true ), "\n";
foreach ( $wpdb->get_results( "SELECT ID,user_login,user_email,user_registered FROM {$wpdb->users} ORDER BY ID" ) as $u ) {
	$caps = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id=%d AND meta_key=%s", $u->ID, $wpdb->prefix . 'capabilities' ) );
	$other = $wpdb->get_col( $wpdb->prepare( "SELECT meta_key FROM {$wpdb->usermeta} WHERE user_id=%d AND meta_key LIKE %s", $u->ID, '%capabilities' ) );
	echo "#{$u->ID} {$u->user_login} caps=" . ( $caps ?: '-' ) . " keys=" . implode( ',', $other ) . "\n";
}
echo "active plugins: ", implode( ', ', (array) get_option( 'active_plugins' ) ), "\n";
echo "mu: ", implode( ', ', array_keys( get_mu_plugins() ) ), "\n";
echo "updraft restore in progress: ", var_export( get_site_option( 'updraft_restore_in_progress' ), true ), "\n";
echo "maintenance file: ", file_exists( ABSPATH . '.maintenance' ) ? 'YES' : 'no', "\n";
echo "aiowps: ", var_export( get_option( 'aio_wp_security_configs' ) ? 'present' : 'none', true ), "\n";
