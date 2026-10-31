<?php
/** wp eval-file tools/verify-translations.php <source_id> — проверка переводов поста. */
$src = (int) ( $args[0] ?? 0 );
$out = array();
foreach ( pll_get_post_translations( $src ) as $lang => $id ) {
	$c   = (string) get_post_field( 'post_content', $id );
	$url = get_permalink( $id );
	$res = wp_remote_get( $url, array( 'timeout' => 20, 'sslverify' => false, 'headers' => array( 'Cache-Control' => 'no-cache' ) ) );
	$body = is_wp_error( $res ) ? '' : (string) wp_remote_retrieve_body( $res );
	$out[ $lang ] = array(
		'id'        => $id,
		'status'    => get_post_status( $id ),
		'lang'      => pll_get_post_language( $id ),
		'cats'      => wp_list_pluck( get_the_category( $id ), 'slug' ),
		'thumb'     => get_post_thumbnail_id( $id ),
		'h2'        => substr_count( $c, '<h2' ),
		'tables'    => substr_count( $c, '<table' ),
		'mark'      => substr_count( $c, '<mark' ),
		'links'     => substr_count( $c, '<a ' ),
		'http'      => is_wp_error( $res ) ? $res->get_error_message() : wp_remote_retrieve_response_code( $res ),
		'title_in_page' => '' !== $body && false !== strpos( $body, esc_html( get_the_title( $id ) ) ),
		'hreflang'  => preg_match_all( '#hreflang="([a-zA-Z-]+)"#', $body, $m ) ? array_values( array_unique( $m[1] ) ) : array(),
		'url'       => $url,
	);
}
echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), "\n";
