<?php
/**
 * Plugin Name: LAI — Migration 2026-11-26 (статья «Криптовалютная компания в Эстонии 2026» на 8 языков)
 * Description: Одноразово по кнопке: переводы статьи «Криптовалютная компания в Эстонии 2026» на en, uk, de, es, fr, pt, tr, zh — с датой оригинала, рубрикой «Блог» каждого языка, изображением, кратким описанием и SEO-мета Rank Math. Требует, чтобы русская статья уже была (миграция 2026-11-25). Повторный запуск безопасен. После применения файл и lai-data/migrate-20261126.json можно удалить.
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'pll_get_post' ) ) return;
	$st = get_option( 'lai_mig_20261126' );
	if ( is_array( $st ) ) {
		if ( isset( $_GET['lai_mig_h'] ) ) echo '<div class="notice notice-success"><p><b>LAI migration 2026-11-26 — выполнено.</b></p><pre style="white-space:pre-wrap;font-size:12px;max-height:420px;overflow:auto">' . esc_html( implode( "\n", $st['log'] ) ) . '</pre><p>Файлы <code>mu-plugins/lai-migrate-20261126.php</code> и <code>mu-plugins/lai-data/migrate-20261126.json</code> можно удалить.</p></div>';
		return;
	}
	if ( false === get_option( 'lai_mig_20261125' ) && file_exists( __DIR__ . '/lai-migrate-20261125.php' ) ) {
		echo '<div class="notice notice-info"><p><b>LAI: миграция 2026-11-26</b> (переводы статьи о криптокомпании) станет доступна после применения миграции 2026-11-25.</p></div>';
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=lai_mig_20261126' ), 'lai_mig_20261126' );
	echo '<div class="notice notice-warning"><p><b>LAI: готова миграция 2026-11-26.</b> Переводы статьи «Криптовалютная компания в Эстонии 2026» на 8 языков (в блог каждой языковой версии). Перед запуском сделайте бэкап базы.</p><p><a class="button button-primary" href="' . esc_url( $url ) . '">Применить миграцию 2026-11-26</a></p></div>';
} );

add_action( 'admin_post_lai_mig_20261126', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
	check_admin_referer( 'lai_mig_20261126' );
	if ( function_exists( 'set_time_limit' ) ) @set_time_limit( 600 );
	$log = lai_mig_20261126_run();
	if ( empty( $log['fail'] ) ) update_option( 'lai_mig_20261126', array( 'log' => $log, 'time' => current_time( 'mysql' ) ), false );
	else { set_transient( 'lai_mig_20261126_err', $log, 600 ); }
	wp_safe_redirect( admin_url( 'index.php?lai_mig_h=1' ) );
	exit;
} );
add_action( 'admin_notices', function () {
	if ( isset( $_GET['lai_mig_h'] ) && ( $e = get_transient( 'lai_mig_20261126_err' ) ) ) { delete_transient( 'lai_mig_20261126_err' ); echo '<div class="notice notice-error"><p><b>LAI migration 2026-11-26 не выполнена:</b> ' . esc_html( $e[0] ) . '</p></div>'; }
} );

/** Относительные ссылки (/en-path/) → адрес страницы на нужном языке; если перевода нет — близкая услуга. */
function lai_mig_20261126_links( string $html, string $lang, int &$miss ): string {
	static $fb = array( 'vat-registration-estonia' => 2261, 'oss-and-ioss-in-estonia' => 2791, 'estonian-corporate-tax' => 2761, 'how-to-open-a-company-in-estonia-as-a-non-resident' => 2263, 'what-is-estonian-e-residency' => 2247, 'estonian-ou-balance-sheet-income-statement' => 2746, 'how-to-open-a-bank-account-for-an-estonian-company' => 2263, 'annual-report-estonia' => 1398, 'corporate-tax-estonia-2026' => 2761, 'how-to-choose-an-accountant-in-estonia' => 2746, 'dividends-or-salary-estonia' => 2761, 'what-is-e-residency' => 2247, 'how-to-get-e-residency' => 2247 );
	return preg_replace_callback( '#href="(/[^"/][^"]*)"#', function ( $m ) use ( $lang, $fb, &$miss ) {
		$url = home_url( html_entity_decode( $m[1] ) );
		$pid = url_to_postid( $url );
		if ( ! $pid ) {
			$p   = get_page_by_path( trim( $m[1], '/' ), OBJECT, array( 'page', 'service', 'licensing', 'industry', 'target', 'post' ) );
			$p   = $p ?: get_page_by_path( basename( trim( $m[1], '/' ) ), OBJECT, array( 'licensing', 'industry', 'target' ) );
			$pid = $p ? $p->ID : 0;
		}
		$tid = $pid ? pll_get_post( $pid, $lang ) : 0;
		if ( $tid && 'publish' === get_post_status( $tid ) ) return 'href="' . esc_url( get_permalink( $tid ) ) . '"';
		$slug = basename( untrailingslashit( $m[1] ) );
		if ( isset( $fb[ $slug ] ) && ( $alt = pll_get_post( $fb[ $slug ], $lang ) ) && 'publish' === get_post_status( $alt ) ) return 'href="' . esc_url( get_permalink( $alt ) ) . '"';
		$miss++;
		return 'href="' . esc_url( $url ) . '"';
	}, $html );
}

function lai_mig_20261126_run(): array {
	$d = json_decode( (string) file_get_contents( __DIR__ . '/lai-data/migrate-20261126.json' ), true );
	if ( ! $d ) return array( 'data file missing', 'fail' => 1 );
	$f   = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'meta_key' => '_lai_post_key', 'meta_value' => $d['source_key'], 'lang' => '', 'numberposts' => 1 ) );
	$src = $f ? $f[0] : get_page_by_path( $d['source_slug'], OBJECT, 'post' );
	if ( ! $src ) return array( 'русская статья «Криптовалютная компания в Эстонии 2026» не найдена — сначала примените миграцию 2026-11-25', 'fail' => 1 );
	$log    = array( "== #{$src->ID} {$src->post_name} ({$src->post_date})" );
	$langs  = pll_languages_list( array( 'fields' => 'slug' ) );
	$slang  = pll_get_post_language( $src->ID );
	$tr_map = pll_get_post_translations( $src->ID );
	foreach ( $d['langs'] as $lang => $tr ) {
		if ( $lang === $slang || ! in_array( $lang, $langs, true ) ) continue;
		$existing = (int) ( $tr_map[ $lang ] ?? 0 );
		if ( $existing && ! get_post_meta( $existing, '_lai_tr_source', true ) ) { $log[] = "  $lang: skipped, translation #$existing already exists"; continue; }
		$miss = 0;
		$cats = array();
		foreach ( wp_get_post_categories( $src->ID ) as $cid ) { $t = pll_get_term( $cid, $lang ); if ( $t ) $cats[] = (int) $t; }
		$postarr = array(
			'post_type' => 'post', 'post_status' => $src->post_status,
			'post_title' => (string) $tr['title'], 'post_name' => sanitize_title( (string) $tr['slug'] ),
			'post_content' => lai_mig_20261126_links( (string) $tr['content'], $lang, $miss ), 'post_excerpt' => (string) $tr['excerpt'],
			'post_author' => (int) $src->post_author, 'post_date' => $src->post_date, 'post_date_gmt' => $src->post_date_gmt,
			'comment_status' => $src->comment_status, 'ping_status' => $src->ping_status, 'post_category' => $cats,
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
		$log[] = '  ' . $lang . ': ' . ( $existing ? 'updated' : 'created' ) . " #$id " . get_permalink( $id ) . ( $miss ? " ($miss links without translation)" : '' );
	}
	update_option( 'lai_il_salt', (string) microtime( true ), false );
	do_action( 'litespeed_purge_all' );
	return $log;
}
