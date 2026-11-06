<?php
/**
 * Plugin Name: LAI — Migration 2026-11-06 (дубли и переводы)
 * Description: Одноразово по кнопке в админке: связывает переводы, убирает копии-дубли в корзину, создаёт UK-версию «Бухгалтерия для компаний e-резидентов». После применения файл можно удалить.
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

const LAI_MIG_OPT = 'lai_mig_20261106';

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'pll_save_post_translations' ) ) return;
	$st = get_option( LAI_MIG_OPT );
	if ( is_array( $st ) ) {
		if ( isset( $_GET['lai_mig'] ) ) {
			echo '<div class="notice notice-success"><p><b>LAI migration 2026-11-06 — выполнено.</b></p><pre style="white-space:pre-wrap;font-size:12px">' . esc_html( implode( "\n", $st['log'] ) ) . '</pre><p>Файл <code>mu-plugins/lai-migrate-20261106.php</code> теперь можно удалить.</p></div>';
		}
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=lai_mig_20261106' ), 'lai_mig_20261106' );
	echo '<div class="notice notice-warning"><p><b>LAI: готова миграция «дубли и переводы» (2026-11-06).</b> Свяжет переводы MTÜ (ZH) и TSD (RU), уберёт 8 копий-дублей в корзину (адреса редиректятся 301), исправит slug DE /fur-start-ups/ и заголовок DE AGB, создаст UK-страницу «Бухгалтерія для компаній e-резидентів Естонії». Перед запуском сделайте бэкап базы в UpdraftPlus.</p><p><a class="button button-primary" href="' . esc_url( $url ) . '">Применить миграцию</a></p></div>';
} );

add_action( 'admin_post_lai_mig_20261106', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
	check_admin_referer( 'lai_mig_20261106' );
	@set_time_limit( 300 );
	$log = lai_mig_20261106_run();
	update_option( LAI_MIG_OPT, array( 'time' => current_time( 'mysql' ), 'log' => $log ), false );
	wp_safe_redirect( admin_url( 'index.php?lai_mig=1' ) );
	exit;
} );

function lai_mig_20261106_run(): array {
	$log = array();
	$ok  = fn( $id ) => $id && get_post( $id ) && 'trash' !== get_post_status( $id );

	/* 1. Связи переводов */
	foreach ( array( array( 3639, 'zh', 3641 ), array( 3633, 'ru', 3629 ) ) as list( $base, $lang, $pid ) ) {
		if ( ! $ok( $base ) || ! $ok( $pid ) ) { $log[] = "SKIP link #$pid: post missing"; continue; }
		$g = array_merge( pll_get_post_translations( $pid ), pll_get_post_translations( $base ) );
		if ( isset( $g[ $lang ] ) && (int) $g[ $lang ] === $pid && (int) ( pll_get_post_translations( $pid )['en'] ?? 0 ) === $base ) { $log[] = "ok: #$pid already linked"; continue; }
		$g[ $lang ] = $pid;
		$g = array_filter( $g, fn( $p ) => 'publish' === get_post_status( $p ) );
		pll_set_post_language( $pid, $lang );
		pll_save_post_translations( $g );
		$log[] = "linked #$pid ($lang) with #$base: " . wp_json_encode( $g );
	}

	/* 2. Копии на одном языке → корзина */
	$pairs = array( 4545 => 4535, 4516 => 4504, 4471 => 4468, 2461 => 2456, 2376 => 2372, 2378 => 2373, 2526 => 2515, 3641 => 3642 );
	foreach ( $pairs as $keep => $stray ) {
		if ( ! $ok( $stray ) ) { $log[] = "ok: #$stray already removed"; continue; }
		if ( ! $ok( $keep ) || pll_get_post_language( $keep ) !== pll_get_post_language( $stray ) ) { $log[] = "SKIP #$stray: main page #$keep missing or language differs"; continue; }
		$lang = pll_get_post_language( $stray );
		$g = pll_get_post_translations( $stray );
		if ( (int) ( $g[ $lang ] ?? 0 ) === $stray && count( $g ) > 1 ) { unset( $g[ $lang ] ); pll_save_post_translations( $g ); }
		$slug = get_post_field( 'post_name', $stray );
		wp_trash_post( $stray );
		$log[] = "trashed #$stray ($lang) /$slug/ -> 301 to #$keep";
		if ( 4545 === $keep && 'fur-start-ups' === $slug ) {
			wp_update_post( array( 'ID' => 4545, 'post_name' => 'fur-start-ups' ) );
			$log[] = 'DE #4545 slug -> fur-start-ups';
		}
	}

	/* 3. DE AGB */
	if ( 'Terms and Conditions' === get_post_field( 'post_title', 198 ) ) {
		wp_update_post( array( 'ID' => 198, 'post_title' => 'Allgemeine Geschäftsbedingungen' ) );
		$rt = (string) get_post_meta( 198, 'rank_math_title', true );
		if ( false !== stripos( $rt, 'Terms and Conditions' ) ) update_post_meta( 198, 'rank_math_title', str_ireplace( 'Terms and Conditions', 'Allgemeine Geschäftsbedingungen', $rt ) );
		$log[] = 'DE #198 title -> Allgemeine Geschäftsbedingungen';
	}

	/* 4. UK-перевод «Бухгалтерия для компаний e-резидентов» (из RU #3624) */
	$log = array_merge( $log, lai_mig_20261106_uk() );

	if ( function_exists( 'lai_sync_all_menus' ) ) { lai_sync_all_menus(); $log[] = 'menus synced'; }
	flush_rewrite_rules( false );
	do_action( 'litespeed_purge_all' );
	$log[] = 'cache purged';
	return $log;
}

function lai_mig_20261106_uk(): array {
	$file = __DIR__ . '/lai-data/uk-3624.json';
	$d = json_decode( (string) file_get_contents( $file ), true );
	if ( ! $d ) return array( 'SKIP uk: data file missing' );
	$src = get_post( (int) $d['source_id'] );
	if ( ! $src || 'ru' !== pll_get_post_language( $src->ID ) ) return array( 'SKIP uk: RU source #3624 missing' );
	$group = pll_get_post_translations( $src->ID );
	if ( ! empty( $group['uk'] ) && 'publish' === get_post_status( $group['uk'] ) ) return array( 'ok: UK translation already exists #' . $group['uk'] );
	$home = wp_parse_url( home_url(), PHP_URL_HOST );
	$loc = function ( $html ) use ( $home ) {
		return preg_replace_callback( '#href="https?://([^/"]+)(/[^"]*)"#', function ( $m ) use ( $home ) {
			if ( $m[1] !== $home && 'staging.dragonslots-gr.gr' !== $m[1] ) return $m[0];
			$pid = url_to_postid( home_url( $m[2] ) );
			$tid = $pid ? pll_get_post( $pid, 'uk' ) : 0;
			$to  = ( $tid && 'publish' === get_post_status( $tid ) ) ? get_permalink( $tid ) : home_url( $m[2] );
			return 'href="' . esc_url( $to ) . '"';
		}, $html );
	};
	$id = wp_insert_post( wp_slash( array(
		'post_type' => $src->post_type, 'post_status' => 'publish', 'post_title' => $d['title'], 'post_name' => $d['slug'],
		'post_content' => $src->post_content, 'post_author' => $src->post_author, 'menu_order' => $src->menu_order,
	) ), true );
	if ( is_wp_error( $id ) ) return array( 'ERROR uk: ' . $id->get_error_message() );
	foreach ( get_post_meta( $src->ID ) as $k => $vals ) {
		if ( in_array( $k, array( '_edit_lock', '_edit_last', '_wp_old_slug' ), true ) || 0 === strpos( $k, 'rank_math_internal' ) ) continue;
		foreach ( $vals as $v ) add_post_meta( $id, $k, wp_slash( maybe_unserialize( $v ) ) );
	}
	foreach ( $d['fields'] as $k => $v ) update_post_meta( $id, $k, wp_slash( $loc( $v ) ) );
	update_post_meta( $id, '_lai_tr_source', $src->ID );
	pll_set_post_language( $id, 'uk' );
	foreach ( get_object_taxonomies( $src->post_type ) as $tax ) {
		if ( in_array( $tax, array( 'language', 'post_translations' ), true ) ) continue;
		$terms = array();
		foreach ( wp_get_object_terms( $src->ID, $tax, array( 'fields' => 'ids' ) ) as $t ) $terms[] = pll_is_translated_taxonomy( $tax ) ? (int) pll_get_term( $t, 'uk' ) : $t;
		wp_set_object_terms( $id, array_filter( $terms ), $tax );
	}
	$group['uk'] = $id;
	pll_save_post_translations( $group );
	return array( "created UK #$id " . get_permalink( $id ) );
}
