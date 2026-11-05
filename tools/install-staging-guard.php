<?php
/** wp eval-file tools/install-staging-guard.php — положить staging-guard.php в mu-plugins (без полного деплоя). */
$src = __DIR__ . '/files/staging-guard.php';
if ( ! is_readable( $src ) ) {
	$src = dirname( ABSPATH ) . '/lai-tools/files/staging-guard.php';
}
$dst = WPMU_PLUGIN_DIR . '/staging-guard.php';
wp_mkdir_p( WPMU_PLUGIN_DIR );
echo copy( $src, $dst ) ? "installed $dst\n" : "FAILED copy $src -> $dst\n";
$robots = ABSPATH . 'robots.txt';
file_put_contents( $robots, "User-agent: *\nDisallow: /\n" );
echo "robots.txt written\n";
echo 'env=', wp_get_environment_type(), "\n";
