<?php
/**
 * GeneratePress child theme functions and definitions.
 *
 * Add your custom PHP in this file.
 * Only edit this file if you have direct access to it on your server (to fix errors if they happen).
 */

define('BP_VER', 'v.0.6.1');
define('BP_PANEL_MARGIN_DIVIDER', 2);
define('BP_PANEL_MARGIN_FALLBACK', 45);
define('BP_ADVANCED_MENU', 0);

/** Requires **/

require_once get_stylesheet_directory().'/classes/bp.view.class.php';
require_once get_stylesheet_directory().'/classes/bp.controller.class.php';

/** Init **/

$bp_child = bp_controller::instance();

function bp() {
  return bp_controller::instance();
}

function bp_after_setup_theme() {
    remove_theme_support( 'widgets-block-editor' );
}
add_action( 'after_setup_theme', 'bp_after_setup_theme' );

/*
* Fonts Stuff you may need to disable

add_action( 'admin_init', function() {
  add_filter( 'generate_google_fonts_array', '__return_empty_array' );
} );

add_action( 'wp_enqueue_scripts', function() {
    wp_dequeue_script( 'generate-fonts' );
} );

*/
