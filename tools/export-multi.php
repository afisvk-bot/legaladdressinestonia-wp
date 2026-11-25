<?php
/** wp eval-file tools/export-multi.php <id,id,...> — как export-fields.php, для нескольких записей (JSON-объект id → данные). */
$res = array();
foreach ( array_map( 'intval', explode( ',', (string) ( $args[0] ?? '' ) ) ) as $id ) {
	$p = get_post( $id );
	if ( ! $p ) { $res[ $id ] = null; continue; }
	$fields = array(); $all = array();
	foreach ( get_post_meta( $id ) as $k => $v ) {
		$val = $v[0];
		if ( 0 === strpos( $k, '_' ) ) continue;
		$all[ $k ] = mb_substr( (string) $val, 0, 80 );
		if ( is_serialized( $val ) || is_numeric( $val ) || '' === trim( $val ) || preg_match( '/^field_/', $val ) ) continue;
		if ( preg_match( '/^(rank_math_(title|description|focus_keyword|facebook_title|facebook_description|twitter_title|twitter_description))$|^content_blocks_/', $k ) ) {
			if ( preg_match( '#^https?://#', trim( $val ) ) || preg_match( '/^(#|[a-z0-9_\-]+)$/i', trim( $val ) ) && mb_strlen( $val ) < 25 && ! preg_match( '/\p{Cyrillic}/u', $val ) ) continue;
			$fields[ $k ] = $val;
		}
	}
	$res[ $id ] = array( 'id' => $id, 'lang' => pll_get_post_language( $id ), 'type' => $p->post_type, 'status' => $p->post_status, 'slug' => $p->post_name, 'title' => $p->post_title, 'date' => $p->post_date, 'modified' => $p->post_modified, 'author' => $p->post_author,
		'excerpt' => $p->post_excerpt, 'content' => $p->post_content, 'group' => pll_get_post_translations( $id ), 'thumb' => get_post_thumbnail_id( $id ), 'thumb_alt' => get_post_meta( get_post_thumbnail_id( $id ), '_wp_attachment_image_alt', true ), 'fields' => $fields, 'all_meta' => $all );
}
echo wp_json_encode( $res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), "\n";
