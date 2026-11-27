<?php
/**
 * Plugin Name: LAI — Migration 2026-11-28 (6 новых услуг: правки RU, WebP-обложки, 8 языков, меню)
 * Description: Одноразово по кнопке: правки русских страниц «VAT-декларации», «Расчёт зарплаты», «Смена члена правления», «Продажа и передача долей», «Увеличение капитала», «Уменьшение капитала» (битые ссылки, лишние span/style, SEO-заголовки и описания, мелкие опечатки), WebP-обложки с русским alt, переводы на en, uk, de, es, fr, pt, tr, zh и пункты меню на всех языках (как в русском меню). Повторный запуск безопасен. После применения файл и lai-data/migrate-20261128.json можно удалить.
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'pll_get_post' ) ) return;
	$st = get_option( 'lai_mig_20261128' );
	if ( is_array( $st ) ) {
		if ( isset( $_GET['lai_mig_j'] ) ) echo '<div class="notice notice-success"><p><b>LAI migration 2026-11-28 — выполнено.</b></p><pre style="white-space:pre-wrap;font-size:12px;max-height:420px;overflow:auto">' . esc_html( implode( "\n", $st['log'] ) ) . '</pre><p>Файлы <code>mu-plugins/lai-migrate-20261128.php</code> и <code>mu-plugins/lai-data/migrate-20261128.json</code> можно удалить.</p></div>';
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=lai_mig_20261128' ), 'lai_mig_20261128' );
	echo '<div class="notice notice-warning"><p><b>LAI: готова миграция 2026-11-28.</b> 6 новых услуг: правки русских страниц, WebP-обложки, переводы на 8 языков и пункты меню на всех языках. Перед запуском сделайте бэкап базы.</p><p><a class="button button-primary" href="' . esc_url( $url ) . '">Применить миграцию 2026-11-28</a></p></div>';
} );

add_action( 'admin_post_lai_mig_20261128', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
	check_admin_referer( 'lai_mig_20261128' );
	if ( function_exists( 'set_time_limit' ) ) @set_time_limit( 900 );
	update_option( 'lai_mig_20261128', array( 'log' => lai_mig_20261128_run(), 'time' => current_time( 'mysql' ) ), false );
	wp_safe_redirect( admin_url( 'index.php?lai_mig_j=1' ) );
	exit;
} );

/** Опубликованная страница ID (любого языка) на нужном языке; 0 — нет. */
function lai_mig_20261128_tr( int $id, string $lang ): int {
	$t = (int) pll_get_post( $id, $lang );
	return ( $t && 'publish' === get_post_status( $t ) ) ? $t : 0;
}

/** {L:ID} → постоянная ссылка страницы на нужном языке (если перевода нет — EN). */
function lai_mig_20261128_links( string $html, string $lang, int &$miss, array $alt = array() ): string {
	return preg_replace_callback( '#\{L:(\d+)\}#', function ( $m ) use ( $lang, &$miss, $alt ) {
		$t = lai_mig_20261128_tr( (int) $m[1], $lang );
		if ( ! $t && ! empty( $alt[ $m[1] ] ) ) $t = lai_mig_20261128_tr( (int) $alt[ $m[1] ], $lang ); // близкая статья на том же языке
		if ( $t ) return esc_url( get_permalink( $t ) );
		$miss++;
		$t = lai_mig_20261128_tr( (int) $m[1], 'en' );
		return esc_url( get_permalink( $t ?: (int) $m[1] ) );
	}, $html );
}

/** Правки русской страницы. Каждая правка применяется только если исходный текст на месте. */
function lai_mig_20261128_fix_ru( int $pid, array $fix, array $link_map ): array {
	$log = array();
	$n   = array( 'span' => 0, 'link' => 0, 'unlink' => 0 );
	foreach ( get_post_meta( $pid ) as $k => $vals ) {
		if ( '_' === $k[0] || 1 !== count( $vals ) ) continue;
		$v = (string) $vals[0];
		if ( false === strpos( $v, '<' ) || is_serialized( $v ) ) continue;
		$o = $v;
		// span/style из копирования из Google Docs: только если других span в поле нет
		$open = preg_match_all( '#<span\b#i', $v );
		$fw   = preg_match_all( '#<span style="font-weight:\s*400;?">#i', $v );
		if ( $open && $open === $fw ) {
			$v = preg_replace( '#<span style="font-weight:\s*400;?">#i', '', $v );
			$v = str_ireplace( '</span>', '', $v );
			$n['span'] += $fw;
		}
		$v = preg_replace( '#<li style="font-weight:\s*400;?"(?: aria-level="\d+")?>#i', '<li>', $v );
		// битые ссылки
		foreach ( $link_map as $path => $target ) {
			$re = '#<a href="(?:https?://[^/"]+)?' . preg_quote( $path, '#' ) . '"[^>]*>(.*?)</a>#su';
			$v  = preg_replace_callback( $re, function ( $m ) use ( $target, &$n ) {
				$t = $target ? lai_mig_20261128_tr( (int) $target, 'ru' ) : 0;
				if ( $t ) { $n['link']++; return '<a href="' . esc_url( get_permalink( $t ) ) . '">' . $m[1] . '</a>'; }
				$n['unlink']++;
				return $m[1];
			}, $v );
		}
		if ( $v !== $o ) update_post_meta( $pid, $k, wp_slash( $v ) );
	}
	if ( array_sum( $n ) ) $log[] = "  RU #$pid: span −{$n['span']}, links fixed {$n['link']}, unlinked {$n['unlink']}";

	foreach ( (array) ( $fix['li_links'] ?? array() ) as $r ) {
		list( $k, $text, $target ) = $r;
		$v = (string) get_post_meta( $pid, $k, true );
		$t = lai_mig_20261128_tr( (int) $target, 'ru' );
		if ( $t && false !== strpos( $v, '<li>' . $text ) ) {
			update_post_meta( $pid, $k, wp_slash( str_replace( '<li>' . $text, '<li><a href="' . esc_url( get_permalink( $t ) ) . '">' . $text . '</a>', $v ) ) );
			$log[] = "  RU #$pid: linked «{$text}»";
		}
	}
	foreach ( (array) ( $fix['regex'] ?? array() ) as $r ) {
		list( $k, $re, $rep ) = $r;
		$v = (string) get_post_meta( $pid, $k, true );
		$w = preg_replace( '#' . str_replace( '#', '\#', $re ) . '#u', $rep, $v, 1 );
		if ( null !== $w && $w !== $v ) { update_post_meta( $pid, $k, wp_slash( $w ) ); $log[] = "  RU #$pid: $k fixed"; }
	}
	foreach ( (array) ( $fix['exact'] ?? array() ) as $r ) {
		list( $k, $old, $new ) = $r;
		$v = (string) get_post_meta( $pid, $k, true );
		if ( trim( $v ) === trim( $old ) ) {
			update_post_meta( $pid, $k, wp_slash( $new ) );
			$log[] = "  RU #$pid: $k → «{$new}»";
			// ACF: ссылка на ключ поля для нового значения
			if ( '' === trim( $old ) && ! get_post_meta( $pid, '_' . $k, true ) ) {
				$sib = preg_replace( '#_items_(\d+)_#', '_items_1_', $k, 1 );
				if ( $ref = get_post_meta( $pid, '_' . $sib, true ) ) update_post_meta( $pid, '_' . $k, $ref );
			}
		}
	}
	// SEO-заголовок: двойной пробел перед %currentyear%
	$t = (string) get_post_meta( $pid, 'rank_math_title', true );
	$w = preg_replace( '#[\s\x{00A0}]{2,}%currentyear%#u', ' %currentyear%', $t );
	if ( $w !== $t ) { update_post_meta( $pid, 'rank_math_title', $w ); $log[] = "  RU #$pid: rank_math_title spacing fixed"; }
	return $log;
}

/** WebP-обложка (≈1600 px, q80) с alt = заголовок RU-страницы; переводы берут ту же обложку. */
function lai_mig_20261128_webp( int $pid, string $name ): string {
	$old = (int) get_post_thumbnail_id( $pid );
	if ( ! $old ) return "  RU #$pid: no thumbnail";
	$title = get_the_title( $pid );
	if ( 'image/webp' === get_post_mime_type( $old ) ) {
		if ( ! get_post_meta( $old, '_wp_attachment_image_alt', true ) ) update_post_meta( $old, '_wp_attachment_image_alt', $title );
		return "  RU #$pid: thumbnail already WebP (#$old)";
	}
	if ( ! wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) return "  RU #$pid: server cannot write WebP, thumbnail kept";
	$file = get_attached_file( $old );
	if ( ! $file || ! file_exists( $file ) ) return "  RU #$pid: thumbnail file missing";
	$ed = wp_get_image_editor( $file );
	if ( is_wp_error( $ed ) ) return "  RU #$pid: editor error " . $ed->get_error_message();
	$size = $ed->get_size();
	if ( ( $size['width'] ?? 0 ) > 1600 ) $ed->resize( 1600, null, false );
	$ed->set_quality( 80 );
	$dir  = dirname( $file );
	$dest = $dir . '/' . wp_unique_filename( $dir, sanitize_file_name( $name ) . '.webp' );
	$res  = $ed->save( $dest, 'image/webp' );
	if ( is_wp_error( $res ) ) return "  RU #$pid: save error " . $res->get_error_message();
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$att = wp_insert_attachment( array( 'post_mime_type' => 'image/webp', 'post_title' => $title, 'post_status' => 'inherit', 'post_parent' => $pid ), $res['path'], $pid, true );
	if ( is_wp_error( $att ) ) return "  RU #$pid: attachment error " . $att->get_error_message();
	wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $res['path'] ) );
	update_post_meta( $att, '_wp_attachment_image_alt', $title );
	update_post_meta( $att, '_lai_webp_of', $old );
	if ( function_exists( 'pll_set_post_language' ) && pll_is_translated_post_type( 'attachment' ) ) pll_set_post_language( $att, 'ru' );
	set_post_thumbnail( $pid, $att );
	return sprintf( '  RU #%d: thumbnail → WebP #%d %s (%d KB → %d KB)', $pid, $att, basename( $res['path'] ), filesize( $file ) / 1024, filesize( $res['path'] ) / 1024 );
}

function lai_mig_20261128_run(): array {
	$d = json_decode( (string) file_get_contents( __DIR__ . '/lai-data/migrate-20261128.json' ), true );
	if ( ! $d ) return array( 'data file missing' );
	$log   = array();
	$langs = pll_languages_list( array( 'fields' => 'slug' ) );
	$skip  = array( '_edit_lock', '_edit_last', '_wp_old_slug', '_lai_tr_source', '_wp_trash_meta_status', '_wp_trash_meta_time', '_wp_desired_post_slug' );
	$srcs  = array();
	$todo  = array(); // [id, lang, fields]

	// 1) RU-правки и обложки
	foreach ( $d['pages'] as $key => $pg ) {
		$src = get_post( (int) $pg['source_id'] );
		if ( ! $src || $src->post_name !== $pg['source_slug'] ) $src = get_page_by_path( $pg['source_slug'], OBJECT, $pg['type'] );
		if ( ! $src || 'publish' !== $src->post_status || 'ru' !== pll_get_post_language( $src->ID ) ) { $log[] = "SKIP $key: RU page {$pg['source_slug']} not found or not published"; continue; }
		$srcs[ $key ] = $src->ID;
		$log[] = "== {$key}: RU #{$src->ID} " . get_permalink( $src->ID );
		$log   = array_merge( $log, lai_mig_20261128_fix_ru( $src->ID, (array) $pg['ru'], (array) $d['link_map'] ) );
		$log[] = lai_mig_20261128_webp( $src->ID, (string) $pg['thumb_name'] );
		clean_post_cache( $src->ID );
	}

	// 2) создать/обновить переводы (метаданные копируются из уже исправленной RU-страницы)
	foreach ( $d['pages'] as $key => $pg ) {
		if ( empty( $srcs[ $key ] ) ) continue;
		$src   = get_post( $srcs[ $key ] );
		$group = pll_get_post_translations( $src->ID );
		foreach ( $pg['langs'] as $lang => $tr ) {
			if ( ! in_array( $lang, $langs, true ) ) continue;
			$existing = (int) ( $group[ $lang ] ?? 0 );
			if ( $existing ) {
				$own   = (int) get_post_meta( $existing, '_lai_tr_source', true ) === $src->ID;
				$draft = in_array( get_post_status( $existing ), array( 'draft', 'auto-draft', 'pending' ), true );
				if ( ! $own && ! $draft ) { $log[] = "  $key/$lang: skipped, published translation #$existing already exists"; continue; }
			}
			$id = wp_insert_post( wp_slash( array(
				'ID' => $existing, 'post_type' => $src->post_type, 'post_status' => 'publish', 'post_title' => $tr['title'],
				'post_name' => sanitize_title( $tr['slug'] ), 'post_content' => $src->post_content, 'post_author' => $src->post_author,
				'menu_order' => $src->menu_order, 'post_parent' => $src->post_parent ? (int) pll_get_post( $src->post_parent, $lang ) : 0,
			) ), true );
			if ( is_wp_error( $id ) ) { $log[] = "  $key/$lang: ERROR " . $id->get_error_message(); continue; }
			foreach ( get_post_meta( $src->ID ) as $k => $vals ) {
				if ( in_array( $k, $skip, true ) || 0 === strpos( $k, 'rank_math_internal' ) ) continue;
				delete_post_meta( $id, $k );
				foreach ( $vals as $v ) add_post_meta( $id, $k, wp_slash( maybe_unserialize( $v ) ) );
			}
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
			$todo[] = array( $id, $lang, $tr['fields'], $key, $existing ? 'updated' : 'created' );
		}
	}

	// 3) поля переводов — когда все 6 страниц уже существуют на всех языках (ссылки друг на друга)
	foreach ( $todo as $t ) {
		list( $id, $lang, $fields, $key, $how ) = $t;
		$miss = 0;
		foreach ( $fields as $k => $v ) update_post_meta( $id, $k, wp_slash( lai_mig_20261128_links( (string) $v, $lang, $miss, (array) ( $d['alt'] ?? array() ) ) ) );
		clean_post_cache( $id );
		$log[] = "  $key/$lang: $how #$id " . get_permalink( $id ) . ( $miss ? " ($miss links → EN)" : '' );
	}

	$log = array_merge( $log, lai_mig_20261128_menu( $d['menu'], $srcs ) );

	delete_transient( 'lai_service_paths_all' );
	delete_transient( 'i10v_service_paths' );
	set_transient( 'i10v_service_flush_needed', 1, HOUR_IN_SECONDS );
	update_option( 'lai_il_salt', (string) microtime( true ), false );
	wp_cache_flush();
	do_action( 'litespeed_purge_all' );
	return $log;
}

/** EN-мастер-меню: «Company Changes» (4 подпункта) после «Company Liquidation», «VAT Returns» и «Payroll» в «Accounting & Tax»; подписи всех языков; синхронизация. */
function lai_mig_20261128_menu( array $m, array $srcs ): array {
	$log = array();
	if ( ! function_exists( 'lai_menu_map' ) ) return array( 'menu: lai_menu_map missing' );
	$master = (int) ( lai_menu_map()['primary-menu'] ?? 0 );
	$get    = function () use ( $master ) { $i = (array) wp_get_nav_menu_items( $master, array( 'post_status' => 'any' ) ); usort( $i, function ( $a, $b ) { return $a->menu_order <=> $b->menu_order; } ); return $i; };
	$items  = $get();
	if ( ! $items ) return array( 'menu: master menu not found' );
	$en_of = function ( $key ) use ( $srcs ) { $s = (int) ( $srcs[ $key ] ?? 0 ); return $s ? lai_mig_20261128_tr( $s, 'en' ) : 0; };
	$find  = function ( $spec ) use ( &$items, $en_of ) {
		if ( isset( $spec['page'] ) ) $spec = array( 'obj' => $en_of( $spec['page'] ) );
		foreach ( $items as $i ) {
			if ( ! empty( $spec['obj'] ) && 'post_type' === $i->type && (int) $i->object_id === (int) $spec['obj'] ) return $i;
			if ( isset( $spec['title'] ) && 'custom' === $i->type && (string) $i->post_title === $spec['title'] && ( empty( $spec['top'] ) || 0 === (int) $i->menu_item_parent ) ) return $i;
		}
		return null;
	};
	$after_ids = array();
	foreach ( $m['items'] as $it ) {
		$parent = $find( $it['parent'] );
		if ( ! $parent ) { $log[] = "menu: {$it['label']} — parent not found, skipped"; continue; }
		if ( isset( $it['custom'] ) ) {
			$have = $find( array( 'title' => $it['custom'] ) );
			$args = array( 'menu-item-type' => 'custom', 'menu-item-url' => '#', 'menu-item-title' => $it['label'], 'menu-item-parent-id' => (int) $parent->ID, 'menu-item-status' => 'publish' );
		} else {
			$en = $en_of( $it['page'] );
			if ( ! $en ) { $log[] = "menu: {$it['label']} — EN page missing, skipped"; continue; }
			$have = $find( array( 'obj' => $en ) );
			$args = array( 'menu-item-object-id' => $en, 'menu-item-object' => get_post_type( $en ), 'menu-item-type' => 'post_type', 'menu-item-title' => $it['label'], 'menu-item-parent-id' => (int) $parent->ID, 'menu-item-status' => 'publish' );
		}
		if ( $have ) {
			$nid = (int) $have->ID;
			$log[] = "menu: «{$it['label']}» already in EN menu (#$nid)";
		} else {
			$nid = wp_update_nav_menu_item( $master, 0, $args );
			if ( is_wp_error( $nid ) ) { $log[] = 'menu: add ERROR ' . $nid->get_error_message(); continue; }
			$log[] = "menu: added «{$it['label']}» #$nid";
			$items = $get();
		}
		if ( ! empty( $it['after'] ) && ( $after = $find( $it['after'] ) ) ) $after_ids[ (int) $nid ] = (int) $after->ID;
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
