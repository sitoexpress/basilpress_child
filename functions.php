<?php
/**
 * GeneratePress child theme functions and definitions.
 *
 * Add your custom PHP in this file.
 * Only edit this file if you have direct access to it on your server (to fix errors if they happen).
 */

define('BP_VER', 'v.0.6.0');
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

add_filter('advanced-ads-placement-types', 'tag24_ad_placements');
function tag24_ad_placements($placements) {
  $placements['my-placement'] = array(
    'title'       => __( 'My Placement', 'advanced-ads' ),
    'description' => __( 'This is a custom placement.', 'advanced-ads' ),
    'image'       => ADVADS_BASE_URL . 'admin/assets/img/placements/footer.png',
    'order'       => 95,
    'options'     => array(
      'show_position'  => true,
      'show_lazy_load' => true,
      'amp'            => true,
    ),
  );
  return $placements;
}

/*
* Fonts Stuff you may need to disable

add_action( 'admin_init', function() {
  add_filter( 'generate_google_fonts_array', '__return_empty_array' );
} );

add_action( 'wp_enqueue_scripts', function() {
    wp_dequeue_script( 'generate-fonts' );
} );

*/
