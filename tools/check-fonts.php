<?php
/** wp eval-file tools/check-fonts.php — проверка подключения шрифтов. */
do_action( 'litespeed_purge_all' );
$url  = pll_home_url( 'ru' );
$res  = wp_remote_get( add_query_arg( 'nocache', time(), $url ), array( 'timeout' => 30, 'sslverify' => false ) );
$body = wp_remote_retrieve_body( $res );
preg_match_all( '#<link[^>]+>#i', $body, $l );
foreach ( $l[0] as $x ) {
	if ( preg_match( '#css|font#i', $x ) ) {
		echo substr( $x, 0, 250 ), "\n";
	}
}
preg_match_all( '#<style[^>]*>(.{0,200})#is', $body, $s );
foreach ( $s[1] as $x ) {
	if ( preg_match( '#font#i', $x ) ) {
		echo 'STYLE: ', substr( preg_replace( '/\s+/', ' ', $x ), 0, 200 ), "\n";
	}
}
preg_match_all( '#href="([^"]+/litespeed/css/[^"]+)"#', $body, $c );
foreach ( $c[1] as $css ) {
	$txt = (string) file_get_contents( ABSPATH . ltrim( (string) wp_parse_url( $css, PHP_URL_PATH ), '/' ) );
	preg_match_all( '#@font-face\{[^}]*\}|@import[^;]+;#', $txt, $f );
	echo 'COMBINED fontfaces: ', count( $f[0] ), "\n";
	foreach ( array_slice( $f[0], 0, 6 ) as $x ) echo '  ', substr( $x, 0, 200 ), "\n";
	preg_match_all( '#[^}]{0,80}Montserrat[^}]{0,80}#', $txt, $mm );
	echo 'MONT: ', implode( ' | ', $mm[0] ), "\n";
}
foreach ( array( 'optm-localize', 'optm-localize_domains', 'optm-css_exc', 'optm-css_async', 'optm-ccss_per_url', 'optm-css_font_display' ) as $k ) {
	echo $k, '=', wp_json_encode( get_option( 'litespeed.conf.' . $k ) ), "\n";
}
