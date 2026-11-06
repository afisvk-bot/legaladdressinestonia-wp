<?php
/** wp eval-file tools/export-fields.php <id> — текстовые поля записи (заголовок, ACF, Rank Math) для перевода. */
$id = (int) ( $args[0] ?? 0 );
$p  = get_post( $id );
$fields = array();
foreach ( get_post_meta( $id ) as $k => $v ) {
	$val = $v[0];
	if ( 0 === strpos( $k, '_' ) && ! in_array( $k, array(), true ) ) continue;
	if ( is_serialized( $val ) || is_numeric( $val ) || '' === trim( $val ) || preg_match( '/^field_/', $val ) ) continue;
	if ( preg_match( '/^(rank_math_(title|description|focus_keyword|facebook_title|facebook_description|twitter_title|twitter_description))$|^content_blocks_/', $k ) ) {
		if ( preg_match( '#^https?://#', trim( $val ) ) || preg_match( '/^(#|[a-z0-9_\-]+)$/i', trim( $val ) ) && mb_strlen( $val ) < 25 && ! preg_match( '/\p{Cyrillic}/u', $val ) ) continue;
		$fields[ $k ] = $val;
	}
}
$en = pll_get_post( $id, 'en' );
echo wp_json_encode( array(
	'id' => $id, 'lang' => pll_get_post_language( $id ), 'type' => $p->post_type, 'slug' => $p->post_name, 'title' => $p->post_title,
	'excerpt' => $p->post_excerpt, 'content' => $p->post_content, 'group' => pll_get_post_translations( $id ), 'en' => $en,
	'uk_slug_sample' => get_post_field( 'post_name', pll_get_post( 4529, 'uk' ) ), 'fields' => $fields,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), "\n";
