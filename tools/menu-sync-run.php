<?php
/** wp eval-file tools/menu-sync-run.php [dry] — синхронизация меню EN → все языки. */
$dry  = in_array( 'dry', $args, true );
$rows = lai_sync_all_menus( $dry );
do_action( 'litespeed_purge_all' );
wp_cache_flush();
echo wp_json_encode( $rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ), "\n";
