<?php
/**
 * wp eval-file tools/import-translations.php <data/file.json> [dry]
 *
 * data.json: { "source_id": 3035, "langs": { "de": { "title": "...", "slug": "...", "content": "..." }, ... } }
 *
 * Для каждого языка: создаёт пост-перевод (или обновляет созданный этим скриптом ранее),
 * ставит язык Polylang и связывает с исходником, переносит рубрики (переведённые термины),
 * миниатюру, автора, дату и мета Easy TOC. Внутренние ссылки в контенте заменяются на
 * переводы страниц этого языка (если перевода нет — ссылка остаётся как есть).
 * Существующие переводы, созданные не этим скриптом, НЕ трогаются.
 */
$file = $args[0] ?? '';
$dry  = in_array( 'dry', $args, true );
$path = $file;
if ( 0 !== strpos( $file, '/' ) ) {
	foreach ( array( dirname( ABSPATH ) . '/lai-tools/', __DIR__ . '/' ) as $base ) {
		if ( is_readable( $base . $file ) ) {
			$path = $base . $file;
			break;
		}
	}
}
$data = json_decode( (string) file_get_contents( $path ), true );
if ( ! is_array( $data ) || empty( $data['source_id'] ) || empty( $data['langs'] ) ) {
	WP_CLI::error( "bad data file: $path" );
}

$src = get_post( (int) $data['source_id'] );
if ( ! $src ) {
	WP_CLI::error( 'source post not found' );
}
$src_lang = pll_get_post_language( $src->ID );

$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
wp_set_current_user( $admins ? (int) $admins[0] : (int) $src->post_author );

$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
$langs     = pll_languages_list( array( 'fields' => 'slug' ) );

function lai_tr_localize_links( string $html, string $lang, string $home_host, array &$log ): string {
	return preg_replace_callback( '#href="([^"]+)"#', function ( $m ) use ( $lang, $home_host, &$log ) {
		$url = html_entity_decode( $m[1] );
		if ( wp_parse_url( $url, PHP_URL_HOST ) !== $home_host ) {
			return $m[0];
		}
		$pid = url_to_postid( $url );
		if ( ! $pid ) {
			$p = get_page_by_path( trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' ), OBJECT, array( 'page', 'service', 'licensing', 'industry', 'target', 'post' ) );
			$pid = $p ? $p->ID : 0;
		}
		$tid = $pid ? pll_get_post( $pid, $lang ) : 0;
		if ( $tid && 'publish' === get_post_status( $tid ) ) {
			$new = get_permalink( $tid );
			$log[] = "$url -> $new";
			return 'href="' . esc_url( $new ) . '"';
		}
		$log[] = "$url -> (no $lang translation, kept)";
		return $m[0];
	}, $html );
}

$translations = pll_get_post_translations( $src->ID );
$report       = array();

foreach ( $data['langs'] as $lang => $tr ) {
	$row = array( 'lang' => $lang );
	if ( $lang === $src_lang || ! in_array( $lang, $langs, true ) ) {
		$row['status'] = 'skipped: not a target language';
		$report[] = $row;
		continue;
	}
	$existing = $translations[ $lang ] ?? 0;
	if ( $existing && ! get_post_meta( $existing, '_lai_tr_source', true ) ) {
		$row['status'] = "skipped: translation #$existing already exists (manual)";
		$report[] = $row;
		continue;
	}

	$links   = array();
	$content = lai_tr_localize_links( (string) $tr['content'], $lang, (string) $home_host, $links );

	$cats = array();
	foreach ( wp_get_post_categories( $src->ID ) as $cid ) {
		$t = pll_get_term( $cid, $lang );
		if ( $t ) {
			$cats[] = (int) $t;
		}
	}

	$postarr = array(
		'post_type'      => $src->post_type,
		'post_status'    => $src->post_status,
		'post_title'     => (string) $tr['title'],
		'post_name'      => sanitize_title( (string) $tr['slug'] ),
		'post_content'   => $content,
		'post_excerpt'   => (string) ( $tr['excerpt'] ?? '' ),
		'post_author'    => (int) $src->post_author,
		'post_date'      => $src->post_date,
		'post_date_gmt'  => $src->post_date_gmt,
		'comment_status' => $src->comment_status,
		'ping_status'    => $src->ping_status,
		'post_category'  => $cats,
	);
	if ( $existing ) {
		$postarr['ID'] = $existing;
	}

	$row['title'] = $postarr['post_title'];
	$row['links'] = $links;
	$row['cats']  = $cats;
	if ( $dry ) {
		$row['status'] = $existing ? "dry: would update #$existing" : 'dry: would create';
		$report[] = $row;
		continue;
	}

	$id = wp_insert_post( wp_slash( $postarr ), true );
	if ( is_wp_error( $id ) ) {
		$row['status'] = 'error: ' . $id->get_error_message();
		$report[] = $row;
		continue;
	}
	pll_set_post_language( $id, $lang );
	wp_set_post_categories( $id, $cats );

	$thumb = (int) get_post_thumbnail_id( $src->ID );
	if ( $thumb ) {
		$t_thumb = function_exists( 'pll_get_post' ) ? (int) pll_get_post( $thumb, $lang ) : 0;
		set_post_thumbnail( $id, $t_thumb ?: $thumb );
	}
	foreach ( get_post_meta( $src->ID ) as $k => $vals ) {
		if ( 0 === strpos( $k, '_ez-toc-' ) ) {
			update_post_meta( $id, $k, maybe_unserialize( $vals[0] ) );
		}
	}
	update_post_meta( $id, '_lai_tr_source', $src->ID );

	$translations[ $lang ] = $id;
	pll_save_post_translations( $translations );

	clean_post_cache( $id );
	$row['status'] = $existing ? "updated #$id" : "created #$id";
	$row['url']    = get_permalink( $id );
	$report[] = $row;
}

echo wp_json_encode( array( 'source' => $src->ID, 'translations' => pll_get_post_translations( $src->ID ), 'report' => $report ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), "\n";
