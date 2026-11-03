<?php
/** wp eval-file tools/check-fonts.php — проверка подключения шрифтов на страницах RU/UK/EN. */
foreach ( array( 'en', 'ru', 'uk' ) as $l ) {
	$url  = function_exists( 'pll_home_url' ) ? pll_home_url( $l ) : home_url( '/' );
	$res  = wp_remote_get( add_query_arg( 'nocache', time(), $url ), array( 'timeout' => 20, 'sslverify' => false ) );
	$body = is_wp_error( $res ) ? '' : wp_remote_retrieve_body( $res );
	preg_match( '#fonts\.googleapis\.com/css2\?[^"\']+#', $body, $m );
	echo $l, ' ', is_wp_error( $res ) ? $res->get_error_message() : wp_remote_retrieve_response_code( $res ), ' font-url=', $m[0] ?? '-', ' inline=', strpos( $body, '"Poppins","Montserrat"' ) ? 'yes' : 'no', "\n";
}
