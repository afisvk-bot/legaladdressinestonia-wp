<!-- Calendly inline widget begin -->
<?php
// 1. Получаем слаг текущего языка из Polylang (по умолчанию 'en')
$cur_lang = function_exists( 'pll_current_language' ) ? pll_current_language() : 'en';

// 2. Карта ID событий для каждого языка
$calendly_ids = [
    'en' => 'd3kt-jbm-397',
    'ru' => 'dtfs-3xn-4q7',
    'uk' => 'dz9f-jhb-vcr',
    'ua' => 'dz9f-jhb-vcr',
    'de' => 'd33v-693-8js',
    'tr' => 'd3q5-bpz-g8t',
    'pt' => 'dv9j-p7n-gg2',
    'es' => 'dvs4-2w2-gjq',
    'zh' => 'dtq2-23z-44b',
    'ch' => 'dtq2-23z-44b',
    'fr' => 'dv55-y6y-8yd',
];

// 3. Берем нужный ID (если языка нет в массиве — берем английский по умолчанию)
$event_id = $calendly_ids[ $cur_lang ] ?? $calendly_ids['en'];
$calendly_url = "https://calendly.com/d/{$event_id}?hide_gdpr_banner=1&primary_color=0edf11";
?>

<!-- Calendly inline widget begin -->
<div class="calendly-inline-widget" data-url="<?php echo esc_url( $calendly_url ); ?>"></div>
<script type="text/javascript" src="https://assets.calendly.com/assets/external/widget.js" async></script>
<!-- Calendly inline widget end -->