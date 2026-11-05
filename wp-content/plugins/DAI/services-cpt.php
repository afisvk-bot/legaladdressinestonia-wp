<?php
/**
 * Plugin Name: Custom Services CPTs
 * Description: service / licensing / industry / target post types
 */

defined('ABSPATH') || exit;

add_action('init', function () {

    // ---------------------------------------------------------
    // 1) SERVICE — /{L2}/{L3}/  (префикс "services/" убирается ниже,
    //    в functions.php через post_type_link + rewrite_rules_array)
    // ---------------------------------------------------------
    register_post_type('service', [
        'labels' => [
            'name'          => 'Services',
            'singular_name' => 'Service',
        ],
        'public'        => true,
        'show_in_rest'  => true,
        'hierarchical'  => true,
        'has_archive'   => false,
        'menu_icon'     => 'dashicons-portfolio',
        'supports'      => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'custom-fields', 'author'],
        'rewrite'       => [
            'slug'       => 'services',
            'with_front' => false,
        ],
    ]);

    // ---------------------------------------------------------
    // 2) LICENSING — /licensing/{L2}/{L3}/
    // ---------------------------------------------------------
    register_post_type('licensing', [
        'labels' => [
            'name'          => 'Licensing',
            'singular_name' => 'Licensing',
        ],
        'public'        => true,
        'show_in_rest'  => true,
        'hierarchical'  => true,
        'has_archive'   => false,
        'menu_icon'     => 'dashicons-awards',
        'supports'      => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'custom-fields', 'author'],
        'rewrite'       => [
            'slug'       => 'licensing',
            'with_front' => false,
        ],
    ]);

    // ---------------------------------------------------------
    // 3) INDUSTRY — /industry/{item}/
    // ---------------------------------------------------------
    register_post_type('industry', [
        'labels' => [
            'name'          => 'Industries',
            'singular_name' => 'Industry',
        ],
        'public'        => true,
        'show_in_rest'  => true,
        'hierarchical'  => false,
        'has_archive'   => false,
        'menu_icon'     => 'dashicons-building',
        'supports'      => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'custom-fields', 'author'],
        'rewrite'       => [
            'slug'       => 'industry',
            'with_front' => false,
        ],
    ]);

    // ---------------------------------------------------------
    // 4) TARGET — /target/{item}/
    // ---------------------------------------------------------
    register_post_type('target', [
        'labels' => [
            'name'          => 'Target',
            'singular_name' => 'Target',
        ],
        'public'        => true,
        'show_in_rest'  => true,
        'hierarchical'  => false,
        'has_archive'   => false,
        'menu_icon'     => 'dashicons-groups',
        'supports'      => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'custom-fields', 'author'],
        'rewrite'       => [
            'slug'       => 'target',
            'with_front' => false,
        ],
    ]);
});

// Принудительный flush правил при изменении номера версии —
// без него правки rewrite-правил не применяются до ручного
// сохранения Настройки → Постоянные ссылки.
add_action('init', function () {
    $version = '1.2'; // повышайте при каждом изменении rewrite-логики
    if (get_option('services_cpt_rewrite_version') !== $version) {
        flush_rewrite_rules();
        update_option('services_cpt_rewrite_version', $version);
    }
}, 999);


/**
 * Разрешаем просмотр черновиков CPT (service, licensing, industry, target)
 * по прямой ссылке для залогиненных пользователей с правом редактирования.
 * Обычные посетители черновики не увидят.
 */
add_action( 'pre_get_posts', function ( $query ) {

    // Работаем только с главным запросом на фронте
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    // Только для одиночных страниц наших CPT
    $allowed_post_types = array( 'service', 'licensing', 'industry', 'target' );

    if ( ! $query->is_singular( $allowed_post_types ) ) {
        return;
    }

    // Только для залогиненных пользователей
    if ( ! is_user_logged_in() ) {
        return;
    }

    // Проверяем право редактировать записи хотя бы одного из этих типов
    $can_view_drafts = false;

    foreach ( $allowed_post_types as $pt ) {
        $obj = get_post_type_object( $pt );
        if ( $obj && current_user_can( $obj->cap->edit_posts ) ) {
            $can_view_drafts = true;
            break;
        }
    }

    if ( $can_view_drafts ) {
        $query->set( 'post_status', array( 'publish', 'draft', 'pending', 'future' ) );
    }
} );