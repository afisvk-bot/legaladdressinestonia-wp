<?php
/** wp eval-file tools/menu-strings-import.php — импорт новых подписей из lai-menu-strings.json (без перезаписи) и синхронизация меню. */
$out = array();
foreach ( lai_menu_strings_read_file( LAI_MENU_STRINGS_FILE ) as $lang => $map ) {
	$out['imported'][ $lang ] = lai_save_string_translations( $lang, $map, false );
}
$out['sync'] = array_map( function ( $r ) {
	return $r['lang'] . '/' . $r['location'] . ': +' . $r['created'] . ' ~' . $r['updated'] . ' -' . $r['removed'];
}, lai_sync_all_menus() );
do_action( 'litespeed_purge_all' );
wp_cache_flush();
echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ), "\n";
