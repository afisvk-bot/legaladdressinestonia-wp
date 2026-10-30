<?php
/**
 * Walker для мобильного меню (поддержка любой вложенности).
 */
class Mobile_Menu_Walker extends Walker_Nav_Menu {

    public function start_lvl( &$output, $depth = 0, $args = null ) {
        $indent  = str_repeat( "\t", $depth );
        $mod     = $depth > 0 ? ' mob-sub-menu--depth-' . ( $depth + 1 ) : '';
        $output .= "\n{$indent}<ul class=\"mob-sub-menu{$mod}\">\n";
    }

    public function end_lvl( &$output, $depth = 0, $args = null ) {
        $indent  = str_repeat( "\t", $depth );
        $output .= "{$indent}</ul>\n";
    }

    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $classes      = empty( $item->classes ) ? [] : (array) $item->classes;
        $has_children = in_array( 'menu-item-has-children', $classes, true );

        $class_names = implode( ' ', array_filter(
            apply_filters( 'nav_menu_css_class', $classes, $item, $args, $depth )
        ) );

        $output .= '<li class="' . esc_attr( $class_names ) . '">';

        $href  = $has_children ? 'javascript:void(0);' : esc_url( $item->url );
        $class = $has_children ? ' class="link"' : '';

        $output .= '<a href="' . $href . '"' . $class . '>'
                 . apply_filters( 'the_title', $item->title, $item->ID )
                 . '</a>';
    }

    public function end_el( &$output, $item, $depth = 0, $args = null ) {
        $output .= "</li>\n";
    }
}