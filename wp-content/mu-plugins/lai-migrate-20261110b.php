<?php
/**
 * Plugin Name: LAI — Migration 2026-11-10b (переводы 5 статей блога)
 * Description: Одноразово по кнопке: переводы 5 последних статей блога на недостающие языки (35 постов) с датой исходной публикации, рубриками, миниатюрой, автором, SEO-мета Rank Math и локализованными ссылками; плюс исправление заголовка/SEO статьи о трудовом договоре (EN/RU). Существующие ручные переводы не трогаются. После применения файл и lai-data/migrate-20261110b-blog.json можно удалить.
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'pll_get_post' ) ) return;
	$st = get_option( 'lai_mig_20261110b' );
	if ( is_array( $st ) ) {
		if ( isset( $_GET['lai_mig_b'] ) ) echo '<div class="notice notice-success"><p><b>LAI migration 2026-11-10b — выполнено.</b></p><pre style="white-space:pre-wrap;font-size:12px;max-height:400px;overflow:auto">' . esc_html( implode( "\n", $st['log'] ) ) . '</pre><p>Файлы <code>mu-plugins/lai-migrate-20261110b.php</code> и <code>mu-plugins/lai-data/migrate-20261110b-blog.json</code> можно удалить.</p></div>';
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=lai_mig_20261110b' ), 'lai_mig_20261110b' );
	echo '<div class="notice notice-warning"><p><b>LAI: готова миграция 2026-11-10b.</b> Переводы 5 последних статей блога на недостающие языки (35 постов, даты как у оригиналов). Перед запуском сделайте бэкап базы.</p><p><a class="button button-primary" href="' . esc_url( $url ) . '">Применить миграцию 2026-11-10b</a></p></div>';
} );

add_action( 'admin_post_lai_mig_20261110b', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
	check_admin_referer( 'lai_mig_20261110b' );
	if ( function_exists( 'set_time_limit' ) ) @set_time_limit( 600 );
	update_option( 'lai_mig_20261110b', array( 'log' => lai_mig_20261110b_run(), 'time' => current_time( 'mysql' ) ), false );
	wp_safe_redirect( admin_url( 'index.php?lai_mig_b=1' ) );
	exit;
} );

function lai_mig_20261110b_links( string $html, string $lang, string $host, int &$miss ): string {
	static $fb = array( 'vat-registration-estonia' => 2261, 'oss-and-ioss-in-estonia' => 2791, 'estonian-corporate-tax' => 2761, 'how-to-open-a-company-in-estonia-as-a-non-resident' => 2263, 'what-is-estonian-e-residency' => 2247, 'estonian-ou-balance-sheet-income-statement' => 2746, 'how-to-open-a-bank-account-for-an-estonian-company' => 2263, 'annual-report-estonia' => 1398, 'corporate-tax-estonia-2026' => 2761, 'how-to-choose-an-accountant-in-estonia' => 2746, 'dividends-or-salary-estonia' => 2761, 'what-is-e-residency' => 2247, 'how-to-get-e-residency' => 2247 );
	return preg_replace_callback( '#href="([^"]+)"#', function ( $m ) use ( $lang, $host, $fb, &$miss ) {
		$url = html_entity_decode( $m[1] );
		if ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) $url = home_url( $url );
		if ( wp_parse_url( $url, PHP_URL_HOST ) !== $host ) return $m[0];
		$pid = url_to_postid( $url );
		if ( ! $pid ) {
			$p   = get_page_by_path( trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' ), OBJECT, array( 'page', 'service', 'licensing', 'industry', 'target', 'post' ) );
			$pid = $p ? $p->ID : 0;
		}
		$tid = $pid ? pll_get_post( $pid, $lang ) : 0;
		if ( $tid && 'publish' === get_post_status( $tid ) ) return 'href="' . esc_url( get_permalink( $tid ) ) . '"';
		$slug = basename( untrailingslashit( (string) wp_parse_url( $url, PHP_URL_PATH ) ) );
		if ( isset( $fb[ $slug ] ) && ( $alt = pll_get_post( $fb[ $slug ], $lang ) ) && 'publish' === get_post_status( $alt ) ) return 'href="' . esc_url( get_permalink( $alt ) ) . '"';
		$miss++;
		return 'href="' . esc_url( $url ) . '"';
	}, $html );
}

function lai_mig_20261110b_run(): array {
	$d = json_decode( (string) file_get_contents( __DIR__ . '/lai-data/migrate-20261110b-blog.json' ), true );
	if ( ! $d ) return array( 'data file missing' );
	$log   = array();
	$host  = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	$langs = pll_languages_list( array( 'fields' => 'slug' ) );

	// 1. Исправление исходников статьи о трудовом договоре (только если поля пустые / заголовок обрезан)
	foreach ( $d['fix_sources'] as $id => $f ) {
		$id = (int) $id;
		if ( ! get_post( $id ) ) { $log[] = "fix #$id: not found"; continue; }
		if ( ! empty( $f['trunc'] ) && get_post_field( 'post_title', $id ) === $f['trunc'] ) {
			wp_update_post( array( 'ID' => $id, 'post_title' => $f['title'] ) );
			$log[] = "fix #$id: title → «{$f['title']}»";
		}
		foreach ( array( 'rank_math_title', 'rank_math_description', 'rank_math_focus_keyword' ) as $i => $k ) {
			if ( '' === trim( (string) get_post_meta( $id, $k, true ) ) ) { update_post_meta( $id, $k, wp_slash( $f['rm'][ $i ] ) ); $log[] = "fix #$id: $k set"; }
		}
	}

	// 2. Переводы статей
	foreach ( $d['articles'] as $a ) {
		$src = get_post( (int) $a['source_id'] );
		if ( ! $src || 'post' !== $src->post_type || $src->post_name !== $a['source_slug'] ) {
			$p   = get_page_by_path( $a['source_slug'], OBJECT, 'post' );
			$src = $p ?: null;
		}
		if ( ! $src ) { $log[] = "SKIP article {$a['source_slug']}: source not found"; continue; }
		$src_lang = pll_get_post_language( $src->ID );
		$tr_map   = pll_get_post_translations( $src->ID );
		$log[]    = "== #{$src->ID} {$src->post_name} ({$src->post_date})";
		foreach ( $a['langs'] as $lang => $tr ) {
			if ( $lang === $src_lang || ! in_array( $lang, $langs, true ) ) continue;
			$existing = (int) ( $tr_map[ $lang ] ?? 0 );
			if ( $existing && ! get_post_meta( $existing, '_lai_tr_source', true ) ) { $log[] = "  $lang: skipped, translation #$existing already exists"; continue; }
			$miss    = 0;
			$content = lai_mig_20261110b_links( (string) $tr['content'], $lang, $host, $miss );
			$cats    = array();
			foreach ( wp_get_post_categories( $src->ID ) as $cid ) {
				$t = pll_get_term( $cid, $lang );
				if ( $t ) $cats[] = (int) $t;
			}
			$postarr = array(
				'post_type' => 'post', 'post_status' => $src->post_status,
				'post_title' => (string) $tr['title'], 'post_name' => sanitize_title( (string) $tr['slug'] ),
				'post_content' => $content, 'post_author' => (int) $src->post_author,
				'post_date' => $src->post_date, 'post_date_gmt' => $src->post_date_gmt,
				'comment_status' => $src->comment_status, 'ping_status' => $src->ping_status,
				'post_category' => $cats,
			);
			if ( $existing ) $postarr['ID'] = $existing;
			$id = wp_insert_post( wp_slash( $postarr ), true );
			if ( is_wp_error( $id ) ) { $log[] = "  $lang: ERROR " . $id->get_error_message(); continue; }
			pll_set_post_language( $id, $lang );
			wp_set_post_categories( $id, $cats );
			$thumb = (int) get_post_thumbnail_id( $src->ID );
			if ( $thumb ) { $tt = (int) pll_get_post( $thumb, $lang ); set_post_thumbnail( $id, $tt ?: $thumb ); }
			foreach ( get_post_meta( $src->ID ) as $k => $vals ) {
				if ( 0 === strpos( $k, '_ez-toc-' ) ) update_post_meta( $id, $k, maybe_unserialize( $vals[0] ) );
			}
			update_post_meta( $id, '_lai_tr_source', $src->ID );
			foreach ( array( 'title' => 'rank_math_title', 'desc' => 'rank_math_description', 'kw' => 'rank_math_focus_keyword' ) as $k => $mk ) {
				if ( ! empty( $tr['rm'][ $k ] ) ) update_post_meta( $id, $mk, wp_slash( $tr['rm'][ $k ] ) );
			}
			$tr_map[ $lang ] = $id;
			pll_save_post_translations( $tr_map );
			clean_post_cache( $id );
			$log[] = '  ' . $lang . ': ' . ( $existing ? 'updated' : 'created' ) . " #$id " . get_permalink( $id ) . ( $miss ? " ($miss links without translation kept)" : '' );
		}
	}
	update_option( 'lai_il_salt', (string) microtime( true ), false );
	do_action( 'litespeed_purge_all' );
	return $log;
}
