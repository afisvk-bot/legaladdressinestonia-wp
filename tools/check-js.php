<?php
/** wp eval-file tools/check-js.php — какой JS мобильного меню реально отдаётся. */
do_action( 'litespeed_purge_all' );
$body = wp_remote_retrieve_body( wp_remote_get( home_url( '/ru/' ) . '?nc=' . microtime( true ), array( 'timeout' => 30, 'sslverify' => false ) ) );
preg_match_all( '#<script[^>]*src="([^"]+)"#', $body, $m );
foreach ( $m[1] as $src ) {
	$path = ABSPATH . ltrim( (string) wp_parse_url( $src, PHP_URL_PATH ), '/' );
	$txt  = is_readable( $path ) ? file_get_contents( $path ) : '';
	$hit  = array();
	foreach ( array( 'laiMMInit', 'mm-lang-open', 'langClose', 'mm-tabs', 'Accordion' ) as $k ) {
		if ( false !== strpos( $txt, $k ) ) $hit[] = $k;
	}
	echo basename( (string) wp_parse_url( $src, PHP_URL_PATH ) ), ' ', strlen( $txt ), ' ', implode( ',', $hit ), "\n";
}
$f = get_template_directory() . '/js/mobile-menu.js';
echo 'theme file: ', filesize( $f ), ' has langClose=', strpos( file_get_contents( $f ), 'langClose' ) ? 'yes' : 'no', "\n";
echo 'ls js_comb=', wp_json_encode( get_option( 'litespeed.conf.optm-js_comb' ) ), ' js_min=', wp_json_encode( get_option( 'litespeed.conf.optm-js_min' ) ), ' js_defer=', wp_json_encode( get_option( 'litespeed.conf.optm-js_defer' ) ), "\n";
preg_match_all( '#data-src="([^"]+)"|litespeed_vary|type="litespeed/javascript"#', $body, $d );
echo 'deferred markers: ', count( $d[0] ), "\n";
