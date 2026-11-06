<?php
/**
 * Plugin Name: LAI — Migration 2026-11-06c (внутренние ссылки)
 * Description: Одноразово по кнопке: ссылки в тексте на страницы другого языка → перевод на языке страницы; битые адреса юридических и старых страниц → правильные; ссылки в формах Contact Form 7. После применения файл можно удалить.
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'pll_get_post' ) ) return;
	$st = get_option( 'lai_mig_20261106c' );
	if ( is_array( $st ) ) {
		if ( isset( $_GET['lai_mig_c'] ) ) echo '<div class="notice notice-success"><p><b>LAI migration 2026-11-06c (ссылки) — выполнено.</b> ' . esc_html( $st['summary'] ) . '</p><details><summary>Подробности</summary><pre style="white-space:pre-wrap;font-size:12px;max-height:400px;overflow:auto">' . esc_html( $st['log'] ) . '</pre></details><p>Файл <code>mu-plugins/lai-migrate-20261106c.php</code> можно удалить.</p></div>';
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=lai_mig_20261106c' ), 'lai_mig_20261106c' );
	echo '<div class="notice notice-warning"><p><b>LAI: готова миграция «внутренние ссылки» (2026-11-06c).</b> Исправит ~310 ссылок в текстах: ссылки на страницы другого языка и битые адреса. Перед запуском сделайте бэкап базы.</p><p><a class="button button-primary" href="' . esc_url( $url ) . '">Применить миграцию ссылок</a></p></div>';
} );

add_action( 'admin_post_lai_mig_20261106c', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
	check_admin_referer( 'lai_mig_20261106c' );
	@set_time_limit( 600 );
	update_option( 'lai_mig_20261106c', lai_mig_20261106c_run(), false );
	wp_safe_redirect( admin_url( 'index.php?lai_mig_c=1' ) );
	exit;
} );

function lai_mig_20261106c_run(): array {
	ini_set( 'pcre.backtrack_limit', '50000000' );
	$host  = wp_parse_url( home_url(), PHP_URL_HOST );
	$types = array( 'page', 'post', 'service', 'licensing', 'industry', 'target' );
	
	// битые пути → EN id группы
	$fixed = array(
		'/yuridicheskij-adres' => 1269, '/kontaktne-litso-v-estonii' => 2254, '/zmina-yurydychnogo-adresu-v-estonii' => 1482,
		'/yurydychnyi-adres-v-estonii' => 1269, '/virtualnyi-ofis-v-estonii' => 1410, '/robocha-viza-ta-dozvil-na-prozhyvannia-v-estonii' => 2776,
		'/reiestratsiia-pdv-v-estonii' => 2261, '/en' => 2, '/ua' => 2, '/ru' => 2,
	);
	// юридические страницы по признаку в пути
	$legal = array( '/(privacy|konfidentsialnost|privacidad|privacidade|gizlilik|datenschutz)/i' => 3, '/aml/i' => 1168, '/(cookie|cerez)/i' => 113, '/(gdpr|dsgvo)/i' => 1164, '/(terms|usloviya|agb)/i' => 118 );
	
	$path2id = array();
	foreach ( $types as $t ) foreach ( get_posts( array( 'post_type' => $t, 'post_status' => 'publish', 'numberposts' => -1, 'lang' => '', 'fields' => 'ids' ) ) as $id ) {
		$path2id[ untrailingslashit( wp_parse_url( get_permalink( $id ), PHP_URL_PATH ) ) ?: '/' ] = $id;
	}
	$stats = array(); $log = array();
	$fix = function ( string $html, string $lang, string $where ) use ( $host, $path2id, $fixed, $legal, &$stats, &$log ) {
		return preg_replace_callback( '~href=(["\'])([^"\'#]+)(#[^"\']*)?\1~i', function ( $m ) use ( $host, $path2id, $fixed, $legal, $lang, $where, &$stats, &$log ) {
			$u = html_entity_decode( $m[2] );
			$h = wp_parse_url( $u, PHP_URL_HOST );
			if ( $h && ! in_array( $h, array( $host, 'legaladdressinestonia.com', 'www.legaladdressinestonia.com' ), true ) ) return $m[0];
			if ( preg_match( '~^(mailto|tel|javascript):~i', $u ) ) return $m[0];
			$p = untrailingslashit( (string) wp_parse_url( $u, PHP_URL_PATH ) ) ?: '/';
			if ( preg_match( '~^/(wp-|blog|blogue)~', $p ) && ! preg_match( '~^/(blog|blogue)[^/]*/.+~', $p ) ) return $m[0];
			$target = 0; $why = '';
			if ( isset( $path2id[ $p ] ) ) {
				$id = $path2id[ $p ];
				if ( pll_get_post_language( $id ) === $lang ) return $m[0];
				$target = $id; $why = 'xlang';
			} elseif ( isset( $fixed[ $p ] ) ) { $target = $fixed[ $p ]; $why = 'broken';
			} else {
				foreach ( $legal as $re => $gid ) if ( preg_match( $re, $p ) ) { $target = $gid; $why = 'broken-legal'; break; }
			}
			if ( ! $target ) return $m[0];
			$tr = pll_get_post( $target, $lang );
			if ( ! $tr || 'publish' !== get_post_status( $tr ) ) { $stats["$why-no-translation"] = ( $stats["$why-no-translation"] ?? 0 ) + 1; $log[] = "NO $lang translation for $p ($where)"; return $m[0]; }
			$new = get_permalink( $tr );
			if ( untrailingslashit( wp_parse_url( $new, PHP_URL_PATH ) ) === $p ) return $m[0];
			$stats[ $why ] = ( $stats[ $why ] ?? 0 ) + 1;
			if ( count( $log ) < 400 ) $log[] = "$why $lang $where: $p -> " . wp_parse_url( $new, PHP_URL_PATH );
			return 'href=' . $m[1] . esc_url( $new ) . ( $m[3] ?? '' ) . $m[1];
		}, $html );
	};
	
	$changed_posts = 0; $changed_fields = 0;
	foreach ( $path2id as $id ) {
		$lang = pll_get_post_language( $id ); $touched = false;
		$c = get_post_field( 'post_content', $id );
		$n = $fix( $c, $lang, "#$id content" );
		if ( $n !== $c ) { $touched = true; $changed_fields++; if ( true ) { global $wpdb; $wpdb->update( $wpdb->posts, array( 'post_content' => $n ), array( 'ID' => $id ) ); clean_post_cache( $id ); } }
		foreach ( get_post_meta( $id ) as $k => $v ) {
			if ( 0 !== strpos( $k, 'content_blocks_' ) || ! is_string( $v[0] ) || false === stripos( $v[0], 'href' ) ) continue;
			$n = $fix( $v[0], $lang, "#$id $k" );
			if ( $n !== $v[0] ) { $touched = true; $changed_fields++; if ( true ) update_post_meta( $id, $k, wp_slash( $n ) ); }
		}
		if ( $touched ) $changed_posts++;
	}
	// CF7
	$cf7lang = array( 'form_EN' => 'en', 'form_RU' => 'ru', 'form_UK' => 'uk', 'form_DE' => 'de', 'form_FR' => 'fr', 'form_PT' => 'pt', 'form_TR' => 'tr', 'form_ES' => 'es', 'form_CN' => 'zh' );
	foreach ( $cf7lang as $k => $v ) $cf7lang[ 'cont_' . $k ] = $v;
	foreach ( get_posts( array( 'post_type' => 'wpcf7_contact_form', 'numberposts' => -1, 'post_status' => 'any', 'lang' => '' ) ) as $f ) {
		$lang = $cf7lang[ $f->post_title ] ?? '';
		if ( ! $lang ) { $log[] = "cf7 skip '{$f->post_title}'"; continue; }
		foreach ( array( '_form', '_messages' ) as $k ) {
			$v = get_post_meta( $f->ID, $k, true );
			if ( ! is_string( $v ) ) continue;
			$n = $fix( $v, $lang, "cf7 {$f->post_title} $k" );
			if ( $n !== $v ) { $changed_fields++; if ( true ) update_post_meta( $f->ID, $k, wp_slash( $n ) ); }
		}
		$n = $fix( $f->post_content, $lang, "cf7 {$f->post_title} content" );
		if ( $n !== $f->post_content && true ) wp_update_post( array( 'ID' => $f->ID, 'post_content' => $n ) );
	}
	
	update_option( 'lai_il_salt', (string) microtime( true ), false );
	do_action( 'litespeed_purge_all' );
	return array( 'summary' => "pages $changed_posts, fields $changed_fields, " . wp_json_encode( $stats ), 'log' => implode( "\n", $log ), 'time' => current_time( 'mysql' ) );
}
