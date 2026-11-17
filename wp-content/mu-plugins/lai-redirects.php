<?php
/**
 * Plugin Name: LAI — Redirects
 * Description: 301-редиректы с адресов объединённых страниц-дублей на основные страницы. Срабатывает только если по старому адресу 404 (копия удалена).
 * Version:     1.3.0
 *
 * Карта: путь (без домена) => ID основной записи. ID одинаковы на staging и проде (одна база).
 * Цель берётся через get_permalink(), поэтому редирект не ломается при смене slug основной страницы.
 */

defined( 'ABSPATH' ) || exit;

function lai_redirect_map(): array {
	return array(
		// 2026-11-06: копии-дубли на одном языке → основная страница группы переводов
		'/fur-start-ups-2/'                                                        => 4545, // DE: основная получила slug /fur-start-ups/
		'/contabilidade-para-residentes-eletronicos-na-estonia/'                  => 4516, // PT
		'/registo-de-empresas-por-procuraco/'                                      => 4471, // PT
		'/industry/cryptomonnaies-web3/'                                           => 2461, // FR
		'/target/une-soci-t-estonienne-pour-les-ressortissants-hors-ue/'           => 2376, // FR
		'/target/uma-empresa-est-nia-para-fundadores-de-fora-da-ue/'               => 2378, // PT
		'/licensing/holding-crypto-igaming/'                                       => 2526, // ZH
		'/registering-an-mt-in-estonia-2/'                                         => 3641, // ZH: английская копия → китайский перевод
		// 2026-11-10: ES-версия «Восстановления бухучёта» получила испанский адрес
		'/clean-up-2/'                                                             => 4597, // ES
		// 2026-11-10: в статье о визах (EN/RU) ссылка на несуществующую /industry/startups/
		// 2026-11-17: EN «Восстановление учёта» — исходный адрес /clean-up/ (новый slug accounting-restoration-in-estonia)
		'/clean-up/'                                                               => 4578, // EN
		'/restablecimiento-de-la-contabilidad-en-estonia-2/'                       => 4578, // EN (был испанский slug)
		'/industry/startups/'                                                      => 2398, // EN SaaS, IT & developers
	);
}

add_action( 'template_redirect', function () {
	if ( ! is_404() ) {
		return;
	}
	$path = trailingslashit( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) );
	$map  = lai_redirect_map();
	if ( isset( $map[ $path ] ) && 'publish' === get_post_status( $map[ $path ] ) ) {
		$url = get_permalink( $map[ $path ] );
		// Polylang может подставить префикс языка из cookie посетителя (/ru/…) — ставим язык самой страницы.
		if ( function_exists( 'PLL' ) && function_exists( 'pll_get_post_language' ) && isset( PLL()->links_model ) ) {
			$lang = PLL()->model->get_language( (string) pll_get_post_language( $map[ $path ] ) );
			if ( $lang ) $url = PLL()->links_model->switch_language_in_link( $url, $lang );
		}
		wp_safe_redirect( $url, 301, 'LAI Redirects' );
		exit;
	}
}, 1 );
