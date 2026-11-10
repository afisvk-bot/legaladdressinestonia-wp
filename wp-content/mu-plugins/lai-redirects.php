<?php
/**
 * Plugin Name: LAI — Redirects
 * Description: 301-редиректы с адресов объединённых страниц-дублей на основные страницы. Срабатывает только если по старому адресу 404 (копия удалена).
 * Version:     1.2.0
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
		wp_safe_redirect( get_permalink( $map[ $path ] ), 301, 'LAI Redirects' );
		exit;
	}
}, 1 );
