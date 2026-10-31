<?php
/**
 * wp eval-file tools/export-post.php <slug> [post_type]
 * Выводит JSON: языки Polylang, пост (поля, мета, ACF, рубрики/метки), существующие переводы.
 */
$slug = $args[0] ?? '';
$type = $args[1] ?? 'any';
if ( '' === $slug ) {
	WP_CLI::error( 'slug required' );
}

$q = get_posts( array(
	'name'             => $slug,
	'post_type'        => $type,
	'post_status'      => 'any',
	'numberposts'      => 5,
	'lang'             => '',
	'suppress_filters' => false,
) );
if ( ! $q ) {
	WP_CLI::error( "post '$slug' not found" );
}
$p = $q[0];

$langs = array();
foreach ( pll_languages_list( array( 'fields' => '' ) ) as $l ) {
	$langs[] = array( 'slug' => $l->slug, 'locale' => $l->locale, 'name' => $l->name, 'default' => (bool) ( $l->is_default ?? false ) );
}

$meta = array();
foreach ( get_post_meta( $p->ID ) as $k => $v ) {
	$meta[ $k ] = count( $v ) > 1 ? $v : $v[0];
}

$terms = array();
foreach ( get_object_taxonomies( $p->post_type ) as $tax ) {
	if ( in_array( $tax, array( 'language', 'post_translations' ), true ) ) {
		continue;
	}
	foreach ( (array) wp_get_object_terms( $p->ID, $tax ) as $t ) {
		if ( is_wp_error( $t ) ) {
			continue;
		}
		$tr = function_exists( 'pll_get_term_translations' ) ? pll_get_term_translations( $t->term_id ) : array();
		$names = array();
		foreach ( $tr as $lang => $tid ) {
			$tt = get_term( $tid );
			$names[ $lang ] = $tt && ! is_wp_error( $tt ) ? array( 'id' => $tid, 'name' => $tt->name, 'slug' => $tt->slug ) : $tid;
		}
		$terms[ $tax ][] = array( 'id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug, 'translations' => $names );
	}
}

$translations = array();
foreach ( pll_get_post_translations( $p->ID ) as $lang => $id ) {
	$translations[ $lang ] = array( 'id' => $id, 'status' => get_post_status( $id ), 'slug' => get_post_field( 'post_name', $id ), 'title' => get_the_title( $id ), 'url' => get_permalink( $id ) );
}

// Пример уже переведённого поста того же типа — чтобы повторить формат slug и мета.
$sample = array();
$other  = get_posts( array( 'post_type' => $p->post_type, 'numberposts' => 30, 'lang' => '', 'exclude' => array( $p->ID ), 'suppress_filters' => false ) );
foreach ( $other as $o ) {
	$tr = pll_get_post_translations( $o->ID );
	if ( count( $tr ) >= 4 ) {
		foreach ( $tr as $lang => $id ) {
			$sample[ $lang ] = array(
				'id'       => $id,
				'slug'     => get_post_field( 'post_name', $id ),
				'title'    => get_the_title( $id ),
				'url'      => get_permalink( $id ),
				'rm_title' => get_post_meta( $id, 'rank_math_title', true ),
				'rm_desc'  => get_post_meta( $id, 'rank_math_description', true ),
				'rm_kw'    => get_post_meta( $id, 'rank_math_focus_keyword', true ),
				'author'   => get_post_field( 'post_author', $id ),
			);
		}
		break;
	}
}

echo wp_json_encode( array(
	'languages'    => $langs,
	'post'         => array(
		'ID'           => $p->ID,
		'post_type'    => $p->post_type,
		'post_status'  => $p->post_status,
		'post_name'    => $p->post_name,
		'post_title'   => $p->post_title,
		'post_excerpt' => $p->post_excerpt,
		'post_author'  => $p->post_author,
		'post_date'    => $p->post_date,
		'lang'         => pll_get_post_language( $p->ID ),
		'url'          => get_permalink( $p->ID ),
		'post_content' => $p->post_content,
		'page_template'=> get_page_template_slug( $p->ID ),
		'thumbnail_id' => get_post_thumbnail_id( $p->ID ),
	),
	'meta'         => $meta,
	'acf'          => function_exists( 'get_fields' ) ? get_fields( $p->ID, false ) : null,
	'terms'        => $terms,
	'translations' => $translations,
	'sample'       => $sample,
	'pll_sync'     => ( get_option( 'polylang' )['sync'] ?? null ),
), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
echo "\n";
