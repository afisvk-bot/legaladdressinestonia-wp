<?php
/**
 * Plugin Name: LAI — i18n helpers (Polylang / WPML)
 * Description: Общий слой абстракции над Polylang и WPML. Используется модулями меню и перелинковки.
 * Version:     1.0.0
 * Author:      legaladdressinestonia.com
 *
 * Установка: положить в wp-content/mu-plugins/
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'lai_ml_engine' ) ) :

	/**
	 * Какой мультиязычный плагин активен: polylang | wpml | none.
	 * Не кэшируем статически: mu-plugins грузятся раньше обычных плагинов.
	 */
	function lai_ml_engine(): string {
		if ( function_exists( 'pll_current_language' ) ) {
			return 'polylang';
		}
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return 'wpml';
		}
		return 'none';
	}

	function lai_default_lang(): string {
		switch ( lai_ml_engine() ) {
			case 'polylang':
				return (string) pll_default_language( 'slug' );
			case 'wpml':
				return (string) apply_filters( 'wpml_default_language', null );
		}
		return substr( get_locale(), 0, 2 );
	}

	function lai_current_lang(): string {
		switch ( lai_ml_engine() ) {
			case 'polylang':
				$lang = pll_current_language( 'slug' );
				return $lang ? (string) $lang : lai_default_lang();
			case 'wpml':
				return (string) apply_filters( 'wpml_current_language', null );
		}
		return lai_default_lang();
	}

	/** Слаги всех активных языков. */
	function lai_languages(): array {
		switch ( lai_ml_engine() ) {
			case 'polylang':
				return (array) pll_languages_list( array( 'fields' => 'slug' ) );
			case 'wpml':
				$langs = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );
				return is_array( $langs ) ? array_keys( $langs ) : array();
		}
		return array( lai_default_lang() );
	}

	/** Язык конкретной записи/страницы. */
	function lai_post_lang( int $post_id ): string {
		switch ( lai_ml_engine() ) {
			case 'polylang':
				return (string) pll_get_post_language( $post_id, 'slug' );
			case 'wpml':
				$d = apply_filters( 'wpml_post_language_details', null, $post_id );
				return ( is_array( $d ) && ! empty( $d['language_code'] ) ) ? (string) $d['language_code'] : '';
		}
		return lai_default_lang();
	}

	/** ID перевода записи на язык $lang, 0 — если перевода нет. */
	function lai_translate_post_id( int $post_id, string $lang ): int {
		if ( $post_id <= 0 ) {
			return 0;
		}
		switch ( lai_ml_engine() ) {
			case 'polylang':
				return (int) pll_get_post( $post_id, $lang );
			case 'wpml':
				return (int) apply_filters( 'wpml_object_id', $post_id, get_post_type( $post_id ), false, $lang );
		}
		return $post_id;
	}

	/** ID перевода термина (рубрики/метки/кастомной таксономии), 0 — если нет. */
	function lai_translate_term_id( int $term_id, string $taxonomy, string $lang ): int {
		if ( $term_id <= 0 ) {
			return 0;
		}
		switch ( lai_ml_engine() ) {
			case 'polylang':
				return (int) pll_get_term( $term_id, $lang );
			case 'wpml':
				return (int) apply_filters( 'wpml_object_id', $term_id, $taxonomy, false, $lang );
		}
		return $term_id;
	}

	/** Главная страница языковой версии. */
	function lai_home_url( string $lang ): string {
		switch ( lai_ml_engine() ) {
			case 'polylang':
				return (string) pll_home_url( $lang );
			case 'wpml':
				return (string) apply_filters( 'wpml_permalink', home_url( '/' ), $lang );
		}
		return home_url( '/' );
	}

	/** Опубликована и не под паролем — только на такие страницы можно ставить ссылки. */
	function lai_is_public_post( int $post_id ): bool {
		return $post_id > 0
			&& 'publish' === get_post_status( $post_id )
			&& '' === (string) get_post_field( 'post_password', $post_id );
	}

	/**
	 * Канонический URL записи. Отличается от get_permalink() тем, что учитывает
	 * особенности сайта через фильтр 'lai_permalink' (см. совместимость ниже).
	 */
	function lai_permalink( int $post_id ): string {
		$url = (string) get_permalink( $post_id );
		return (string) apply_filters( 'lai_permalink', $url, $post_id );
	}

	/**
	 * ID записи по её URL на ЛЮБОМ языке. url_to_postid() на странице /es/ ищет
	 * только среди испанских записей (Polylang фильтрует запрос), поэтому
	 * английская ссылка из мастер-меню не находилась.
	 */
	function lai_url_to_post_id( string $url ): int {
		static $cache = array();
		if ( isset( $cache[ $url ] ) ) {
			return $cache[ $url ];
		}
		$path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
		if ( '' === $path ) {
			return $cache[ $url ] = 0;
		}
		$parts = explode( '/', $path );
		if ( count( $parts ) > 1 && in_array( $parts[0], lai_languages(), true ) ) {
			array_shift( $parts ); // языковой префикс
		}
		global $wpdb;
		$slug  = sanitize_title( (string) end( $parts ) );
		$types = array_values( get_post_types( array( 'public' => true ) ) );
		$in    = implode( ',', array_fill( 0, count( $types ), '%s' ) );
		$ids   = $wpdb->get_col( $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_status = 'publish' AND post_type IN ($in) ORDER BY ID",
			array_merge( array( $slug ), $types )
		) );
		$id = 0;
		foreach ( $ids as $candidate ) {
			// при одинаковых слагах — тот, чей полный путь совпадает
			if ( wp_make_link_relative( untrailingslashit( (string) get_permalink( $candidate ) ) ) === '/' . implode( '/', $parts )
				|| wp_make_link_relative( untrailingslashit( lai_permalink( (int) $candidate ) ) ) === '/' . implode( '/', $parts ) ) {
				$id = (int) $candidate;
				break;
			}
		}
		if ( ! $id && 1 === count( $ids ) ) {
			$id = (int) $ids[0];
		}
		if ( ! $id ) {
			$id = (int) url_to_postid( $url );
		}
		return $cache[ $url ] = $id;
	}

	/**
	 * Совместимость с темой LegalAddress: услуги и лицензии живут в корне без
	 * языкового префикса (адрес с /ru/… отвечает 301). Тема срезает префикс
	 * функцией i10v_strip_lang_prefix() — используем её же, чтобы наши ссылки
	 * совпадали с каноническими и не давали лишних редиректов.
	 */
	add_filter( 'lai_permalink', function ( $url, $post_id ) {
		$types = (array) apply_filters( 'lai_unprefixed_post_types', array( 'service', 'licensing' ) );
		if ( function_exists( 'i10v_strip_lang_prefix' ) && in_array( get_post_type( $post_id ), $types, true ) ) {
			$url = i10v_strip_lang_prefix( $url );
		}
		return $url;
	}, 10, 2 );

	/**
	 * Записать переводы строк в Polylang (то же, что «Языки → Переводы строк»).
	 * $map = [ 'исходная строка' => 'перевод', ... ]. Возвращает число записанных.
	 * Существующие переводы перезаписываются только при $overwrite = true.
	 */
	function lai_save_string_translations( string $lang_slug, array $map, bool $overwrite = false ): int {
		if ( 'polylang' !== lai_ml_engine() || ! class_exists( 'PLL_MO' ) ) {
			return 0;
		}
		$lang = PLL()->model->get_language( $lang_slug );
		if ( ! $lang ) {
			return 0;
		}
		$mo = new PLL_MO();
		$mo->import_from_db( $lang );
		$n = 0;
		foreach ( $map as $source => $translation ) {
			$source      = (string) $source;
			$translation = trim( (string) $translation );
			if ( '' === $source || '' === $translation ) {
				continue;
			}
			$existing = $mo->translate_if_any( $source );
			if ( ! $overwrite && '' !== (string) $existing && $existing !== $source ) {
				continue;
			}
			$mo->add_entry( $mo->make_entry( $source, $translation ) );
			$n++;
		}
		if ( $n ) {
			$mo->export_to_db( $lang );
		}
		return $n;
	}

	/** Регистрация строки для перевода (Polylang → «Переводы строк», WPML → String Translation). */
	function lai_register_string( string $name, string $value, string $group = 'LAI' ): void {
		if ( '' === trim( $value ) ) {
			return;
		}
		if ( 'polylang' === lai_ml_engine() && function_exists( 'pll_register_string' ) ) {
			pll_register_string( $name, $value, $group );
		} elseif ( 'wpml' === lai_ml_engine() ) {
			do_action( 'wpml_register_single_string', $group, $name, $value );
		}
	}

	/**
	 * Перевод строки или '' если перевода нет (в отличие от pll_translate_string,
	 * которая возвращает оригинал и не отличает «нет перевода» от «перевод = оригинал»).
	 */
	function lai_translate_string_strict( string $value, string $lang ): string {
		static $mos = array();
		if ( 'polylang' === lai_ml_engine() && class_exists( 'PLL_MO' ) ) {
			if ( ! array_key_exists( $lang, $mos ) ) {
				$obj          = PLL()->model->get_language( $lang );
				$mos[ $lang ] = null;
				if ( $obj ) {
					$mos[ $lang ] = new PLL_MO();
					$mos[ $lang ]->import_from_db( $obj );
				}
			}
			return $mos[ $lang ] ? (string) $mos[ $lang ]->translate_if_any( $value ) : '';
		}
		$t = lai_translate_string( $value, $value, $lang );
		return $t !== $value ? $t : '';
	}

	function lai_translate_string( string $name, string $value, string $lang, string $group = 'LAI' ): string {
		if ( 'polylang' === lai_ml_engine() && function_exists( 'pll_translate_string' ) ) {
			return (string) pll_translate_string( $value, $lang );
		}
		if ( 'wpml' === lai_ml_engine() ) {
			return (string) apply_filters( 'wpml_translate_single_string', $value, $group, $name, $lang );
		}
		return $value;
	}

endif;
