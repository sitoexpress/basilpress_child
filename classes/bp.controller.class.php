<?php

class bp_controller {

	protected static $_instance = null;
	public $options;
	public $view;
	public $bp_image_sizes;

	public function __construct() {

		$this->options = get_option('generate_settings');

		/* Load Textdomain */
		add_action( 'after_setup_theme', array($this, 'bp_textdomain'));

		// Actions, Frontend
		add_action( 'wp_enqueue_scripts', array($this, 'bp_enqueue'), 10);
		add_action( 'wp_enqueue_scripts', array($this, 'bp_dequeue_gp'), 50);
		add_action( 'admin_enqueue_scripts', array($this, 'bp_enqueue_admin'), 10);

		// Actions, Admin
		add_action( 'login_enqueue_scripts', array($this, 'bp_admin_ui'), 10 );

		// Actions
		add_action('customize_register', array($this, 'bp_customizer' ));
		add_action('wp_head', array($this, 'bp_print_options'));
		add_action('init', array($this, 'bp_remove_image_size'), 100);
		add_shortcode('CF7_LOGO', array($this, 'bp_cf7_logo'));

		// Filters
		add_filter( 'option_generate_settings', array($this, 'bp_content_single_post'), 999);

		// Support for SiteOrigin Page Builder Layouts in /view/ folder
		add_filter( 'siteorigin_panels_local_layouts_directories', array($this, 'bp_so_layouts_folder' ));
		add_filter( 'siteorigin_panels_postloop_template_directory', array($this, 'bp_so_postloop_folder'));

		/* Disable YOAST Annoying ads */
		add_filter( 'wpseo_update_notice_content', '__return_null' );

		/* Removes useless SVG stuff */
		remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );

		// Methods & Properties
		$this->view();

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
	}

	public function bp_enqueue_admin() {
		/* Enqueue custom.style.admin.css */
		wp_enqueue_style('bp-css-custom-admin', get_stylesheet_directory_uri().'/assets/css/bp.custom.style.admin.css', '', BP_VER);
		wp_enqueue_script('bp-js-custom-admin', get_stylesheet_directory_uri().'/assets/js/bp.custom.scripts.admin.js', '', BP_VER);
	}

	public function bp_enqueue() {

		/* Remove useless emojis */
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );

		/* Enqueue custom.css */
		wp_enqueue_style('bp-css-custom', get_stylesheet_directory_uri().'/style.custom.css', array('generate-child'), BP_VER);

		/* Enqueue Local Scripts */
		wp_enqueue_script('bp-modernizr', get_stylesheet_directory_uri() . '/assets/js/modernizr.min.js');
  	wp_enqueue_script('bp-js', get_stylesheet_directory_uri().'/assets/js/bp.scripts.js', array('jquery', 'sauce-script'), BP_VER);
		wp_enqueue_script('bp-js-custom', get_stylesheet_directory_uri().'/assets/js/bp.custom.scripts.js', array('jquery', 'sauce-script'), BP_VER);

		/* Enqueue WooCommerce Scripts/Styles  */
		if(defined("WC_VERSION")) {
			wp_enqueue_style('bp-wc-custom', get_stylesheet_directory_uri().'/assets/css/bp.woocommerce.css', '', BP_VER);
			wp_enqueue_script('bp-js-wc', get_stylesheet_directory_uri().'/assets/js/bp.woocommerce.js', array('jquery', 'sauce-script', 'woocommerce'), BP_VER);
		}
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
			register_sidebar( array(
				'name'          => esc_html__( 'Default Fixed Area', 'basilblank' ),
				'id'            => 'fixed-bar',
				'description'   => esc_html__( 'Fixed area on all pages at the bottom right corner.', 'basilblank' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			) );
    }

    public function bp_admin_ui() {
			$backend_logo_obj = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ) , 'full' );
			if($backend_logo_obj) {
				$backend_logo = $backend_logo_obj[0];
			} else {
				$backend_logo = get_stylesheet_directory_uri().'/assets/img/backend_logo.png';
			}
		?>
	    <style type="text/css">
	    	body {
	    		background-color: #f2f2f2 !important;
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
