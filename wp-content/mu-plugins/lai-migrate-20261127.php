<?php
/**
 * Plugin Name: LAI — Migration 2026-11-27 (3 новые услуги на 8 языков + меню)
 * Description: Одноразово по кнопке: переводы на en, uk, de, es, fr, pt, tr, zh страниц «Ликвидация компании в Эстонии», «Регистрация товарного знака и патента в Эстонии и ЕС» и «Эстонская компания (OÜ) для украинцев»; пункты в меню всех языков на тех же местах, что в русском меню; две мелкие правки русской страницы для украинцев (лишнее «(70 символов)» в SEO-заголовке и пропущенный пункт списка в пакете «Команда в Украине»). Повторный запуск безопасен. После применения файл и lai-data/migrate-20261127.json можно удалить.
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'pll_get_post' ) ) return;
	$st = get_option( 'lai_mig_20261127' );
	if ( is_array( $st ) ) {
		if ( isset( $_GET['lai_mig_i'] ) ) echo '<div class="notice notice-success"><p><b>LAI migration 2026-11-27 — выполнено.</b></p><pre style="white-space:pre-wrap;font-size:12px;max-height:420px;overflow:auto">' . esc_html( implode( "\n", $st['log'] ) ) . '</pre><p>Файлы <code>mu-plugins/lai-migrate-20261127.php</code> и <code>mu-plugins/lai-data/migrate-20261127.json</code> можно удалить.</p></div>';
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=lai_mig_20261127' ), 'lai_mig_20261127' );
	echo '<div class="notice notice-warning"><p><b>LAI: готова миграция 2026-11-27.</b> Переводы на 8 языков: «Ликвидация компании», «Регистрация товарного знака», «Эстонская компания для украинцев» и пункты меню на всех языках. Перед запуском сделайте бэкап базы.</p><p><a class="button button-primary" href="' . esc_url( $url ) . '">Применить миграцию 2026-11-27</a></p></div>';
} );

add_action( 'admin_post_lai_mig_20261127', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
	check_admin_referer( 'lai_mig_20261127' );
	if ( function_exists( 'set_time_limit' ) ) @set_time_limit( 900 );
	update_option( 'lai_mig_20261127', array( 'log' => lai_mig_20261127_run(), 'time' => current_time( 'mysql' ) ), false );
	wp_safe_redirect( admin_url( 'index.php?lai_mig_i=1' ) );
	exit;
} );

/**
 * Ссылки в переводах: {L:ID} — страница ID (любого языка) на нужном языке;
 * относительные /path/ — та же страница на нужном языке; если перевода нет — запасная страница.
 */
function lai_mig_20261127_links( string $html, string $lang, array $fallback, int &$miss ): string {
	$html = preg_replace_callback( '#\{L:(\d+)\}#', function ( $m ) use ( $lang, &$miss ) {
		$t = (int) pll_get_post( (int) $m[1], $lang );
		if ( $t && 'publish' === get_post_status( $t ) ) return esc_url( get_permalink( $t ) );
		$t = (int) pll_get_post( (int) $m[1], 'en' );
		$miss++;
		return esc_url( get_permalink( $t ?: (int) $m[1] ) );
	}, $html );
	return preg_replace_callback( '#href="(/[^"/][^"]*)"#', function ( $m ) use ( $lang, $fallback, &$miss ) {
		$pid = url_to_postid( home_url( $m[1] ) );
		if ( ! $pid ) {
			$p   = get_page_by_path( basename( untrailingslashit( $m[1] ) ), OBJECT, array( 'post', 'target', 'service', 'licensing', 'industry', 'page' ) );
			$pid = $p ? $p->ID : 0;
		}
		$t = $pid ? (int) pll_get_post( $pid, $lang ) : 0;
		if ( $t && 'publish' === get_post_status( $t ) ) return 'href="' . esc_url( get_permalink( $t ) ) . '"';
		foreach ( $fallback as $needle => $fid ) {
			if ( false !== strpos( $m[1], $needle ) && ( $f = (int) pll_get_post( (int) $fid, $lang ) ) && 'publish' === get_post_status( $f ) ) return 'href="' . esc_url( get_permalink( $f ) ) . '"';
		}
		$miss++;
		return 'href="' . esc_url( home_url( $m[1] ) ) . '"';
	}, $html );
}

function lai_mig_20261127_run(): array {
	$d = json_decode( (string) file_get_contents( __DIR__ . '/lai-data/migrate-20261127.json' ), true );
	if ( ! $d ) return array( 'data file missing' );
	$log   = array();
	$langs = pll_languages_list( array( 'fields' => 'slug' ) );
	$skip  = array( '_edit_lock', '_edit_last', '_wp_old_slug', '_lai_tr_source', '_wp_trash_meta_status', '_wp_trash_meta_time', '_wp_desired_post_slug' );
	$srcs  = array();

	foreach ( $d['pages'] as $key => $pg ) {
		$src = get_post( (int) $pg['source_id'] );
		if ( ! $src || $src->post_name !== $pg['source_slug'] ) $src = get_page_by_path( $pg['source_slug'], OBJECT, $pg['type'] );
		if ( ! $src || 'publish' !== $src->post_status || 'ru' !== pll_get_post_language( $src->ID ) ) { $log[] = "SKIP $key: RU page {$pg['source_slug']} not found or not published"; continue; }
		$srcs[ $key ] = $src->ID;

		// правки RU
		foreach ( (array) ( $pg['ru_fix'] ?? array() ) as $k => $pair ) {
			$cur = (string) get_post_meta( $src->ID, $k, true );
			if ( '' !== $pair[0] && false !== strpos( $cur, $pair[0] ) && ( '' === $pair[1] || false === strpos( $cur, $pair[1] ) ) ) {
				update_post_meta( $src->ID, $k, wp_slash( str_replace( $pair[0], $pair[1], $cur ) ) );
				$log[] = "RU #{$src->ID} $k: fixed";
			}
		}

		$group = pll_get_post_translations( $src->ID );
		$log[] = "== {$key}: RU #{$src->ID} " . get_permalink( $src->ID );
		foreach ( $pg['langs'] as $lang => $tr ) {
			if ( ! in_array( $lang, $langs, true ) ) continue;
			$existing = (int) ( $group[ $lang ] ?? 0 );
			if ( $existing ) {
				$own   = (int) get_post_meta( $existing, '_lai_tr_source', true ) === $src->ID;
				$draft = in_array( get_post_status( $existing ), array( 'draft', 'auto-draft', 'pending' ), true );
				if ( ! $own && ! $draft ) { $log[] = "  $lang: skipped, published translation #$existing already exists"; continue; }
			}
			$id = wp_insert_post( wp_slash( array(
				'ID' => $existing, 'post_type' => $src->post_type, 'post_status' => 'publish', 'post_title' => $tr['title'],
				'post_name' => sanitize_title( $tr['slug'] ), 'post_content' => $src->post_content, 'post_author' => $src->post_author,
				'menu_order' => $src->menu_order, 'post_parent' => $src->post_parent ? (int) pll_get_post( $src->post_parent, $lang ) : 0,
			) ), true );
			if ( is_wp_error( $id ) ) { $log[] = "  $lang: ERROR " . $id->get_error_message(); continue; }
			foreach ( get_post_meta( $src->ID ) as $k => $vals ) {
				if ( in_array( $k, $skip, true ) || 0 === strpos( $k, 'rank_math_internal' ) ) continue;
				delete_post_meta( $id, $k );
				foreach ( $vals as $v ) add_post_meta( $id, $k, wp_slash( maybe_unserialize( $v ) ) );
			}
			$miss = 0;
			foreach ( $tr['fields'] as $k => $v ) update_post_meta( $id, $k, wp_slash( lai_mig_20261127_links( $v, $lang, (array) $d['fallback'], $miss ) ) );
			update_post_meta( $id, '_lai_tr_source', $src->ID );
			$tpl = get_page_template_slug( $src->ID );
			if ( $tpl ) update_post_meta( $id, '_wp_page_template', $tpl );
			pll_set_post_language( $id, $lang );
			foreach ( get_object_taxonomies( $src->post_type ) as $tax ) {
				if ( in_array( $tax, array( 'language', 'post_translations' ), true ) ) continue;
				$terms = array();
				foreach ( wp_get_object_terms( $src->ID, $tax, array( 'fields' => 'ids' ) ) as $t ) $terms[] = pll_is_translated_taxonomy( $tax ) ? (int) pll_get_term( $t, $lang ) : (int) $t;
				wp_set_object_terms( $id, array_filter( $terms ), $tax );
			}
			$group[ $lang ] = $id;
			pll_save_post_translations( $group );
			clean_post_cache( $id );
			$log[] = '  ' . $lang . ': ' . ( $existing ? 'updated' : 'created' ) . " #$id " . get_permalink( $id ) . ( $miss ? " ($miss links → fallback)" : '' );
		}
	}

	$log = array_merge( $log, lai_mig_20261127_menu( $d['menu'], $srcs ) );

	delete_transient( 'lai_service_paths_all' );
	delete_transient( 'i10v_service_paths' );
	set_transient( 'i10v_service_flush_needed', 1, HOUR_IN_SECONDS );
	update_option( 'lai_il_salt', (string) microtime( true ), false );
	wp_cache_flush();
	do_action( 'litespeed_purge_all' );
	return $log;
}

/** EN-мастер-меню: пункты на тех же местах, что в RU; подписи для всех языков; синхронизация меню. */
function lai_mig_20261127_menu( array $m, array $srcs ): array {
	$log = array();
	if ( ! function_exists( 'lai_menu_map' ) ) return array( 'menu: lai_menu_map missing' );
	$master = (int) ( lai_menu_map()['primary-menu'] ?? 0 );
	$get    = function () use ( $master ) { $i = (array) wp_get_nav_menu_items( $master, array( 'post_status' => 'any' ) ); usort( $i, function ( $a, $b ) { return $a->menu_order <=> $b->menu_order; } ); return $i; };
	$items  = $get();
	if ( ! $items ) return array( 'menu: master menu not found' );
	$find = function ( $spec ) use ( &$items ) {
		foreach ( $items as $i ) {
			if ( isset( $spec['obj'] ) && 'post_type' === $i->type && (int) $i->object_id === (int) $spec['obj'] ) return $i;
			if ( isset( $spec['title'] ) && 'custom' === $i->type && (string) $i->post_title === $spec['title'] && ( ! isset( $spec['top'] ) || 0 === (int) $i->menu_item_parent ) ) return $i;
		}
		return null;
	};
	$after_ids = array(); // новый пункт → ID пункта, после ветки которого он стоит
	foreach ( $m['items'] as $it ) {
		$src_id = (int) ( $srcs[ $it['page'] ] ?? 0 );
		$en     = $src_id ? (int) pll_get_post( $src_id, 'en' ) : 0;
		if ( ! $en || 'publish' !== get_post_status( $en ) ) { $log[] = "menu: {$it['label']} — EN page missing, skipped"; continue; }
		$parent = $find( $it['parent'] );
		$after  = $find( $it['after'] );
		if ( ! $parent || ! $after ) { $log[] = "menu: {$it['label']} — parent/anchor item not found, skipped"; continue; }
		$have = $find( array( 'obj' => $en ) );
		if ( $have ) {
			$nid = (int) $have->ID;
			$log[] = "menu: «{$it['label']}» already in EN menu (#$nid)";
		} else {
			$nid = wp_update_nav_menu_item( $master, 0, array(
				'menu-item-object-id' => $en, 'menu-item-object' => get_post_type( $en ), 'menu-item-type' => 'post_type',
				'menu-item-title' => $it['label'], 'menu-item-parent-id' => (int) $parent->ID, 'menu-item-status' => 'publish',
			) );
			if ( is_wp_error( $nid ) ) { $log[] = 'menu: add ERROR ' . $nid->get_error_message(); continue; }
			$log[] = "menu: added «{$it['label']}» #$nid";
			$items = $get();
		}
		$after_ids[ (int) $nid ] = (int) $after->ID;
	}
	// порядок: каждый новый пункт сразу после ветки своего «соседа»
	$items = $get();
	$kids  = array();
	foreach ( $items as $i ) $kids[ (int) $i->menu_item_parent ][] = $i;
	foreach ( $kids as $p => $list ) {
		$ids = array_map( function ( $i ) { return (int) $i->ID; }, $list );
		foreach ( $after_ids as $nid => $aid ) {
			if ( ! in_array( $nid, $ids, true ) || ! in_array( $aid, $ids, true ) ) continue;
			$ids = array_values( array_diff( $ids, array( $nid ) ) );
			array_splice( $ids, array_search( $aid, $ids, true ) + 1, 0, array( $nid ) );
		}
		$by = array(); foreach ( $list as $i ) $by[ (int) $i->ID ] = $i;
		$kids[ $p ] = array_map( function ( $id ) use ( $by ) { return $by[ $id ]; }, $ids );
	}
	$n    = 0;
	$walk = function ( $parent ) use ( &$walk, &$n, $kids ) {
		foreach ( $kids[ $parent ] ?? array() as $i ) {
			$n++;
			if ( (int) $i->menu_order !== $n ) wp_update_post( array( 'ID' => $i->ID, 'menu_order' => $n ) );
			$walk( (int) $i->ID );
		}
	};
	$walk( 0 );
	$log[] = "menu: reordered ($n items)";
	if ( function_exists( 'lai_save_string_translations' ) ) {
		foreach ( $m['labels'] as $lang => $map ) lai_save_string_translations( $lang, $map, false );
	}
	if ( function_exists( 'lai_sync_all_menus' ) ) {
		foreach ( lai_sync_all_menus() as $r ) $log[] = "menu sync {$r['lang']}/{$r['location']}: +{$r['created']} ~{$r['updated']} -{$r['removed']}";
	}
	return $log;
}
