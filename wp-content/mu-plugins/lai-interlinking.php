<?php
/**
 * Plugin Name: LAI — Multilingual auto interlinking
 * Description: Контекстная перелинковка по матрице ключевых слов. Строгая языковая изоляция, Silo-ограничения, лимиты на страницу, кэш. Работает с the_content и с полями ACF.
 * Version:     1.1.0
 * Requires:    lai-i18n-helpers.php
 *
 * ПРИНЦИПЫ
 * - Матрица — JSON (wp-content/lai-link-matrix.json). Цель задаётся ОДНИМ ID записи
 *   на любом языке; модуль сам берёт её перевод на языке текущей страницы.
 * - Нет перевода цели → ссылки нет. Ссылка с /es/ на /en/ невозможна by design.
 * - Текст берётся из the_content (блог) И из WYSIWYG-полей ACF (услуги, лицензии,
 *   отрасли, target — на этом сайте весь коммерческий текст во flexible content).
 *   Лимиты общие на всю страницу: одна цель = одна ссылка, не больше N ссылок.
 * - Ссылки не ставятся: в заголовках, существующих ссылках, коде, кнопках, формах,
 *   nav, блоках с классом .lai-no-autolink, на саму себя.
 * - Записи с мета-полем _lai_no_autolinks = 1 не обрабатываются.
 * - В БД ничего не пишется: отключили модуль — контент чистый.
 */

defined( 'ABSPATH' ) || exit;

// ---------------- Настройки (можно переопределить в wp-config.php) ----------------
defined( 'LAI_LINK_MATRIX_FILE' ) || define( 'LAI_LINK_MATRIX_FILE', WP_CONTENT_DIR . '/lai-link-matrix.json' );
defined( 'LAI_LINK_POST_TYPES' )  || define( 'LAI_LINK_POST_TYPES', 'post,page,service,licensing,industry,target' );
defined( 'LAI_LINK_ACF_FIELDS' )  || define( 'LAI_LINK_ACF_FIELDS', 'content,text_left,text_right' ); // WYSIWYG-поля для обработки
defined( 'LAI_LINK_CACHE_TTL' )   || define( 'LAI_LINK_CACHE_TTL', DAY_IN_SECONDS );

function lai_link_post_types(): array {
	return array_values( array_filter( array_map( 'trim', explode( ',', (string) LAI_LINK_POST_TYPES ) ) ) );
}

function lai_link_acf_fields(): array {
	return array_values( array_filter( array_map( 'trim', explode( ',', (string) LAI_LINK_ACF_FIELDS ) ) ) );
}

function lai_link_matrix(): array {
	static $m = null;
	if ( null !== $m ) {
		return $m;
	}
	$m = array( 'settings' => array(), 'rules' => array(), 'hash' => '' );

	if ( ! is_readable( LAI_LINK_MATRIX_FILE ) ) {
		return $m;
	}
	$raw  = (string) file_get_contents( LAI_LINK_MATRIX_FILE );
	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) {
		error_log( 'LAI interlinking: invalid JSON in ' . LAI_LINK_MATRIX_FILE );
		return $m;
	}

	$m['settings'] = wp_parse_args( (array) ( $data['settings'] ?? array() ), array(
		'max_links_per_post'  => 6,
		'max_links_per_block' => 1,
		'link_class'          => 'lai-autolink',
	) );
	$m['rules'] = array_values( array_filter( (array) ( $data['rules'] ?? array() ), 'is_array' ) );
	usort( $m['rules'], function ( $a, $b ) {
		return (int) ( $b['priority'] ?? 0 ) <=> (int) ( $a['priority'] ?? 0 );
	} );
	$m['hash'] = md5( $raw );
	return $m;
}

// Сброс кэша при изменении любых записей (смена URL, снятие с публикации и т.п.).
function lai_il_salt(): string {
	return (string) get_option( 'lai_il_salt', '1' );
}
add_action( 'save_post', function ( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	update_option( 'lai_il_salt', (string) microtime( true ), false );
} );

/** Состояние страницы: какие цели уже слинкованы и сколько ссылок поставлено. */
function &lai_il_state( int $post_id ): array {
	static $state = array();
	if ( ! isset( $state[ $post_id ] ) ) {
		$state[ $post_id ] = array( 'targets' => array(), 'total' => 0 );
	}
	return $state[ $post_id ];
}

/** Обрабатываем только основную запись страницы, на фронтенде. */
function lai_il_current_post(): ?WP_Post {
	if ( is_admin() || is_feed() || ! is_singular() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return null;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post || ! in_array( $post->post_type, lai_link_post_types(), true ) ) {
		return null;
	}
	if ( get_post_meta( $post->ID, '_lai_no_autolinks', true ) ) {
		return null;
	}
	return $post;
}

// ---------------- Источники текста ----------------
add_filter( 'the_content', function ( $content ) {
	if ( ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$post = lai_il_current_post();
	if ( ! $post || get_the_ID() !== $post->ID ) {
		return $content;
	}
	return lai_autolink_html( (string) $content, $post, 'the_content' );
}, 99 );

add_filter( 'acf/format_value/type=wysiwyg', function ( $value, $post_id, $field ) {
	if ( ! is_string( $value ) || '' === trim( $value ) ) {
		return $value;
	}
	$name = (string) ( $field['_name'] ?? $field['name'] ?? '' );
	if ( ! in_array( $name, lai_link_acf_fields(), true ) ) {
		return $value;
	}
	$post = lai_il_current_post();
	if ( ! $post || (int) $post_id !== (int) $post->ID ) {
		return $value;
	}
	return lai_autolink_html( $value, $post, 'acf:' . ( $field['name'] ?? $name ) );
}, 99, 3 );

/** Общая точка: вставить ссылки в фрагмент HTML с учётом лимитов всей страницы. */
function lai_autolink_html( string $html, WP_Post $post, string $context ): string {
	if ( '' === trim( $html ) || ! class_exists( 'DOMDocument' ) ) {
		return $html;
	}
	$matrix = lai_link_matrix();
	if ( ! $matrix['rules'] ) {
		return $html;
	}

	$state = &lai_il_state( (int) $post->ID );
	$max   = max( 0, (int) $matrix['settings']['max_links_per_post'] );
	if ( $state['total'] >= $max ) {
		return $html;
	}

	$lang      = lai_post_lang( (int) $post->ID ) ?: lai_current_lang();
	$cache_key = 'lai_il_' . md5( implode( '|', array(
		$post->ID, $lang, $context, $matrix['hash'], lai_il_salt(), md5( $html ),
		implode( ',', array_keys( $state['targets'] ) ), $state['total'],
	) ) );
	$cached = get_transient( $cache_key );
	if ( is_array( $cached ) && isset( $cached['html'] ) ) {
		foreach ( (array) $cached['targets'] as $t ) {
			$state['targets'][ (int) $t ] = true;
		}
		$state['total'] += (int) $cached['added'];
		return (string) $cached['html'];
	}

	$rules = lai_resolve_rules( $post, $lang, $matrix['rules'], $html, $state['targets'] );
	$added = array();
	$out   = $rules
		? lai_inject_links( $html, $rules, $matrix['settings'], $max - $state['total'], $added )
		: $html;

	foreach ( $added as $t ) {
		$state['targets'][ $t ] = true;
	}
	$state['total'] += count( $added );

	set_transient( $cache_key, array( 'html' => $out, 'targets' => $added, 'added' => count( $added ) ), (int) LAI_LINK_CACHE_TTL );
	return $out;
}

/** Превращает строки матрицы в готовые правила для языка страницы. */
function lai_resolve_rules( WP_Post $post, string $lang, array $rules, string $html, array $used_targets ): array {
	$resolved = array();
	$seen     = $used_targets;

	foreach ( $rules as $rule ) {
		$anchors = $rule['anchors'][ $lang ] ?? array();
		$anchors = array_values( array_filter( array_map( 'trim', (array) $anchors ), 'strlen' ) );
		if ( ! $anchors ) {
			continue;
		}

		$target = lai_translate_post_id( (int) ( $rule['target'] ?? 0 ), $lang );
		if ( ! lai_is_public_post( $target ) || $target === (int) $post->ID || isset( $seen[ $target ] ) ) {
			continue;
		}
		// Жёсткая языковая изоляция: цель обязана быть на языке страницы.
		if ( 'none' !== lai_ml_engine() && lai_post_lang( $target ) !== $lang ) {
			continue;
		}
		if ( ! lai_rule_silo_allows( $post, $rule, $lang ) ) {
			continue;
		}

		$url = lai_permalink( $target );
		if ( ! $url ) {
			continue;
		}
		// Ручная ссылка на эту цель уже есть во фрагменте (с префиксом языка или без).
		$path_a = wp_make_link_relative( untrailingslashit( $url ) );
		$path_b = wp_make_link_relative( untrailingslashit( (string) get_permalink( $target ) ) );
		if ( ( '' !== $path_a && false !== strpos( $html, $path_a . '/' ) ) || ( '' !== $path_b && false !== strpos( $html, $path_b . '/' ) ) ) {
			continue;
		}

		usort( $anchors, function ( $a, $b ) {
			return mb_strlen( $b ) <=> mb_strlen( $a );
		} );

		$resolved[]      = array( 'id' => (string) ( $rule['id'] ?? '' ), 'target' => $target, 'url' => $url, 'anchors' => $anchors );
		$seen[ $target ] = true;
	}
	return $resolved;
}

/**
 * Silo-логика (все ID — мастер-языка, переводы находятся автоматически):
 *  pillar: true            — можно ссылаться со всех страниц языка;
 *  from_post_types: [...]  — только со страниц этих типов (например ["post"] — только из блога);
 *  silo_term: ID           — только из записей этой рубрики (и подрубрик), silo_taxonomy — её таксономия;
 *  silo_page: ID           — только с этой страницы и её дочерних;
 *  ничего не задано        — глобальное правило.
 */
function lai_rule_silo_allows( WP_Post $post, array $rule, string $lang ): bool {
	if ( ! empty( $rule['from_post_types'] ) && ! in_array( $post->post_type, (array) $rule['from_post_types'], true ) ) {
		return false;
	}
	if ( ! empty( $rule['pillar'] ) ) {
		return true;
	}
	if ( empty( $rule['silo_term'] ) && empty( $rule['silo_page'] ) ) {
		return true;
	}

	if ( ! empty( $rule['silo_term'] ) ) {
		$tax = (string) ( $rule['silo_taxonomy'] ?? 'category' );
		$tid = lai_translate_term_id( (int) $rule['silo_term'], $tax, $lang );
		if ( $tid ) {
			$post_terms = wp_get_post_terms( $post->ID, $tax, array( 'fields' => 'ids' ) );
			foreach ( is_wp_error( $post_terms ) ? array() : $post_terms as $pt ) {
				if ( (int) $pt === $tid || term_is_ancestor_of( $tid, (int) $pt, $tax ) ) {
					return true;
				}
			}
		}
	}

	if ( ! empty( $rule['silo_page'] ) ) {
		$pid = lai_translate_post_id( (int) $rule['silo_page'], $lang );
		if ( $pid && ( $pid === (int) $post->ID || in_array( $pid, array_map( 'intval', get_post_ancestors( $post ) ), true ) ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Вставляет ссылки. $budget — сколько ещё ссылок можно поставить на странице.
 * $added получает ID целей, на которые поставлены ссылки.
 */
function lai_inject_links( string $content, array $rules, array $s, int $budget, array &$added ): string {
	$max_block = max( 1, (int) $s['max_links_per_block'] );
	if ( $budget <= 0 ) {
		return $content;
	}

	$prev = libxml_use_internal_errors( true );
	$dom  = new DOMDocument( '1.0', 'UTF-8' );
	$ok   = $dom->loadHTML(
		'<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><div id="lai-root">'
		. $content . '</div></body></html>'
	);
	libxml_clear_errors();
	libxml_use_internal_errors( $prev );
	if ( ! $ok ) {
		return $content;
	}
	$dom->encoding = 'UTF-8';

	$xpath = new DOMXPath( $dom );
	$root  = $xpath->query( '//div[@id="lai-root"]' )->item( 0 );
	if ( ! $root ) {
		return $content;
	}

	$skip = apply_filters( 'lai_autolink_skip_tags', array(
		'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'script', 'style', 'code', 'pre', 'kbd',
		'button', 'label', 'select', 'option', 'textarea', 'nav', 'form', 'figcaption', 'sup', 'sub',
	) );
	$cond  = implode( ' and ', array_map( function ( $t ) {
		return "not(ancestor::{$t})";
	}, $skip ) );
	$cond .= ' and not(ancestor::*[contains(concat(" ", normalize-space(@class), " "), " lai-no-autolink ")])';
	$query = './/text()[normalize-space() != "" and ' . $cond . ']';

	$block_tags  = array( 'p', 'li', 'td', 'th', 'blockquote', 'dd', 'div' );
	$block_count = array();
	$total       = 0;

	foreach ( $rules as $rule ) {
		if ( $total >= $budget ) {
			break;
		}
		foreach ( $rule['anchors'] as $anchor ) {
			$anchor  = preg_replace( '/\s+/u', ' ', html_entity_decode( $anchor, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
			$quoted  = preg_quote( $anchor, '/' );
			$quoted  = str_replace( ' ', '[\s\x{00A0}]+', $quoted );
			$quoted  = preg_replace( "/['’]/u", "['’]", $quoted ); // Estonya’da = Estonya'da
			$pattern = '/(?<![\p{L}\p{N}_-])' . $quoted . '(?![\p{L}\p{N}_-])/iu';

			$linked = false;
			foreach ( $xpath->query( $query, $root ) as $node ) {
				$block = lai_closest_block( $node, $block_tags );
				$bkey  = $block ? $block->getNodePath() : '_root';
				if ( ( $block_count[ $bkey ] ?? 0 ) >= $max_block ) {
					continue;
				}

				$text = (string) $node->nodeValue;
				if ( ! preg_match( $pattern, $text, $m, PREG_OFFSET_CAPTURE ) ) {
					continue;
				}
				list( $match, $offset ) = $m[0];

				$a = $dom->createElement( 'a' );
				$a->setAttribute( 'href', $rule['url'] );
				$a->setAttribute( 'class', (string) $s['link_class'] );
				$a->appendChild( $dom->createTextNode( $match ) );

				$frag   = $dom->createDocumentFragment();
				$before = substr( $text, 0, $offset );
				$after  = substr( $text, $offset + strlen( $match ) );
				if ( '' !== $before ) {
					$frag->appendChild( $dom->createTextNode( $before ) );
				}
				$frag->appendChild( $a );
				if ( '' !== $after ) {
					$frag->appendChild( $dom->createTextNode( $after ) );
				}
				$node->parentNode->replaceChild( $frag, $node );

				$block_count[ $bkey ] = ( $block_count[ $bkey ] ?? 0 ) + 1;
				$total++;
				$added[] = (int) $rule['target'];
				$linked  = true;
				break;
			}
			if ( $linked ) {
				break;
			}
		}
	}

	if ( 0 === $total ) {
		return $content;
	}

	$out = '';
	foreach ( $root->childNodes as $child ) {
		$out .= $dom->saveHTML( $child );
	}
	return $out;
}

function lai_closest_block( DOMNode $node, array $tags ): ?DOMNode {
	for ( $n = $node->parentNode; $n && XML_ELEMENT_NODE === $n->nodeType; $n = $n->parentNode ) {
		if ( 'lai-root' === $n->getAttribute( 'id' ) ) {
			return null;
		}
		if ( in_array( strtolower( $n->nodeName ), $tags, true ) ) {
			return $n;
		}
	}
	return null;
}

// ---------------- WP-CLI: аудит покрытия матрицы ----------------
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * По каждому правилу и языку: есть ли перевод цели, сколько анкоров,
	 * на скольких страницах этого языка встречается хотя бы один анкор;
	 * предупреждает о каннибализации (один анкор → разные цели).
	 *
	 *   wp lai links-audit
	 *   wp lai links-audit --format=csv > audit.csv
	 */
	WP_CLI::add_command( 'lai links-audit', function ( $args, $assoc ) {
		global $wpdb;
		$m       = lai_link_matrix();
		$rows    = array();
		$anchors = array();
		$fields  = lai_link_acf_fields();

		// Тексты всех опубликованных страниц по языкам (post_content + WYSIWYG-поля ACF).
		$texts = array();
		$ids   = get_posts( array( 'post_type' => lai_link_post_types(), 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'lang' => '' ) );
		foreach ( $ids as $id ) {
			$l = lai_post_lang( (int) $id );
			$t = (string) get_post_field( 'post_content', $id );
			foreach ( get_post_meta( $id ) as $k => $v ) {
				foreach ( $fields as $f ) {
					if ( $k === $f || preg_match( '/_\d+_' . preg_quote( $f, '/' ) . '$/', $k ) ) {
						$t .= ' ' . $v[0];
					}
				}
			}
			$texts[ $l ][ $id ] = wp_strip_all_tags( $t );
		}

		foreach ( $m['rules'] as $r ) {
			foreach ( lai_languages() as $lang ) {
				$t   = lai_translate_post_id( (int) ( $r['target'] ?? 0 ), $lang );
				$anc = array_values( array_filter( array_map( 'trim', (array) ( $r['anchors'][ $lang ] ?? array() ) ), 'strlen' ) );
				foreach ( $anc as $a ) {
					$anchors[ $lang ][ mb_strtolower( $a ) ][] = $r['id'] ?? '?';
				}
				$pages = 0;
				foreach ( $texts[ $lang ] ?? array() as $pid => $txt ) {
					if ( $pid === $t ) {
						continue;
					}
					foreach ( $anc as $a ) {
						$a = str_replace( array( "'", '’' ), "['’]", preg_quote( html_entity_decode( $a, ENT_QUOTES, 'UTF-8' ), '/' ) );
						if ( preg_match( '/(?<![\p{L}\p{N}_-])' . $a . '(?![\p{L}\p{N}_-])/iu', $txt ) ) {
							$pages++;
							break;
						}
					}
				}
				$rows[] = array(
					'rule'      => $r['id'] ?? '?',
					'lang'      => $lang,
					'target'    => $t ?: '-',
					'url'       => $t ? wp_make_link_relative( lai_permalink( $t ) ) : '-',
					'anchors'   => count( $anc ),
					'pages_hit' => $pages,
					'status'    => ! lai_is_public_post( $t ) ? 'НЕТ ПЕРЕВОДА ЦЕЛИ' : ( ! $anc ? 'НЕТ АНКОРОВ' : ( $pages ? 'OK' : 'АНКОР НЕ ВСТРЕЧАЕТСЯ' ) ),
				);
			}
		}

		WP_CLI\Utils\format_items( $assoc['format'] ?? 'table', $rows, array( 'rule', 'lang', 'target', 'url', 'anchors', 'pages_hit', 'status' ) );

		foreach ( $anchors as $lang => $list ) {
			foreach ( $list as $a => $rule_ids ) {
				if ( count( array_unique( $rule_ids ) ) > 1 ) {
					WP_CLI::warning( "[{$lang}] анкор «{$a}» ведёт на разные цели: " . implode( ', ', array_unique( $rule_ids ) ) );
				}
			}
		}
	} );
}
