<?php
/**
 * Plugin Name: LAI — Auto multilingual menu
 * Description: Одно мастер-меню на локацию для всех языков: автоподмена пунктов на переводы, подписи через «Переводы строк» Polylang, автогенерация подменю.
 * Version:     1.2.1
 * Requires:    lai-i18n-helpers.php
 *
 * КАК ЭТО РАБОТАЕТ
 * 1. Для каждой локации темы вы ведёте ОДНО меню (мастер, на EN).
 *    Карта «локация → ID мастер-меню» задаётся в wp-config.php:
 *        define( 'LAI_MASTER_MENUS', 'primary-menu:154,mobile_menu:157' );
 * 2. На любом языке пункты меню (услуги, лицензии, отрасли, страницы, рубрики)
 *    подменяются их переводами (pll_get_post / pll_get_term). Нет перевода — пункт
 *    скрыт, поэтому ссылок на «чужой» язык в меню не бывает.
 * 3. Подписи пунктов («Services», «Via Notary»…) переводятся через
 *    «Языки → Переводы строк», группа «LAI Menu». Одна строка = один перевод для
 *    всех меню. Нет перевода → заголовок переведённой страницы.
 * 4. Автоподменю по CSS-классу пункта-родителя (необязательно):
 *      lai-auto-tax-{taxonomy}  — термины таксономии текущего языка;
 *      lai-auto-parent-{ID}     — вместе с предыдущим: только дочерние термина ID;
 *      lai-auto-children        — дочерние записи страницы-родителя.
 * 5. WP-CLI:
 *      wp lai menu-strings status
 *      wp lai menu-strings seed-from-menu --menu=155 --lang=ru   (взять подписи из готового меню языка)
 *      wp lai menu-strings import lai-menu-strings.json          (словарь подписей для всех языков)
 *
 * Совместимость с темой LegalAddress: тема в wp_nav_menu_objects (приоритет 20)
 * срезает языковой префикс у service/licensing и возвращает custom-ссылкам исходный URL.
 * Поэтому локализация идёт на приоритете 15 (до темы), а переведённые custom-URL
 * восстанавливаются на приоритете 25 (после темы).
 */

defined( 'ABSPATH' ) || exit;

// ---------------- Настройки ----------------
defined( 'LAI_MASTER_MENUS' )     || define( 'LAI_MASTER_MENUS', '' );          // 'location:menuID,location:menuID'
defined( 'LAI_MASTER_MENU_ID' )   || define( 'LAI_MASTER_MENU_ID', 0 );         // старый формат: одно меню для LAI_MENU_LOCATIONS
defined( 'LAI_MENU_LOCATIONS' )   || define( 'LAI_MENU_LOCATIONS', 'primary' );
defined( 'LAI_MENU_MASTER_LANG' ) || define( 'LAI_MENU_MASTER_LANG', '' );      // '' = язык сайта по умолчанию
defined( 'LAI_AUTO_MAX_DEPTH' )   || define( 'LAI_AUTO_MAX_DEPTH', 2 );
defined( 'LAI_AUTO_HIDE_EMPTY' )  || define( 'LAI_AUTO_HIDE_EMPTY', true );
defined( 'LAI_MENU_STRING_GROUP' ) || define( 'LAI_MENU_STRING_GROUP', 'LAI Menu' );

/** Карта локация → ID мастер-меню. */
function lai_menu_map(): array {
	static $map = null;
	if ( null !== $map ) {
		return $map;
	}
	$map = array();
	foreach ( array_filter( array_map( 'trim', explode( ',', (string) LAI_MASTER_MENUS ) ) ) as $pair ) {
		$parts = array_map( 'trim', explode( ':', $pair ) );
		if ( 2 === count( $parts ) && '' !== $parts[0] && (int) $parts[1] > 0 ) {
			$map[ $parts[0] ] = (int) $parts[1];
		}
	}
	if ( ! $map && LAI_MASTER_MENU_ID ) {
		foreach ( array_filter( array_map( 'trim', explode( ',', (string) LAI_MENU_LOCATIONS ) ) ) as $loc ) {
			$map[ $loc ] = (int) LAI_MASTER_MENU_ID;
		}
	}
	return $map;
}

function lai_menu_master_lang(): string {
	return LAI_MENU_MASTER_LANG ? (string) LAI_MENU_MASTER_LANG : lai_default_lang();
}

function lai_is_managed_menu( $args ): bool {
	// Локализуем только когда подставили мастер-меню (у языка нет своего).
	return is_object( $args ) ? ! empty( $args->lai_master_override ) : ! empty( $args['lai_master_override'] );
}

// ---------------- 1. Одно меню на все языки ----------------
// Если у языка есть своё заполненное меню (создано синхронизацией lai-menu-sync.php
// или вручную) — выводится оно, с ручными правками подписей. Мастер-меню с переводом
// «на лету» подставляется только когда меню языка пустое или не назначено (новый язык).
//
// Тема вызывает wp_nav_menu() только с 'theme_location', а Polylang на фронтенде
// подменяет меню через theme_mod_nav_menu_locations и в $args['menu'] ничего не пишет.
// Поэтому меню языка ищем сами: назначение Polylang для локации и текущего языка,
// затем get_nav_menu_locations(). 1500 — после фильтров Polylang (у него 2000 только в кастомайзере).
function lai_lang_menu_for_location( string $loc, string $lang ) {
	$id = function_exists( 'lai_sync_lang_menu_id' ) ? lai_sync_lang_menu_id( $loc, $lang ) : 0;
	if ( ! $id ) {
		$locations = get_nav_menu_locations();
		$id        = (int) ( $locations[ $loc ] ?? 0 );
	}
	return $id ? wp_get_nav_menu_object( $id ) : false;
}

add_filter( 'wp_nav_menu_args', function ( $args ) {
	$loc = $args['theme_location'] ?? '';
	if ( '' === $loc || ! isset( lai_menu_map()[ $loc ] ) ) {
		return $args;
	}
	$current = $args['menu'] ?? '';
	$menu    = $current ? wp_get_nav_menu_object( $current ) : lai_lang_menu_for_location( $loc, lai_current_lang() );
	if ( $menu && (int) $menu->count > 0 ) {
		$args['menu'] = (int) $menu->term_id;
		return $args;
	}
	$args['menu']                = lai_menu_map()[ $loc ];
	$args['lai_master_override'] = true;
	return $args;
}, 1500 );

add_filter( 'has_nav_menu', function ( $has, $location ) {
	return isset( lai_menu_map()[ $location ] ) ? true : $has;
}, 10, 2 );


// ---------------- 2. Подписи пунктов → «Переводы строк» ----------------
function lai_menu_labels(): array {
	$labels = array();
	foreach ( array_unique( lai_menu_map() ) as $menu_id ) {
		foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
			if ( '' !== (string) $item->post_title ) {
				$labels[ (string) $item->post_title ] = true;
			}
		}
	}
	return array_keys( $labels );
}

add_action( 'admin_init', function () {
	foreach ( lai_menu_labels() as $label ) {
		lai_register_string( 'menu: ' . $label, $label, LAI_MENU_STRING_GROUP );
	}
} );

// ---------------- 3. Локализация пунктов и автогенерация подменю ----------------
add_filter( 'wp_nav_menu_objects', 'lai_localize_menu_items', 15, 2 );
add_filter( 'wp_nav_menu_objects', 'lai_restore_custom_urls', 25, 2 );

function lai_localize_menu_items( array $items, $args ): array {
	if ( ! lai_is_managed_menu( $args ) ) {
		return $items;
	}

	$lang    = lai_current_lang();
	$removed = array();
	$out     = array();

	foreach ( $items as $item ) {
		$parent = (int) $item->menu_item_parent;
		if ( ( $parent && isset( $removed[ $parent ] ) ) || ! lai_localize_single_item( $item, $lang ) ) {
			$removed[ (int) $item->db_id ] = true;
			continue;
		}
		$out[] = $item;

		$generated = lai_generate_auto_children( $item, $lang );
		if ( $generated ) {
			$item->classes[] = 'menu-item-has-children';
			array_push( $out, ...$generated );
		}
	}

	// Пустые группы («#»-заголовки без детей после фильтрации) не показываем.
	$has_child = array();
	foreach ( $out as $i ) {
		$has_child[ (int) $i->menu_item_parent ] = true;
	}
	$out = array_values( array_filter( $out, function ( $i ) use ( $has_child ) {
		return ! ( 'custom' === $i->type && in_array( trim( (string) $i->url ), array( '', '#' ), true ) && empty( $has_child[ (int) $i->db_id ] ) );
	} ) );

	// current-* классы считались по объектам мастер-языка — пересчитываем.
	$ctx_classes = array(
		'current-menu-item', 'current-menu-parent', 'current-menu-ancestor',
		'current_page_item', 'current_page_parent', 'current_page_ancestor', 'current-page-ancestor',
	);
	foreach ( $out as $i ) {
		$i->classes               = array_values( array_unique( array_diff( (array) $i->classes, $ctx_classes ) ) );
		$i->current               = false;
		$i->current_item_ancestor = false;
		$i->current_item_parent   = false;
	}
	if ( function_exists( '_wp_menu_item_classes_by_context' ) ) {
		_wp_menu_item_classes_by_context( $out );
	}

	$sorted = array();
	$n      = 1;
	foreach ( $out as $i ) {
		$i->menu_order  = $n;
		$sorted[ $n++ ] = $i;
	}
	return $sorted;
}

/** Тема сбрасывает URL custom-пунктов на исходный — возвращаем переведённый. */
function lai_restore_custom_urls( array $items, $args ): array {
	if ( ! lai_is_managed_menu( $args ) ) {
		return $items;
	}
	foreach ( $items as $item ) {
		if ( ! empty( $item->lai_url ) ) {
			$item->url = $item->lai_url;
		}
	}
	return $items;
}

/** Подменяет объект пункта переводом. false — перевода нет, пункт скрываем. */
function lai_localize_single_item( $item, string $lang ): bool {
	$custom_label = (string) $item->post_title;

	switch ( $item->type ) {
		case 'post_type':
			$tid = lai_translate_post_id( (int) $item->object_id, $lang );
			if ( ! lai_is_public_post( $tid ) ) {
				return false;
			}
			$item->object_id = $tid;
			$item->url       = lai_permalink( $tid );
			$fallback        = get_the_title( $tid );
			break;

		case 'taxonomy':
			$tid  = lai_translate_term_id( (int) $item->object_id, (string) $item->object, $lang );
			$term = $tid ? get_term( $tid, (string) $item->object ) : null;
			if ( ! $term || is_wp_error( $term ) ) {
				return false;
			}
			$link = get_term_link( $term );
			if ( is_wp_error( $link ) ) {
				return false;
			}
			$item->object_id = $tid;
			$item->url       = $link;
			$fallback        = $term->name;
			break;

		case 'post_type_archive':
			$link = get_post_type_archive_link( (string) $item->object );
			if ( $link ) {
				$item->url = $link;
			}
			$fallback = (string) $item->title;
			break;

		case 'custom':
			$url = (string) get_post_meta( $item->ID, '_menu_item_url', true );
			if ( '' !== $url && '#' !== $url[0] && false === strpos( $url, 'pll_switcher' ) ) {
				$master_home = untrailingslashit( lai_home_url( lai_menu_master_lang() ) );
				if ( untrailingslashit( $url ) === $master_home || untrailingslashit( $url ) === untrailingslashit( home_url() ) ) {
					$item->url = $item->lai_url = lai_home_url( $lang );
				} elseif ( $pid = lai_url_to_post_id( $url ) ) {
					// Внутренняя ссылка, вставленная вручную, тоже переводится.
					$tid = lai_translate_post_id( $pid, $lang );
					if ( ! lai_is_public_post( $tid ) ) {
						return false;
					}
					$item->url = $item->lai_url = lai_permalink( $tid );
				}
			}
			$fallback = $custom_label;
			break;

		default:
			return true;
	}

	$item->title = lai_menu_item_title( $item, $custom_label, (string) $fallback, $lang );
	return true;
}

function lai_menu_item_title( $item, string $custom_label, string $fallback, string $lang ): string {
	if ( '' === $custom_label ) {
		return $fallback;
	}
	if ( $lang === lai_menu_master_lang() ) {
		return $custom_label;
	}
	$translated = lai_translate_string_strict( $custom_label, $lang );
	if ( '' !== $translated ) {
		return $translated;
	}
	// Нет перевода подписи: у записи — её переведённый заголовок, у custom-пункта — исходная подпись.
	return 'custom' === $item->type ? $custom_label : $fallback;
}

function lai_generate_auto_children( $parent_item, string $lang ): array {
	$tax           = '';
	$master_parent = 0;
	$pages         = false;

	foreach ( (array) $parent_item->classes as $c ) {
		if ( 0 === strpos( $c, 'lai-auto-tax-' ) ) {
			$tax = substr( $c, 13 );
		} elseif ( 0 === strpos( $c, 'lai-auto-parent-' ) ) {
			$master_parent = (int) substr( $c, 16 );
		} elseif ( 'lai-auto-children' === $c ) {
			$pages = true;
		}
	}

	if ( $tax && taxonomy_exists( $tax ) ) {
		$root = $master_parent ? lai_translate_term_id( $master_parent, $tax, $lang ) : 0;
		if ( $master_parent && ! $root ) {
			return array();
		}
		return lai_build_term_items( $tax, $root, (int) $parent_item->db_id, $lang );
	}

	if ( $pages && 'post_type' === $parent_item->type ) {
		return lai_build_page_items( (int) $parent_item->object_id, (int) $parent_item->db_id, 1 );
	}

	return array();
}

function lai_build_term_items( string $tax, int $root, int $parent_db_id, string $lang ): array {
	$q = array(
		'taxonomy'     => $tax,
		'hide_empty'   => (bool) LAI_AUTO_HIDE_EMPTY,
		'hierarchical' => true,
		'orderby'      => 'name',
	);
	if ( $root ) {
		$q['child_of'] = $root;
	}
	if ( 'category' === $tax ) {
		$uncat = lai_translate_term_id( (int) get_option( 'default_category' ), 'category', $lang );
		if ( $uncat ) {
			$q['exclude'] = array( $uncat );
		}
	}
	if ( 'polylang' === lai_ml_engine() ) {
		$q['lang'] = $lang;
	}
	$terms = get_terms( apply_filters( 'lai_auto_menu_terms_query', $q, $lang ) );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}

	$by_parent = array();
	foreach ( $terms as $t ) {
		$by_parent[ (int) $t->parent ][] = $t;
	}
	return lai_term_tree_to_items( $by_parent, $root, $tax, $parent_db_id, 1 );
}

function lai_term_tree_to_items( array $by_parent, int $parent_term, string $tax, int $parent_db_id, int $depth ): array {
	$items = array();
	foreach ( $by_parent[ $parent_term ] ?? array() as $term ) {
		$url = get_term_link( $term );
		if ( is_wp_error( $url ) ) {
			continue;
		}
		$item    = lai_fake_menu_item( array(
			'title'     => $term->name,
			'url'       => $url,
			'object'    => $tax,
			'object_id' => (int) $term->term_id,
			'type'      => 'taxonomy',
			'parent'    => $parent_db_id,
		) );
		$items[] = $item;

		if ( $depth < (int) LAI_AUTO_MAX_DEPTH ) {
			$sub = lai_term_tree_to_items( $by_parent, (int) $term->term_id, $tax, (int) $item->db_id, $depth + 1 );
			if ( $sub ) {
				$item->classes[] = 'menu-item-has-children';
				array_push( $items, ...$sub );
			}
		}
	}
	return $items;
}

function lai_build_page_items( int $page_id, int $parent_db_id, int $depth ): array {
	$children = get_posts( apply_filters( 'lai_auto_menu_pages_query', array(
		'post_parent'      => $page_id,
		'post_type'        => get_post_type( $page_id ),
		'orderby'          => 'menu_order title',
		'order'            => 'ASC',
		'post_status'      => 'publish',
		'numberposts'      => 50,
		'suppress_filters' => false,
	), $page_id ) );

	$items = array();
	foreach ( (array) $children as $p ) {
		if ( ! lai_is_public_post( (int) $p->ID ) ) {
			continue;
		}
		$item    = lai_fake_menu_item( array(
			'title'     => get_the_title( $p ),
			'url'       => lai_permalink( (int) $p->ID ),
			'object'    => $p->post_type,
			'object_id' => (int) $p->ID,
			'type'      => 'post_type',
			'parent'    => $parent_db_id,
		) );
		$items[] = $item;

		if ( $depth < (int) LAI_AUTO_MAX_DEPTH ) {
			$sub = lai_build_page_items( (int) $p->ID, (int) $item->db_id, $depth + 1 );
			if ( $sub ) {
				$item->classes[] = 'menu-item-has-children';
				array_push( $items, ...$sub );
			}
		}
	}
	return $items;
}

function lai_fake_menu_item( array $d ): WP_Post {
	static $seq = 0;
	$id = 1900000000 + ( ++$seq );

	return new WP_Post( (object) array(
		'ID'                    => $id,
		'db_id'                 => $id,
		'post_type'             => 'nav_menu_item',
		'post_status'           => 'publish',
		'post_parent'           => 0,
		'post_title'            => '',
		'post_name'             => '',
		'post_content'          => '',
		'post_excerpt'          => '',
		'filter'                => 'raw',
		'menu_item_parent'      => (string) $d['parent'],
		'object_id'             => (string) $d['object_id'],
		'object'                => (string) $d['object'],
		'type'                  => (string) $d['type'],
		'type_label'            => '',
		'title'                 => (string) $d['title'],
		'url'                   => (string) $d['url'],
		'target'                => '',
		'attr_title'            => '',
		'description'           => '',
		'classes'               => array( 'menu-item', 'menu-item-type-' . $d['type'], 'menu-item-object-' . $d['object'], 'lai-auto-item' ),
		'xfn'                   => '',
		'current'               => false,
		'current_item_ancestor' => false,
		'current_item_parent'   => false,
		'menu_order'            => 0,
	) );
}

// ---------------- Подписи: общие функции (используются CLI и первичной настройкой) ----------------
defined( 'LAI_MENU_STRINGS_FILE' ) || define( 'LAI_MENU_STRINGS_FILE', WP_CONTENT_DIR . '/lai-menu-strings.json' );

/**
 * Сопоставить подписи мастер-меню с подписями готового меню языка.
 * Возвращает [ 'Services' => 'Услуги', ... ].
 */
function lai_menu_strings_map_from_menu( int $master_id, int $menu_id, string $lang ): array {
	$src = array_values( (array) wp_get_nav_menu_items( $master_id ) );
	$dst = array_values( (array) wp_get_nav_menu_items( $menu_id ) );
	if ( ! $src || ! $dst ) {
		return array();
	}
	$by_obj = array();
	foreach ( $dst as $d ) {
		if ( 'post_type' === $d->type ) {
			$by_obj[ (int) $d->object_id ] = $d;
		}
	}
	$src_custom = array_values( array_filter( $src, fn( $i ) => 'custom' === $i->type ) );
	$dst_custom = array_values( array_filter( $dst, fn( $i ) => 'custom' === $i->type ) );

	$map = array();
	foreach ( $src as $s ) {
		if ( '' === (string) $s->post_title ) {
			continue;
		}
		$match = null;
		if ( 'post_type' === $s->type ) {
			$match = $by_obj[ lai_translate_post_id( (int) $s->object_id, $lang ) ] ?? null;
		} elseif ( 'custom' === $s->type ) {
			$idx   = array_search( $s, $src_custom, true );
			$match = false === $idx ? null : ( $dst_custom[ $idx ] ?? null );
		}
		if ( $match && '' !== (string) $match->post_title ) {
			$map[ (string) $s->post_title ] = (string) $match->post_title;
		}
	}
	return $map;
}

/** Словарь подписей из JSON → [ lang => [ label => перевод ] ]. */
function lai_menu_strings_read_file( string $file ): array {
	$data = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
	if ( ! is_array( $data ) ) {
		return array();
	}
	$per_lang = array();
	foreach ( $data as $label => $tr ) {
		if ( '_comment' === $label || ! is_array( $tr ) ) {
			continue;
		}
		foreach ( $tr as $l => $t ) {
			$per_lang[ (string) $l ][ (string) $label ] = (string) $t;
		}
	}
	return $per_lang;
}

// ---------------- WP-CLI: переводы подписей ----------------
if ( defined( 'WP_CLI' ) && WP_CLI ) {

	WP_CLI::add_command( 'lai menu-strings', new class() {

		/**
		 * Таблица: какие подписи мастер-меню переведены на какие языки.
		 *
		 * ## EXAMPLES
		 *     wp lai menu-strings status
		 */
		public function status( $args, $assoc ) {
			$labels = lai_menu_labels();
			if ( ! $labels ) {
				WP_CLI::error( 'Нет мастер-меню. Задайте LAI_MASTER_MENUS в wp-config.php.' );
			}
			$langs = array_values( array_diff( lai_languages(), array( lai_menu_master_lang() ) ) );
			$rows  = array();
			$miss  = 0;
			foreach ( $labels as $label ) {
				$row = array( 'label' => $label );
				foreach ( $langs as $l ) {
					$t         = lai_translate_string_strict( $label, $l );
					$ok        = '' !== $t;
					$row[ $l ] = $ok ? $t : '—';
					$miss     += $ok ? 0 : 1;
				}
				$rows[] = $row;
			}
			WP_CLI\Utils\format_items( $assoc['format'] ?? 'table', $rows, array_merge( array( 'label' ), $langs ) );
			WP_CLI::log( "Без перевода: {$miss}" );
		}

		/**
		 * Взять подписи из уже готового меню языка (сопоставление по переводам записей и по позиции).
		 *
		 * ## OPTIONS
		 * --menu=<id>      : ID готового меню на языке (например Primary RU = 155)
		 * --lang=<slug>    : язык этого меню
		 * [--master=<id>]  : ID мастер-меню (по умолчанию первое из LAI_MASTER_MENUS)
		 * [--overwrite]    : перезаписать уже существующие переводы
		 * [--dry-run]
		 *
		 * ## EXAMPLES
		 *     wp lai menu-strings seed-from-menu --menu=155 --lang=ru --dry-run
		 *
		 * @subcommand seed-from-menu
		 */
		public function seed_from_menu( $args, $assoc ) {
			$lang   = (string) $assoc['lang'];
			$menus  = array_values( lai_menu_map() );
			$master = (int) ( $assoc['master'] ?? ( $menus[0] ?? 0 ) );
			$map    = lai_menu_strings_map_from_menu( $master, (int) $assoc['menu'], $lang );
			if ( ! $map ) {
				WP_CLI::error( 'Пустое мастер-меню или меню языка.' );
			}

			foreach ( $map as $from => $to ) {
				WP_CLI::log( sprintf( '  %-28s → %s', $from, $to ) );
			}
			if ( isset( $assoc['dry-run'] ) ) {
				WP_CLI::success( 'Пробный прогон: ' . count( $map ) . ' подписей.' );
				return;
			}
			$n = lai_save_string_translations( $lang, $map, isset( $assoc['overwrite'] ) );
			WP_CLI::success( "Записано переводов ({$lang}): {$n}" );
		}

		/**
		 * Импорт словаря подписей из JSON: { "Services": { "de": "Leistungen", ... }, ... }
		 *
		 * ## OPTIONS
		 * [<file>]     : путь к JSON (по умолчанию LAI_MENU_STRINGS_FILE)
		 * [--overwrite]  : перезаписать существующие переводы
		 *
		 * ## EXAMPLES
		 *     wp lai menu-strings import wp-content/lai-menu-strings.json
		 */
		public function import( $args, $assoc ) {
			$per_lang = lai_menu_strings_read_file( (string) ( $args[0] ?? LAI_MENU_STRINGS_FILE ) );
			if ( ! $per_lang ) {
				WP_CLI::error( 'Невалидный или пустой JSON.' );
			}
			foreach ( $per_lang as $l => $map ) {
				$n = lai_save_string_translations( $l, $map, isset( $assoc['overwrite'] ) );
				WP_CLI::log( "{$l}: записано {$n} из " . count( $map ) );
			}
			WP_CLI::success( 'Готово. Проверьте: wp lai menu-strings status' );
		}
	} );
}
