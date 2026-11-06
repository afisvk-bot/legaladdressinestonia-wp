<?php
/**
 * Plugin Name: LAI — Article E-E-A-T (schema + author box)
 * Description: Дополняет разметку Rank Math для статей блога: чистый headline, wordCount, keywords, полноценный Person автора (должность, био на языке страницы, фото, LinkedIn). Выводит блок «Об авторе» и дату обновления в статье. Работает поверх Rank Math, без второго JSON-LD.
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

/** Профили в соцсетях, которых нет в профиле пользователя WP (slug автора => URL). */
function lai_author_same_as_extra(): array {
	return array(
		'jana-kamoza' => array( 'https://www.linkedin.com/in/jana-kamoza-international-business-legal' ),
	);
}

function lai_ab_lang(): string {
	$l = function_exists( 'pll_current_language' ) ? (string) pll_current_language() : '';
	return $l ?: substr( get_locale(), 0, 2 );
}

function lai_ab_label( string $key ): string {
	$t = array(
		'about'   => array( 'en' => 'About the author', 'ru' => 'Об авторе', 'uk' => 'Про автора', 'de' => 'Über den Autor', 'fr' => 'À propos de l’auteur', 'es' => 'Sobre el autor', 'pt' => 'Sobre o autor', 'tr' => 'Yazar hakkında', 'zh' => '关于作者' ),
		'updated' => array( 'en' => 'Updated', 'ru' => 'Обновлено', 'uk' => 'Оновлено', 'de' => 'Aktualisiert', 'fr' => 'Mis à jour', 'es' => 'Actualizado', 'pt' => 'Atualizado', 'tr' => 'Güncellendi', 'zh' => '更新于' ),
		'all'     => array( 'en' => 'All articles by the author', 'ru' => 'Все статьи автора', 'uk' => 'Усі статті автора', 'de' => 'Alle Artikel des Autors', 'fr' => 'Tous les articles de l’auteur', 'es' => 'Todos los artículos del autor', 'pt' => 'Todos os artigos do autor', 'tr' => 'Yazarın tüm makaleleri', 'zh' => '作者的全部文章' ),
	);
	$l = lai_ab_lang();
	return $t[ $key ][ $l ] ?? $t[ $key ]['en'];
}

/** Данные автора на языке страницы. */
function lai_author_profile( int $uid ): array {
	$lang = lai_ab_lang();
	$bio  = (string) get_user_meta( $uid, 'description_' . $lang, true );
	if ( '' === trim( $bio ) ) {
		$bio = (string) get_the_author_meta( 'description', $uid );
	}
	$bio = wp_strip_all_tags( $bio );
	for ( $i = 0; $i < 3 && $bio !== html_entity_decode( $bio, ENT_QUOTES, 'UTF-8' ); $i++ ) {
		$bio = html_entity_decode( $bio, ENT_QUOTES, 'UTF-8' ); // в профиле встречается «&amp;amp;»
	}
	$bio = trim( $bio );
	// должность = первое предложение био («… at eBusiness Solutions OÜ.»)
	$job = trim( (string) preg_split( '/\.\s|。/u', $bio, 2 )[0] );
	$slug = (string) get_the_author_meta( 'user_nicename', $uid );

	$img = '';
	$sla = get_user_meta( $uid, 'simple_local_avatar', true );
	if ( is_array( $sla ) && ! empty( $sla['full'] ) ) {
		$img = $sla['full'];
	}
	if ( ! $img ) {
		$img = (string) get_avatar_url( $uid, array( 'size' => 512 ) );
	}

	$same = array();
	foreach ( preg_split( '/[\s,]+/', (string) get_user_meta( $uid, 'additional_profile_urls', true ) ) as $u ) {
		if ( $u ) $same[] = $u;
	}
	foreach ( array( 'linkedin', 'facebook', 'twitter' ) as $k ) {
		$u = (string) get_user_meta( $uid, $k, true );
		if ( $u ) $same[] = 'twitter' === $k && 0 !== strpos( $u, 'http' ) ? 'https://x.com/' . ltrim( $u, '@' ) : $u;
	}
	$same = array_values( array_unique( array_filter( array_merge( $same, lai_author_same_as_extra()[ $slug ] ?? array() ), fn( $u ) => (bool) wp_http_validate_url( $u ) ) ) );

	return array(
		'name'  => (string) get_the_author_meta( 'display_name', $uid ),
		'bio'   => $bio,
		'job'   => $job && $job !== $bio ? $job : '',
		'image' => $img,
		'url'   => get_author_posts_url( $uid ),
		'same'  => $same,
	);
}

/* ---------------- JSON-LD: дополнение графа Rank Math ---------------- */
add_filter( 'rank_math/json_ld', function ( $data ) {
	if ( ! is_array( $data ) ) return $data;
	$single = is_singular( 'post' );
	if ( ! $single && ! is_author() ) return $data;

	$uid = $single ? (int) get_post_field( 'post_author', get_queried_object_id() ) : (int) get_queried_object_id();
	if ( ! $uid ) return $data;
	$p = lai_author_profile( $uid );

	foreach ( $data as $k => $node ) {
		if ( ! is_array( $node ) ) continue;
		$type = (array) ( $node['@type'] ?? array() );

		if ( in_array( 'Person', $type, true ) && ( $node['name'] ?? '' ) === $p['name'] ) {
			if ( $p['bio'] )  $data[ $k ]['description'] = $p['bio'];
			if ( $p['job'] )  $data[ $k ]['jobTitle'] = $p['job'];
			if ( $p['same'] ) $data[ $k ]['sameAs'] = $p['same'];
			if ( $p['image'] ) {
				$data[ $k ]['image'] = array( '@type' => 'ImageObject', '@id' => $p['image'], 'url' => $p['image'], 'caption' => $p['name'] );
			}
		}

		if ( $single && array_intersect( $type, array( 'BlogPosting', 'Article', 'NewsArticle' ) ) ) {
			$id    = get_queried_object_id();
			$title = trim( html_entity_decode( wp_strip_all_tags( get_the_title( $id ) ), ENT_QUOTES, 'UTF-8' ) );
			$data[ $k ]['headline'] = mb_strlen( $title ) > 110 ? mb_substr( $title, 0, 107 ) . '…' : $title;
			$data[ $k ]['name']     = $title;
			$data[ $k ]['url']      = get_permalink( $id );
			$text = wp_strip_all_tags( (string) get_post_field( 'post_content', $id ) );
			$data[ $k ]['wordCount'] = preg_match( '/\p{Han}/u', $text )
				? (int) preg_match_all( '/\p{Han}|[A-Za-z0-9]+/u', $text )
				: count( preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY ) );
			$kw = trim( (string) get_post_meta( $id, 'rank_math_focus_keyword', true ) );
			if ( $kw ) $data[ $k ]['keywords'] = array_map( 'trim', explode( ',', $kw ) );
			$data[ $k ]['author'] = array( '@id' => $data[ $k ]['author']['@id'] ?? $p['url'], 'name' => $p['name'], 'url' => $p['url'] );
		}

		// headline/name страницы — без «&mdash; Название сайта» в исходном виде
		if ( $single && in_array( 'WebPage', $type, true ) && isset( $node['name'] ) ) {
			$data[ $k ]['name'] = html_entity_decode( $node['name'], ENT_QUOTES, 'UTF-8' );
		}
	}
	return $data;
}, 99 );

/* ---------------- Видимая часть: «Обновлено» и блок «Об авторе» ---------------- */
function lai_article_updated_html( int $post_id = 0 ): string {
	$post_id = $post_id ?: get_the_ID();
	$pub = (int) get_post_time( 'U', true, $post_id );
	$mod = (int) get_post_modified_time( 'U', true, $post_id );
	if ( $mod - $pub < DAY_IN_SECONDS ) return '';
	return '<div class="lai-updated"><span>' . esc_html( lai_ab_label( 'updated' ) ) . ':</span> <time datetime="' . esc_attr( get_post_modified_time( 'c', true, $post_id ) ) . '">' . esc_html( get_the_modified_date( 'F j, Y', $post_id ) ) . '</time></div>';
}

function lai_author_box_html( int $post_id = 0 ): string {
	$post_id = $post_id ?: get_the_ID();
	$uid = (int) get_post_field( 'post_author', $post_id );
	if ( ! $uid ) return '';
	$p = lai_author_profile( $uid );
	$li = '';
	foreach ( $p['same'] as $u ) {
		if ( false !== strpos( $u, 'linkedin.com' ) ) { $li = $u; break; }
	}
	ob_start(); ?>
<aside class="lai-ab" aria-label="<?php echo esc_attr( lai_ab_label( 'about' ) ); ?>">
	<div class="lai-ab__photo"><img src="<?php echo esc_url( $p['image'] ); ?>" width="88" height="88" alt="<?php echo esc_attr( $p['name'] ); ?>" loading="lazy" decoding="async"></div>
	<div class="lai-ab__body">
		<div class="lai-ab__label"><?php echo esc_html( lai_ab_label( 'about' ) ); ?></div>
		<div class="lai-ab__name"><a href="<?php echo esc_url( $p['url'] ); ?>"><?php echo esc_html( $p['name'] ); ?></a></div>
		<?php if ( $p['bio'] ) : ?><p class="lai-ab__bio"><?php echo esc_html( $p['bio'] ); ?></p><?php endif; ?>
		<div class="lai-ab__links">
			<a href="<?php echo esc_url( $p['url'] ); ?>"><?php echo esc_html( lai_ab_label( 'all' ) ); ?> →</a>
			<?php if ( $li ) : ?><a href="<?php echo esc_url( $li ); ?>" target="_blank" rel="noopener me">LinkedIn</a><?php endif; ?>
		</div>
	</div>
</aside>
	<?php
	return (string) ob_get_clean();
}

add_action( 'wp_head', function () {
	if ( ! is_singular( 'post' ) ) return; ?>
<style id="lai-ab-css">
.lai-updated{font-size:13px;color:#667085;margin-top:6px}.lai-updated span{font-weight:600;color:#10192D}
.lai-ab{display:flex;gap:20px;align-items:flex-start;margin:48px 0 8px;padding:24px;border:1px solid #E4E7EC;border-radius:14px;background:#F9FAFB}
.lai-ab__photo img{width:88px;height:88px;border-radius:50%;object-fit:cover;display:block}
.lai-ab__label{font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:#31d44b;font-weight:600;margin-bottom:4px}
.lai-ab__name{font-size:20px;font-weight:600;line-height:1.3;margin-bottom:6px}.lai-ab__name a{color:#10192D;text-decoration:none;background:none}
.lai-ab__bio{font-size:14px;line-height:1.6;color:#475467;margin:0 0 10px}
.lai-ab__links{display:flex;flex-wrap:wrap;gap:8px 20px;font-size:14px}.lai-ab__links a{color:#10192D;text-decoration:underline;text-underline-offset:3px;background:none}
@media (max-width:575px){.lai-ab{flex-direction:column;gap:14px;padding:20px}}
</style>
	<?php
} );
