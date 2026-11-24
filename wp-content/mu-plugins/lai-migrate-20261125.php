<?php
/**
 * Plugin Name: LAI — Migration 2026-11-25 (блог: бухгалтерия OÜ на 8 языков + статья о криптокомпании)
 * Description: Одноразово по кнопке: 1) статья «Как вести бухгалтерию OÜ самостоятельно» (RU): дата 16.11.2026, исправлены 3 битые ссылки, внутренние ссылки без nofollow, SEO-мета Rank Math; 2) её переводы на en, uk, de, es, fr, pt, tr, zh; 3) новая статья «Криптовалютная компания в Эстонии 2026» (RU, 23.11.2026) с изображением WebP и alt. Повторный запуск безопасен. После применения файл и lai-data/migrate-20261125.json, lai-data/crypto-company-estonia-2026.webp можно удалить.
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'pll_get_post' ) ) return;
	$st = get_option( 'lai_mig_20261125' );
	if ( is_array( $st ) ) {
		if ( isset( $_GET['lai_mig_g'] ) ) echo '<div class="notice notice-success"><p><b>LAI migration 2026-11-25 — выполнено.</b></p><pre style="white-space:pre-wrap;font-size:12px;max-height:420px;overflow:auto">' . esc_html( implode( "\n", $st['log'] ) ) . '</pre><p>Файлы <code>mu-plugins/lai-migrate-20261125.php</code>, <code>mu-plugins/lai-data/migrate-20261125.json</code> и <code>mu-plugins/lai-data/crypto-company-estonia-2026.webp</code> можно удалить.</p></div>';
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=lai_mig_20261125' ), 'lai_mig_20261125' );
	echo '<div class="notice notice-warning"><p><b>LAI: готова миграция 2026-11-25.</b> Статья «Как вести бухгалтерию OÜ самостоятельно»: дата 16.11, исправление ссылок и SEO, переводы на 8 языков; новая статья «Криптовалютная компания в Эстонии 2026» (23.11) с изображением. Перед запуском сделайте бэкап базы.</p><p><a class="button button-primary" href="' . esc_url( $url ) . '">Применить миграцию 2026-11-25</a></p></div>';
} );

add_action( 'admin_post_lai_mig_20261125', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
	check_admin_referer( 'lai_mig_20261125' );
	if ( function_exists( 'set_time_limit' ) ) @set_time_limit( 600 );
	update_option( 'lai_mig_20261125', array( 'log' => lai_mig_20261125_run(), 'time' => current_time( 'mysql' ) ), false );
	wp_safe_redirect( admin_url( 'index.php?lai_mig_g=1' ) );
	exit;
} );

/** Относительные ссылки (/en-path/) → адрес страницы на нужном языке; если перевода нет — близкая услуга. */
function lai_mig_20261125_links( string $html, string $lang, int &$miss ): string {
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

function lai_mig_20261125_rm( int $id, array $rm ): void {
	foreach ( array( 'title' => 'rank_math_title', 'desc' => 'rank_math_description', 'kw' => 'rank_math_focus_keyword' ) as $k => $mk ) {
		if ( ! empty( $rm[ $k ] ) ) update_post_meta( $id, $mk, wp_slash( $rm[ $k ] ) );
	}
}

function lai_mig_20261125_run(): array {
	$d = json_decode( (string) file_get_contents( __DIR__ . '/lai-data/migrate-20261125.json' ), true );
	if ( ! $d ) return array( 'data file missing' );
	$log   = array();
	$langs = pll_languages_list( array( 'fields' => 'slug' ) );

	// 1. Исходная статья RU
	$s   = $d['source'];
	$src = get_post( (int) $s['id'] );
	if ( ! $src || 'post' !== $src->post_type || $src->post_name !== $s['slug'] ) $src = get_page_by_path( $s['slug'], OBJECT, 'post' );
	if ( ! $src ) return array( 'SKIP: source article ' . $s['slug'] . ' not found' );
	$slang = pll_get_post_language( $src->ID );
	$miss  = 0;
	$new   = lai_mig_20261125_links( (string) $s['content'], $slang, $miss );
	$arr   = array( 'ID' => $src->ID, 'post_date' => $s['date'], 'post_date_gmt' => get_gmt_from_date( $s['date'] ), 'edit_date' => true );
	$cur_h = md5( str_replace( untrailingslashit( home_url() ), '__HOST__', $src->post_content ) );
	if ( $cur_h === $s['orig_md5_hosted'] || $src->post_content === $new ) {
		$arr['post_content'] = $new;
		$log[] = "#{$src->ID}: content updated (links fixed)" . ( $miss ? ", $miss unresolved" : '' );
	} else {
		$log[] = "#{$src->ID}: content was edited on the site after export — text NOT replaced (check 3 links /accounting/... manually)";
	}
	wp_update_post( wp_slash( $arr ) );
	lai_mig_20261125_rm( $src->ID, $s['rm'] );
	$src = get_post( $src->ID );
	$log[] = "#{$src->ID}: date {$src->post_date}, Rank Math meta set — " . get_permalink( $src->ID );

	// 2. Переводы
	$tr_map = pll_get_post_translations( $src->ID );
	foreach ( $d['article']['langs'] as $lang => $tr ) {
		if ( $lang === $slang || ! in_array( $lang, $langs, true ) ) continue;
		$existing = (int) ( $tr_map[ $lang ] ?? 0 );
		if ( $existing && ! get_post_meta( $existing, '_lai_tr_source', true ) ) { $log[] = "  $lang: skipped, translation #$existing already exists"; continue; }
		$miss = 0;
		$cats = array();
		foreach ( wp_get_post_categories( $src->ID ) as $cid ) { $t = pll_get_term( $cid, $lang ); if ( $t ) $cats[] = (int) $t; }
		$postarr = array(
			'post_type' => 'post', 'post_status' => $src->post_status,
			'post_title' => (string) $tr['title'], 'post_name' => sanitize_title( (string) $tr['slug'] ),
			'post_content' => lai_mig_20261125_links( (string) $tr['content'], $lang, $miss ), 'post_author' => (int) $src->post_author,
			'post_date' => $src->post_date, 'post_date_gmt' => $src->post_date_gmt,
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
		lai_mig_20261125_rm( $id, $tr['rm'] );
		$tr_map[ $lang ] = $id;
		pll_save_post_translations( $tr_map );
		clean_post_cache( $id );
		$log[] = '  ' . $lang . ': ' . ( $existing ? 'updated' : 'created' ) . " #$id " . get_permalink( $id ) . ( $miss ? " ($miss links without translation)" : '' );
	}

	// 3. Новая статья о криптокомпании
	$p    = $d['post'];
	$lang = $p['lang'];
	$found = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'meta_key' => '_lai_post_key', 'meta_value' => $p['key'], 'lang' => '', 'fields' => 'ids', 'numberposts' => 1 ) );
	if ( ! $found && ( $same = get_page_by_path( $p['slug'], OBJECT, 'post' ) ) ) { $log[] = "crypto: post with slug {$p['slug']} already exists (#{$same->ID}) — skipped"; $found = null; $skip = true; }
	if ( empty( $skip ) ) {
		$miss = 0;
		$cat  = (int) pll_get_term( (int) $p['cat_en'], $lang );
		$arr  = array(
			'post_type' => 'post', 'post_status' => 'publish', 'post_title' => $p['title'], 'post_name' => $p['slug'],
			'post_content' => lai_mig_20261125_links( (string) $p['content'], $lang, $miss ), 'post_excerpt' => $p['excerpt'],
			'post_author' => get_userdata( (int) $p['author'] ) ? (int) $p['author'] : (int) $src->post_author,
			'post_date' => $p['date'], 'post_date_gmt' => get_gmt_from_date( $p['date'] ), 'post_category' => $cat ? array( $cat ) : array(),
		);
		if ( $found ) $arr['ID'] = (int) $found[0];
		$id = wp_insert_post( wp_slash( $arr ), true );
		if ( is_wp_error( $id ) ) {
			$log[] = 'crypto: ERROR ' . $id->get_error_message();
		} else {
			pll_set_post_language( $id, $lang );
			if ( $cat ) wp_set_post_categories( $id, array( $cat ) );
			update_post_meta( $id, '_lai_post_key', $p['key'] );
			lai_mig_20261125_rm( $id, $p['rm'] );
			foreach ( get_post_meta( $src->ID ) as $k => $vals ) {
				if ( 0 === strpos( $k, '_ez-toc-' ) ) update_post_meta( $id, $k, maybe_unserialize( $vals[0] ) );
			}
			$att = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_lai_img_key', 'meta_value' => $p['image'], 'lang' => '', 'fields' => 'ids', 'numberposts' => 1 ) );
			$att = $att ? (int) $att[0] : 0;
			if ( ! $att && is_readable( __DIR__ . '/lai-data/' . $p['image'] ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/media.php';
				require_once ABSPATH . 'wp-admin/includes/image.php';
				$tmp = wp_tempnam( $p['image'] );
				copy( __DIR__ . '/lai-data/' . $p['image'], $tmp );
				$r = media_handle_sideload( array( 'name' => $p['image'], 'tmp_name' => $tmp ), $id, $p['image_title'] );
				if ( is_wp_error( $r ) ) { $log[] = 'crypto image: ERROR ' . $r->get_error_message(); } else { $att = (int) $r; update_post_meta( $att, '_lai_img_key', $p['image'] ); }
			}
			if ( $att ) {
				update_post_meta( $att, '_wp_attachment_image_alt', wp_slash( $p['alt'] ) );
				if ( pll_is_translated_post_type( 'attachment' ) ) pll_set_post_language( $att, $lang );
				set_post_thumbnail( $id, $att );
			}
			clean_post_cache( $id );
			$log[] = 'crypto: ' . ( $found ? 'updated' : 'created' ) . " #$id " . get_permalink( $id ) . ( $att ? " (image #$att " . wp_get_attachment_url( $att ) . ')' : ' (NO IMAGE)' ) . ( $miss ? " ($miss unresolved links)" : '' );
		}
	}

	update_option( 'lai_il_salt', (string) microtime( true ), false );
	do_action( 'litespeed_purge_all' );
	return $log;
}
