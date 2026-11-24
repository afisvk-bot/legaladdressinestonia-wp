<?php
/**
 * Plugin Name: LAI — Migration 2026-11-24 (дубли переводов блога)
 * Description: Одноразово по кнопке: 8 лишних копий TR/ZH-переводов статей блога (не привязаны к оригиналу, текст идентичен привязанной версии) — в корзину. Привязанной версии возвращается «чистый» адрес без -2/-3; со всех старых адресов работает переадресация 301. После применения файл можно удалить.
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'pll_get_post' ) ) return;
	$st = get_option( 'lai_mig_20261124' );
	if ( is_array( $st ) ) {
		if ( isset( $_GET['lai_mig_f'] ) ) echo '<div class="notice notice-success"><p><b>LAI migration 2026-11-24 — выполнено.</b></p><pre style="white-space:pre-wrap;font-size:12px">' . esc_html( implode( "\n", $st['log'] ) ) . '</pre><p>Файл <code>mu-plugins/lai-migrate-20261124.php</code> можно удалить.</p></div>';
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=lai_mig_20261124' ), 'lai_mig_20261124' );
	echo '<div class="notice notice-warning"><p><b>LAI: готова миграция 2026-11-24.</b> Удаление 8 дублей TR/ZH-переводов статей блога (в корзину) и возврат чистых адресов. Перед запуском сделайте бэкап базы.</p><p><a class="button button-primary" href="' . esc_url( $url ) . '">Применить миграцию 2026-11-24</a></p></div>';
} );

add_action( 'admin_post_lai_mig_20261124', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
	check_admin_referer( 'lai_mig_20261124' );
	update_option( 'lai_mig_20261124', array( 'log' => lai_mig_20261124_run(), 'time' => current_time( 'mysql' ) ), false );
	wp_safe_redirect( admin_url( 'index.php?lai_mig_f=1' ) );
	exit;
} );

function lai_mig_20261124_run(): array {
	// дубль => ожидаемый язык (привязанная версия определяется по группе переводов)
	$dups = array( 4684 => 'zh', 4682 => 'tr', 4667 => 'zh', 4664 => 'tr', 4653 => 'zh', 4652 => 'zh', 4638 => 'zh', 4636 => 'tr' );
	$log  = array();
	$keep = array(); // keeper => [slugs дублей]
	foreach ( $dups as $id => $lang ) {
		$p = get_post( $id );
		if ( ! $p || 'post' !== $p->post_type || 'publish' !== $p->post_status || pll_get_post_language( $id ) !== $lang ) { $log[] = "#$id: skipped (" . ( $p ? $p->post_status : 'not found' ) . ')'; continue; }
		$tr = pll_get_post_translations( $id );
		$k  = (int) ( $tr[ $lang ] ?? 0 );
		if ( ! $k || $k === $id || 'publish' !== get_post_status( $k ) ) { $log[] = "#$id: skipped (is the linked translation or no linked version)"; continue; }
		if ( $p->post_content !== get_post_field( 'post_content', $k ) ) { $log[] = "#$id: skipped (content differs from #$k)"; continue; }
		$keep[ $k ][] = $p->post_name;
		wp_trash_post( $id );
		$log[] = "#$id $lang «" . $p->post_title . "» → trash (linked version #$k)";
	}
	foreach ( $keep as $k => $slugs ) {
		$old   = get_post_field( 'post_name', $k );
		$clean = preg_replace( '/-\d+$/', '', $old );
		if ( $clean !== $old && ! get_page_by_path( $clean, OBJECT, 'post' ) ) {
			wp_update_post( array( 'ID' => $k, 'post_name' => $clean ) ); // старый адрес сохраняется в _wp_old_slug → 301
			$log[] = "#$k: slug $old → " . get_post_field( 'post_name', $k );
		}
		foreach ( $slugs as $s ) {
			$s = preg_replace( '/__trashed$/', '', $s );
			if ( $s !== get_post_field( 'post_name', $k ) && ! in_array( $s, get_post_meta( $k, '_wp_old_slug' ), true ) ) add_post_meta( $k, '_wp_old_slug', $s );
		}
		$log[] = "  #$k URL: " . get_permalink( $k ) . ' (old: ' . implode( ', ', get_post_meta( $k, '_wp_old_slug' ) ) . ')';
	}
	update_option( 'lai_il_salt', (string) microtime( true ), false );
	do_action( 'litespeed_purge_all' );
	return $log;
}
