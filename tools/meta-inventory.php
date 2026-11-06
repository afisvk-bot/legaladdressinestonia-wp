<?php
/** wp eval-file tools/meta-inventory.php — title/description/focus kw всех опубликованных страниц + реальный <title> из HTML. */
@set_time_limit( 1800 );
$types = array( 'page', 'post', 'service', 'licensing', 'industry', 'target' );
$out = array();
foreach ( $types as $t ) {
	foreach ( get_posts( array( 'post_type' => $t, 'post_status' => 'publish', 'numberposts' => -1, 'lang' => '', 'fields' => 'ids' ) ) as $id ) {
		$robots = (array) get_post_meta( $id, 'rank_math_robots', true );
		$txt = get_post_field( 'post_content', $id );
		foreach ( (array) get_post_meta( $id ) as $k => $v ) {
			if ( preg_match( '/^content_blocks_\d+_(content|text_left|text_right|description|text)$/', $k ) ) $txt .= ' ' . $v[0];
		}
		$txt = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $txt ) ) );
		$out[] = array(
			'id' => $id, 'type' => $t, 'lang' => pll_get_post_language( $id ), 'url' => get_permalink( $id ),
			'en' => pll_get_post( $id, 'en' ), 'title' => get_the_title( $id ),
			'rm_title' => get_post_meta( $id, 'rank_math_title', true ), 'rm_desc' => get_post_meta( $id, 'rank_math_description', true ),
			'kw' => get_post_meta( $id, 'rank_math_focus_keyword', true ), 'noindex' => in_array( 'noindex', $robots, true ),
			'tpl' => get_page_template_slug( $id ), 'front' => (int) get_option( 'page_on_front' ) === (int) pll_get_post( $id, pll_default_language() ),
			'excerpt' => get_post_field( 'post_excerpt', $id ), 'text' => mb_substr( $txt, 0, 700 ),
		);
	}
}
// реальный <title> и description из HTML
$mh = curl_multi_init(); $chs = array();
foreach ( $out as $i => $r ) {
	$ch = curl_init( add_query_arg( 'mi', 1, $r['url'] ) );
	curl_setopt_array( $ch, array( CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 60, CURLOPT_SSL_VERIFYPEER => 0, CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_FOLLOWLOCATION => 1 ) );
	$chs[ $i ] = $ch;
}
foreach ( array_chunk( $chs, 8, true ) as $batch ) {
	foreach ( $batch as $ch ) curl_multi_add_handle( $mh, $ch );
	do { curl_multi_exec( $mh, $run ); curl_multi_select( $mh, 1 ); } while ( $run );
	foreach ( $batch as $i => $ch ) {
		$b = curl_multi_getcontent( $ch );
		preg_match( '#<title>(.*?)</title>#s', $b, $tt ); preg_match( '#<meta name="description" content="([^"]*)"#', $b, $dd );
		preg_match( '#<h1[^>]*>(.*?)</h1>#s', $b, $h1 );
		$out[ $i ]['html_title'] = html_entity_decode( trim( $tt[1] ?? '' ), ENT_QUOTES, 'UTF-8' );
		$out[ $i ]['html_desc'] = html_entity_decode( $dd[1] ?? '', ENT_QUOTES, 'UTF-8' );
		$out[ $i ]['h1'] = trim( html_entity_decode( wp_strip_all_tags( $h1[1] ?? '' ), ENT_QUOTES, 'UTF-8' ) );
		curl_multi_remove_handle( $mh, $ch );
	}
}
$out_opts = array( 'titles' => array_intersect_key( (array) get_option( 'rank-math-options-titles' ), array_flip( array( 'title_separator', 'homepage_title', 'homepage_description', 'pt_page_title', 'pt_page_description', 'pt_post_title', 'pt_post_description', 'pt_service_title', 'pt_service_description', 'pt_licensing_title', 'pt_licensing_description', 'pt_industry_title', 'pt_industry_description', 'pt_target_title', 'pt_target_description' ) ) ) );
$file = WP_CONTENT_DIR . '/uploads/lai-meta-inventory.json';
file_put_contents( $file, wp_json_encode( array( 'opts' => $out_opts, 'rows' => $out ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
echo 'rows ' . count( $out ) . "\n" . wp_json_encode( $out_opts, JSON_UNESCAPED_UNICODE ) . "\n";
echo base64_encode( gzencode( file_get_contents( $file ), 9 ) ), "\n";
unlink( $file );
