<?php
/**
 * Plugin Name: LAI — Migration 2026-11-06b (SEO title/description/keyword)
 * Description: Одноразово по кнопке: короткие SEO-title (до 60 символов), description (до 160), фокусные ключевые слова Rank Math для ~470 страниц на 9 языках; связывает переводы статьи про банковский счёт. Значение перезаписывается, только если на сайте стоит прежнее значение со стейджа или пусто — ручные правки не трогаются. После применения файл можно удалить.
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

const LAI_MIG_B_OPT = 'lai_mig_20261106b';

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'pll_save_post_translations' ) ) return;
	$st = get_option( LAI_MIG_B_OPT );
	if ( is_array( $st ) ) {
		if ( isset( $_GET['lai_mig_b'] ) ) {
			echo '<div class="notice notice-success"><p><b>LAI migration 2026-11-06b (SEO meta) — выполнено.</b> ' . esc_html( $st['summary'] ) . '</p><details><summary>Подробности</summary><pre style="white-space:pre-wrap;font-size:12px;max-height:400px;overflow:auto">' . esc_html( implode( "\n", $st['log'] ) ) . '</pre></details><p>Файл <code>mu-plugins/lai-migrate-20261106b.php</code> теперь можно удалить.</p></div>';
		}
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=lai_mig_20261106b' ), 'lai_mig_20261106b' );
	echo '<div class="notice notice-warning"><p><b>LAI: готова миграция «SEO title/description» (2026-11-06b).</b> Обновит Rank Math title, description и фокусные слова ~470 страниц на 9 языках (ручные правки, сделанные после 06.11, не перезаписываются) и свяжет переводы статьи о банковском счёте. Перед запуском сделайте бэкап базы.</p><p><a class="button button-primary" href="' . esc_url( $url ) . '">Применить миграцию SEO</a></p></div>';
} );

add_action( 'admin_post_lai_mig_20261106b', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
	check_admin_referer( 'lai_mig_20261106b' );
	@set_time_limit( 600 );
	$r = lai_mig_20261106b_run();
	update_option( LAI_MIG_B_OPT, $r + array( 'time' => current_time( 'mysql' ) ), false );
	wp_safe_redirect( admin_url( 'index.php?lai_mig_b=1' ) );
	exit;
} );

function lai_mig_20261106b_run(): array {
	$d = json_decode( (string) file_get_contents( __DIR__ . '/lai-data/meta-20261106.json' ), true );
	if ( ! $d ) return array( 'summary' => 'data file missing', 'log' => array() );
	$map = array( 'title' => 'rank_math_title', 'desc' => 'rank_math_description', 'kw' => 'rank_math_focus_keyword' );
	$log = array(); $n = array( 'set' => 0, 'same' => 0, 'kept' => 0, 'skip' => 0 );
	foreach ( $d['rows'] as $r ) {
		$id = (int) $r['id'];
		if ( 'publish' !== get_post_status( $id ) || ( function_exists( 'pll_get_post_language' ) && pll_get_post_language( $id ) !== $r['lang'] ) ) {
			$n['skip']++; $log[] = "SKIP #$id: not published or language differs"; continue;
		}
		foreach ( $map as $k => $meta ) {
			if ( empty( $r[ $k ] ) ) continue;
			$cur = (string) get_post_meta( $id, $meta, true );
			if ( $cur === $r[ $k ] ) { $n['same']++; continue; }
			if ( '' !== trim( $cur ) && $cur !== $r['old'][ $k ] ) { $n['kept']++; $log[] = "KEPT #$id $k (edited manually): $cur"; continue; }
			update_post_meta( $id, $meta, wp_slash( $r[ $k ] ) );
			$n['set']++;
		}
	}
	foreach ( (array) ( $d['links'] ?? array() ) as $g ) {
		$g = array_filter( array_map( 'intval', $g ), fn( $p ) => 'publish' === get_post_status( $p ) );
		$merged = array();
		foreach ( $g as $p ) $merged = array_merge( $merged, pll_get_post_translations( $p ) );
		foreach ( $g as $l => $p ) $merged[ $l ] = $p;
		pll_save_post_translations( $merged );
		$log[] = 'linked ' . wp_json_encode( $merged );
	}
	do_action( 'litespeed_purge_all' );
	return array( 'summary' => "updated {$n['set']} fields, already ok {$n['same']}, kept manual edits {$n['kept']}, skipped pages {$n['skip']}", 'log' => $log, 'n' => $n );
}
