<?php
/**
 * Plugin Name: LAI — Table format
 * Description: Приводит таблицы и заголовки, вставленные из Google Docs/Word, к чистому виду при выводе: первая строка из жирных ячеек → <thead><th>, убираются <span style="font-weight:400">, <b> в заголовках h1–h6 снимается, <b> в ячейках → <strong>. База не меняется.
 * Version:     1.0.0
 *
 * ЧТО ДЕЛАЕТ (на лету, при выводе страницы — содержимое в редакторе не трогается)
 *   <h3><b>Ключевые факты</b></h3>              → <h3>Ключевые факты</h3>
 *   <span style="font-weight: 400;">текст</span> → текст
 *   первая строка, где ВСЕ ячейки целиком жирные   → <thead><tr><th>…</th></tr></thead>
 *   <b>в обычной ячейке</b>                       → <strong>…</strong> (или снимается, см. LAI_TABLE_KEEP_BOLD)
 * Таблицы, где <thead> уже есть, не перестраиваются. Повторная обработка ничего не меняет.
 *
 * Работает для основного контента (the_content) и WYSIWYG-полей ACF (блоки content-flex).
 * Отключить на время: define( 'LAI_TABLE_FORMAT', false ); в wp-config.php.
 */

defined( 'ABSPATH' ) || exit;

defined( 'LAI_TABLE_FORMAT' )    || define( 'LAI_TABLE_FORMAT', true );
defined( 'LAI_TABLE_KEEP_BOLD' ) || define( 'LAI_TABLE_KEEP_BOLD', true ); // false — жирный внутри ячеек убирается полностью

if ( LAI_TABLE_FORMAT ) {
	add_filter( 'the_content', 'lai_tbl_normalize_html', 20 );
	add_filter( 'acf/format_value/type=wysiwyg', function ( $value ) {
		return is_string( $value ) ? lai_tbl_normalize_html( $value ) : $value;
	}, 20 );
}

/** Точка входа: обрабатывает заголовки и таблицы во фрагменте HTML. */
function lai_tbl_normalize_html( $html ) {
	if ( ! is_string( $html ) || '' === $html ) {
		return $html;
	}
	$has_table   = false !== stripos( $html, '<table' );
	$has_heading = (bool) preg_match( '#<h[1-6]\b[^>]*>\s*<(?:b|strong)\b#i', $html );
	if ( ! $has_table && ! $has_heading ) {
		return $html;
	}

	if ( $has_heading ) {
		// <h3><b>Текст</b></h3> → <h3>Текст</h3> (только когда жирный обёрнут вокруг всего заголовка).
		$html = preg_replace_callback(
			'#<(h[1-6])(\b[^>]*)>\s*<(b|strong)\b[^>]*>((?:(?!</?\3\b).)*?)</\3>\s*</\1>#is',
			function ( $m ) {
				return '<' . $m[1] . $m[2] . '>' . $m[4] . '</' . $m[1] . '>';
			},
			$html
		);
	}

	if ( $has_table ) {
		$html = preg_replace_callback( '#<table\b.*?</table>#is', 'lai_tbl_normalize_table', $html );
	}
	return $html;
}

/** Одна таблица. При любой неожиданности возвращает исходный HTML без изменений. */
function lai_tbl_normalize_table( array $m ): string {
	$src = $m[0];
	if ( ! class_exists( 'DOMDocument' ) || substr_count( strtolower( $src ), '<table' ) > 1 ) {
		return $src; // вложенные таблицы не трогаем
	}

	$doc  = new DOMDocument( '1.0', 'UTF-8' );
	$prev = libxml_use_internal_errors( true );
	$ok   = $doc->loadHTML( '<?xml encoding="UTF-8"?><div>' . $src . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $prev );
	if ( ! $ok ) {
		return $src;
	}
	$xp    = new DOMXPath( $doc );
	$table = $xp->query( '//table' )->item( 0 );
	if ( ! $table ) {
		return $src;
	}

	// 1. Пустые «обёртки» Google Docs: <span style="font-weight: 400;">.
	foreach ( iterator_to_array( $xp->query( './/span', $table ) ) as $span ) {
		if ( lai_tbl_is_plain_span( $span ) ) {
			lai_tbl_unwrap( $span );
		}
	}

	// 2. Строка-шапка → <thead><th>.
	if ( ! $xp->query( './thead', $table )->length ) {
		$first = $xp->query( './tbody/tr[1] | ./tr[1]', $table )->item( 0 );
		if ( $first && lai_tbl_row_is_header( $first, $xp ) ) {
			$thead = $doc->createElement( 'thead' );
			$table->insertBefore( $thead, $xp->query( './tbody | ./tr', $table )->item( 0 ) );
			$thead->appendChild( $doc->createTextNode( "\n" ) );
			$ws = $first->previousSibling; // пробельный узел перед строкой, чтобы в tbody не оставалась пустая строка
			$thead->appendChild( $first ); // переносим строку из tbody
			$thead->appendChild( $doc->createTextNode( "\n" ) );
			if ( $ws && XML_TEXT_NODE === $ws->nodeType && '' === trim( $ws->nodeValue ) && $ws->nextSibling && XML_TEXT_NODE === $ws->nextSibling->nodeType ) {
				$ws->parentNode->removeChild( $ws );
			}
			$thead->parentNode->insertBefore( $doc->createTextNode( "\n" ), $thead->nextSibling );

			foreach ( iterator_to_array( $xp->query( './td | ./th', $first ) ) as $cell ) {
				$th = 'th' === $cell->nodeName ? $cell : lai_tbl_rename( $cell, 'th' );
				foreach ( iterator_to_array( $xp->query( './/b | .//strong', $th ) ) as $b ) {
					lai_tbl_unwrap( $b );
				}
			}
		}
	}

	// 3. Оставшийся жирный в ячейках.
	foreach ( iterator_to_array( $xp->query( './/b', $table ) ) as $b ) {
		LAI_TABLE_KEEP_BOLD ? lai_tbl_rename( $b, 'strong' ) : lai_tbl_unwrap( $b );
	}

	$out = $doc->saveHTML( $table );
	return ( is_string( $out ) && '' !== $out ) ? $out : $src;
}

/** <span> без атрибутов или только со style вида font-weight:400/normal — можно снять. */
function lai_tbl_is_plain_span( DOMElement $span ): bool {
	foreach ( $span->attributes as $attr ) {
		if ( ! in_array( $attr->name, array( 'style', 'dir' ), true ) ) {
			return false;
		}
	}
	$style = strtolower( (string) $span->getAttribute( 'style' ) );
	foreach ( array_filter( array_map( 'trim', explode( ';', $style ) ) ) as $decl ) {
		$parts = array_map( 'trim', explode( ':', $decl, 2 ) );
		$prop  = $parts[0];
		$val   = $parts[1] ?? '';
		$neutral = ( 'font-weight' === $prop && in_array( $val, array( '400', 'normal' ), true ) )
			|| ( 'font-style' === $prop && 'normal' === $val )
			|| ( 'text-decoration' === $prop && in_array( $val, array( 'none', 'none solid' ), true ) )
			|| ( 'vertical-align' === $prop && 'baseline' === $val )
			|| ( 'white-space' === $prop && in_array( $val, array( 'pre-wrap', 'normal' ), true ) );
		if ( ! $neutral ) {
			return false;
		}
	}
	return true;
}

/** Строка — шапка, если в каждой ячейке есть текст и он целиком жирный (или ячейки уже <th>). */
function lai_tbl_row_is_header( DOMElement $tr, DOMXPath $xp ): bool {
	$cells = $xp->query( './td | ./th', $tr );
	if ( ! $cells->length ) {
		return false;
	}
	foreach ( $cells as $cell ) {
		if ( 'th' === $cell->nodeName ) {
			continue;
		}
		$all = lai_tbl_squash( $cell->textContent );
		if ( '' === $all ) {
			return false;
		}
		$bold = '';
		foreach ( $xp->query( './/b[not(ancestor::b) and not(ancestor::strong)] | .//strong[not(ancestor::b) and not(ancestor::strong)]', $cell ) as $b ) {
			$bold .= $b->textContent;
		}
		if ( lai_tbl_squash( $bold ) !== $all ) {
			return false;
		}
	}
	return true;
}

function lai_tbl_squash( string $s ): string {
	return trim( preg_replace( '/\s+/u', ' ', str_replace( "\xC2\xA0", ' ', $s ) ) );
}

/** Убрать тег, оставив содержимое. */
function lai_tbl_unwrap( DOMNode $node ): void {
	$parent = $node->parentNode;
	if ( ! $parent ) {
		return;
	}
	while ( $node->firstChild ) {
		$parent->insertBefore( $node->firstChild, $node );
	}
	$parent->removeChild( $node );
}

/** Сменить имя тега, сохранив атрибуты и содержимое. */
function lai_tbl_rename( DOMElement $node, string $name ): DOMElement {
	$new = $node->ownerDocument->createElement( $name );
	foreach ( iterator_to_array( $node->attributes ) as $attr ) {
		$new->setAttribute( $attr->name, $attr->value );
	}
	while ( $node->firstChild ) {
		$new->appendChild( $node->firstChild );
	}
	$node->parentNode->replaceChild( $new, $node );
	return $new;
}
