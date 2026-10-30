<?php
/**
 * Plugin Name: LAI — Config
 * Description: Настройки модулей LAI (меню всех языков и автоперелинковка). Загружается первым.
 */
defined( 'ABSPATH' ) || exit;

// Мастер-меню (EN) для каждой локации темы: локация:ID меню
defined( 'LAI_MASTER_MENUS' ) || define( 'LAI_MASTER_MENUS', 'primary-menu:154,mobile_menu:157' );

// Данные модулей
defined( 'LAI_LINK_MATRIX_FILE' )  || define( 'LAI_LINK_MATRIX_FILE', __DIR__ . '/lai-data/lai-link-matrix.json' );
defined( 'LAI_MENU_STRINGS_FILE' ) || define( 'LAI_MENU_STRINGS_FILE', __DIR__ . '/lai-data/lai-menu-strings.json' );

// Типы записей и ACF-поля для автоперелинковки
defined( 'LAI_LINK_POST_TYPES' ) || define( 'LAI_LINK_POST_TYPES', 'post,page,service,licensing,industry,target' );
defined( 'LAI_LINK_ACF_FIELDS' ) || define( 'LAI_LINK_ACF_FIELDS', 'content,text_left,text_right' );
