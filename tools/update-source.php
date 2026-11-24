<?php
/**
 * wp eval-file tools/update-source.php <data/blog-ID.json> "<Y-m-d H:i:s>"
 * Обновляет исходную статью: дата публикации, контент на её языке (ссылки локализуются), мета Rank Math.
 */
$file = $args[0] ?? '';
$date = str_replace( 'T', ' ', $args[1] ?? '' );
$path = is_readable( __DIR__ . '/' . $file ) ? __DIR__ . '/' . $file : $file;
$data = json_decode( (string) file_get_contents( $path ), true );
$src  = get_post( (int) ( $data['source_id'] ?? 0 ) );
if ( ! $src ) WP_CLI::error( 'source not found' );
$lang = pll_get_post_language( $src->ID );
$tr   = $data['langs'][ $lang ] ?? null;
if ( ! $tr ) WP_CLI::error( "no $lang data" );

$log = array();
$c = preg_replace_callback( '#href="(/[^"]*)"#', function ( $m ) use ( $lang, &$log ) {
	$url = home_url( $m[1] );
	$pid = url_to_postid( $url );
	if ( ! $pid ) {
		$p = get_page_by_path( trim( $m[1], '/' ), OBJECT, array( 'page', 'service', 'licensing', 'industry', 'target', 'post' ) );
		$pid = $p ? $p->ID : 0;
	}
	$t = $pid ? pll_get_post( $pid, $lang ) : 0;
	if ( $t && 'publish' === get_post_status( $t ) ) {
		$log[] = $m[1] . ' -> ' . get_permalink( $t );
		return 'href="' . esc_url( get_permalink( $t ) ) . '"';
	}
	$log[] = $m[1] . ' -> NOT RESOLVED';
	return $m[0];
}, (string) $tr['content'] );

$arr = array( 'ID' => $src->ID, 'post_content' => $c );
if ( $date ) {
	$arr['post_date']     = $date;
	$arr['post_date_gmt'] = get_gmt_from_date( $date );
	$arr['edit_date']     = true;
}
$r = wp_update_post( wp_slash( $arr ), true );
if ( is_wp_error( $r ) ) WP_CLI::error( $r->get_error_message() );
foreach ( array( 'title' => 'rank_math_title', 'desc' => 'rank_math_description', 'kw' => 'rank_math_focus_keyword' ) as $k => $mk ) {
	if ( ! empty( $tr['rm'][ $k ] ) ) update_post_meta( $src->ID, $mk, wp_slash( $tr['rm'][ $k ] ) );
}
clean_post_cache( $src->ID );
echo wp_json_encode( array( 'id' => $src->ID, 'lang' => $lang, 'date' => get_post_field( 'post_date', $src->ID ), 'links' => $log, 'url' => get_permalink( $src->ID ) ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), "\n";
