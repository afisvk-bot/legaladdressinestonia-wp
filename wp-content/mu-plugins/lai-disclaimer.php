<?php
/**
 * Plugin Name: LAI — Disclaimer
 * Description: Общий дисклеймер на страницах услуг (service, licensing, industry, target) на языке страницы. Три варианта оформления, место вставки настраивается.
 * Version:     1.1.0
 *
 * НАСТРОЙКИ (wp-config.php, по желанию)
 *   define( 'LAI_DISCLAIMER_STYLE', 'card' );  // card — плашка | note — сноска | details — сворачиваемый блок
 *   define( 'LAI_DISCLAIMER_POS', 'image' );   // image — сразу под главным изображением | faq — перед FAQ (иначе перед финальным CTA) | top — после оглавления | bottom — в самом конце
 *   define( 'LAI_DISCLAIMER', false );         // выключить везде
 * Выключить на отдельной странице: произвольное поле lai_disclaimer = off.
 * Предпросмотр (на staging или для залогиненных редакторов): ?disc=card|note|details&disc_pos=image|faq|top|bottom
 * Тексты — в lai_disclaimer_texts(). Требует фильтр lai_service_body в single-service.php темы.
 */

defined( 'ABSPATH' ) || exit;

defined( 'LAI_DISCLAIMER' )       || define( 'LAI_DISCLAIMER', true );
defined( 'LAI_DISCLAIMER_STYLE' ) || define( 'LAI_DISCLAIMER_STYLE', 'note' );
defined( 'LAI_DISCLAIMER_POS' )   || define( 'LAI_DISCLAIMER_POS', 'image' );

function lai_disclaimer_texts(): array {
	return array(
		'en' => array( 'Disclaimer', 'This material is for general information only and does not constitute tax or employment law advice. Tax consequences depend on the type of contract, the employee’s tax residency and where the work is performed. We review each specific situation individually.' ),
		'ru' => array( 'Дисклеймер', 'Материал носит общий информационный характер и не является налоговой или трудово-правовой консультацией. Налоговые последствия зависят от вида договора, налогового резидентства работника и места выполнения работы. Конкретную ситуацию мы проверяем индивидуально.' ),
		'uk' => array( 'Застереження', 'Матеріал має загальний інформаційний характер і не є податковою чи трудово-правовою консультацією. Податкові наслідки залежать від виду договору, податкового резидентства працівника та місця виконання роботи. Конкретну ситуацію ми перевіряємо індивідуально.' ),
		'de' => array( 'Haftungsausschluss', 'Dieser Beitrag dient ausschließlich der allgemeinen Information und stellt keine Steuer- oder arbeitsrechtliche Beratung dar. Die steuerlichen Folgen hängen von der Art des Vertrags, der steuerlichen Ansässigkeit des Arbeitnehmers und dem Ort der Tätigkeit ab. Ihre konkrete Situation prüfen wir individuell.' ),
		'fr' => array( 'Avertissement', 'Ce contenu est fourni à titre d’information générale et ne constitue pas un conseil fiscal ni un conseil en droit du travail. Les conséquences fiscales dépendent du type de contrat, de la résidence fiscale du salarié et du lieu d’exécution du travail. Nous examinons chaque situation au cas par cas.' ),
		'es' => array( 'Aviso legal', 'Este contenido tiene carácter meramente informativo y no constituye asesoramiento fiscal ni laboral. Las consecuencias fiscales dependen del tipo de contrato, de la residencia fiscal del trabajador y del lugar donde se realiza el trabajo. Analizamos cada situación de forma individual.' ),
		'pt' => array( 'Aviso legal', 'Este conteúdo tem caráter meramente informativo e não constitui aconselhamento fiscal nem jurídico-laboral. As consequências fiscais dependem do tipo de contrato, da residência fiscal do trabalhador e do local onde o trabalho é prestado. Analisamos cada situação de forma individual.' ),
		'tr' => array( 'Yasal uyarı', 'Bu içerik yalnızca genel bilgilendirme amaçlıdır ve vergi veya iş hukuku danışmanlığı niteliği taşımaz. Vergisel sonuçlar sözleşmenin türüne, çalışanın vergi mukimliğine ve işin yapıldığı yere bağlıdır. Her somut durumu ayrı ayrı inceliyoruz.' ),
		'zh' => array( '免责声明', '本文内容仅供一般参考，不构成税务或劳动法方面的咨询意见。税务后果取决于合同类型、员工的税务居民身份以及工作履行地点。我们会针对具体情况进行单独评估。' ),
	);
}

function lai_disclaimer_types(): array {
	return array( 'service', 'licensing', 'industry', 'target' );
}

/** Вариант оформления и место с учётом предпросмотра. */
function lai_disclaimer_opts(): array {
	$style = LAI_DISCLAIMER_STYLE;
	$pos   = LAI_DISCLAIMER_POS;
	$can_preview = 'production' !== wp_get_environment_type() || current_user_can( 'edit_posts' );
	if ( $can_preview && isset( $_GET['disc'] ) && in_array( $_GET['disc'], array( 'card', 'note', 'details', 'off' ), true ) ) {
		$style = $_GET['disc'];
	}
	if ( $can_preview && isset( $_GET['disc_pos'] ) && in_array( $_GET['disc_pos'], array( 'image', 'faq', 'top', 'bottom' ), true ) ) {
		$pos = $_GET['disc_pos'];
	}
	return array( $style, $pos );
}

function lai_disclaimer_enabled( int $post_id ): bool {
	return LAI_DISCLAIMER
		&& is_singular( lai_disclaimer_types() )
		&& 'off' !== strtolower( (string) get_post_meta( $post_id, 'lai_disclaimer', true ) )
		&& 'off' !== lai_disclaimer_opts()[0];
}

function lai_disclaimer_html( string $style, string $pos = '' ): string {
	$lang  = function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'slug' ) : 'en';
	$texts = lai_disclaimer_texts();
	list( $label, $text ) = $texts[ $lang ] ?? $texts['en'];

	$icon = '<svg class="lai-disc__icon" width="22" height="22" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="12" fill="currentColor"/><rect x="10.75" y="5.5" width="2.5" height="8.5" rx="1.25" fill="#fff"/><circle cx="12" cy="17.6" r="1.5" fill="#fff"/></svg>';

	switch ( $style ) {
		case 'note':
			$inner = '<p class="lai-disc__note"><span class="lai-disc__mark" aria-hidden="true">*</span><span><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $text ) . '</span></p>';
			break;
		case 'details':
			$inner = '<details class="lai-disc__details"><summary>' . $icon . '<span>' . esc_html( $label ) . '</span></summary><p>' . esc_html( $text ) . '</p></details>';
			break;
		default:
			$style = 'card';
			$inner = '<div class="lai-disc__card" role="note">' . $icon . '<div><p class="lai-disc__title">' . esc_html( $label ) . '</p><p class="lai-disc__text">' . esc_html( $text ) . '</p></div></div>';
	}

	return "\n<!-- LAI disclaimer -->\n" . '<div class="row lai-disc lai-disc--' . esc_attr( $style ) . '"><div class="col-12 col-sm-12 col-md-11 col-lg-8 col-xl-8 mx-lg-auto">' . $inner . '</div></div>' . "\n";
}

/** Вставка в тело страницы услуги (фильтр из single-service.php). */
add_filter( 'lai_service_body', function ( $body, $post_id = 0 ) {
	if ( ! lai_disclaimer_enabled( (int) ( $post_id ?: get_the_ID() ) ) ) {
		return $body;
	}
	list( $style, $pos ) = lai_disclaimer_opts();
	if ( 'image' === $pos ) {
		return $body; // выводится в хуке lai_service_after_image
	}
	$block = lai_disclaimer_html( $style, $pos );

	if ( 'top' === $pos ) {
		return $block . $body;
	}
	if ( 'faq' === $pos ) {
		// Перед строкой с FAQ, иначе перед финальным CTA.
		foreach ( array( 'service-faq', 'service-final-cta' ) as $marker ) {
			$at = strpos( $body, $marker );
			if ( false === $at ) {
				continue;
			}
			$row = strrpos( substr( $body, 0, $at ), '<div class="row' );
			if ( false !== $row ) {
				return substr( $body, 0, $row ) . $block . substr( $body, $row );
			}
		}
	}
	return $body . $block;
}, 10, 2 );

/** Под главным изображением страницы услуги (хук из single-service.php). */
add_action( 'lai_service_after_image', function ( $post_id = 0 ) {
	if ( ! lai_disclaimer_enabled( (int) ( $post_id ?: get_the_ID() ) ) ) {
		return;
	}
	list( $style, $pos ) = lai_disclaimer_opts();
	if ( 'image' === $pos ) {
		echo lai_disclaimer_html( $style, $pos ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
} );

add_action( 'wp_head', function () {
	if ( ! LAI_DISCLAIMER || ! is_singular( lai_disclaimer_types() ) ) {
		return;
	}
	?>
<style id="lai-disclaimer-css">
.lai-disc{margin:1.5rem 0 3rem}
.lai-disc p{margin:0}
.lai-disc__icon{flex:0 0 22px;color:#31D44B}
/* Плашка */
.lai-disc__card{display:flex;gap:1rem;align-items:flex-start;background:#f4fbf5;border:1px solid rgba(49,212,75,.25);border-left:4px solid #31D44B;border-radius:0 1.25rem 1.25rem 0;padding:1.25rem 1.5rem}
.lai-disc__title{font-weight:600;font-size:1rem;line-height:1.4;color:#10192D;margin-bottom:.35rem!important}
.lai-disc__text{font-size:.875rem;line-height:1.6;color:#4a5363}
/* Сноска */
.lai-disc--note{margin:1rem 0 2.5rem}
.lai-disc__note{display:flex;gap:.5rem;border-top:1px solid #EFEFEF;padding-top:1rem;font-size:.8125rem;line-height:1.6;color:#6b7280}
.lai-disc__note strong{font-weight:600;color:#10192D}
.lai-disc--at-image{margin:0 0 1.25rem}
.lai-disc--at-image .lai-disc__note{border-top:0;padding-top:0}
.lai-disc__mark{color:#31D44B;font-weight:600;font-size:1rem;line-height:1.3}
/* Сворачиваемый блок — в стиле оглавления */
.lai-disc__details{border:solid 1px rgb(35 35 35 / 4%);border-radius:1.25rem;box-shadow:1px 1px 150px 0 rgb(91 91 91 / 7%);background:#fff;padding:1.25rem 1.5rem}
.lai-disc__details summary{display:flex;align-items:center;gap:.75rem;cursor:pointer;font-weight:600;color:#10192D;list-style:none}
.lai-disc__details summary::-webkit-details-marker{display:none}
.lai-disc__details summary::after{content:"▸";margin-left:auto;transition:transform .15s}
.lai-disc__details[open] summary::after{transform:rotate(90deg)}
.lai-disc__details p{font-size:.875rem;line-height:1.6;color:#4a5363;margin-top:.875rem}
@media (max-width:576px){.lai-disc__card{padding:1rem 1.125rem;gap:.75rem}.lai-disc__details{padding:1rem 1.125rem}}
</style>
	<?php
} );
