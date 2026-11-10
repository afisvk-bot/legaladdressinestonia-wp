<?php
/**
 * Plugin Name: LAI — Migration 2026-11-10 (новая услуга и статья)
 * Description: Одноразово по кнопке: испанский перевод услуги «Восстановление бухучёта» (сейчас там английская копия), SEO title/description/keyword для этой услуги на 9 языках, статьи о визах (EN/RU) и UK-страницы «Бухгалтерия для e-резидентов». После применения файл можно удалить.
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'pll_get_post' ) ) return;
	$st = get_option( 'lai_mig_20261110' );
	if ( is_array( $st ) ) {
		if ( isset( $_GET['lai_mig_d'] ) ) echo '<div class="notice notice-success"><p><b>LAI migration 2026-11-10 — выполнено.</b></p><pre style="white-space:pre-wrap;font-size:12px;max-height:400px;overflow:auto">' . esc_html( implode( "\n", $st['log'] ) ) . '</pre><p>Файл <code>mu-plugins/lai-migrate-20261110.php</code> можно удалить.</p></div>';
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=lai_mig_20261110' ), 'lai_mig_20261110' );
	echo '<div class="notice notice-warning"><p><b>LAI: готова миграция 2026-11-10.</b> Испанский перевод услуги «Восстановление бухучёта», SEO-мета для неё (9 языков), для статьи о визах и UK-страницы бухгалтерии e-резидентов. Перед запуском сделайте бэкап базы.</p><p><a class="button button-primary" href="' . esc_url( $url ) . '">Применить миграцию 2026-11-10</a></p></div>';
} );

add_action( 'admin_post_lai_mig_20261110', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
	check_admin_referer( 'lai_mig_20261110' );
	update_option( 'lai_mig_20261110', array( 'log' => lai_mig_20261110_run(), 'time' => current_time( 'mysql' ) ), false );
	wp_safe_redirect( admin_url( 'index.php?lai_mig_d=1' ) );
	exit;
} );

function lai_mig_20261110_run(): array {
	$d = json_decode( (string) file_get_contents( __DIR__ . '/lai-data/migrate-20261110.json' ), true );
	if ( ! $d ) return array( 'data file missing' );
	$log = array();
	$set = function ( int $id, array $m ) use ( &$log ) {
		foreach ( array( 'title' => 'rank_math_title', 'desc' => 'rank_math_description', 'kw' => 'rank_math_focus_keyword' ) as $k => $meta ) {
			if ( empty( $m[ $k ] ) ) continue;
			$cur = (string) get_post_meta( $id, $meta, true );
			if ( $cur === $m[ $k ] ) continue;
			update_post_meta( $id, $meta, wp_slash( $m[ $k ] ) );
			$log[] = "#$id $meta: «{$cur}» → «{$m[$k]}»";
		}
	};
	// 1. ES перевод услуги «Восстановление бухучёта»
	$es = $d['es_restoration']; $id = (int) $es['id'];
	if ( 'publish' === get_post_status( $id ) && 'es' === pll_get_post_language( $id ) && get_the_title( $id ) === $es['expect_title'] ) {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		foreach ( $es['fields'] as $k => $v ) {
			$v = preg_replace_callback( '#href="https?://[^/"]+(/[^"]*)"#', function ( $m ) {
				$pid = url_to_postid( home_url( $m[1] ) ); $t = $pid ? pll_get_post( $pid, 'es' ) : 0;
				return 'href="' . esc_url( $t ? get_permalink( $t ) : home_url( $m[1] ) ) . '"';
			}, $v );
			if ( 0 === strpos( $k, 'rank_math_' ) ) continue; // мета ниже, через $set
			update_post_meta( $id, $k, wp_slash( $v ) );
		}
		foreach ( get_post_meta( $id ) as $k => $vals ) {
			if ( 0 === strpos( $k, 'content_blocks_' ) && isset( $es['short'][ $vals[0] ] ) ) update_post_meta( $id, $k, $es['short'][ $vals[0] ] );
		}
		wp_update_post( array( 'ID' => $id, 'post_title' => $es['title'], 'post_name' => $es['slug'] ) );
		$set( $id, array( 'title' => $es['fields']['rank_math_title'], 'desc' => $es['fields']['rank_math_description'], 'kw' => $es['fields']['rank_math_focus_keyword'] ) );
		$log[] = "ES #$id translated, URL " . get_permalink( $id );
	} else {
		$log[] = "ES #$id skipped (already translated or changed: «" . get_the_title( $id ) . '»)';
	}
	// 2. SEO-мета
	foreach ( $d['meta'] as $pid => $m ) {
		$pid = (int) $pid;
		if ( 'publish' !== get_post_status( $pid ) ) { $log[] = "SKIP #$pid not published"; continue; }
		$set( $pid, $m );
	}
	$uk = pll_get_post( (int) $d['uk_accounting']['source_ru'], 'uk' );
	if ( $uk ) $set( (int) $uk, $d['uk_accounting'] ); else $log[] = 'UK accounting page not found';
	update_option( 'lai_il_salt', (string) microtime( true ), false );
	do_action( 'litespeed_purge_all' );
	return $log;
}
