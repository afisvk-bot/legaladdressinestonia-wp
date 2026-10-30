<?php
/**
 * Plugin Name: Rank Math — Author SEO per Language (Polylang)
 * Description: Добавляет в профиль автора поля SEO Title и SEO Description отдельно для каждого языка Polylang и подставляет их на странице автора вместо общего шаблона Rank Math.
 * Version: 1.0
 * Author: Custom snippet
 *
 * Установка: положите этот файл в wp-content/mu-plugins/
 * (папку mu-plugins нужно создать, если её ещё нет). MU-плагины
 * подключаются автоматически, без активации, и не теряются при
 * смене темы — в отличие от кода в functions.php темы.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. Добавляем поля в профиль пользователя — по одному Title/Description на каждый активный язык.
 */
add_action( 'show_user_profile', 'rmpl_render_author_seo_fields' );
add_action( 'edit_user_profile', 'rmpl_render_author_seo_fields' );

function rmpl_render_author_seo_fields( $user ) {
	if ( ! function_exists( 'pll_languages_list' ) ) {
		return;
	}

	$languages = pll_languages_list( array( 'fields' => 'slug' ) );
	if ( empty( $languages ) ) {
		return;
	}
	?>
	<h2>Rank Math SEO — по языкам</h2>
	<table class="form-table">
		<?php foreach ( $languages as $lang ) :
			$title_key = 'rank_math_title_' . $lang;
			$desc_key  = 'rank_math_description_' . $lang;
			$title_val = get_user_meta( $user->ID, $title_key, true );
			$desc_val  = get_user_meta( $user->ID, $desc_key, true );
			?>
			<tr>
				<th><label for="<?php echo esc_attr( $title_key ); ?>">
					SEO Title (<?php echo esc_html( strtoupper( $lang ) ); ?>)
				</label></th>
				<td>
					<input type="text" class="regular-text"
						   name="<?php echo esc_attr( $title_key ); ?>"
						   id="<?php echo esc_attr( $title_key ); ?>"
						   value="<?php echo esc_attr( $title_val ); ?>" />
					<p class="description">Оставьте пустым, чтобы использовать общий шаблон Rank Math.</p>
				</td>
			</tr>
			<tr>
				<th><label for="<?php echo esc_attr( $desc_key ); ?>">
					SEO Description (<?php echo esc_html( strtoupper( $lang ) ); ?>)
				</label></th>
				<td>
					<textarea class="large-text" rows="3"
							  name="<?php echo esc_attr( $desc_key ); ?>"
							  id="<?php echo esc_attr( $desc_key ); ?>"
					><?php echo esc_textarea( $desc_val ); ?></textarea>
					<p class="description">Оставьте пустым, чтобы использовать общий шаблон Rank Math.</p>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php
}

/**
 * 2. Сохраняем значения полей при обновлении профиля.
 */
add_action( 'personal_options_update', 'rmpl_save_author_seo_fields' );
add_action( 'edit_user_profile_update', 'rmpl_save_author_seo_fields' );

function rmpl_save_author_seo_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	if ( ! function_exists( 'pll_languages_list' ) ) {
		return;
	}

	$languages = pll_languages_list( array( 'fields' => 'slug' ) );

	foreach ( $languages as $lang ) {
		$title_key = 'rank_math_title_' . $lang;
		$desc_key  = 'rank_math_description_' . $lang;

		if ( isset( $_POST[ $title_key ] ) ) {
			update_user_meta( $user_id, $title_key, sanitize_text_field( wp_unslash( $_POST[ $title_key ] ) ) );
		}
		if ( isset( $_POST[ $desc_key ] ) ) {
			update_user_meta( $user_id, $desc_key, sanitize_textarea_field( wp_unslash( $_POST[ $desc_key ] ) ) );
		}
	}
}

/**
 * 3. Подставляем значение для текущего языка на странице автора.
 */
add_filter( 'rank_math/frontend/title', 'rmpl_filter_author_title' );

function rmpl_filter_author_title( $title ) {
	if ( ! is_author() || ! function_exists( 'pll_current_language' ) ) {
		return $title;
	}

	$author_id = get_queried_object_id();
	$lang      = pll_current_language();
	$custom    = get_user_meta( $author_id, 'rank_math_title_' . $lang, true );

	return $custom ? $custom : $title;
}

add_filter( 'rank_math/frontend/description', 'rmpl_filter_author_description' );

function rmpl_filter_author_description( $description ) {
	if ( ! is_author() || ! function_exists( 'pll_current_language' ) ) {
		return $description;
	}

	$author_id = get_queried_object_id();
	$lang      = pll_current_language();
	$custom    = get_user_meta( $author_id, 'rank_math_description_' . $lang, true );

	return $custom ? $custom : $description;
}
