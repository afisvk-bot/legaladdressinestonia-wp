<?php

/**
 * If this file is called directly, abort.
 */
if ( !defined( 'ABSPATH' ) ) {
  die( 'Direct access is forbidden.' );
}

/**
 * Class Name: WP_Bootstrap_Navwalker
 * Plugin Name: WP Bootstrap4 Navwalker
 * Plugin URI:  https://github.com/jprieton/wp-bootstrap4-navwalker
 * Description: A custom WordPress nav walker class to implement the Bootstrap 4.x navigation style in a custom theme using the WordPress built in menu manager.
 * Author: Javier Prieto
 * Version: 1.1.2
 * Author URI: https://github.com/jprieton/
 * GitHub Plugin URI: https://github.com/jprieton/wp-bootstrap4-navwalker
 * GitHub Branch: master
 * License: GPL-3.0+
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 */
/* Check if Class Exists. */

if ( !class_exists( 'WP_Bootstrap_Navwalker' ) ) {

  /* WP_Bootstrap_Navwalker class  */

  class WP_Bootstrap_Navwalker extends Walker_Nav_Menu {

    public function start_lvl( &$output, $depth = 0, $args = array() ) {
      if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
        $t = '';
        $n = '';
      } else {
        $t = "\t";
        $n = "\n";
      }
      $this->dropdown = true;
      $output .= $n . str_repeat( $t, $depth ) . '<div class="dropdown-menu" role="menu">' . $n;
    }

    public function end_lvl( &$output, $depth = 0, $args = array() ) {
      if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
        $t = '';
        $n = '';
      } else {
        $t = "\t";
        $n = "\n";
      }
      $output .= $n . str_repeat( $t, $depth ) . '</div>' . $n;
    }

    public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
      if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
        $t = '';
        $n = '';
      } else {
        $t = "\t";
        $n = "\n";
      }
      $indent = str_repeat( $t, $depth );

      if ( 0 < $depth && ( 0 === strcasecmp( $item->attr_title, 'divider' ) || 0 === strcasecmp( $item->title, 'divider' ) ) ) {
        $output .= $indent . '<div class="dropdown-divider"></div>' . $n;
        return;
      }

      $args         = apply_filters( 'nav_menu_item_args', $args, $item, $depth );
      $has_children = $args->walker->has_children;

      // <li> только на верхнем уровне
      if ( 0 === $depth ) {
        $classes   = empty( $item->classes ) ? array() : (array) $item->classes;
        $classes[] = 'nav-item';
        if ( $has_children ) {
          $classes[] = 'dropdown';
        }
        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

        $li_id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
        $li_id = $li_id ? ' id="' . esc_attr( $li_id ) . '"' : '';

        $output .= $indent . '<li' . $li_id . $class_names . '>' . $n;
      }

      $atts           = array();
      $atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
      $atts['target'] = ! empty( $item->target ) ? $item->target : '';
      $atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
      $atts['href']   = ! empty( $item->url ) ? $item->url : '';
      $atts           = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

      if ( $has_children ) {
        $atts['aria-haspopup'] = 'true';
        $atts['aria-expanded'] = 'false';
        // if ( 0 === $depth ) {
        //   $atts['data-toggle'] = 'dropdown';
        // }
      }

      $attributes = '';
      foreach ( $atts as $attr => $value ) {
        if ( ! empty( $value ) ) {
          $value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
          $attributes .= ' ' . $attr . '="' . $value . '"';
        }
      }

      $title = apply_filters( 'the_title', $item->title, $item->ID );
      $title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

      $item_classes = array( 'nav-link' );
      if ( $has_children ) {
        $item_classes[] = 'dropdown-toggle';
      }
      if ( 0 < $depth ) {
        $item_classes   = array_diff( $item_classes, array( 'nav-link' ) );
        $item_classes[] = 'dropdown-item';
        if ( $has_children ) {
          $item_classes[] = 'has-submenu-toggle';
        }
      }

      $item_output  = $args->before;
      $item_output .= '<a class="' . implode( ' ', $item_classes ) . '"' . $attributes . '>';
      $item_output .= $args->link_before . $title . $args->link_after;
      $item_output .= '</a>';
      $item_output .= $args->after;

      // обёртка для пункта 2-го уровня с подменю (закрывается в end_el)
      if ( 0 < $depth && $has_children ) {
        $output .= $indent . '<div class="dropdown-submenu">' . $n;
      }

      $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }

    public function end_el( &$output, $item, $depth = 0, $args = array() ) {
      $n = ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) ? '' : "\n";

      if ( 0 === $depth ) {
        $output .= '</li>' . $n;
      } elseif ( in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
        $output .= '</div>' . $n; // закрываем обёртку .dropdown-submenu
      }
    }

    public static function fallback( $args ) {
      if ( current_user_can( 'edit_theme_options' ) ) {
        
        $defaults = array(
          'container'       => 'div',
          'container_id'    => false,
          'container_class' => false,
          'menu_class'      => 'menu',
          'menu_id'         => false,
        );

        $args     = wp_parse_args( $args, $defaults );
          if ( !empty( $args['container'] ) ) {
            echo sprintf( '<%s id="%s" class="%s">', $args['container'], $args['container_id'], $args['container_class'] );
          }
          echo sprintf( '<ul id="%s" class="%s">', $args['container_id'], $args['container_class'] ) .
            '<li class="nav-item">' .
            '<a href="' . admin_url( 'nav-menus.php' ) . '" class="nav-link">' . __( 'Add a menu' ) . '</a>' .
            '</li></ul>';
          if ( !empty( $args['container'] ) ) {
            echo sprintf( '</%s>', $args['container'] );
          }
      }
    }


  }
}