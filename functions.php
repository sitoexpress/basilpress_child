<?php
/**
 * GeneratePress child theme functions and definitions.
 *
 * Add your custom PHP in this file.
 * Only edit this file if you have direct access to it on your server (to fix errors if they happen).
 */

define('BP_VER', 'v.1.2.0');
define('BP_PANEL_MARGIN_DIVIDER', 2);
define('BP_PANEL_MARGIN_FALLBACK', 45);
define('BP_ADVANCED_MENU', 1);
define('BP_WP_LOGIN_BG', '#ccc');

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

/** Actual Functions */

// get_receipt_invoice_label works for the "Partita IVA per Fattura Elettronica" plugin

function get_receipt_invoice_label($value) {
  $label = __('Receipt', 'basilpress');
  $label = ($value != 'Si') ? $label : __('Invoice', 'basilpress');
  return $label;
}

/*
* Gets the term list for the given taxonomy wrapped around whatever
*/

function bp_get_the_term_list($post_id, $taxonomy, $before, $sep, $after, $trim = '') {

  $terms = array();

  // When more than 1 term is checked, YOAST lets you choose the primary one
  if(class_exists('WPSEO_Primary_Term') && $trim === 1) {

    $wpseo_primary_term = new WPSEO_Primary_Term( $taxonomy, $post_id );
    $wpseo_primary_term = $wpseo_primary_term->get_primary_term();

  }

  $terms = ($wpseo_primary_term) ? array(get_term( $wpseo_primary_term )) : get_the_terms( $post_id, $taxonomy );

  if ( is_wp_error( $terms[0] ) ) {
		return $terms[0];
	}

	if ( empty( $terms ) ) {
		return false;
	}

	$links = array();

	foreach ( $terms as $term ) {
		$link = get_term_link( $term, $taxonomy );
		if ( is_wp_error( $link ) ) {
			return $link;
		}
		$links[] = '<a href="' . esc_url( $link ) . '" rel="tag">' . $term->name . '</a>';
	}

	$term_links = apply_filters( "term_links-{$taxonomy}", $links );  // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

  if($trim) {
    $term_links = array_slice($term_links, 0, $trim);
  }

  $terms = $before.implode($sep, $term_links).$after;

  return $terms;

}

/** Below, some snippets that might come handy */

/*
* sl_()->is_archive()
*
* It's at times annoying to detect an archive view in WordPress
* If Sauce Library is installed, then you can call and extend
* the is_archive() method as follows

  add_filter('sauce_is_archive', 'is_archive_extended');
  function is_archive_extended($is_archive) {

    $is_archive = (
      is_post_type_archive('blossom-recipe') ||
      is_post_type_archive('event') ||
      is_tax('pa_store') ||
      is_tax('recipe-category') ||
      is_tax('recipe-cooking-method') ||
      is_tax('product_cat') ||
      is_tax('product_tag') ||
      is_shop()
      ) ? true : $is_archive;

    return $is_archive;

  }

*/

/*
add_action('wp_head', 'output_all_postmeta' );
function output_all_postmeta() {

	$postmetas = get_post_meta(get_the_ID());

	foreach($postmetas as $meta_key=>$meta_value) {
		echo $meta_key . ' : ' . $meta_value[0] . '<br/>';
	}
}*/

/*
* Fonts Stuff you may need to enable/disable

add_action( 'admin_init', function() {
  add_filter( 'generate_google_fonts_array', '__return_empty_array' );
} );

add_action( 'wp_enqueue_scripts', function() {
    wp_dequeue_script( 'generate-fonts' );
} );

/*
* Load Google/Custom Fonts

add_action('wp_enqueue_scripts', 'fonts_enqueue_stuff', 10);
add_filter('wp_resource_hints', 'fonts_hints', 20, 2);

function fonts_enqueue_stuff() {
  wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@900&family=Red+Hat+Display:wght@400;900&display=swap', '', false);
  wp_enqueue_style('bp-custom-fonts', get_stylesheet_directory_uri().'/assets/fonts/fonts.css', '', false);
}

function fonts_hints($urls, $type) {
  if($type == 'preconnect') {
    $url = array(
      'href' => 'https://fonts.googleapis.com',
      'crossorigin' => ''
    );
    $urls[] = $url;
  }
  return $urls;
}

*/

/*
* Set WooCommerce Gallery Thumbnail Size


add_filter('woocommerce_gallery_thumbnail_size', function($size) { return array(200, 200); });
add_filter('woocommerce_gallery_image_size', 'return_wc_size', 100 );
add_filter('woocommerce_gallery_full_size', 'return_wc_size', 100 );

function return_wc_size() {
  return 'medium-crop-sq';
}

*/

/*
* Redefines some Sauce Library image sizes
* Removes SiteOrigin and/or other plugin's registered image sizes
*

add_filter('sl_image_sizes', 'bp_image_sizes');
function bp_image_sizes($sizes) {

  foreach($sizes as $key => &$size) {
    if($size['name'] != 'square-thumb') unset($sizes[$key]);
  }

  $sizes[] = array(
    'name'      => 'very-large',
    'nicename'  => 'Very Large',
    'width'     => 1920,
    'height'    => 1080,
    'crop'      => true
  );

  $sizes[] = array(
    'name'      => 'medium-crop-h',
    'nicename'  => 'Medium, Cropped',
    'width'     => 800,
    'height'    => 480,
    'crop'      => true
  );

  $sizes[] = array(
    'name'      => 'medium-crop-v',
    'nicename'  => 'Medium, Vertical',
    'width'     => 800,
    'height'    => 960,
    'crop'      => true
  );

  return $sizes;

} */
