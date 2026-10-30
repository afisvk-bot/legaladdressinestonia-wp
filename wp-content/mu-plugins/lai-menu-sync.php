<?php
/**
 * Plugin Name: LAI — Menu sync (EN → все языки)
 * Description: Создаёт и обновляет НАСТОЯЩИЕ меню каждого языка Polylang по английскому мастер-меню: пункты ведут на переведённые страницы, подписи — на языке меню. Ручные правки в меню языка сохраняются.
 * Version:     1.1.0
 * Requires:    lai-i18n-helpers.php, lai-auto-menu.php (карта LAI_MASTER_MENUS и словарь подписей)
 *
 * КАК РАБОТАТЬ
 * 1. Новый раздел: создайте страницу на EN и её переводы (Polylang), добавьте её в EN-меню
 *    (Primary EN / Mobile Menu EN) и нажмите «Сохранить меню». Синхронизация запустится
 *    сама: пункт появится в меню всех языков, на их языке.
 * 2. Меню любого языка можно править вручную («Внешний вид → Меню → Primary UK»):
 *      - переименовали синхронизированный пункт → ваше название сохранится;
 *      - добавили свой пункт → он останется (синхронизация его не трогает);
 *      - удалили синхронизированный пункт → при следующей синхронизации он вернётся
 *        (чтобы убрать пункт везде — удалите его из EN-меню).
 * 3. Вручную: «Внешний вид → Синхронизация меню» → кнопка, или WP-CLI:
 *        wp lai menu-sync --dry-run
 *        wp lai menu-sync
 *        wp lai menu-sync --lang=uk
 *
 * Пункт, у страницы которого нет перевода на язык, в меню этого языка не попадает
 * (и появится при следующей синхронизации после публикации перевода — она запускается
 * автоматически при публикации перевода страницы, которая есть в EN-меню).
 */

defined( 'ABSPATH' ) || exit;

const LAI_SYNC_META_MASTER = '_lai_master_item';   // ID пункта EN-меню, из которого создан пункт
const LAI_SYNC_META_TITLE  = '_lai_synced_title';  // подпись, которую поставила синхронизация

/** Меню, назначенное Polylang для локации и языка (0 — нет). */
function lai_sync_lang_menu_id( string $location, string $lang ): int {
	$o     = (array) get_option( 'polylang', array() );
	$theme = (string) get_option( 'stylesheet' );
	return (int) ( $o['nav_menus'][ $theme ][ $location ][ $lang ] ?? 0 );
}

function lai_sync_set_lang_menu_id( string $location, string $lang, int $menu_id ): void {
	$o     = (array) get_option( 'polylang', array() );
	$theme = (string) get_option( 'stylesheet' );
	$o['nav_menus'][ $theme ][ $location ][ $lang ] = $menu_id;
	update_option( 'polylang', $o );
}

/** Подпись пункта на языке: перевод строки → переведённый заголовок → исходная подпись. */
function lai_sync_label( $master_item, string $lang, int $target_object_id ): string {
	$label = (string) $master_item->post_title;
	if ( '' !== $label ) {
		$t = lai_translate_string_strict( $label, $lang );
		if ( '' !== $t ) {
			return $t;
		}
	}
	if ( $target_object_id ) {
		if ( 'post_type' === $master_item->type ) {
			return (string) get_post_field( 'post_title', $target_object_id, 'raw' );
		}
		if ( 'taxonomy' === $master_item->type ) {
			$term = get_term( $target_object_id, (string) $master_item->object );
			return ( $term && ! is_wp_error( $term ) ) ? (string) $term->name : $label;
		}
	}
	return '' !== $label ? $label : (string) $master_item->title;
}

/**
 * Что должно стоять в меню языка вместо пункта мастера. null — пункт пропускается
 * (нет перевода страницы/термина).
 */
function lai_sync_target_spec( $m, string $lang ): ?array {
	$spec = array(
		'menu-item-type'        => (string) $m->type,
		'menu-item-object'      => (string) $m->object,
		'menu-item-object-id'   => 0,
		'menu-item-url'         => '',
		'menu-item-classes'     => implode( ' ', array_filter( (array) $m->classes ) ),
		'menu-item-target'      => (string) $m->target,
		'menu-item-attr-title'  => (string) $m->attr_title,
		'menu-item-description' => (string) $m->description,
		'menu-item-xfn'         => (string) $m->xfn,
		'menu-item-status'      => 'publish',
	);

	switch ( $m->type ) {
		case 'post_type':
			$tid = lai_translate_post_id( (int) $m->object_id, $lang );
			if ( ! lai_is_public_post( $tid ) ) {
				return null;
			}
			$spec['menu-item-object-id'] = $tid;
			break;

		case 'taxonomy':
			$tid = lai_translate_term_id( (int) $m->object_id, (string) $m->object, $lang );
			if ( ! $tid ) {
				return null;
			}
			$spec['menu-item-object-id'] = $tid;
			break;

		case 'custom':
			$url = (string) get_post_meta( $m->ID, '_menu_item_url', true );
			$tid = 0;
			if ( '' !== $url && '#' !== $url[0] && false === strpos( $url, 'pll_switcher' ) ) {
				$master_home = untrailingslashit( lai_home_url( lai_menu_master_lang() ) );
				if ( untrailingslashit( $url ) === $master_home || untrailingslashit( $url ) === untrailingslashit( home_url() ) ) {
					$url = lai_home_url( $lang );
				} elseif ( $pid = lai_url_to_post_id( $url ) ) {
					$tid = lai_translate_post_id( $pid, $lang );
					if ( ! lai_is_public_post( $tid ) ) {
						return null;
					}
					$url = lai_permalink( $tid );
				}
			}
			$spec['menu-item-url'] = $url;
			$spec['_label']        = lai_sync_label( $m, $lang, $tid );
			return $spec;

		default:
			// post_type_archive и прочее — копируем как есть
			$spec['menu-item-object-id'] = (int) $m->object_id;
			$spec['menu-item-url']       = (string) $m->url;
	}

	$spec['_label'] = lai_sync_label( $m, $lang, (int) $spec['menu-item-object-id'] );
	return $spec;
}

/** Нормализация URL для сравнения. */
function lai_sync_norm_url( string $u ): string {
	return untrailingslashit( preg_replace( '#^https?://#', '//', trim( $u ) ) );
}

/**
 * Синхронизировать одно меню языка. Возвращает статистику.
 * $dry — только посчитать, ничего не менять.
 */
function lai_sync_menu( int $master_id, string $location, string $lang, bool $dry = false ): array {
	$stats = array( 'location' => $location, 'lang' => $lang, 'menu' => 0, 'created' => 0, 'updated' => 0, 'kept_manual' => 0, 'removed' => 0, 'skipped_no_translation' => 0, 'user_items' => 0 );

	$master = array_values( (array) wp_get_nav_menu_items( $master_id, array( 'post_status' => 'publish' ) ) );
	if ( ! $master ) {
		return $stats;
	}

	// Меню языка: назначенное Polylang, иначе создаём.
	$menu_id = lai_sync_lang_menu_id( $location, $lang );
	if ( ! $menu_id || ! wp_get_nav_menu_object( $menu_id ) ) {
		if ( $dry ) {
			$stats['menu'] = 'new';
			$stats['created'] = count( $master );
			return $stats;
		}
		$master_obj = wp_get_nav_menu_object( $master_id );
		$name       = preg_replace( '/\s+[A-Z]{2}$/', '', (string) $master_obj->name ) . ' ' . strtoupper( $lang );
		$exists     = wp_get_nav_menu_object( $name );
		$menu_id    = $exists ? (int) $exists->term_id : (int) wp_create_nav_menu( $name );
		if ( ! $menu_id || is_wp_error( $menu_id ) ) {
			return $stats;
		}
		lai_sync_set_lang_menu_id( $location, $lang, $menu_id );
	}
	$stats['menu'] = $menu_id;

	$existing = array_values( (array) wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) ) );

	// Индексы существующих пунктов.
	$by_master = array();
	$free      = array(); // пункты без привязки — кандидаты на «усыновление» (готовое меню, сделанное вручную)
	foreach ( $existing as $e ) {
		$mid = (int) get_post_meta( $e->ID, LAI_SYNC_META_MASTER, true );
		if ( $mid ) {
			$by_master[ $mid ] = $e;
		} else {
			$free[ $e->ID ] = $e;
		}
	}

	$master_ids  = array();
	$map         = array(); // master db_id → target db_id
	$position    = 0;
	$skip_parent = array();

	foreach ( $master as $m ) {
		$master_ids[ (int) $m->ID ] = true;
		$parent_master = (int) $m->menu_item_parent;

		if ( $parent_master && ( isset( $skip_parent[ $parent_master ] ) || ! isset( $map[ $parent_master ] ) ) ) {
			$skip_parent[ (int) $m->ID ] = true; // родитель пропущен → пропускаем ветку
			continue;
		}

		$spec = lai_sync_target_spec( $m, $lang );
		if ( null === $spec ) {
			$stats['skipped_no_translation']++;
			$skip_parent[ (int) $m->ID ] = true;
			continue;
		}
		$label = (string) $spec['_label'];
		unset( $spec['_label'] );

		// Найти существующий пункт: по привязке, иначе усыновить подходящий ручной.
		$target = $by_master[ (int) $m->ID ] ?? null;
		if ( ! $target ) {
			foreach ( $free as $fid => $f ) {
				$same = false;
				if ( 'custom' !== $m->type ) {
					$same = $f->type === $spec['menu-item-type'] && (int) $f->object_id === (int) $spec['menu-item-object-id'];
				} else {
					$fu   = (string) get_post_meta( $f->ID, '_menu_item_url', true );
					$same = 'custom' === $f->type && (
						( '' !== $fu && '#' !== $fu && lai_sync_norm_url( $fu ) === lai_sync_norm_url( (string) $spec['menu-item-url'] ) )
						|| (string) $f->post_title === $label
					);
				}
				if ( $same ) {
					$target = $f;
					unset( $free[ $fid ] );
					break;
				}
			}
		}

		$position++;
		$parent_target = $parent_master ? (int) $map[ $parent_master ] : 0;

		// Как ядро сохранит подпись: совпадающую с заголовком объекта хранит пустой строкой.
		$label_stored = $label;
		if ( 'post_type' === $spec['menu-item-type'] && $label === (string) get_post_field( 'post_title', (int) $spec['menu-item-object-id'], 'raw' ) ) {
			$label_stored = '';
		} elseif ( 'taxonomy' === $spec['menu-item-type'] ) {
			$t = get_term( (int) $spec['menu-item-object-id'], (string) $spec['menu-item-object'] );
			if ( $t && ! is_wp_error( $t ) && $label === (string) $t->name ) {
				$label_stored = '';
			}
		}

		$manual = false;
		if ( $target ) {
			$current = (string) $target->post_title;
			if ( metadata_exists( 'post', $target->ID, LAI_SYNC_META_TITLE ) ) {
				$manual = $current !== (string) get_post_meta( $target->ID, LAI_SYNC_META_TITLE, true );
			} else {
				$manual = '' !== $current && $current !== $label_stored; // усыновлённый пункт ручного меню
			}
			if ( $manual ) {
				$stats['kept_manual']++;
			}
			$title = $manual ? $current : $label;

			$changed = (int) $target->menu_item_parent !== $parent_target
				|| (int) $target->menu_order !== $position
				|| ( 'custom' !== $spec['menu-item-type'] && (int) $target->object_id !== (int) $spec['menu-item-object-id'] )
				|| ( 'custom' === $spec['menu-item-type'] && lai_sync_norm_url( (string) get_post_meta( $target->ID, '_menu_item_url', true ) ) !== lai_sync_norm_url( (string) $spec['menu-item-url'] ) )
				|| ( ! $manual && $current !== $label_stored )
				|| 'publish' !== $target->post_status
				|| (int) get_post_meta( $target->ID, LAI_SYNC_META_MASTER, true ) !== (int) $m->ID;

			if ( $changed ) {
				$stats['updated']++;
				if ( ! $dry ) {
					wp_update_nav_menu_item( $menu_id, (int) $target->ID, array_merge( $spec, array(
						'menu-item-title'     => $title,
						'menu-item-parent-id' => $parent_target,
						'menu-item-position'  => $position,
					) ) );
				}
			}
			$tid = (int) $target->ID;
		} else {
			$stats['created']++;
			if ( $dry ) {
				$map[ (int) $m->ID ] = - (int) $m->ID;
				continue;
			}
			$tid = (int) wp_update_nav_menu_item( $menu_id, 0, array_merge( $spec, array(
				'menu-item-title'     => $label,
				'menu-item-parent-id' => $parent_target,
				'menu-item-position'  => $position,
			) ) );
			if ( ! $tid || is_wp_error( $tid ) ) {
				continue;
			}
		}

		if ( ! $dry ) {
			update_post_meta( $tid, LAI_SYNC_META_MASTER, (int) $m->ID );
			if ( ! $manual ) {
				// Запоминаем подпись, поставленную синхронизацией (как её сохранило ядро).
				update_post_meta( $tid, LAI_SYNC_META_TITLE, (string) get_post_field( 'post_title', $tid, 'raw' ) );
			}
		}
		$map[ (int) $m->ID ] = $tid;
	}

	// Синхронизированные пункты, которых больше нет в мастере (или нет перевода) — удалить.
	foreach ( $by_master as $mid => $e ) {
		if ( ! isset( $map[ $mid ] ) ) {
			$stats['removed']++;
			if ( ! $dry ) {
				wp_delete_post( (int) $e->ID, true );
			}
		}
	}

	// Пункты, добавленные вручную в меню языка, остаются; ставим их после синхронизированных.
	foreach ( $free as $f ) {
		$stats['user_items']++;
		if ( $dry ) {
			continue;
		}
		$position++;
		if ( (int) $f->menu_order !== $position ) {
			wp_update_post( array( 'ID' => (int) $f->ID, 'menu_order' => $position ) );
		}
	}

	return $stats;
}

/** Синхронизировать все локации и языки (или один язык). */
function lai_sync_all_menus( bool $dry = false, string $only_lang = '' ): array {
	static $running = false;
	if ( $running || ! function_exists( 'lai_menu_map' ) ) {
		return array();
	}
	$running = true;
	$out     = array();
	$master_lang = lai_menu_master_lang();
	foreach ( lai_menu_map() as $location => $master_id ) {
		foreach ( lai_languages() as $lang ) {
			if ( $lang === $master_lang || ( $only_lang && $lang !== $only_lang ) ) {
				continue;
			}
			$out[] = lai_sync_menu( (int) $master_id, (string) $location, (string) $lang, $dry );
		}
	}
	if ( ! $dry ) {
		update_option( 'lai_menu_sync_last', array( 'time' => time(), 'stats' => $out ), false );
	}
	$running = false;
	return $out;
}

// ---------------- Автосинхронизация ----------------

// Сохранили EN-меню → синхронизировать (после того как ядро сохранит все пункты).
add_action( 'wp_update_nav_menu', function ( $menu_id ) {
	if ( function_exists( 'lai_menu_map' ) && in_array( (int) $menu_id, array_map( 'intval', lai_menu_map() ), true ) ) {
		add_action( 'shutdown', function () {
			lai_sync_all_menus();
		} );
	}
} );

// Опубликовали перевод страницы, которая есть в EN-меню → досинхронизировать этот язык.
add_action( 'transition_post_status', function ( $new, $old, $post ) {
	if ( 'publish' !== $new || 'publish' === $old || 'nav_menu_item' === $post->post_type || ! function_exists( 'lai_menu_map' ) ) {
		return;
	}
	$lang = lai_post_lang( (int) $post->ID );
	if ( ! $lang || $lang === lai_menu_master_lang() ) {
		return;
	}
	$master_obj = lai_translate_post_id( (int) $post->ID, lai_menu_master_lang() );
	if ( ! $master_obj ) {
		return;
	}
	foreach ( array_unique( lai_menu_map() ) as $mid ) {
		foreach ( (array) wp_get_nav_menu_items( $mid ) as $i ) {
			if ( 'post_type' === $i->type && (int) $i->object_id === $master_obj ) {
				add_action( 'shutdown', function () use ( $lang ) {
					lai_sync_all_menus( false, $lang );
				} );
				return;
			}
		}
	}
}, 10, 3 );

// ---------------- Первичная настройка (один раз, при первом входе в админку) ----------------
/**
 * 1) Для языков, у которых уже есть готовое меню, сделанное вручную (на этом сайте — RU),
 *    берёт подписи из него, чтобы сохранить привычные названия.
 * 2) Остальные подписи — из словаря LAI_MENU_STRINGS_FILE (существующие переводы не перезаписываются).
 * 3) Синхронизирует меню всех языков.
 */
function lai_menu_initial_setup(): array {
	$report = array( 'seeded' => array(), 'imported' => array(), 'sync' => array() );
	$master_lang = lai_menu_master_lang();
	$map         = lai_menu_map();
	$primary_loc = (string) array_key_first( $map );
	$master_id   = (int) ( $map[ $primary_loc ] ?? 0 );

	foreach ( lai_languages() as $lang ) {
		if ( $lang === $master_lang || ! $master_id ) {
			continue;
		}
		$menu_id = lai_sync_lang_menu_id( $primary_loc, $lang );
		$items   = $menu_id ? (array) wp_get_nav_menu_items( $menu_id ) : array();
		$synced  = false;
		foreach ( $items as $i ) {
			if ( get_post_meta( $i->ID, LAI_SYNC_META_MASTER, true ) ) {
				$synced = true;
				break;
			}
		}
		if ( $items && ! $synced ) {
			$labels = lai_menu_strings_map_from_menu( $master_id, $menu_id, $lang );
			$report['seeded'][ $lang ] = lai_save_string_translations( $lang, $labels, false );
		}
	}

	foreach ( lai_menu_strings_read_file( LAI_MENU_STRINGS_FILE ) as $lang => $labels ) {
		$report['imported'][ $lang ] = lai_save_string_translations( $lang, $labels, false );
	}

	$report['sync'] = lai_sync_all_menus();
	return $report;
}

add_action( 'admin_init', function () {
	if ( wp_doing_ajax() || get_option( 'lai_menu_setup_done' ) || ! current_user_can( 'edit_theme_options' ) || ! function_exists( 'lai_menu_map' ) || ! lai_menu_map() ) {
		return;
	}
	update_option( 'lai_menu_setup_done', time(), false ); // ставим сразу — защита от двойного запуска
	$report = lai_menu_initial_setup();
	update_option( 'lai_menu_setup_report', $report, false );
	set_transient( 'lai_menu_setup_notice', 1, HOUR_IN_SECONDS );
} );

add_action( 'admin_notices', function () {
	if ( ! get_transient( 'lai_menu_setup_notice' ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	delete_transient( 'lai_menu_setup_notice' );
	$r       = (array) get_option( 'lai_menu_setup_report', array() );
	$created = 0;
	$menus   = 0;
	foreach ( (array) ( $r['sync'] ?? array() ) as $row ) {
		$created += (int) $row['created'] + (int) $row['updated'];
		$menus++;
	}
	printf(
		'<div class="notice notice-success is-dismissible"><p><strong>LAI: меню языков настроены.</strong> Подписи: из готовых меню — %s; из словаря — %d. Обработано меню: %d, пунктов: %d. <a href="%s">Подробности</a></p></div>',
		esc_html( $r['seeded'] ? implode( ', ', array_map( fn( $l, $n ) => strtoupper( $l ) . " ($n)", array_keys( $r['seeded'] ), $r['seeded'] ) ) : '—' ),
		(int) array_sum( (array) ( $r['imported'] ?? array() ) ),
		$menus,
		$created,
		esc_url( admin_url( 'themes.php?page=lai-menu-sync' ) )
	);
} );

// ---------------- Админка: «Внешний вид → Синхронизация меню» ----------------
add_action( 'admin_menu', function () {
	add_theme_page( 'Синхронизация меню', 'Синхронизация меню', 'edit_theme_options', 'lai-menu-sync', 'lai_sync_admin_page' );
} );

function lai_sync_admin_page(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$result = null;
	if ( isset( $_POST['lai_sync'] ) && check_admin_referer( 'lai_menu_sync' ) ) {
		$result = lai_sync_all_menus();
	}
	$last = get_option( 'lai_menu_sync_last' );
	echo '<div class="wrap"><h1>Синхронизация меню EN → все языки</h1>';
	echo '<p>Структуру меню ведите в английском меню (' . esc_html( implode( ', ', array_map( function ( $id ) {
		$o = wp_get_nav_menu_object( $id );
		return $o ? $o->name : '#' . $id;
	}, array_unique( lai_menu_map() ) ) ) ) . '). При сохранении EN-меню синхронизация запускается сама. ';
	echo 'Меню языков можно править вручную — переименования и свои пункты сохраняются.</p>';
	if ( $result ) {
		echo '<div class="notice notice-success"><p>Синхронизация выполнена.</p></div>';
	}
	echo '<form method="post">';
	wp_nonce_field( 'lai_menu_sync' );
	submit_button( 'Синхронизировать сейчас', 'primary', 'lai_sync' );
	echo '</form>';

	$rows = $result ?: ( $last['stats'] ?? array() );
	if ( $rows ) {
		echo '<h2>' . ( $result ? 'Результат' : 'Последняя синхронизация: ' . esc_html( wp_date( 'd.m.Y H:i', (int) $last['time'] ) ) ) . '</h2>';
		echo '<table class="widefat striped"><thead><tr><th>Локация</th><th>Язык</th><th>Меню</th><th>Создано</th><th>Обновлено</th><th>Ручные подписи</th><th>Удалено</th><th>Нет перевода</th><th>Свои пункты</th></tr></thead><tbody>';
		foreach ( $rows as $r ) {
			$menu = is_numeric( $r['menu'] ) ? wp_get_nav_menu_object( (int) $r['menu'] ) : null;
			$link = $menu ? '<a href="' . esc_url( admin_url( 'nav-menus.php?action=edit&menu=' . (int) $menu->term_id ) ) . '">' . esc_html( $menu->name ) . '</a>' : '—';
			printf( '<tr><td>%s</td><td>%s</td><td>%s</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td></tr>',
				esc_html( $r['location'] ), esc_html( strtoupper( $r['lang'] ) ), $link,
				$r['created'], $r['updated'], $r['kept_manual'], $r['removed'], $r['skipped_no_translation'], $r['user_items'] );
		}
		echo '</tbody></table>';
	}
	echo '</div>';
}

// Подсказка на экране редактирования меню языка.
add_action( 'admin_notices', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'nav-menus' !== $screen->id || ! function_exists( 'lai_menu_map' ) ) {
		return;
	}
	$menu = isset( $_GET['menu'] ) ? (int) $_GET['menu'] : (int) get_user_option( 'nav_menu_recently_edited' );
	if ( in_array( $menu, array_map( 'intval', lai_menu_map() ), true ) ) {
		echo '<div class="notice notice-info"><p><strong>Это мастер-меню (EN).</strong> После сохранения изменения автоматически попадут в меню всех языков.</p></div>';
		return;
	}
	foreach ( (array) wp_get_nav_menu_items( $menu ) as $i ) {
		if ( get_post_meta( $i->ID, LAI_SYNC_META_MASTER, true ) ) {
			echo '<div class="notice notice-info"><p><strong>Меню синхронизируется с английским.</strong> Новые разделы добавляйте в EN-меню — они появятся здесь сами. Здесь можно переименовывать пункты и добавлять свои: эти правки сохранятся.</p></div>';
			return;
		}
	}
} );

// ---------------- WP-CLI ----------------
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * Синхронизировать меню языков с EN-меню.
	 *
	 * ## OPTIONS
	 * [--dry-run]     : только показать, что изменится
	 * [--lang=<slug>] : только один язык
	 */
	WP_CLI::add_command( 'lai menu-sync', function ( $args, $assoc ) {
		$rows = lai_sync_all_menus( isset( $assoc['dry-run'] ), (string) ( $assoc['lang'] ?? '' ) );
		WP_CLI\Utils\format_items( 'table', $rows, array( 'location', 'lang', 'menu', 'created', 'updated', 'kept_manual', 'removed', 'skipped_no_translation', 'user_items' ) );
		WP_CLI::success( isset( $assoc['dry-run'] ) ? 'Пробный прогон.' : 'Меню синхронизированы.' );
	} );
}
