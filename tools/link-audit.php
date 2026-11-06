<?php
/** wp eval-file tools/link-audit.php — контекстные внутренние ссылки (без header/footer/nav) по всем опубликованным страницам. */
@set_time_limit( 1800 );
ini_set( 'pcre.backtrack_limit', '50000000' );
ini_set( 'pcre.jit', '0' );
$types = array( 'page', 'post', 'service', 'licensing', 'industry', 'target' );
$home  = wp_parse_url( home_url(), PHP_URL_HOST );
$rows  = array(); $by_path = array();
foreach ( $types as $t ) {
	foreach ( get_posts( array( 'post_type' => $t, 'post_status' => 'publish', 'numberposts' => -1, 'lang' => '', 'fields' => 'ids' ) ) as $id ) {
		$u = get_permalink( $id );
		$rows[ $id ] = array( 'id' => $id, 'type' => $t, 'lang' => pll_get_post_language( $id ), 'en' => pll_get_post( $id, 'en' ), 'url' => $u, 'noindex' => in_array( 'noindex', (array) get_post_meta( $id, 'rank_math_robots', true ), true ), 'out' => array() );
		$by_path[ untrailingslashit( wp_parse_url( $u, PHP_URL_PATH ) ) ] = $id;
	}
}
$mh = curl_multi_init(); $chs = array();
foreach ( $rows as $id => $r ) {
	$ch = curl_init( add_query_arg( 'la', 1, $r['url'] ) );
	curl_setopt_array( $ch, array( CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 60, CURLOPT_SSL_VERIFYPEER => 0, CURLOPT_SSL_VERIFYHOST => 0 ) );
	$chs[ $id ] = $ch;
}
$ext = array();
foreach ( array_chunk( $chs, 8, true ) as $batch ) {
	foreach ( $batch as $ch ) curl_multi_add_handle( $mh, $ch );
	do { curl_multi_exec( $mh, $run ); curl_multi_select( $mh, 1 ); } while ( $run );
	foreach ( $batch as $id => $ch ) {
		$b = curl_multi_getcontent( $ch );
		curl_multi_remove_handle( $mh, $ch );
		foreach ( array( 'script', 'style', 'header', 'footer', 'nav', 'aside' ) as $tg ) {
			$x = preg_replace( '#<' . $tg . '\b.*?</' . $tg . '>#si', '', $b );
			if ( null === $x ) { $rows[ $id ]['err'] = $tg; continue; }
			$b = $x;
		}
		$rows[ $id ]['len'] = strlen( $b );
		preg_match_all( '~<a\b([^>]*)href="([^"#]+)[^"]*"([^>]*)>(.*?)</a>~si', $b, $m, PREG_SET_ORDER );
		foreach ( $m as $a ) {
			$h = html_entity_decode( $a[2] );
			$host = wp_parse_url( $h, PHP_URL_HOST );
			if ( $host && $host !== $home ) continue;
			$p = untrailingslashit( (string) wp_parse_url( $h, PHP_URL_PATH ) );
			if ( '' === $p || preg_match( '#/(wp-content|wp-admin|feed|author|category|tag|page)/|\.(pdf|jpg|png|webp)$#', $p ) ) continue;
			$auto = false !== strpos( $a[1] . $a[3], 'lai-autolink' );
			$anchor = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $a[4] ) ) );
			$rows[ $id ]['out'][] = array( $p, $by_path[ $p ] ?? 0, $auto ? 1 : 0, mb_substr( $anchor, 0, 60 ) );
		}
	}
}
$file = WP_CONTENT_DIR . '/uploads/lai-link-audit.json';
file_put_contents( $file, wp_json_encode( array_values( $rows ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
echo 'rows ' . count( $rows ) . "\n" . base64_encode( gzencode( file_get_contents( $file ), 9 ) ) . "\n";
unlink( $file );
