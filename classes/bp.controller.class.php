<?php

class bp_controller {

	protected static $_instance = null;
	public $options;
	public $view;
	public $bp_image_sizes;

	public function __construct() {

		$this->options = get_option('generate_settings');
		$this->view();

		/* Load Textdomain */
		add_action( 'after_setup_theme', array($this, 'bp_textdomain'));

		// Actions, Frontend
		add_action( 'wp_enqueue_scripts', array($this, 'bp_enqueue'), 10);
		add_action( 'wp_enqueue_scripts', array($this, 'bp_dequeue_gp'), 50);
		add_action( 'admin_enqueue_scripts', array($this, 'bp_enqueue_admin'), 10);

		// Actions, Admin
		add_action( 'login_enqueue_scripts', array($this, 'bp_admin_ui'), 10 );

		// Customize BP Options
		add_action('customize_register', array($this, 'bp_customizer' ));
		add_action('generate_layout_meta_box_content', array($this, 'bp_metabox_options'));
		add_action('generate_metabox_tabs', array($this, 'bp_metabox_tab'), 10, 1);
		add_action('generate_layout_meta_box_save', array($this, 'bp_metabox_save'));

		// Actions
		add_action('wp_head', array($this, 'bp_print_options'));
		add_shortcode('CF7_LOGO', array($this, 'bp_cf7_logo'));
		add_action('widgets_init', array($this, 'bp_widget_areas'));

		// Remove unused image sizes
		add_action('init', array($this, 'bp_remove_image_size'), 100);
		add_filter('intermediate_image_sizes', array($this, 'bp_remove_intermediate_image_size'));

		// Filters
		add_filter( 'option_generate_settings', array($this, 'bp_content_single_post'), 999);

		// Support for SiteOrigin Page Builder Layouts in /view/ folder
		add_filter('siteorigin_panels_local_layouts_directories', array($this, 'bp_so_layouts_folder' ));
		add_filter('siteorigin_panels_postloop_template_directory', array($this, 'bp_so_postloop_folder'));
		add_action('save_post', array($this, 'so_builder_check'), 10, 3);

		/* Disable YOAST Annoying ads */
		add_filter( 'wpseo_update_notice_content', '__return_null' );

		/* Removes useless SVG stuff */
		remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );

		// WooCommerce
		add_filter('woocommerce_checkout_get_value', array($this, 'partita_iva_per_fattura_elettronica_fix'), 20, 2);
		add_filter('woocommerce_login_redirect', array($this, 'woocommerce_login_redirect'));

	}

	public function so_builder_check($id, $post, $update) {

			$blocks = parse_blocks($post->post_content);
			$has_builder_meta = get_post_meta($id, 'has_so_builder', true);
			$has_builder_now = 0;

			if(!empty($blocks)) {
				foreach($blocks as $block) {
					if($block['blockName'] == 'siteorigin-panels/layout-block' && !$has_builder_meta) {
						$has_builder_now = 1;
						update_post_meta($id, 'has_so_builder', 1);
						break;
					} else
					if($block['blockName'] == 'siteorigin-panels/layout-block' && $has_builder_meta) {
						$has_builder_now = 1;
						break;
					}
				}
			}

			if($has_builder_now == 0 && $has_builder_meta) delete_post_meta($id, 'has_so_builder');
	}

	public function bp_metabox_tab($tabs) {
		$tabs['basilpress'] = array(
			'title' => esc_html__( 'Additional Settings', 'basilpress' ),
			'target' => '#generate-layout-bp-metabox',
			'class' => '',
		);
		return $tabs;
	}

	public function bp_metabox_options() { ?>
		<div id="generate-layout-bp-metabox" style="display: none;">
			<label class="generate-layout-metabox-section-title"><?php esc_html_e( 'Additional Settings', 'basilpress' ); ?></label>
			<div class="generate_bp_metabox">
				<label for="basil-no-content-margin" style="display:block;margin: 0 0 1em;" title="<?php esc_attr_e( 'Content Margin', 'basilpress' ); ?>">
					<input type="checkbox" name="_basil-no-content-margin" id="basil-no-content-margin" value="true" <?php checked( get_post_meta(get_the_id(), '_basil-no-content-margin', true), 'true' ); ?>>
					<?php esc_html_e( 'Disable content top margin', 'basilpress' ); ?>
					<p class="page-builder-content" style="color:#666;font-size:11px;margin-top:5px;">
						<?php esc_html_e( 'Useful when content should slip below the fixed header.', 'basilpress' ); ?>
					</p>
				</label>
			</div>
		</div>
		<?php
	}

	public function bp_metabox_save($post_id) {
		$margin_container_key   = '_basil-no-content-margin';
		$margin_container_value = filter_input( INPUT_POST, $margin_container_key, FILTER_SANITIZE_STRING );

		if ( $margin_container_value ) {
			update_post_meta( $post_id, $margin_container_key, $margin_container_value );
		} else {
			delete_post_meta( $post_id, $margin_container_key );
		}
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/*
	* Load Textdomain
	*/

	public function bp_textdomain() {
	    load_theme_textdomain( 'basilpress', get_stylesheet_directory() . '/languages' );
	}

	/**
	 * Ensures single posts display full content and not just an excerpt.
	 */

	public function bp_content_single_post( $options ) {
	    if(!is_main_query() || is_admin()) return $options;
	    if ( is_singular() || is_single() ) {
	        $options['post_content'] = 'full';
	    }
	    return $options;
	}

	/**
	 * Register a custom layouts folder location for SiteOrigin Page Builder.
	 */

	public function bp_so_layouts_folder( $layout_folders ) {
	    $layout_folders[] = get_stylesheet_directory() . "/view";
	    return $layout_folders;
	}

	/**
	 * Registering another position for the SOPB Loop widget templates
	 */

	public function bp_so_postloop_folder( $directories ) {
		$directories[] = get_stylesheet_directory().'/loops/';
		return $directories;
	}

	/**
	 * Remove SOPB useless image sizes
	 */

	public function bp_remove_image_size() {
		remove_image_size("sow-carousel-default");
		remove_image_size('2048x2048');
    remove_image_size('1536x1536');
	}

	public function bp_remove_intermediate_image_size($sizes) {
	  return array_diff($sizes, ['medium_large']);  // Medium Large (768 x 0)
	}

	public function bp_enqueue_admin() {
		/* Enqueue custom.style.admin.css */
		wp_enqueue_style('bp-css-custom-admin', get_stylesheet_directory_uri().'/assets/css/admin.css', '', BP_VER);
		wp_enqueue_script('bp-js-custom-admin', get_stylesheet_directory_uri().'/assets/js/admin.js', '', BP_VER);
	}

	public function bp_enqueue() {

		// Remove useless emojis
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );

		// Modernizr
		wp_enqueue_script('bp-modernizr', get_stylesheet_directory_uri() . '/assets/js/modernizr.min.js');

		// Base scripts and styles
		wp_enqueue_style('bp-wp', get_stylesheet_directory_uri().'/assets/css/wp.css', array('generate-child'), BP_VER);
		wp_enqueue_script('bp-wp', get_stylesheet_directory_uri().'/assets/js/wp.js', array('jquery', 'sauce-script'), BP_VER);
		$deps[] = 'bp-wp';

		/* Enqueue WooCommerce Scripts/Styles  */
		if(defined("WC_VERSION")) {
			wp_enqueue_style('bp-wc', get_stylesheet_directory_uri().'/assets/css/wc.css', $deps, BP_VER);
			wp_enqueue_script('bp-wc', get_stylesheet_directory_uri().'/assets/js/wc.js', array('jquery', 'sauce-script', 'woocommerce'), BP_VER);
			$deps[] = 'bp-wc';
		}

		// Customizable scripts and styles
		wp_enqueue_style('bp-custom', get_stylesheet_directory_uri().'/custom.css', $deps, BP_VER);
		wp_enqueue_script('bp-custom', get_stylesheet_directory_uri().'/custom.js', $deps, BP_VER);

  	}

    public function bp_dequeue_gp() {

			/* Dequeue Generatepress Navigation Scripts */
			wp_deregister_script( 'generate-menu' );
			wp_dequeue_script( 'generate-menu' );
	  	wp_dequeue_script( 'generate-dropdown' );
			wp_dequeue_script('generate-main');

    }

    public function bp_sizes_name($sizes) {
	    return array_merge( $sizes, array(
	    	'big-800' => __( 'Big 800x480' ),
	        'medium-640' => __( 'Medium 640x360' ),
	        'thin-640' => __( 'Medium Thin 640x240' ),
	        'square-480' => __( 'Square Crop 480x480' ),
	    ) );
    }

    public function bp_widget_areas() {

			if(isset($this->options['central_logo']) && $this->options['central_logo']) {

				register_sidebar( array(
					'name'          => esc_html__( 'Logo Left', 'basilblank' ),
					'id'            => 'logo-left-area',
					'description'   => esc_html__( 'Appears on the left side of the logo. Widgets inlined.', 'basilblank' ),
					'before_widget' => '<section id="%1$s" class="widget %2$s">',
					'after_widget'  => '</section>',
					'before_title'  => '<h2 class="widget-title">',
					'after_title'   => '</h2>',
				) );

			}

			register_sidebar( array(
				'name'          => esc_html__( 'Logo Right', 'basilblank' ),
				'id'            => 'logo-right-area',
				'description'   => esc_html__( 'Appears on the right side of the logo. Widgets inlined.', 'basilblank' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			) );

			register_sidebar( array(
				'name'          => esc_html__( 'Logo Right - WooCommerce', 'basilblank' ),
				'id'            => 'logo-right-area-wc',
				'description'   => esc_html__( 'Appears on the right side of the logo on WC pages/archives (if set). Widgets inlined.', 'basilblank' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			) );

			register_sidebar( array(
				'name'          => esc_html__( 'Default Fixed Area', 'basilblank' ),
				'id'            => 'fixed-bar',
				'description'   => esc_html__( 'Fixed area on all pages at the bottom right corner.', 'basilblank' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			) );

			register_sidebar(
				array(
					'id' => 'top-widget-area',
					'name' => esc_html__( 'Top Widget Area - Blog', 'basilpress' ),
					'description' => esc_html__( 'Appears below the page title in blog archives/posts', 'basilpress' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget' => '</div>',
					'before_title' => '<h3 class="widget-title">',
					'after_title' => '</h3>'
				)
			);

			register_sidebar(
				array(
					'id' => 'woocommerce-top-widget-area',
					'name' => esc_html__( 'Top Widget Area - WooCommerce', 'basilpress' ),
					'description' => esc_html__( 'Appears below the page title in WooCommerce archives', 'basilpress' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget' => '</div>',
					'before_title' => '<h3 class="widget-title">',
					'after_title' => '</h3>'
				)
			);

    }

    public function bp_admin_ui() {
		$backend_logo_obj = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ) , 'full' );
		if($backend_logo_obj) {
			$backend_logo = $backend_logo_obj[0];
		} else {
			$backend_logo = get_stylesheet_directory_uri().'/assets/img/backend_logo.png';
		}
		$backend_bg_color = (defined('BP_WP_LOGIN_BG')) ? BP_WP_LOGIN_BG : '#f2f2f2';
	?>
	<style type="text/css">
		body {
			background-color: <?php echo $backend_bg_color; ?> !important;
		}
		.wp-core-ui .button-primary {
			background-color: #555 !important;
			border-color: #555 !important;
			box-shadow: none !important;
			text-shadow: none !important;
		}
		.login #backtoblog a, .login #nav a {
			color: #555 !important;
		}
		#login h1 a, .login h1 a {
			background-image: url(<?php echo $backend_logo; ?>);
			padding-bottom: 0;
			width: 225px;
			background-size: contain;
		}
	</style>
<?php
}

	/**
	 * Custom WPCF7 fields
	 */


	public function bp_cf7_logo() {
		$image = (wp_get_attachment_image_src(get_theme_mod( 'custom_logo' ), 'full' ));
		$image = ($image) ? $image[0] : get_template_directory_uri().'/img/backend_logo.png';
    return $image;
	}

	/**
	 * Customizer Settings
	 */

	public function bp_customizer($wp_customize) {

		$wp_customize->add_setting(
			'generate_settings[nav_is_fixed]',
			array(
				'default' => 0,
				'type' => 'option',
				'sanitize_callback' => 'generate_sanitize_choices',
				'transport' => 'refresh',
			)
		);

		$wp_customize->add_setting(
			'generate_settings[header_on_left]',
			array(
				'default' => 0,
				'type' => 'option',
				'sanitize_callback' => 'generate_sanitize_choices',
				'transport' => 'refresh',
			)
		);

		$wp_customize->add_setting(
			'generate_settings[page_header_global]',
			array(
				'default' => 0,
				'type' => 'option',
				'sanitize_callback' => 'generate_sanitize_choices',
				'transport' => 'refresh',
			)
		);

		$wp_customize->add_setting(
			'generate_settings[central_logo]',
			array(
				'default' => 0,
				'type' => 'option',
				'sanitize_callback' => 'generate_sanitize_choices',
				'transport' => 'refresh',
			)
		);

		$wp_customize->add_control(
			'generate_settings[nav_is_fixed]',
			array(
				'type' => 'select',
				'label' => __( 'Fixed Header & Navigation (BP)', 'generatepress' ),
				'section' => 'generate_layout_header',
				'choices' => array(
					0 => __( 'Disabled', 'generatepress' ),
					1 => __( 'Enabled', 'generatepress' ),
				),
				'settings' => 'generate_settings[nav_is_fixed]',
				'priority' => 15,
			)
		);

		$wp_customize->add_control(
			'generate_settings[header_on_left]',
			array(
				'type' => 'select',
				'label' => __( 'Left Header & Navigation (BP)', 'generatepress' ),
				'section' => 'generate_layout_header',
				'choices' => array(
					0 => __( 'Disabled', 'generatepress' ),
					1 => __( 'Enabled', 'generatepress' ),
				),
				'settings' => 'generate_settings[header_on_left]',
				'priority' => 15,
			)
		);

		$wp_customize->add_control(
			'generate_settings[page_header_global]',
			array(
				'type' => 'select',
				'label' => __( 'Page Header Global Setting (BP)', 'generatepress' ),
				'section' => 'generate_layout_header',
				'choices' => array(
					0 => __( 'Disabled', 'generatepress' ),
					1 => __( 'Enabled', 'generatepress' ),
				),
				'settings' => 'generate_settings[page_header_global]',
				'priority' => 15,
			)
		);

		$wp_customize->add_control(
			'generate_settings[central_logo]',
			array(
				'type' => 'select',
				'label' => __( 'Central Logo (BP)', 'generatepress' ),
				'section' => 'generate_layout_header',
				'choices' => array(
					0 => __( 'Disabled', 'generatepress' ),
					1 => __( 'Enabled', 'generatepress' ),
				),
				'settings' => 'generate_settings[central_logo]',
				'priority' => 15,
			)
		);

	}

	public function view() {

		$this->view = bp_view::instance();

	}

	public function bp_print_options() {

		if(!defined('BP_DEBUG')) return;

		?>

			<noscript id="bp_debug"><?php echo print_r($this->options, true); ?></noscript>

		<?php
	}

	/*
	* Yith Catalog Mode Integration
	*/

	public function is_yith_catalog_enabled() {

		if(!class_exists('YITH_WooCommerce_Catalog_Mode')) return false;
		
		$status = (get_option( 'ywctm_disable_shop' ) == 'yes') ? true : false;
		
		if($status == false) return false;
		
		// The realm of status = true
		
		if(is_user_logged_in()) {
			
			// Checking what to do for admin
		
			if((current_user_can( 'administrator' ) || current_user_can( 'manage_vendor_store' )) && 'no' === get_option( 'ywctm_admin_view' . $vendor_id )) {
			$status = false;
			}
		
		}
		
		return $status;
	
	}

	/*
	* WooCommerce Stuff Below
	*/

	// Redirect WooCommerce login to shop page

	public function woocommerce_login_redirect( $redirect) {

		$redirect_page_id = url_to_postid( $redirect );
		$checkout_page_id = wc_get_page_id( 'checkout' );
		
		if( $redirect_page_id == $checkout_page_id) {
			return $redirect;
		}

		return wc_get_page_permalink( 'shop' );

	}

	// WooCommerce Partita IVA per Fattura Elettronica fix for receipt / invoice

	public function partita_iva_per_fattura_elettronica_fix($input, $key) {

	  if($key == 'billing_fatt') {
	    global $current_user;
	    if(get_user_meta($current_user->ID, 'billing_fatt', true) == "Si") {
	      $input = 1;
	    }
	  }

	  return $input;
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
