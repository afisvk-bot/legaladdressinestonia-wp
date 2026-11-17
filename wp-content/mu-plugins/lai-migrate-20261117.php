<?php
/**
 * Plugin Name: LAI — Migration 2026-11-17 (бухгалтерия: смена бухгалтера, меню, исправление EN «Восстановление учёта»)
 * Description: Одноразово по кнопке: 1) снимает синхронизацию Polylang у пар EN↔ES «Восстановление учёта» и EN↔ZH «MTÜ»; 2) возвращает английский текст EN-странице «Восстановление учёта»; 3) удаляет в корзину черновики-дубли «Годовой отчёт» (RU/UK); 4) переводы услуги «Смена бухгалтера» на 8 языков; 5) меню Accounting & Tax: «Годовой отчёт» и «Смена бухгалтера». После применения файл и lai-data/migrate-20261117.json можно удалить.
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'pll_get_post' ) ) return;
	$st = get_option( 'lai_mig_20261117' );
	if ( is_array( $st ) ) {
		if ( isset( $_GET['lai_mig_e'] ) ) echo '<div class="notice notice-success"><p><b>LAI migration 2026-11-17 — выполнено.</b></p><pre style="white-space:pre-wrap;font-size:12px;max-height:420px;overflow:auto">' . esc_html( implode( "\n", $st['log'] ) ) . '</pre><p>Файлы <code>mu-plugins/lai-migrate-20261117.php</code> и <code>mu-plugins/lai-data/migrate-20261117.json</code> можно удалить.</p></div>';
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=lai_mig_20261117' ), 'lai_mig_20261117' );
	echo '<div class="notice notice-warning"><p><b>LAI: готова миграция 2026-11-17.</b> Услуга «Смена бухгалтера» на 8 языках, пункты меню в Accounting &amp; Tax, удаление черновиков-дублей «Годовой отчёт», исправление английской страницы «Восстановление учёта» (сейчас там испанский текст). Перед запуском сделайте бэкап базы.</p><p><a class="button button-primary" href="' . esc_url( $url ) . '">Применить миграцию 2026-11-17</a></p></div>';
} );

add_action( 'admin_post_lai_mig_20261117', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
	check_admin_referer( 'lai_mig_20261117' );
	if ( function_exists( 'set_time_limit' ) ) @set_time_limit( 600 );
	update_option( 'lai_mig_20261117', array( 'log' => lai_mig_20261117_run(), 'time' => current_time( 'mysql' ) ), false );
	wp_safe_redirect( admin_url( 'index.php?lai_mig_e=1' ) );
	exit;
} );

/** Внутренние ссылки (относительные или на любой хост сайта) → страница нужного языка. */
function lai_mig_20261117_links( string $html, string $lang, int &$miss ): string {
	$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	return preg_replace_callback( '#href="([^"]+)"#', function ( $m ) use ( $lang, $host, &$miss ) {
		$url = html_entity_decode( $m[1] );
		if ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) $url = home_url( $url );
		if ( wp_parse_url( $url, PHP_URL_HOST ) !== $host ) return $m[0];
		$pid = url_to_postid( $url );
		if ( ! $pid ) {
			$path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
			$p    = get_page_by_path( basename( $path ), OBJECT, array( 'service', 'licensing', 'industry', 'target', 'page', 'post' ) );
			$pid  = $p ? $p->ID : 0;
		}
		$tid = $pid ? (int) pll_get_post( $pid, $lang ) : 0;
		if ( $tid && 'publish' === get_post_status( $tid ) ) return 'href="' . esc_url( get_permalink( $tid ) ) . '"';
		$miss++;
		return 'href="' . esc_url( $url ) . '"';
	}, $html );
}

function lai_mig_20261117_run(): array {
	$d = json_decode( (string) file_get_contents( __DIR__ . '/lai-data/migrate-20261117.json' ), true );
	if ( ! $d ) return array( 'data file missing' );
	$log = array();

	// 1. Снять синхронизацию Polylang Pro (обновление одной версии перезаписывало другую).
	foreach ( $d['unsync_posts'] as $pid ) {
		$terms = wp_get_object_terms( (int) $pid, 'post_translations' );
		if ( ! $terms || is_wp_error( $terms ) ) { $log[] = "unsync #$pid: no translation group"; continue; }
		$t    = $terms[0];
		$desc = maybe_unserialize( $t->description );
		if ( is_array( $desc ) && isset( $desc['sync'] ) ) {
			$was = wp_json_encode( $desc['sync'] );
			unset( $desc['sync'] );
			wp_update_term( $t->term_id, 'post_translations', array( 'description' => maybe_serialize( $desc ) ) );
			clean_term_cache( $t->term_id, 'post_translations' );
			$log[] = "unsync group of #$pid (term {$t->term_id}): removed sync $was";
		} else {
			$log[] = "unsync #$pid: not synchronized";
		}
	}
	wp_cache_flush();

	// 2. EN «Восстановление учёта»: вернуть английский текст.
	$r  = $d['restore_en'];
	$id = (int) $r['id'];
	if ( 'en' === pll_get_post_language( $id ) && get_the_title( $id ) === $r['expect_title'] ) {
		$miss = 0;
		foreach ( $r['fields'] as $k => $v ) update_post_meta( $id, $k, wp_slash( lai_mig_20261117_links( $v, 'en', $miss ) ) );
		$n = 0;
		foreach ( get_post_meta( $id ) as $k => $vals ) {
			if ( 0 === strpos( $k, 'content_blocks_' ) && isset( $r['short_inv'][ $vals[0] ] ) ) { update_post_meta( $id, $k, $r['short_inv'][ $vals[0] ] ); $n++; }
		}
		wp_update_post( array( 'ID' => $id, 'post_title' => $r['title'], 'post_name' => $r['slug'] ) );
		$es = (int) pll_get_post( $id, 'es' );
		$log[] = "EN #$id restored (" . count( $r['fields'] ) . " fields + $n short labels), URL " . get_permalink( $id ) . ( $miss ? " ($miss links kept)" : '' );
		$log[] = "  ES #$es still: «" . get_the_title( $es ) . '» ' . get_permalink( $es );
	} else {
		$log[] = "EN #$id skipped: title is «" . get_the_title( $id ) . '»';
	}

	// 3. Черновики-дубли «Годовой отчёт».
	foreach ( $d['trash'] as $t ) {
		$p = get_post( (int) $t['id'] );
		if ( $p && 'draft' === $p->post_status && $p->post_title === $t['title'] && pll_get_post_language( $p->ID ) === $t['lang'] ) {
			wp_trash_post( $p->ID );
			$log[] = "trash #{$p->ID} {$t['lang']} draft «{$t['title']}»";
		} else {
			$log[] = "trash #{$t['id']}: skipped (" . ( $p ? $p->post_status . ' «' . $p->post_title . '»' : 'not found' ) . ')';
		}
	}

	// 4. «Смена бухгалтера»: правки RU и переводы.
	$c   = $d['change'];
	$src = get_post( (int) $c['source_id'] );
	if ( ! $src || 'service' !== $src->post_type || 'ru' !== pll_get_post_language( $src->ID ) ) {
		$log[] = 'SKIP change-of-accountant: RU source not found';
	} else {
		foreach ( $c['ru_set_if_empty'] as $k => $v ) {
			if ( '' === (string) get_post_meta( $src->ID, $k, true ) ) { update_post_meta( $src->ID, $k, wp_slash( $v ) ); $log[] = "RU #{$src->ID} $k set"; }
		}
		foreach ( $c['ru_replace'] as $k => $pair ) {
			$cur = (string) get_post_meta( $src->ID, $k, true );
			if ( md5( str_replace( "\r", '', $cur ) ) === $pair['old_md5'] ) {
				$miss = 0;
				update_post_meta( $src->ID, $k, wp_slash( lai_mig_20261117_links( $pair['new'], 'ru', $miss ) ) );
				$log[] = "RU #{$src->ID} $k updated (links)";
			} else {
				$log[] = "RU #{$src->ID} $k: changed manually, kept";
			}
		}
		$skip  = array( '_edit_lock', '_edit_last', '_wp_old_slug', '_lai_tr_source', '_wp_trash_meta_status', '_wp_trash_meta_time', '_wp_desired_post_slug' );
		$group = pll_get_post_translations( $src->ID );
		foreach ( $c['langs'] as $lang => $tr ) {
			$existing = (int) ( $group[ $lang ] ?? 0 );
			if ( $existing ) {
				$own   = (int) get_post_meta( $existing, '_lai_tr_source', true ) === $src->ID;
				$draft = 'draft' === get_post_status( $existing );
				if ( ! $own && ! $draft ) { $log[] = "  $lang: skipped, published translation #$existing exists"; continue; }
			}
			$id = wp_insert_post( wp_slash( array(
				'ID' => $existing, 'post_type' => $src->post_type, 'post_status' => $src->post_status, 'post_title' => $tr['title'],
				'post_name' => sanitize_title( $tr['slug'] ), 'post_content' => $src->post_content, 'post_author' => $src->post_author,
				'menu_order' => $src->menu_order,
			) ), true );
			if ( is_wp_error( $id ) ) { $log[] = "  $lang: ERROR " . $id->get_error_message(); continue; }
			foreach ( get_post_meta( $src->ID ) as $k => $vals ) {
				if ( in_array( $k, $skip, true ) || 0 === strpos( $k, 'rank_math_internal' ) ) continue;
				delete_post_meta( $id, $k );
				foreach ( $vals as $v ) add_post_meta( $id, $k, wp_slash( maybe_unserialize( $v ) ) );
			}
			$miss = 0;
			foreach ( $tr['fields'] as $k => $v ) {
				// Абзац со ссылкой на статью блога: если статьи на этом языке нет — абзац убираем.
				$v = preg_replace_callback( '#<p class="lai-art">.*?</p>#s', function ( $m ) use ( $lang ) {
					$x = 0; $p = lai_mig_20261117_links( $m[0], $lang, $x );
					return $x ? '' : $p;
				}, $v );
				update_post_meta( $id, $k, wp_slash( lai_mig_20261117_links( $v, $lang, $miss ) ) );
			}
			update_post_meta( $id, '_lai_tr_source', $src->ID );
			$tpl = get_page_template_slug( $src->ID );
			if ( $tpl ) update_post_meta( $id, '_wp_page_template', $tpl );
			pll_set_post_language( $id, $lang );
			$group[ $lang ] = $id;
			pll_save_post_translations( $group );
			clean_post_cache( $id );
			$log[] = '  ' . $lang . ': ' . ( $existing ? 'updated' : 'created' ) . " #$id " . get_permalink( $id ) . ( $miss ? " ($miss links without translation kept)" : '' );
		}
	}

	// 5. Меню: Accounting & Tax ← «Смена бухгалтера» + «Годовой отчёт».
	$log = array_merge( $log, lai_mig_20261117_menu( $d['menu'], (int) pll_get_post( (int) $c['source_id'], 'en' ) ) );

	update_option( 'lai_il_salt', (string) microtime( true ), false );
	wp_cache_flush();
	do_action( 'litespeed_purge_all' );
	return $log;
}

function lai_mig_20261117_menu( array $m, int $new_en ): array {
	$log = array();
	if ( ! function_exists( 'lai_menu_map' ) ) return array( 'menu: lai_menu_map missing' );
	$master = (int) ( lai_menu_map()['primary-menu'] ?? 0 );
	$items  = $master ? (array) wp_get_nav_menu_items( $master, array( 'post_status' => 'any' ) ) : array();
	if ( ! $items ) return array( 'menu: master menu not found' );
	$by_obj = array();
	foreach ( $items as $i ) if ( 'post_type' === $i->type ) $by_obj[ (int) $i->object_id ] = $i;
	$acc = $by_obj[ (int) $m['acc_obj'] ] ?? null;
	if ( ! $acc ) return array( 'menu: Accounting & Tax item not found' );

	if ( $new_en && empty( $by_obj[ $new_en ] ) ) {
		$nid = wp_update_nav_menu_item( $master, 0, array(
			'menu-item-object-id' => $new_en, 'menu-item-object' => 'service', 'menu-item-type' => 'post_type',
			'menu-item-title' => $m['label'], 'menu-item-parent-id' => (int) $acc->ID, 'menu-item-status' => 'publish',
		) );
		$log[] = is_wp_error( $nid ) ? 'menu: add ERROR ' . $nid->get_error_message() : "menu: added «{$m['label']}» #$nid";
	} elseif ( ! $new_en ) {
		$log[] = 'menu: EN page not found, item not added';
	}
	if ( isset( $by_obj[ (int) $m['annual_obj'] ] ) ) {
		update_post_meta( $by_obj[ (int) $m['annual_obj'] ]->ID, '_menu_item_menu_item_parent', (string) $acc->ID );
		$log[] = 'menu: «Annual Report» moved under Accounting & Tax';
	}

	// Порядок: дети Accounting & Tax по списку, остальное — как было.
	$items = (array) wp_get_nav_menu_items( $master, array( 'post_status' => 'any' ) );
	usort( $items, function ( $a, $b ) { return $a->menu_order <=> $b->menu_order; } );
	$kids = array();
	foreach ( $items as $i ) $kids[ (int) $i->menu_item_parent ][] = $i;
	$want = array_map( function ( $o ) use ( $new_en ) { return 'NEW' === $o ? $new_en : (int) $o; }, $m['order'] );
	if ( isset( $kids[ (int) $acc->ID ] ) ) {
		usort( $kids[ (int) $acc->ID ], function ( $a, $b ) use ( $want ) {
			$pa = array_search( (int) $a->object_id, $want, true ); $pb = array_search( (int) $b->object_id, $want, true );
			return ( false === $pa ? 99 : $pa ) <=> ( false === $pb ? 99 : $pb );
		} );
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
		foreach ( $m['labels'] as $lang => $label ) lai_save_string_translations( $lang, array( $m['label'] => $label ), false );
	}
	if ( function_exists( 'lai_sync_all_menus' ) ) {
		foreach ( lai_sync_all_menus() as $r ) $log[] = "menu sync {$r['lang']}/{$r['location']}: +{$r['created']} ~{$r['updated']} -{$r['removed']}";
	}
	return $log;
}
