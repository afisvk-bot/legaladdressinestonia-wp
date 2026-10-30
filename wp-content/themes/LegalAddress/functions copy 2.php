<?php
add_action( 'wp_enqueue_scripts', 'get_styles' );
function get_styles() {
  $style_ver = filemtime( get_stylesheet_directory() . '/style.css' );
  wp_enqueue_style( 'style', get_stylesheet_directory_uri().'/CSS/style.css', [], $style_ver, 'all');
  wp_enqueue_style( 'bootstrap', get_stylesheet_directory_uri().'/CSS/bootstrap.css', [], $style_ver, 'all');
  wp_enqueue_style( 'slick', get_template_directory_uri(). '/CSS/slick.min.css' );
}
add_action( 'wp_enqueue_scripts', 'get_js' );
function get_js() {
  $js_ver = filemtime( get_template_directory() . '/js/main.js' );
  wp_enqueue_script( 'jquery224', get_template_directory_uri() . '/js/jquery-2.2.4.min.js', [], $js_ver, false);
  wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js', ['jquery224'], $js_ver, true);
  wp_enqueue_script( 'slick', get_template_directory_uri() . '/js/slick.js', ['jquery224'], $js_ver, true);
  wp_enqueue_script( 'lazysizes', get_template_directory_uri() . '/js/lazysizes.min.js', ['jquery224'], $js_ver, true);
  wp_enqueue_script( 'main', get_template_directory_uri() . '/js/main.js', ['jquery224'], $js_ver, true);
}
// Register menu's
function register_theme_menus() {
  register_nav_menus(
    array(
      'primary-menu'   => __('Primary menu'),
      'lan-menu'       => __('Lan menu'),
      'menu-footer'    => __('Menu Footer'),
      'menu-footer-2'  => __('Menu Footer 2'),
      'menu-soc'       => __('Menu Social'),
      'mobile_menu'    => __('Mobile Menu')
    )
  );
}
add_action('init', 'register_theme_menus');

// Register Custom Navigation Walker
require_once get_template_directory() . '/wp-bootstrap-navwalker.php';

// Register Walker Mobile menu
class Mobile_Menu_Walker extends Walker_Nav_Menu {
    public function start_lvl(&$output, $depth = 0, $args = null) {
        $output .= '<ul class="mob-sub-menu" style="display:none;">';
    }
    public function end_lvl(&$output, $depth = 0, $args = null) {
        $output .= '</ul>';
    }
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $has_children = in_array('menu-item-has-children', $item->classes);

        $output .= '<li';

        if (!$has_children) {
            $output .= ' itemprop="name"';
        }

        $output .= '>';

        // Первый уровень с дочерними
        if ($depth == 0 && $has_children) {

            $output .= '<div class="link">';
            $output .= '<a href="javascript:void(0);">';
            $output .= esc_html($item->title);
            $output .= '</a>';
            $output .= '<span class="fm-arrow"></span>';
            $output .= '</div>';

        } else {

            $output .= sprintf(
                '<a itemprop="url" href="%s">%s</a>',
                esc_url($item->url),
                esc_html($item->title)
            );

        }
    }
    public function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= '</li>';
    }
}

// Add Font
function legaladdress_google_fonts() {
  wp_enqueue_style(
    'google-font-poppins',
    'https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap',
    array(),
    null
  );
}
add_action( 'wp_enqueue_scripts', 'legaladdress_google_fonts');

// Breadcrumb - Home translite
add_filter( 'rank_math/frontend/breadcrumb/items', function( $crumbs, $class ) {
    $labels = [
      'en' => 'Home',
      'ru' => 'Главная',
      'uk' => 'Головна',
      'de' => 'Startseite',
      'fr' => 'Daccueil',
      'es' => 'Página inicial',
      'zh' => '主页',
      'pt' => 'Página inicial',
      'tr' => 'Ev',
    ];
    $lang = function_exists('pll_current_language') ? pll_current_language() : 'ru';
    if ( isset( $labels[ $lang ] ) ) {
        $crumbs[0][0] = $labels[ $lang ];
    }
    $crumbs[0][1] = function_exists('pll_home_url') ? pll_home_url( $lang ) : home_url();
    return $crumbs;
}, 10, 2 );




// 1) Убираем /services/ из отображаемой ссылки
add_filter('post_type_link', function ($link, $post) {
    if ($post->post_type === 'service') {
        $link = str_replace(home_url('/services/'), home_url('/'), $link);
    }
    return $link;
}, 10, 2);

// 2) Точные rewrite-правила под каждый реально существующий service-пост
//    (по аналогии с тем, как WP сам генерирует правила для Page).
//    Список путей кэшируем в transient, чтобы не гонять get_posts() на каждый запрос.
add_action('init', function () {

    $paths = get_transient('i10v_service_paths');

    if ($paths === false) {
        $ids = get_posts([
            'post_type'      => 'service',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]);

        $paths = [];
        foreach ($ids as $id) {
            $uri = get_page_uri($id); // полный путь с учётом иерархии, без префикса CPT
            if ($uri) {
                $paths[] = $uri;
            }
        }

        set_transient('i10v_service_paths', $paths, HOUR_IN_SECONDS);
    }

    foreach ($paths as $path) {
        $quoted = preg_quote($path, '#');

        add_rewrite_rule(
            '^' . $quoted . '/?$',
            'index.php?service=' . $path,
            'top'
        );
    }

}, 20); // после регистрации CPT (обычно приоритет 10)


// Сбрасываем кэш путей и flush-им правила при сохранении/удалении service-постов
add_action('save_post_service', 'i10v_flush_service_rules');
add_action('deleted_post', 'i10v_flush_service_rules');
add_action('wp_trash_post', 'i10v_flush_service_rules');

function i10v_flush_service_rules($post_id = null) {
    if ($post_id && get_post_type($post_id) !== 'service') {
        return;
    }
    delete_transient('i10v_service_paths');
    // flush обязательно после того, как правила будут пересобраны на следующем init
    set_transient('i10v_service_flush_needed', 1, HOUR_IN_SECONDS);
}

add_action('init', function () {
    if (get_transient('i10v_service_flush_needed')) {
        flush_rewrite_rules();
        delete_transient('i10v_service_flush_needed');
    }
}, 30); // после того, как правила выше уже добавлены


add_filter('wp_nav_menu_objects', function ($items) {
    // Произвольные ссылки выводим ровно так, как введены в админке:
    // Polylang на рендере дописывает им языковой префикс (/ru/…), а услуги
    // живут в корне без префикса — получался лишний 301 с каждой ссылки.
    // Пункты языкового переключателя (#pll_switcher) не трогаем — их URL
    // Polylang подставляет сам.
    $langs = function_exists('pll_languages_list') ? pll_languages_list() : array();

    foreach ($items as $item) {
        if ($item->object === 'custom') {
            $orig = get_post_meta($item->ID, '_menu_item_url', true);
            if ($orig && strpos($orig, 'pll_switcher') === false) {
                $item->url = $orig;
            }
            continue;
        }
        // Пункты-услуги: их канонический адрес — корень без языкового
        // префикса (services живут в SEO-силосе, /ru/… отвечает 301),
        // а get_permalink через Polylang префикс дописывает — срезаем.
        if ($langs && in_array($item->object, array('service', 'licensing'), true)) {
            // Схему не проверяем: home_url() может быть http, а ссылка https.
            $item->url = preg_replace(
                '#^((?:https?://[^/]+)?)/(?:' . implode('|', array_map('preg_quote', $langs)) . ')/#',
                '$1/',
                $item->url
            );
        }
    }

    return $items;
}, 20);

// Contact Forms auto-detected
function get_localized_cf7_form() {
    $forms_map = [
        'en' => [ 'id' => '616a252', 'title' => 'form_EN', 'class' => 'EN' ],
        'ru' => [ 'id' => 'b8271e9', 'title' => 'form_RU', 'class' => 'RU' ],
        'uk' => [ 'id' => '9fbb0a8', 'title' => 'form_UK', 'class' => 'UK' ],
        'de' => [ 'id' => 'a90e33c', 'title' => 'form_DE', 'class' => 'DE' ],
        'fr' => [ 'id' => 'f230389', 'title' => 'form_FR', 'class' => 'FR' ],
        'pt' => [ 'id' => '66dcdde', 'title' => 'form_PT', 'class' => 'PT' ],
        'tr' => [ 'id' => 'd448ac2', 'title' => 'form_TR', 'class' => 'TR' ],
        'es' => [ 'id' => 'bbfc08b', 'title' => 'form_ES', 'class' => 'ES' ],
        'cn' => [ 'id' => '243c53c', 'title' => 'form_CN', 'class' => 'CN' ],
    ];
    $current_lang = function_exists( 'pll_current_language' )
        ? pll_current_language()
        : 'ru';
    $form = $forms_map[ $current_lang ] ?? $forms_map['ru'];
    return [
        'shortcode'   => sprintf( '[contact-form-7 id="%s" title="%s"]', $form['id'], $form['title'] ),
        'modal_class' => $form['class'],
    ];
}



// Переводы - Группа 'Author page'
add_action( 'init', 'la_register_author_page_strings' );

function la_register_author_page_strings() {

    if ( ! function_exists( 'pll_register_string' ) ) {
        return; // Polylang не активен — просто выходим.
    }

    $group = 'Author page';

    pll_register_string( 'author_title',         'Автор: %s',            $group );
    pll_register_string( 'author_about',         'Об авторе:',           $group );
    pll_register_string( 'author_website',       'Вебсайт:',             $group );
    pll_register_string( 'author_total_articles','Всего статей:',        $group );
    pll_register_string( 'author_all_articles',  'Все статьи автора:',   $group );
    pll_register_string( 'author_no_articles',   'Нет статей.',          $group );
    pll_register_string( 'author_min',           'мин.',                 $group );
    pll_register_string( 'pagination_prev',      '« Назад',              $group );
    pll_register_string( 'pagination_next',      'Далее »',              $group );

    // Хлебные крошки Rank Math (см. фильтр ниже)
    pll_register_string( 'breadcrumb_home',           'Главная',              'Rank Math breadcrumbs' );
    pll_register_string( 'breadcrumb_archive_format', 'Архив автора: %s',     'Rank Math breadcrumbs' );
}

/**
 * Rank Math хранит подписи хлебных крошек (Home Label и т.п.) одним
 * общим значением на весь сайт, поэтому сам их не переводит.
 * Подменяем текст и ссылку первой крошки ("Главная") на перевод
 * и URL главной страницы текущего языка Polylang.
 */
add_filter( 'rank_math/frontend/breadcrumb/items', 'la_translate_rankmath_breadcrumbs', 10, 2 );

function la_translate_rankmath_breadcrumbs( $crumbs, $class ) {

    if ( ! function_exists( 'pll__' ) || ! function_exists( 'pll_current_language' ) ) {
        return $crumbs; // Polylang не активен.
    }

    if ( isset( $crumbs[0] ) ) {
        $crumbs[0][0] = pll__( 'Главная' );                              // подпись
        $crumbs[0][1] = pll_home_url( pll_current_language() );          // ссылка на главную нужного языка
    }

    return $crumbs;
}

/**
 * Небольшая обёртка на случай, если Polylang вдруг отключат —
 * тогда шаблон не сломается, а просто покажет исходный (русский) текст.
 */
function la_pll( $string ) {
    return function_exists( 'pll__' ) ? pll__( $string ) : $string;
}

function la_pll_e( $string ) {
    echo esc_html( la_pll( $string ) );
}

add_filter( 'rank_math/frontend/breadcrumb/strings', function( $strings ) {
    if ( function_exists( 'pll__' ) ) {
        $strings['archive_format'] = pll__( 'Архив автора: %s' );
    }
    return $strings;
});


//Clean Head
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_post_rel_link_wp_head', 10, 0);
// REMOVE EMOJI ICONS
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
// removes oembed discorvery link
remove_action('wp_head','rest_output_link_wp_head');
remove_action('wp_head','wp_oembed_add_discovery_links', 10 );
remove_action('wp_head','wp_oembed_add_host_js');
// remove meta rel=dns-prefetch href=//s.w.org
remove_action( 'wp_head', 'wp_resource_hints', 2 );
//Clean Head
add_filter( 'get_the_archive_title', 'artabr_remove_name_cat' );
function artabr_remove_name_cat( $title ){
  if ( is_category() ) {
    $title = single_cat_title( '', false );
  } elseif ( is_tag() ) {
    $title = single_tag_title( '', false );
  }
  return $title;
}
// numbers article achvite
function custom_posts_per_page($query){
  if(is_archive()){ $query->set('posts_per_page',12); }
}
add_action('pre_get_posts','custom_posts_per_page');
// Hide H2 paginations
add_filter('navigation_markup_template', 'my_navigation_template', 10, 2 );
function my_navigation_template( $template, $class ) {
  return '
    <nav class="%1$s" role="navigation">
      <div class="nav-links">%3$s</div>
    </nav>    
  ';
}
// Head_scripts
function remove_head_scripts() {
  remove_action('wp_head', 'wp_print_scripts');
  remove_action('wp_head', 'wp_print_head_scripts', 9);
  remove_action('wp_head', 'wp_enqueue_scripts', 1);
  add_action('wp_footer', 'wp_print_scripts', 5);
  add_action('wp_footer', 'wp_enqueue_scripts', 5);
  add_action('wp_footer', 'wp_print_head_scripts', 5);
}
add_action( 'wp_enqueue_scripts', 'remove_head_scripts', 'wpbootstrap_scripts_with_jquery' );

// Thumb support
add_theme_support('post-thumbnails'); 
set_post_thumbnail_size(255, 191, false);
add_filter('widget_text', 'do_shortcode');

// длинна 
add_filter( 'excerpt_length', function(){
  return 14;
} );
// remove
if( 'disable_gutenberg' ){
  add_filter( 'use_block_editor_for_post_type', '__return_false', 100 );
  add_action( 'admin_init', function() {
  remove_action( 'admin_notices', [ 'WP_Privacy_Policy_Content', 'notice' ] );
  add_action( 'edit_form_after_title', [ 'WP_Privacy_Policy_Content', 'notice' ] ); } );
}
// remove_filter( 'the_content', 'wpautop' );


// ─────────────────────────────────────────────────────────────────────────
// Оглавление для модульного шаблона (single-service.php).
//
// ez-toc строит оглавление фильтром the_content() и не видит заголовки,
// которые модули (шаги, FAQ) рисуют в шаблоне мимо the_content(). Этот хелпер
// работает с уже отрендеренным HTML — источник разметки не важен: находит
// h2/h3, проставляет им якоря и собирает раскрывающееся «Содержание».
// ─────────────────────────────────────────────────────────────────────────
// Транслитерация кириллицы в латиницу для чистых якорей (id на латинице,
// а не %d0%ba…). sanitize_title сам кириллицу не транслитерирует, а урл-кодирует.
if ( ! function_exists( 'i10v_translit' ) ) :
function i10v_translit( $text ) {
  $map = array(
    'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh',
    'з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o',
    'п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'ts',
    'ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
    'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E','Ё'=>'E','Ж'=>'Zh',
    'З'=>'Z','И'=>'I','Й'=>'Y','К'=>'K','Л'=>'L','М'=>'M','Н'=>'N','О'=>'O',
    'П'=>'P','Р'=>'R','С'=>'S','Т'=>'T','У'=>'U','Ф'=>'F','Х'=>'H','Ц'=>'Ts',
    'Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Sch','Ъ'=>'','Ы'=>'Y','Ь'=>'','Э'=>'E','Ю'=>'Yu','Я'=>'Ya',
  );
  return strtr( (string) $text, $map );
}
endif;

if ( ! function_exists( 'i10v_build_toc' ) ) :
/**
 * @param string $html   Готовый HTML тела (собранные модули).
 * @param array  $levels Уровни заголовков, напр. ['h2','h3'].
 * @return array{0:string,1:string} [$html_с_якорями, $html_оглавления]
 */
function i10v_build_toc( $html, $levels = array( 'h2', 'h3' ) ) {
  $html = (string) $html;
  if ( trim( $html ) === '' ) {
    return array( $html, '' );
  }

  // 1) Прячем <script>/<style> за плейсхолдеры — их нельзя гонять через DOM
  //    (в FAQ там JSON-LD, DOMDocument может побить разметку).
  $shield = array();
  $html = preg_replace_callback(
    '#<(script|style)\b[^>]*>.*?</\1>#is',
    function ( $m ) use ( &$shield ) {
      $key = '<!--i10v-shield-' . count( $shield ) . '-->';
      $shield[ $key ] = $m[0];
      return $key;
    },
    $html
  );

  // 2) Разбираем оставшийся HTML.
  $dom = new DOMDocument();
  libxml_use_internal_errors( true );
  $dom->loadHTML( '<?xml encoding="utf-8"?><div id="i10v-root">' . $html . '</div>' );
  libxml_clear_errors();

  $xpath = new DOMXPath( $dom );
  $root  = $xpath->query( '//*[@id="i10v-root"]' )->item( 0 );
  if ( ! $root ) {
    return array( str_replace( array_keys( $shield ), array_values( $shield ), $html ), '' );
  }

  $hq    = './/' . implode( ' | .//', array_map( 'strtolower', $levels ) ); // .//h2 | .//h3
  $nodes = $xpath->query( $hq, $root );
  if ( ! $nodes || $nodes->length === 0 ) {
    $out = '';
    foreach ( $root->childNodes as $c ) { $out .= $dom->saveHTML( $c ); }
    return array( str_replace( array_keys( $shield ), array_values( $shield ), $out ), '' );
  }

  $items = array();
  $used  = array();
  foreach ( $nodes as $node ) {
    $text = trim( $node->textContent );
    if ( $text === '' ) { continue; }
    $id = $node->getAttribute( 'id' );
    if ( $id === '' ) {
      $id = sanitize_title( i10v_translit( $text ) );
      if ( $id === '' ) { $id = 'section'; }
    }
    $base = $id; $n = 2;
    while ( isset( $used[ $id ] ) ) { $id = $base . '-' . $n; $n++; }
    $used[ $id ] = true;
    $node->setAttribute( 'id', $id );
    $items[] = array(
      'id'    => $id,
      'text'  => $text,
      'level' => (int) substr( strtolower( $node->nodeName ), 1 ),
    );
  }



  // HTML тела с якорями + возвращаем спрятанные скрипты.
  $out = '';
  foreach ( $root->childNodes as $c ) { $out .= $dom->saveHTML( $c ); }
  $out = str_replace( array_keys( $shield ), array_values( $shield ), $out );

  // Вложенный список (стек по уровням).
  $min = min( array_map( function ( $it ) { return $it['level']; }, $items ) );
  $list = ''; $prev = 0;
  foreach ( $items as $it ) {
    $lvl = $it['level'] - $min + 1;
    if ( $prev === 0 ) {
      $list .= '<ul class="i10v-toc__list">';
    } elseif ( $lvl > $prev ) {
      $list .= str_repeat( '<ul>', $lvl - $prev );
    } elseif ( $lvl < $prev ) {
      $list .= str_repeat( '</li></ul>', $prev - $lvl ) . '</li>';
    } else {
      $list .= '</li>';
    }
    $list .= '<li><a href="#' . esc_attr( $it['id'] ) . '">' . esc_html( $it['text'] ) . '</a>';
    $prev = $lvl;
  }
  if ( $prev > 0 ) {
    $list .= '</li>' . str_repeat( '</ul></li>', $prev - 1 ) . '</ul>';
  }

  // $toc  = '<details class="i10v-toc" open>';
  // $toc .= '<summary class="i10v-toc__summary">Содержание</summary>';
  // $toc .= '<nav class="i10v-toc__nav" aria-label="Содержание">' . $list . '</nav>';
  // $toc .= '</details>';
  $toc  = '<details class="i10v-toc">';
  $toc .= '<summary class="i10v-toc__summary">' . esc_html( i10v_toc_title() ) . '</summary>';
  $toc .= '<nav class="i10v-toc__nav" aria-label="' . esc_attr( i10v_toc_title() ) . '">' . $list . '</nav>';
  $toc .= '</details>';

  return array( $out, $toc );
}
endif;


add_action( 'init', function () {
  if ( function_exists( 'pll_register_string' ) ) {
    // group 'i10v' — просто имя группы в админке Polylang, можно любое
    pll_register_string( 'i10v_toc_title', 'Содержание', 'i10v', false );
  }
} );

if ( ! function_exists( 'i10v_toc_title' ) ) :
function i10v_toc_title() {
  // Приоритет — перевод строки из Polylang. Если он не задан (pll__ вернул
  // исходник), берём запасной перевод по коду текущего языка — так UA/EN
  // работают даже когда строка в Polylang не сохранена или закэширована.
  $fallback = array(
    'ru' => 'Содержание',
    'en' => 'Table of Contents',
    'uk' => 'Зміст',
    'de' => 'Inhaltsverzeichnis',
    'fr' => 'Table des matières',
    'pt' => 'Índice',
    'tr' => 'İçindekiler',
    'es' => 'Tabla de contenido',
    'cn' => '目录',
  );
  if ( function_exists( 'pll__' ) ) {
    $t = pll__( 'Содержание' );
    if ( $t && $t !== 'Содержание' ) {
      return $t;
    }
  }
  $lang = function_exists( 'pll_current_language' ) ? pll_current_language() : 'ru';
  return isset( $fallback[ $lang ] ) ? $fallback[ $lang ] : 'Содержание';
}
endif;

// Стили оглавления — только на страницах услуг/лицензирования.
add_action( 'wp_head', function () {
  if ( ! is_singular( array( 'service', 'licensing', 'industry', 'target' ) ) ) {
    return;
  }
  echo '<style>'
    . ':target{scroll-margin-top:100px}'
    . '</style>';
} );

?>
<?php
// Переключатель языков (#pll_switcher): на страницах услуг Polylang дописывает
// ссылкам префикс текущего языка (/ru/…), а услуги живут в корне без префикса
// (SEO-силос) — срезаем его тем же способом, что и в пунктах меню выше.
function i10v_strip_lang_prefix($url) {
    $langs = function_exists('pll_languages_list') ? pll_languages_list() : array();
    if (!$langs || !$url) {
        return $url;
    }
    return preg_replace(
        '#^((?:https?://[^/]+)?)/(?:' . implode('|', array_map('preg_quote', $langs)) . ')/#',
        '$1/',
        $url
    );
}

add_filter('pll_the_language_link', function ($url) {
    if ($url && is_singular(array('service', 'licensing'))) {
        $url = i10v_strip_lang_prefix($url);
    }
    return $url;
}, 20);

// hreflang в <head> на страницах услуг страдает тем же префиксом.
add_filter('pll_rel_hreflang_attributes', function ($hreflangs) {
    if (is_singular(array('service', 'licensing'))) {
        foreach ($hreflangs as $lang => $u) {
            $hreflangs[$lang] = i10v_strip_lang_prefix($u);
        }
    }
    return $hreflangs;
}, 20);