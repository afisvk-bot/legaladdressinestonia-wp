<?php
/**
 * Plugin Name: LAI — Layout (единая сетка и типографика услуг и блога)
 * Description: Приводит страницы услуг (service, licensing, industry, target) и статьи блога к сетке главной: контейнер 1320, внутреннее поле 48 px, две ширины — «широкая» (1224: заголовок, хлебные крошки, обложка) и «текстовая» (800: текст, таблицы, оглавление, FAQ). Единая шкала заголовков, размер текста 16/26, один стиль текстовых ссылок. Тему не меняет — только CSS поверх неё.
 * Version:     1.0.0
 */
defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function () {
	if ( ! is_singular( array( 'post', 'service', 'licensing', 'industry', 'target' ) ) || isset( $_GET['lai_old'] ) ) return; // ?lai_old=1 — посмотреть страницу без правок
	echo "<style id=\"lai-layout\">\n" . lai_layout_css() . "\n</style>\n";
}, 100 );

function lai_layout_css(): string {
	// T — текстовые блоки (статья, текстовые модули услуги); C — карточки «картинка + текст»
	$T = 'body .content-page.single-page :is(.article-content,.block-text,.block-cta)';
	$C = 'body .content-page.services .service-img-text';
	$F = 'body .content-page.services .service-faq';
	return <<<CSS
:root{--lai-ink:#10192d;--lai-green:#31d44b;--lai-green-d:#1c9a30;--lai-muted:#5b6475;--lai-line:#e6e8ec;--lai-text:800px;--lai-gut:48px}

/* ── Сетка: шапка, крошки, обложка — широкая колонка с полем 48 px, как секции главной ── */
@media (min-width:992px){
 body .content-page.single-page .head-title{padding-left:var(--lai-gut);padding-right:var(--lai-gut)}
 body .content-page.single-page .col-lg-11.mx-lg-auto{flex:0 0 100%;max-width:100%;padding-left:var(--lai-gut);padding-right:var(--lai-gut)}
 /* текстовая колонка — одна ширина для статьи, модулей услуги, оглавления и мета-строки */
 body .content-page.single-page :is(.col-lg-8,.col-xl-8).mx-lg-auto,body .content-page.single-page .col-lg-9.col-xl-8.mx-lg-auto{flex:0 0 100%;max-width:calc(var(--lai-text) + 30px);margin-left:auto;margin-right:auto}
 /* FAQ: внутреннее поле карточки 48 px → текст ответов стоит ровно по текстовой колонке */
 body .content-page.single-page .col-lg-9.mx-lg-auto:not(.col-xl-8){flex:0 0 100%;max-width:calc(var(--lai-text) + 2*var(--lai-gut) + 30px);margin-left:auto;margin-right:auto}
 $F{padding:var(--lai-gut)}
}
body .content-page.single-page .rank-math-breadcrumb p{margin:0;font-size:14px;line-height:20px}
body .content-page.single-page .data{padding-left:0;padding-right:0}

/* ── Заголовки: шкала главной (H1 45 / H2 секции 40 / H3 28) + уровни внутри текста ── */
body .content-page.single-page .head-title h1{font-size:45px;line-height:1.15}
$T h2{font-size:32px;line-height:1.25;margin:56px 0 16px}
$T h3{font-size:24px;line-height:1.3;margin:40px 0 12px}
$T h4{font-size:20px;line-height:1.35;margin:32px 0 8px}
$T :is(h2,h3,h4):first-child{margin-top:0}
$T :is(h2,h3,h4) b,$T :is(h2,h3,h4) strong{font-weight:inherit}
$C :is(h2,h3,.h2){font-size:32px;line-height:1.25;margin:0 0 16px}

/* ── Текст: 16/26, основной цвет; в карточках — приглушённый, но читаемый ── */
$T :is(p,li,td,th,blockquote){font-size:16px;line-height:26px}
$T p{color:var(--lai-ink);margin:0 0 16px}
$T :is(ul,ol){margin:16px 0 24px}
$T ul li{font-size:16px;line-height:26px;color:var(--lai-ink);padding-left:34px}
$T ul li:not(:last-child),$T ol li:not(:last-child){margin-bottom:10px}
$T ul li:before{top:2px}
$T ul li b,$T ul li strong{color:var(--lai-ink);font-weight:600}
$T table :is(td,th){font-size:14px;line-height:22px}
$C p,$C ul li{color:var(--lai-muted);font-size:16px;line-height:26px}
$C ul li:before{top:2px}
$C ul li{padding-left:34px}
$F .card-body,$F .card-body p{font-size:16px;line-height:26px;color:var(--lai-muted)}
body .content-page.services .service-step .steps .step-number .step-txt{font-size:18px;line-height:1.4}
body .content-page.services .service-tariffs .tariffs .packag ul.list-checked li{font-size:14px;line-height:20px;padding-left:24px}
body .content-page.services .service-tariffs .tariffs .packag ul.list-checked li:before{top:2px}
body .content-page.services .service-tariffs .tariffs .packag :is(.txt-grey,.txt-green){font-size:13px;line-height:18px}

/* ── Ссылки в тексте: один стиль — тёмный текст + зелёное подчёркивание, при наведении зелёный ── */
:is($T,$C,$F) a:not(.btn):not(.ez-toc-link):not(.ez-toc-btn){color:var(--lai-ink);text-decoration:underline;text-decoration-color:var(--lai-green);text-decoration-thickness:2px;text-underline-offset:4px;transition:color .2s,text-decoration-color .2s}
:is($T,$C,$F) a:not(.btn):hover{color:var(--lai-green-d);text-decoration-color:var(--lai-green-d)}
:is($T,$C,$F) a:not(.btn):before{content:none!important}
:is($T,$C,$F) a:not(.btn):focus-visible{outline:2px solid var(--lai-green);outline-offset:2px;border-radius:2px}

/* ── Мобильные ── */
@media (max-width:991.98px){
 body .content-page.single-page .head-title h1{font-size:30px;line-height:1.2}
 $T h2{font-size:26px;line-height:1.25;margin:40px 0 12px}
 $T h3{font-size:21px;line-height:1.3;margin:32px 0 10px}
 $C :is(h2,h3,.h2){font-size:26px}
 $F{padding:32px 20px}
 body .content-page.services .service-step .steps .step-number .step-txt{font-size:16px}
}
CSS;
}
