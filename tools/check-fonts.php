<?php
/** wp eval-file tools/check-fonts.php — проверка подключения шрифтов на страницах RU/UK/EN. */
do_action( 'litespeed_purge_all' );
foreach ( array( 'en', 'ru' ) as $l ) {
	$url  = function_exists( 'pll_home_url' ) ? pll_home_url( $l ) : home_url( '/' );
	$res  = wp_remote_get( add_query_arg( 'nocache', time(), $url ), array( 'timeout' => 30, 'sslverify' => false ) );
	$body = is_wp_error( $res ) ? '' : wp_remote_retrieve_body( $res );
	echo "== $l ", strlen( $body ), "\n";
	preg_match_all( '#[^"\'\s]*(?:googleapis|gstatic|Montserrat|Poppins|litespeed/c?css)[^"\'\s<]*#i', $body, $m );
	echo implode( "\n", array_slice( array_unique( $m[0] ), 0, 15 ) ), "\n";
	// содержимое объединённых CSS LiteSpeed
	preg_match_all( '#href="([^"]+/litespeed/(?:c?css)/[^"]+)"#', $body, $c );
	foreach ( array_slice( $c[1], 0, 3 ) as $css ) {
		$path = ABSPATH . ltrim( (string) wp_parse_url( $css, PHP_URL_PATH ), '/' );
		$txt  = is_readable( $path ) ? file_get_contents( $path ) : '';
		echo '  css ', basename( $path ), ' montserrat=', substr_count( $txt, 'Montserrat' ), ' poppins=', substr_count( $txt, 'Poppins' ), "\n";
	}
}
$opt = get_option( 'litespeed.conf.optm-ggfonts_async' );
echo 'ls ggfonts_async=', var_export( $opt, true ), ' ggfonts_rm=', var_export( get_option( 'litespeed.conf.optm-ggfonts_rm' ), true ), ' css_comb=', var_export( get_option( 'litespeed.conf.optm-css_comb' ), true ), "\n";
