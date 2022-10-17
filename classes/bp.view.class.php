<?php

class bp_view {

	protected static $_instance = null;

	public function __construct() {

		/* Structure Setup */
		add_action('after_setup_theme', array($this, 'after_setup_theme'), 10);

		/* Misc Filters */
		add_filter('body_class', array($this, 'bp_body_classes' ), 1, 100);
		add_filter('generate_meta_viewport', array($this,'bp_meta_viewport'));
		add_filter('get_the_archive_title', array($this, 'bp_remove_archive_word'));
		add_filter('generate_navigation_search_output', array($this, 'bp_nav_search'), 1, 100);
		add_filter('generate_header_items_order', array($this, 'bp_remove_header_area'), 10, 1);

		/* Wp PageNavi Integration */
		add_action('wp', array($this, 'remove_gp_nav'));
		add_action('generate_after_loop', array($this, 'pagenavi_integration'), 20);

		/* SOPB customization */
		add_filter('siteorigin_panels_css_widget_css', array($this, 'custom_widget_general_margin'), 15, 8);
		add_filter('siteorigin_panels_data', array($this, 'custom_panels_data'), 15, 2);

		/* WooCommerce */
		add_filter('woocommerce_get_script_data', array($this, 'bp_wc_change_js_view_cart_button'), 10, 2 );
		add_action('woocommerce_after_shop_loop_item', array($this, 'bp_archive_button_wrapper_open'), 7);
		add_action('woocommerce_after_shop_loop_item', array($this, 'bp_archive_button_wrapper_close'), 25);
		add_filter( 'generate_sidebar_layout', array($this, 'bp_disable_sidebar'), 10, 1);

		/* BasilPress Advanced Menu */
		if(BP_ADVANCED_MENU == 1) {

		  add_action('pre_wp_nav_menu', array($this, 'bam_disable_primary_menu'), 10, 2);
		  add_filter('body_class', array($this, 'bam_body_class'), 10, 1);

		  add_action('widgets_init', array($this, 'bam_widget_area_register'));

		  add_action('generate_after_primary_menu', array($this, 'bam_widget_area_view'));

		  add_filter('generate_mobile_menu_media_query', array($this, 'mobile_menu_breakpoint'), 999);
		  add_filter('generate_not_mobile_menu_media_query', array($this, 'desktop_menu_breakpoint'), 999);

		}

	}

	public function bam_widget_area_view() {
	  if ( is_active_sidebar( 'primary-menu' ) ) :
	    dynamic_sidebar( 'primary-menu' );
	  endif;
	}

	public function bam_widget_area_register() {

		register_sidebar( array(
			'name'          => 'Primary Menu Widget',
	    'id'            => 'primary-menu',
	    'description'   => esc_html__( 'Fixed area on all pages at the bottom right corner.', 'basilblank' ),
	    'before_sidebar' => '<div id="%1$s" class="main-nav widgetized inside-header grid-container">',
	    'after_sidebar'  => '</div>',
	    'before_widget' => '<section id="%1$s" class="widget %2$s">',
	    'after_widget'  => '</section>',
	    'before_title'  => '<h2 class="widget-title">',
	    'after_title'   => '</h2>',
		) );

	}

	public function bam_body_class($classes) {
	  return array_merge($classes, array('bam-active'));
	}

	public function bam_disable_primary_menu($value, $args) {
	  if($args->theme_location == 'primary') {
	    $value = false;
	  }
	  return $value;
	}

	// disable GP's mobile menu (BP's mobile menu already commented in scripts.js and style.css)
	public function mobile_menu_breakpoint($breakpoint) {
	  return '(max-width: 1px)';
	}

	public function desktop_menu_breakpoint($breakpoint) {
	  return '(min-width: 2px)';
	}

	public function after_setup_theme() {

		/* Adds loader */
		add_action('wp_body_open', array($this, 'bp_loader'));

		/* Header Nav Wrap */
		add_action('generate_before_header', array($this, 'bp_header_nav_wrap_open'), 100);
		add_action('generate_after_header', array($this, 'bp_header_nav_wrap_close'), 100);

		/* Main Nav Wrap*/
		add_action("generate_after_mobile_menu_button", array($this, 'wrap_main_nav_open'));
		add_action("generate_after_primary_menu", array($this, 'wrap_main_nav_close'));

		/* Remove Main Nav From all Locations */
		remove_action( 'generate_before_right_sidebar_content', 'generate_add_navigation_before_right_sidebar', 5 );
		remove_action( 'generate_after_header', 'generate_add_navigation_after_header', 5 );
		remove_action( 'generate_before_header', 'generate_add_navigation_before_header', 5 );
		remove_action( 'generate_after_header_content', 'generate_add_navigation_float_right', 5 );
		remove_action( 'generate_before_right_sidebar_content', 'generate_add_navigation_before_right_sidebar', 5 );
		remove_action( 'generate_before_left_sidebar_content', 'generate_add_navigation_before_left_sidebar', 5 );

		/* Force Main Nav within Header */
		add_action('generate_after_header_content', 'generate_navigation_position');

		/* Wraps Archives */
		add_action("generate_before_loop", array($this, "basil_archive_open"), 25);
		add_action("generate_after_loop", array($this, "basil_archive_close"), 5);

		/* Removes page header */
		add_action('wp', array($this, 'bp_remove_page_header'));

		/* Relocate header widget area */
		remove_action('generate_after_header_content', 'generate_do_header_widget');
		add_action('generate_before_header', 'generate_do_header_widget', 110);

		/* Displays Edit Link */
		add_action('wp_footer', array($this, 'bp_edit_link'), 1);

		/* Removes Credits */
		remove_action('generate_footer', 'generate_construct_footer');
	}

	public function bp_remove_header_area($order) {
		$order = (in_array('header-widget', $order)) ? array_diff($order, array('header-widget')) : $order;
		return $order;
	}

	public function bp_edit_link() {
		if(is_singular())	edit_post_link();
	}

	public function bp_construct_header_widget() {
		if ( is_active_sidebar( 'header' ) ) :
			?>
			<div class="header-widget grid-container inside-header">
				<?php dynamic_sidebar( 'header' ); ?>
			</div>
			<?php
		endif;
	}

	public function bp_body_classes($classes) {

		if(isset(bp()->options['nav_is_fixed']) && bp()->options['nav_is_fixed']) $classes[] = 'nav-is-fixed' ;
		if(isset(bp()->options['header_on_left']) && bp()->options['header_on_left']) $classes[] = 'header-on-left' ;

		if(function_exists('siteorigin_panels_setting')) {
			$settings = siteorigin_panels_setting();
			if($settings['margin-bottom'] == 0) {
				$classes[] = 'padded-rows';
			}
		}

		return $classes;

	}

	public function bp_meta_viewport($string) {
		return '<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">';
	}

	public static function bp_remove_archive_word() {
		$title = '';
	    if ( is_category() ) {
	        $title = single_cat_title( '', false );
	    } elseif ( is_tag() ) {
	        $title = single_tag_title( '', false );
	    } elseif ( is_author() ) {
	        $title = '<span class="vcard">' . get_the_author() . '</span>';
	    } elseif ( is_post_type_archive() ) {
	        $title = post_type_archive_title( '', false );
	    } elseif ( is_tax() ) {
	        $title = single_term_title( '', false );
	    }
	    return $title;
	}

	/* Open Archive Button Wrapper */

	public function bp_archive_button_wrapper_open() {
		echo $this->bp_elem_open('div', array('buy-button-wrapper'));
	}

	public function bp_archive_button_wrapper_close() {
		echo $this->bp_elem_close('div');
	}

	public function bp_remove_page_header() {
		if(isset(bp()->options['page_header_global']) && bp()->options['page_header_global']) return;
		remove_action( 'generate_after_header', 'generate_featured_page_header', 10 );
		if(is_singular() && get_post_type() != 'post') {
			remove_action( 'generate_before_content', 'generate_featured_page_header_inside_single', 10);
		}
	}

	public function bp_nav_search($html_output) {
		$text = apply_filters('bp_nav_search_placeholder_text', __('Search...', 'bp_child'));
		$html_output = str_replace('class="search-field"', 'class="search-field" placeholder="'.$text.'" ', $html_output);
		$html_output = str_replace('</form>', '<a class="close-search bp-search-item"></a></form>', $html_output);
		return $html_output;
	}

	public function bp_header_nav_wrap_open() {
		echo $this->bp_elem_open('div', array('header-nav-wrap'));
	}

	public function bp_header_nav_wrap_close() {
		echo $this->bp_elem_close('div');
	}

	public function bp_loader() {
		echo "<div class='bp-loader'><div class='bp-spinner'></div></div>";
	}

	/**
	* SOPB Fix Widget Margin as function of Row Margin
	*/

	public function custom_widget_general_margin($widget_css, $widget_style_data, $row, $ri, $cell, $ci, $widget, $wi) {
	  if ( ! empty( $widget_style_data['margin'] ) ) {
	    // if there's a proper margin set for the widget, let's use it
			$widget_css['margin'] = $widget_style_data['margin'];
		} else {
	    // first, we check if the widget in question is the last widget in the cell
	    $widget_i = count($cell['widgets']) - 1;
	    if($widget_i != $wi) {
	      // if the widget is not the last one, the we apply our bottom margin
	      // as a function of the row margin
	      $settings = siteorigin_panels_setting();
	      $margin = ($settings['margin-bottom'] > 0) ? $settings['margin-bottom']/BP_PANEL_MARGIN_DIVIDER : BP_PANEL_MARGIN_FALLBACK/BP_PANEL_MARGIN_DIVIDER;
	      $widget_css['margin-bottom'] = $margin.'px';
	    }
	  }
	  return $widget_css;
	}

	/**
	* SOPB Fix Row Padding if margin-bottom is zero (padding-mode)
	*/

	public function custom_panels_data($panels_data, $post_id) {

		$settings = siteorigin_panels_setting();

		if($settings['margin-bottom'] == 0) {
			foreach($panels_data['grids'] as &$grid) {
				if(!isset($grid['style'])) {
					$grid['style'] = array();
				}
				if(!isset($grid['style']['padding'])) {
					$grid['style']['padding'] = BP_PANEL_MARGIN_FALLBACK.'px 0 '.BP_PANEL_MARGIN_FALLBACK.'px 0';
				}
			}
		}

		return $panels_data;

	}

	/**
	 * Pagenavi Integration
	 */

	public function remove_gp_nav() {
		if(!function_exists('wp_pagenavi')) return;
	  if (!is_single() || is_archive() || is_author()) {
	      add_filter( 'generate_show_post_navigation', '__return_false' , 9999);
	  }
	}

	public function pagenavi_integration()
	{
		if(!function_exists('wp_pagenavi')) return;
    if(!is_author()) {
      wp_pagenavi();
    }
	}

	/**
	 * Wraps main-nav
	 * This way it's possible to add a secondary menu which will open in the hamburger
	 * F.eg.: add_action("generate_after_primary_menu", "my_another_menu", 10);
	 */

	public function wrap_main_nav_open() {
		echo "<div id='main-nav-wrap' class='main-nav-wrap'>";
	}

	public function wrap_main_nav_close() {
		echo "</div><!-- #main-nav-wrap -->";
	}

	/*
	* Removes sidebar in woocommerce pages if active
	*/

	public function bp_disable_sidebar( $layout ) {
		// Keep sidebar only for single posts
		if ((function_exists( 'is_woocommerce' ) && is_woocommerce())
				|| (!is_category() && !is_home() && !is_author() && !is_tag() && !is_singular('post'))) {
				$layout = 'no-sidebar';
		}
		// Or else, set the regular layout
		return $layout;
	}

	/**
	 * Wraps posts in archives
	 */

	public function basil_archive_open() {
		if(is_home() || is_archive() || is_search()) {
			echo $this->bp_elem_open('div', array('basil-archive-wrap', 'basil-post-wrap'));
		}
	}

	public function basil_archive_close() {
		if(is_home() || is_archive() || is_search()) {
			echo "</div><!-- .basil-post-wrap -->";
		}
	}

	public function bp_elem_open($elem = 'div', $classes = null, $id = null, $data = null, $style = null) {

		$classes = apply_filters('bp_elem_class_filter', $classes, $data, $id);
		$data = apply_filters('bp_elem_data_filter', $data, $classes, $id);
		$style = apply_filters('bp_elem_style_filter', $style, $classes, $id);

		$classes = implode(' ', $classes);
		$data_html = '';
		$style_html = '';

		if($data) {
			foreach($data as $attr => $value) {
				$data_html .= " data-".$attr."='".$value."' ";
			}
		}

		if($style) {
			$style_html = 'style="';
			foreach($style as $attr => $value) {
				$style_html .= $attr.': '.$value.';';
			}
			$style_html .= '"';
		}

		$html = ($id) ? "<$elem id='$id' class='$classes' $data_html $style_html>" : "<$elem class='$classes' $data_html $style_html>" ;

		return $html;

	}

	public function bp_elem_close($elem = 'div') {
		echo "</$elem>";
	}

	public function bp_wc_change_js_view_cart_button( $params, $handle )  {
	    if( 'wc-add-to-cart' !== $handle ) return $params;

	    // Changing "view_cart" button text and URL
	    $params['i18n_view_cart'] = esc_attr__("Proceed Now", "basilpress"); // Text
	    $params['cart_url']      = esc_url( wc_get_checkout_url() ); // URL

	    return $params;
	}

	/**
	* Public instance method for recalling current instance.
	*
	* @return void
	*/

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	* Private clone method to prevent cloning of the instance of the
	* *Singleton* instance.
	*
	* @return void
	*/

	private function __clone() {}

	/**
	* Private unserialize method to prevent unserializing of the *Singleton*
	* instance.
	*
	* @return void
	*/

	private function __wakeup() {}

}
