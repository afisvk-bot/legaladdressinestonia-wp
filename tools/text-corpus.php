<?php
$out = array();
foreach ( array( 'page', 'post', 'service', 'licensing', 'industry', 'target' ) as $t ) foreach ( get_posts( array( 'post_type' => $t, 'post_status' => 'publish', 'numberposts' => -1, 'lang' => '', 'fields' => 'ids' ) ) as $id ) {
	$txt = get_post_field( 'post_content', $id );
	foreach ( get_post_meta( $id ) as $k => $v ) if ( preg_match( '/^content_blocks_\d+_(content|text_left|text_right)$/', $k ) ) $txt .= "\n" . $v[0];
	$txt = preg_replace( '~<(h[1-6]|a)\b.*?</\1>~si', ' ', $txt ); // как модуль: без заголовков и существующих ссылок
	$out[] = array( 'id' => $id, 'type' => $t, 'lang' => pll_get_post_language( $id ), 'en' => pll_get_post( $id, 'en' ), 't' => trim( preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( $txt ), ENT_QUOTES, 'UTF-8' ) ) ) );
}
echo base64_encode( gzencode( wp_json_encode( $out, JSON_UNESCAPED_UNICODE ), 9 ) ), "\n";
