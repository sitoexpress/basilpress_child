<?php

class bp_view {

	protected static $_instance = null;
	private $placeholderify_form_row;

	public function __construct() {

		// After Setup Theme: gp fixes need to be hooked there
		add_action('after_setup_theme', array($this, 'after_setup_theme'), 10);

		// Structure
		add_filter('body_class', array($this, 'bp_body_classes' ), 1, 100);
		add_filter('generate_meta_viewport', array($this,'bp_meta_viewport'));
		add_filter('get_the_archive_title', array($this, 'bp_remove_archive_word'));
		add_filter('generate_navigation_search_output', array($this, 'bp_nav_search'), 1, 100);
		add_filter('generate_header_items_order', array($this, 'bp_remove_header_area'), 15, 1);
		add_action('init', array($this, 'header_footer_max_width_fix'), 100);

		// Single Post
		add_action('generate_after_entry_content', array($this, 'bp_archive_post_link'), 5);
		add_filter('excerpt_more', function(  ) { return '...'; } , 100);
		add_filter('excerpt_length', function( $length ) { return 25; } );

		// Central Logo Stuff
		add_filter('generate_logo_output', array($this, 'central_logo_style'), 1, 10);
		add_action('generate_before_logo', array($this, 'logo_widget_areas'));
		add_action('generate_after_logo', array($this, 'logo_widget_areas'));

		// Wp Colorpickers
		add_action('admin_print_footer_scripts', array($this, 'color_pickers_default'));
		add_action('customize_controls_print_footer_scripts', array($this, 'color_pickers_default'));
		add_action('acf/input/admin_footer', array($this, 'color_pickers_default'));

		// Wp PageNavi Integration
		add_action('wp', array($this, 'remove_gp_nav'));
		add_action('generate_after_loop', array($this, 'pagenavi_integration'), 20);

		// SOPB customization
		add_filter('siteorigin_panels_css_widget_css', array($this, 'custom_widget_general_margin'), 15, 8);
		add_filter('siteorigin_panels_data', array($this, 'custom_panels_data'), 15, 2);

		/*
		* WooCommerce
		*/

		add_filter('woocommerce_enqueue_styles', array($this,'dequeue_wc_styles' ));
		add_filter('generate_sidebar_layout', array($this, 'disable_sidebar'), 10, 1);

		// Products
		add_filter('woocommerce_get_script_data', array($this, 'woocommerce_change_js_view_cart_button'), 10, 2 );
		add_action('woocommerce_after_shop_loop_item', array($this, 'woocommerce_product_stock'), 5);
		add_action('woocommerce_after_shop_loop_item', array($this, 'bp_archive_button_wrapper_open'), 7);
		add_action('woocommerce_after_shop_loop_item', array($this, 'bp_archive_button_wrapper_close'), 25);
		add_filter('woocommerce_product_get_image', array($this, 'product_featured_in_archives'), 10, 4);
		add_filter('woocommerce_sale_flash', array($this, 'add_percentage_to_sale_badge'), 20, 3 );
		add_filter('woocommerce_cart_item_name', array($this, 'cart_item_name'), 5, 3);

		// Variable Products
		add_action('woocommerce_before_shop_loop_item_title', array($this, 'maybe_add_variable_form'), 10);
		add_filter( 'woocommerce_variable_price_html', array($this, 'custom_variable_price_html'), 10, 2 );

		// Single Product
		add_action('woocommerce_single_product_summary', array($this, 'bp_term_list'), 3);

		// WooCommerce Account
		add_action('woocommerce_before_account_navigation', array($this, 'woocommerce_account_navigation_open'), 9999);
		add_action('woocommerce_after_account_navigation', array($this, 'woocommerce_account_navigation_close'), 1);
		add_action('woocommerce_account_content', array($this, 'woocommerce_account_content_title'), 6);
		add_action('woocommerce_account_dashboard', array($this, 'woocommerce_my_last_order_summary'), 20);
		add_action('woocommerce_account_dashboard', array($this, 'woocommerce_my_address_summary'), 15);
		add_action('woocommerce_before_checkout_form', array($this, 'woocommerce_my_address_summary'), 15);

		// WooCommerce Breadcrumbs
    	add_filter('woocommerce_breadcrumb_defaults', array($this, 'woocommerce_breadcrumb_defaults' ));
		add_filter('woocommerce_get_breadcrumb', array($this, 'woocommerce_get_breadcrumb'));
		add_filter('woocommerce_breadcrumb_home_url', array($this, 'woocommerce_breadcrumb_home_url' ));
		add_filter('woocommerce_before_main_content', array($this, 'remove_woocommerce_breadcrumbs'));

		// WooCommerce Checkout 
		$this->placeholderify_form_row = 'this-row-first';
		add_filter('woocommerce_checkout_fields', array($this, 'customize_checkout'), 999 );
		add_filter('woocommerce_checkout_fields', array($this, 'placeholderify_checkout'), 9999 );
		
		add_action('woocommerce_before_edit_account_address_form', function() {
			/* At the moment, we put the filter in here so that it runs only for that specific form */
			add_filter('woocommerce_form_field_args', array($this, 'placeholderify_woocommerce_form_fields'), 10, 3);
		}, 10);

		add_filter('woocommerce_cart_item_name', array($this, 'add_item_thumb_cqoc_cart'), 9, 3);
		add_action('woocommerce_review_order_before_payment', array($this, 'bp_payment_title'));
		add_filter( 'woocommerce_package_rates', array($this, 'hide_shipping_if_free_is_available'), 100 );

		// Privacy Checkbox in Checkout
		add_action( 'woocommerce_checkout_process', array($this, 'woocommerce_validate_privacy_checkout'));
		add_action( 'woocommerce_checkout_after_terms_and_conditions', array($this, 'woocommerce_add_privacy_policy_checkout'), 9 );
		remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20 );

		// WooCommerce Registration
		add_action( 'woocommerce_register_form', array($this, 'woocommerce_add_privacy_policy_registration'), 9 );
		add_filter( 'woocommerce_registration_errors', array($this, 'woocommerce_validate_privacy_registration'), 10, 3 );
		remove_action('woocommerce_register_form', 'wc_registration_privacy_policy_text', 20 );

		// WooCommerce Order Page
		add_filter( 'woocommerce_order_item_name', array($this, 'order_received_item_thumbnail_image'), 10, 3 );
		
		// WooCommerce Emails
		add_action( 'woocommerce_email', array($this, 'disable_mobile_messaging' ));
		// add_filter( 'woocommerce_email_after_order_table', array($this, 'customer_email_order_meta'), 20, 1);
		add_action('woocommerce_email_order_details', array($this, 'woocommerce_email_customizations'), 5, 4);
		add_action('woocommerce_email_subject_new_order', array($this, 'woocommerce_email_admin_subject_customizations'), 5, 2);

		// WPC Fly Cart Customization
		add_filter('woofc_cart_count', array($this, 'woofc_cart_icon'), 10);
		add_filter('woofc_cart_menu', array($this, 'woofc_cart_icon'), 10);
		add_filter('woofc_above_total_content', array($this,'woofc_wrap_open'), 10);
		add_filter('woofc_below_total_content', array($this,'woofc_wrap_close'), 10);
		add_filter('woofc_below_subtotal_content', array($this,'woofc_cart_vat'));

		// Buy Button Wrapper within add to cart variation
		add_action('woocommerce_after_add_to_cart_quantity', array($this, 'bp_archive_button_wrapper_open'), 99);
		add_action('woocommerce_after_add_to_cart_button', array($this, 'bp_archive_button_wrapper_close'), 1);

		// BasilPress Advanced Menu
		if(BP_ADVANCED_MENU == 1) {

		  add_action('pre_wp_nav_menu', array($this, 'bam_disable_primary_menu'), 10, 2);
		  add_filter('body_class', array($this, 'bam_body_class'), 10, 1);

		  add_action('widgets_init', array($this, 'bam_widget_area_register'));

		  add_action('generate_after_primary_menu', array($this, 'bam_widget_area_view'));

		  add_filter('generate_mobile_menu_media_query', array($this, 'mobile_menu_breakpoint'), 999);
		  add_filter('generate_not_mobile_menu_media_query', array($this, 'desktop_menu_breakpoint'), 999);

		}

	}

	/*
	* Methods hooked to after_setup_theme
	*/

	public function after_setup_theme() {

		/* Adds loader */
		add_action('wp_body_open', array($this, 'bp_loader'));

		/* Header Nav Wrap */
		add_action('generate_before_header', array($this, 'bp_header_nav_wrap_open'), 100);
		add_action('generate_after_header', array($this, 'bp_header_nav_wrap_close'), 100);

		/* Main Nav Wrap*/
		add_action("generate_after_mobile_menu_button", array($this, 'wrap_main_nav_open'));
		add_action("generate_after_primary_menu", array($this, 'wrap_main_nav_close'));

		/* Blog / Community Archives */
		add_filter('woocommerce_show_page_title', '__return_false');
		remove_action('generate_archive_title', 'generate_archive_title');
		remove_action( 'generate_before_loop', 'generate_do_search_results_title' );
		add_action('bp_inside_site_content_header_title', array($this, 'add_back_to_shop'), 10);

		/* Single Posts within Archives */
		add_action('generate_before_entry_title', array($this, 'bp_archive_postheader_open'), 1);
    	add_action('generate_after_entry_title', array($this, 'bp_archive_header_close'), 98); // just a closing div for the above
		
		/* Add terms like tags and categories to posts */
		add_action('generate_before_entry_title', array($this, 'bp_term_list'), 5);
    
		/* Adds a div within site-content */
		add_action('generate_inside_site_container', array($this, 'site_content_header_open'), 1);
		add_action('generate_inside_site_container', array($this, 'blog_top_area'), 2);
		add_action('generate_inside_site_container', array($this, 'site_content_header_close'), 3);
		add_action('generate_inside_container', array($this, 'site_content_inner_open'), 4);
		add_action('generate_before_footer', array($this, 'site_content_inner_close'), 1);

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

		/* Fixes on GP page layout */
		add_filter('generate_show_entry_header', array($this, 'fix_generate_show_entry_header'), 99, 1);
		add_action('wp', array($this, 'bp_remove_page_header'));
		add_action('wp', array($this, 'wp_hooks'));

		/* Relocate header-widget area, adding some classes */
		remove_action('generate_after_header_content', 'generate_do_header_widget');
		add_action('generate_before_header', array($this,'bp_construct_header_widget'), 110);

		/* Displays Edit Link */
		add_action('wp_footer', array($this, 'bp_edit_link'), 1);

		/*
		* SiteOrigin CSS Fullwidth
		*/

		// Add custom row options
		add_filter('siteorigin_panels_row_style_fields', array($this, 'sopb_add_row_options'), 20);

		// Add custom row classes
		add_filter('siteorigin_panels_row_classes', array($this, 'sopb_add_row_classes'), 10, 2);

		// Add compatibility CSS
		add_action('generate_base_css', array($this, 'bp_base_css_output'));

		add_filter( 'siteorigin_widgets_accordion_scrollto_offset', function( $offset ) {
			return 0;
		});

		add_filter( 'siteorigin_panels_theme_container_width', function( $container ) {
			return bp()->options['container_width'].'px';
		} );

		add_filter( 'siteorigin_panels_theme_container_selector', function( $selector ) {
			return '.grid-container:not(.inside-header)';
		} );

		/* Removes Credits */
		remove_action('generate_footer', 'generate_construct_footer');
		
	}

	public function wp_hooks() {
		remove_action( 'generate_after_entry_content', 'generate_footer_meta' );
	}

	/*
	* WooCommerce
	*/

	// Display the product thumbnail in order received page

	public function order_received_item_thumbnail_image( $item_name, $item, $is_visible ) {
		// Targeting order received page only
		if( ! is_wc_endpoint_url('order-received') && ! is_wc_endpoint_url('view-order') ) return $item_name;

		// Get the WC_Product object (from order item)
		$product = $item->get_product();

		if( $product->get_image_id() > 0 ){
			$product_image = '<span class="order-thumb">' . $product->get_image(array(110, 110)) . '</span>';
			$item_name = $product_image . $item_name;
		}

		return $item_name;
	}

	// Remove unnecessary WC styles

	public function dequeue_wc_styles( $enqueue_styles ) {
		// unset( $enqueue_styles['woocommerce-general'] );	// Remove the gloss
		unset( $enqueue_styles['woocommerce-layout'] );		// Remove the layout
		unset( $enqueue_styles['woocommerce-smallscreen'] );	// Remove the smallscreen optimisation
		return $enqueue_styles;
	}

	// Breadcrumbs: change the delimeter from '/' to '>'

	public function woocommerce_breadcrumb_defaults( $defaults ) {
		$defaults['delimiter'] = ' &gt; ';
		$defaults['home'] = 'Shop';
		return $defaults;
	}

	// Breadcrumbs: removes value displayed twice in order received page

	public function woocommerce_get_breadcrumb($crumbs) {
		if ( is_checkout() && !empty( is_wc_endpoint_url('order-received') ) ) {
			unset($crumbs[1]);
			$crumbs = array_values($crumbs);
		}
		return $crumbs;
	}

	// Breadcrumbs: set Home url to shop

	public function woocommerce_breadcrumb_home_url() {
	    return get_permalink(wc_get_page_id('shop'));
	}

	// Breadcrumbs: Remove if not single product
	
	public function remove_woocommerce_breadcrumbs() {
			if(!is_product()) {
					remove_action( 'woocommerce_before_main_content','woocommerce_breadcrumb', 20, 0);
			}
	}

	// Archive navigation: adds back to shop below term title

	public function add_back_to_shop() {
		if(function_exists('is_product_category') && !is_product_category() && !is_product_tag()) return;
		echo "<span class='back-to-shop'><a href='".get_permalink(wc_get_page_id('shop'))."'>".__('Back to shop', 'basilpress')."</a></span>";
	}

	/*
	* WooCommerce Account Navigation
	*/

	// Account Navigation: open wrapper

	public function woocommerce_account_navigation_open() {
	  echo bp()->view->bp_elem_open('div', array('bp-wc-navigation-wrapper button-menu inline-menu hscroll-all text-center'));
	}

	// Account Navigation: close wrapper

	public function woocommerce_account_navigation_close() {
	  echo bp()->view->bp_elem_close();
	}

	/*
	* WooCommerce Registration
	*/

	// Add privacy checkbox in registration form

	public function woocommerce_add_privacy_policy_registration() {
		
		$policy_link = "<a href=".get_privacy_policy_url().">Privacy Policy</a>";

		$label = str_replace("[privacy_policy]", $policy_link, get_option( 'woocommerce_registration_privacy_policy_text', sprintf( __( 'Your personal data will be used to create an account, manage the login and support your experience throughout this website, and for other purposes described in our %s according to you preferences.', 'woocommerce' ), $policy_link)));
		woocommerce_form_field( 'privacy_policy_reg', array(
			'type'          => 'checkbox',
			'class'         => array('form-row privacy'),
			'label_class'  => array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),
			'input_class'  => array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
			'required'      => true,
			'label'         => $label
		));

	}
	
	// Validate privacy checkbox in registration form

	public function woocommerce_validate_privacy_registration( $errors, $username, $email ) {

		if ( ! is_checkout() ) {
			if ( ! (int) isset( $_POST['privacy_policy_reg'] ) ) {
				$errors->add( 'privacy_policy_reg_error', __( 'Please accept our privacy policy to create your account!', 'basilpress' ) );
			}
		}

		return $errors;
	}

	/*
	* WooCommerce Shipping
	*/

	// Hide other methods if free shipping
	
	public function hide_shipping_if_free_is_available( $rates ) {
		$free = array();
		foreach ( $rates as $rate_id => $rate ) {
			if ( 'free_shipping' === $rate->method_id ) {
				$free[ $rate_id ] = $rate;
				break;
			}
		}
		return ! empty( $free ) ? $free : $rates;
	}

	/*
	* WooCommerce E-mails
	*/

	// Disable messages about the mobile apps in WooCommerce emails.
	
	public function disable_mobile_messaging( $mailer ) {
		remove_action( 'woocommerce_email_footer', array( $mailer->emails['WC_Email_New_Order'], 'mobile_messaging' ), 9 );
	}

	// Add order meta to customer (TBR)

	public function customer_email_order_meta( $order ) {

		// TODO: THIS MIGHT BE TO REMOVE! 

		$invoice = get_post_meta( $order->get_id(), '_billing_fatt', true );

		if($invoice) {

			$vat = (get_post_meta( $order->get_id(), '_billing_vat', true )) ? get_post_meta( $order->get_id(), '_billing_vat', true ) : '-';
			$nin = get_post_meta( $order->get_id(), '_billing_nin', true ) ? get_post_meta( $order->get_id(), '_billing_nin', true ) : '-';
			$pec = get_post_meta( $order->get_id(), '_billing_pec', true ) ? get_post_meta( $order->get_id(), '_billing_pec', true ) : '-';

			echo '<header class="title" style="margin-top: -20px">
							<h2 style="margin: 0 0 8px 0">'.__('Invoice Data', 'basilpress').'</h2>
				</header>';
			echo '<p>
				<strong>'.__('Company Name:', 'basilpress')."</strong> ".get_post_meta( $order->get_id(), '_billing_company', true ).'<br/>
				<strong>'.__('Fiscal Code / Vat No.:', 'basilpress')."</strong> ".$vat.'<br/>
				<strong>'.__('Unique Code:', 'basilpress')."</strong> ".$nin.'<br/>
				<strong>'.__('Certified Email:', 'basilpress')."</strong> ".$pec.'<br/>
				</p>';
		} else {
			echo '<header class="title" style="margin-top: -20px">
					<h2 style="margin: 0 0 8px 0">'.__('Nessuna fattura richiesta.', 'basilpress').'</h2>
				</header>';
		}
	}

	// Adds Invoice data to order e-mail

	public function  woocommerce_email_customizations($order, $sent_to_admin, $plain_text, $email) {

		if($order->get_status() != 'processing') return;

		$shipping = strtolower($order->get_shipping_method());

		if($sent_to_admin) {
			
			if(get_post_meta($order->get_id(), '_billing_fatt', true)) {
			$html_fatt = "<h3>".__("Invoice Data", "basilpress")."</h3>";
			$html_fatt .= "<p>";
			$html_fatt .= "<strong>".__("Business name", "basilpress")."</strong>";
			$html_fatt .= ($order->get_billing_company()) ? $order->get_billing_company()."<br/>" : $order->get_billing_first_name()." ".$order->get_billing_last_name()."<br/>";
			$html_fatt .= "<strong>".__("VAT No. / Fiscal Code", "basilpress")."</strong>";
			$html_fatt .= (get_post_meta($order->get_id(), '_billing_vat', true)) ? get_post_meta($order->get_id(), '_billing_vat', true)."<br/>" : get_post_meta($order->get_id(), '_billing_cf', true)."<br/>";
			$html_fatt .= "<strong>Unique Code (Italy only): </strong>";
			$html_fatt .= (get_post_meta($order->get_id(), '_billing_nin', true)) ? get_post_meta($order->get_id(), '_billing_nin', true)."<br/>" : __("Not provided", "basilpress")."<br/>";
			$html_fatt .= "<strong>PEC (Italy only): </strong>";
			$html_fatt .= (get_post_meta($order->get_id(), '_billing_pec', true)) ? get_post_meta($order->get_id(), '_billing_pec', true) : __("Not provided", "basilpress");
			$html_fatt .= "</p>";
			} else {
			$html_fatt = "<h3>".__("Receipt only", "basilpress")."</h3>";
			}

		} else {
			$message_shipping = __('You\'ll receive more info about your order status and shipping in few hours.', 'basilpress');
		}
		
		$html = ($message_shipping) ? "<p><strong>".$message_shipping."</strong></p>" : '';
		$html .= ($html_fatt) ? $html_fatt : '';

		echo $html;

	}

	// Adds Invoice request data to order subject

	public function woocommerce_email_admin_subject_customizations($subject, $order) {

		$fatt = (get_post_meta($order->get_id(), '_billing_fatt', true)) ? __('Invoice requested', 'basilpress') : __('Receipt only', 'basilpress');
		// Just in case: $shipping = (strpos(strtolower($order->get_shipping_method()), 'locale') !== false) ? 'Entro GRA' : 'Espresso';

		// Just in case: return $subject." - ".$fatt." - ".$shipping;
		return $subject." - ".$fatt;

	}

	/*
	* WooCommerce Dashboard Customization
	*/

	// WooCommerce Dashboard: add content title in account sub pages

	public function woocommerce_account_content_title() {
	  global $wp;
	  $endpoints = wc_get_account_menu_items();
		if ( ! empty( $wp->query_vars ) ) {
			foreach ( $wp->query_vars as $key => $value ) {
				// Ignore pagename param.
				if ( 'pagename' === $key ) {
					continue;
				}
	      if(isset($endpoints[$key])) {
	        echo "<h1>".$endpoints[$key]."</h1>";
	        return;
	      }
	      if ( 'view-order' === $key ) {
	        echo "<h1 class='order-title'>".__('Summary', 'basilpress')."</h1>";
	        return;
	      }
			}
		}
	  echo "<h1 class='dashboard-title'>".$endpoints['dashboard']."</h1>";
	}

	// WooCommerce Dashboard: add address summary box

	public function woocommerce_my_address_summary() {
	  $user = new WC_Customer(get_current_user_id());
	  $title = (is_checkout()) ? "<h3>".__('Your addresses', 'basilpress')."</h3>" : "<h3>".__('My Address', 'basilpress')."</h3>";
	  if(!$user || (!$user->get_billing_address() && is_checkout())) return;
	  echo bp()->view->bp_elem_open('div', array('account-box'), 'address-summary');
	    echo $title;
	    echo bp()->view->bp_elem_open('div', array('box-inner'));
	      include(get_stylesheet_directory()."/parts/my-address-summary.php");
	    echo bp()->view->bp_elem_close();
	  echo bp()->view->bp_elem_close();
	}

	// WooCommerce Dashboard: add last order table

	public function woocommerce_my_last_order_summary() {
	  echo bp()->view->bp_elem_open('div', array('account-box'), 'last-order-summary');
	    echo "<h3>".__('Last order', 'basilpress')."</h3>";
	    echo bp()->view->bp_elem_open('div', array('box-inner'));
	      echo bp()->view->bp_elem_open('div', array('row'));
	        echo bp()->view->bp_elem_open('div', array('col col-12'));
	      // we get the last order
	      $last_orders = wc_get_orders(
	        apply_filters(
	          'get_last_orders_query',
	          array(
	            'status' => array('wc-processing', 'wc-completed'),
	            'customer_id' => get_current_user_id(),
	            'limit' => 1,
	            'paginate' => true,
	          )
	        )
	      );

	      // This is needed to remove "next" button
	      $last_orders->max_num_pages = 0;

	      wc_get_template(
	        'myaccount/orders.php',
	        array(
	          'current_page'    => 1,
	          'customer_orders' => $last_orders,
	          'has_orders'      => count($last_orders->orders)
	        )
	      );
	        echo bp()->view->bp_elem_close();
	      echo bp()->view->bp_elem_close();
	    echo bp()->view->bp_elem_close();

	    $url = wc_get_endpoint_url('orders', '', get_permalink(get_option('woocommerce_myaccount_page_id')));

	    echo bp()->view->bp_elem_open('div', array('row'));
	      echo bp()->view->bp_elem_open('div', array('col col-12'));
	        echo "<a class='button' href='$url'>".__("See all orders", 'basilpress')."</a>";
	      echo bp()->view->bp_elem_close();
	    echo bp()->view->bp_elem_close();

	  echo bp()->view->bp_elem_close();
	}

	/*
	* WooCommerce Products
	*/

	// Show product stock label
	
	public function woocommerce_product_stock() {
		
		global $product;
	
		// Get the stock status (returns 'instock' or 'outofstock')
		$stock_status = $product->get_stock_status();
	
		// Get the stock availability label based on the stock status
		if($stock_status === 'instock') {
		$stock_label = __('Available', 'basilpress');
		} else
		if($stock_status === 'onbackorder') {
		$stock_label = __('Available to order', 'basilpress');
		} else {
		$stock_label = __('Coming soon', 'basilpress');
		}
	
		echo "<span class='stock-label $stock_status'>".$stock_label."</span>";
	
	}

	// Products: add percentage to sale badge

	public function add_percentage_to_sale_badge( $html, $post, $product ) {
	    if( $product->is_type('variable')){
	        $percentages = array();

	        // Get all variation prices
	        $prices = $product->get_variation_prices();

	        // Loop through variation prices
	        foreach( $prices['price'] as $key => $price ){
	            // Only on sale variations
	            if( $prices['regular_price'][$key] !== $price ){
	                // Calculate and set in the array the percentage for each variation on sale
	                $percentages[] = ( floatval( $prices['regular_price'][ $key ] ) - floatval( $price ) ) / floatval( $prices['regular_price'][ $key ] ) * 100;
	            }
	        }
	        // We keep the highest value
	        $percentage = round(max($percentages)) . '%';
	    } else {
	        $regular_price = (float) $product->get_regular_price();
	        $sale_price    = (float) $product->get_sale_price();

	        $percentage    = round(100 - ($sale_price / $regular_price * 100)) . '%';
	    }
	    return '<span class="onsale"><span class="onsale-percent">-' . $percentage .'</span></span>';
	}

	// Products: 	Setup a different images for archives if archive_featured custom field is set
	//				NOTE: archive_featured returns attachment ID

	public function product_featured_in_archives($image, $product, $size, $attr) {

		if(!function_exists('get_field')) return $image;

		// if we set an attribute like data-type="keep-original" image must be kept
		if(isset($attr['data-type']) && $attr['data-type'] == 'keep-original') return $image;
		
		if(!is_archive() && !is_main_query()) return $image;

		// this is needed to account for product variations
		$featured = ($product->is_type('variation')) ? get_field('archive_featured', $product->get_parent_id()) : get_field('archive_featured', $product->get_id());
		
		if(!$featured) return $image;
		$image = wp_get_attachment_image($featured, $size, false, $attr );
		return $image;

	}

	// Products: open buy button wrapper

	public function bp_archive_button_wrapper_open() {
		echo $this->bp_elem_open('div', array('buy-button-wrapper'));
	}

	// Products: close buy button wrapper

	public function bp_archive_button_wrapper_close() {
		echo $this->bp_elem_close('div');
	}

	// Products: alter button after add to cart

	public function woocommerce_change_js_view_cart_button( $params, $handle )  {
	    if( 'wc-add-to-cart' !== $handle ) return $params;

	    // Changing "view_cart" button text and URL
	    $params['i18n_view_cart'] = esc_attr__("Proceed Now", "basilpress"); // Text
	    $params['cart_url']      = esc_url( wc_get_checkout_url() ); // URL

	    return $params;
	}

	// Products: return name in cart from $instance->get_name() as some plugin might rely on parent product name for variations, which we do not 
	public function cart_item_name($cart_item_name, $cart_item, $cart_item_key) {
		return $cart_item['data']->get_name();
	}

	/* 
	* Variable Products
	*/ 

	// Variable Products: change prince as "Starting from..."

	public function custom_variable_price_html( $price, $product ) {
		// Check if the product is a variable product
		if ( $product->is_type( 'variable' ) ) {
			// Get the variation prices
			$prices = $product->get_variation_prices( true );

			// If there's more than one variation, find the minimum price
			if ( count( $prices['price'] ) > 1 ) {
				$min_price = min( $prices['price'] );
				$price = sprintf( __( 'Starting from %s', 'basilpress' ), wc_price( $min_price ) );
			}
		}

		return $price;
	}
	
	// Variable Products: add to cart via popup/modal within WC archives
	// NOTICE: We're using woocommerce_before_shop_loop_item because otherwise Yith Catalog Mode will add again the button

	public function maybe_add_variable_form() {
	
		if(is_product() || bp()->is_yith_catalog_enabled()) return;
	
		global $product;
	
		if(!$product->is_type('variable')) {
			remove_action('woocommerce_after_shop_loop_item', array($this, 'variable_popup_choice'), 20);
			if(!has_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart')) {
			add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
			}
		} else {
			remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
			add_action('woocommerce_after_shop_loop_item', array($this, 'variable_popup_choice'), 20);
		}

	}

	// Variable Products: render actual popup

	public function variable_popup_choice() {

		global $product;

		$attr = array('data-type' => 'keep-original');
		
		ob_start();

		echo "<div class='row ai-center product-summary'>";
			echo "<div class='col col-4'>".$product->get_image('thumbnail', $attr)."</div>";
			echo "<div class='col col-8'>
					<h2>".$product->get_title()."</h2>";
					if(get_field('subtitle')) {
					echo "<h3 class='product-subtitle'>".get_field('subtitle')."</h3>";
					}
			echo  "</div>";
		echo "</div>";
		echo "<div class='product-summary'>";
			echo  wpautop(wp_trim_words($product->get_short_description(), 35, "... <a class='all-details' href='".$product->get_permalink()."' title='".sprintf(__('More info on %s', 'basilpress'), $product->get_title())."'>".__('More details', 'basilpress')."</a>"));
		echo "</div>";
		
		echo "<div class='popup-add-to-cart'>";
			echo "<label>".__('Make your choice', 'basilpress')."</label>";
			woocommerce_variable_add_to_cart();
		echo "</div>";

		$content = ob_get_contents();

		ob_end_clean();

			$button = '<a class="button go-to-checkout a-appear" aria-label="Ordina prodotto" rel="nofollow">'.__("Choose and buy", "basilpress").'</a>';

		$settings = array('group' => 'product');

		sauce_create_popup($content, $button, $settings);

	}

	/*
	* WooCommerce Checkout
	*/

	// Checkout: add title to payment section

	public function bp_payment_title() {
		echo "<h2 class='payment-title'>".__("Payment method", "basilpress")."</h2>";
	}
  
	  // Checkout: customize field order
  
	public function customize_checkout($fields) {
		unset($fields['billing']['billing_address_2']);
		unset($fields['shipping']['shipping_address_2']);
		$fields['billing']['billing_fatt']['label'] = 'Ho bisogno della fattura';
		$fields['billing']['billing_fatt']['priority'] = 31;
		$fields['billing']['billing_cf']['priority'] = 30;
		$fields['billing']['billing_company']['priority'] = 32;

		$fields['billing']['billing_company']['class'] = "invoice-field a-disappear hide form-row form-row-wide";
		$fields['billing']['billing_vat']['class'] = "invoice-field a-disappear hide form-row form-row-wide";
		$fields['billing']['billing_nin']['class'] = "invoice-field a-disappear hide form-row form-row-wide";
		$fields['billing']['billing_pec']['class'] = "invoice-field a-disappear hide form-row form-row-wide";

		return $fields;
	}
  
	// Checkout and WC Forms: set label as placeholder
  
	public function placeholderify_woocommerce_form_fields($args, $key, $value) {
		$args['placeholder'] = $args['label'];
		$args['label'] = '';
		$args['class'] = $this->placeholderify_form_row($args['class']);
		return $args;
	}
  
	public function placeholderify_form_row($classes) {
		if(in_array('form-row-wide', $classes)) {
			$classes = array_diff($classes, array('form-row-wide'));
			$classes[] = $this->placeholderify_form_row;
			$this->placeholderify_form_row = ($this->placeholderify_form_row == 'this-row-first') ? 'this-row-last' : 'this-row-first';
		}
		return $classes;
	}
  
	public function placeholderify_checkout( $fields ) {
		foreach ( $fields as $section => $section_fields ) {
			foreach ( $section_fields as $section_field => $section_field_settings ) {
				$fields[$section][$section_field]['placeholder'] = $fields[$section][$section_field]['label'];
				$fields[$section][$section_field]['label'] = '';
			}
		}
		return $fields;
	}

	// Privacy Policy Checkout field and validation

	public function woocommerce_add_privacy_policy_checkout() {
		$policy_link = "<a href=".get_privacy_policy_url().">Privacy Policy</a>";
		$label = str_replace("[privacy_policy]", $policy_link, get_option( 'woocommerce_checkout_privacy_policy_text', sprintf( __( 'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our %s.', 'woocommerce' ), $policy_link)));
			woocommerce_form_field( 'woo_custom_checkbox_privacy_policy', array(
			'type' => 'checkbox',
			'class' => array('form-row privacy'),
			'label_class' => array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),
			'input_class' => array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
			'required' => true,
			'label' => $label
			)
		);
	}

	// Show notice if customer does not tick
	public function woocommerce_validate_privacy_checkout() {
		if (isset($_POST['woo_custom_checkbox_privacy_policy']) && $_POST['woo_custom_checkbox_privacy_policy'] != 1) {
			wc_add_notice( __( 'Please accept our privacy policy to place your order!', 'basilpress' ), 'error' );
		}
	}
  
	/*
	* Change Quantity on Checkout For WooCommerce
	*/

	// CQOC: add item thumbnail
  
	public function add_item_thumb_cqoc_cart($html, $cart_item, $cart_item_key) {
		if(strpos($html, 'cqoc_product_name') === false && !is_checkout()) return $html;
		if(is_cart()) return $html;
		$product = wc_get_product($cart_item['product_id']);
		$image = apply_filters( 'woocommerce_cart_item_thumbnail', $product->get_image(), $cart_item, $cart_item_key );
		if($image) {
			$html = $image.$html;
		}
		return $html;
	}
  
	/*
	* WPC Fly Cart
	*/
  
	// Fly Cart: set custom cart icon
  
	public function woofc_cart_icon($html) {
		$pattern = "/<i(.*?)<\/i>/s";
		$replacement = "<span class='my-cart'></span>";
		$html = preg_replace($pattern, $replacement, $html);
		return $html;
	}
  
	// Fly Cart: open custom total wrapper
  
	public function woofc_wrap_open($html) {
		$html .= bp()->view->bp_elem_open('div', array('woofc-total-wrap'));
		return $html;
	}
  
	// Fly Cart: close custom total wrapper

	public function woofc_wrap_close($html) {
		$html .= bp()->view->bp_elem_close('div');
		return $html;
	}
  
	// Fly Cart: Add vat to fly cart

	public function woofc_cart_vat() {
		if(!function_exists('wc_price')) return;
		$html = '<div class="woofc-data tax"><div class="woofc-data-left">'.__('VAT total', 'basilpress').'</div><div class="woofc-data-right">';
		$html .= wc_price(WC()->cart->get_cart_contents_tax());
		$html .= '</div></div>';
		$html .= '<div class="woofc-data shipping"><div class="woofc-data-left">'.__('Shipping total', 'basilpress').'</div><div class="woofc-data-right">';
		$html .= WC()->cart->get_cart_shipping_total();
		$html .= '</div></div>';
		return $html;
	}

	/*
	* Archive Header w/ Widget Area
	*/

	// Archive Header: open custom wrapper

	public function site_content_header_open() {
		if(sl_()->is_archive()) {
			echo bp()->view->bp_elem_open('div', array('site-content-header'));
			if(!is_search()) { ?>
				<header class="page-header has-max-width">
					<h1 class="page-title">
					<?php
						if(is_home()) {
							echo get_the_title( get_option('page_for_posts', true) );
						} else {
							echo get_the_archive_title();
						} 
						do_action('bp_inside_site_content_header_title');
						?>
					</h1>
				</header>
				<?php
			} else {
				echo generate_do_search_results_title('search');
			}
		}
	}

	// Archive Header: close custom wrapper

	public function site_content_header_close() {
		if(sl_()->is_archive()) {
			echo bp()->view->bp_elem_close('div');
		}
	}

	// Archive Header: load correct widget area

	public function blog_top_area() {

		if(!sl_()->is_archive()) return;

		if(function_exists('is_woocommerce') && is_active_sidebar('woocommerce-top-widget-area')) {
			echo bp()->view->bp_elem_open('div', array('top-widget-area'));
			dynamic_sidebar( 'woocommerce-top-widget-area' );
			echo bp()->view->bp_elem_close('div');
		} else
		if(is_active_sidebar('top-widget-area')) {
			echo bp()->view->bp_elem_open('div', array('top-widget-area'));
			dynamic_sidebar( 'top-widget-area' );
			echo bp()->view->bp_elem_close('div');
		}

	}

	/*
	* Central Logo
	*/

	// Central logo: add style for sizing

	public function central_logo_style($html) {
		if(isset(bp()->options['central_logo']) && bp()->options['central_logo'] && isset(bp()->options['logo_width']) && bp()->options['logo_width']) {
			$html = str_replace('class="site-logo"', 'class="site-logo" style="width: '.bp()->options['logo_width'].'px"', $html);
		}
		return $html;
	}

	// Central logo: load logo left & right widget areas

	public function logo_widget_areas() {

		$area = (doing_action('generate_before_logo')) ? 'logo-left-area' : 'logo-right-area';
		$left_active = (is_active_sidebar('logo-left-area')) ? true : false;;
		$right_active = (is_active_sidebar('logo-right-area') || is_active_sidebar('logo-right-area-wc')) ? true : false;

		// Displays dedicated sidebar if is WooCommerce && its sidebar is active
		if(function_exists('is_woocommerce') && sl_()->is_woocommerce() && $area == 'logo-right-area' && is_active_sidebar('logo-right-area-wc')) {
			$area = 'logo-right-area-wc';
		}

		// Left logo sidebar is activated only if logo is central,
		// in other cases, inline styles are not needed
		if(isset(bp()->options['central_logo']) && bp()->options['central_logo']) {
			if(isset(bp()->options['logo_width']) && bp()->options['logo_width']) {
				$width = intval((bp()->options['logo_width'] + 30) / 2);
				$style =	"style='max-width: calc(50% - ".$width."px)'";
			}
		}

		// Add right-only class if left sidebar is not there
		$classes = (!$left_active && $right_active) ? $area." only-right" : $area;

		// Do the sidebarrey ch-k ch-k ch-k z
		if ( is_active_sidebar($area) ) {
			echo "<div class='logo-widget-area $classes' $style>";
				dynamic_sidebar($area);
			echo "</div>";
		}

	}

	/*
	* Basil Advanced Menu - BAM
	*/

	// BAM: show menu sidebar

	public function bam_widget_area_view() {
	  if ( is_active_sidebar( 'primary-menu' ) ) :
	    dynamic_sidebar( 'primary-menu' );
	  endif;
	}

	// BAM: register widget area

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

	// BAM: add body class if active

	public function bam_body_class($classes) {
	  return array_merge($classes, array('bam-active'));
	}

	// BAM: disable primary menu if BAM is active

	public function bam_disable_primary_menu($value, $args) {
	  if($args->theme_location == 'primary') {
	    $value = false;
	  }
	  return $value;
	}

	// BAM: disable GP's mobile menu (BP's mobile menu already commented in scripts.js and style.css)
	
	public function mobile_menu_breakpoint($breakpoint) {
	  return '(max-width: 1px)';
	}

	public function desktop_menu_breakpoint($breakpoint) {
	  return '(min-width: 2px)';
	}

	/*
	* GeneratePress Fixes
	*/ 
	
	// GP Fixes: 	Header & footer max-width usually add content padding value to container width
	// 				but we want those nicely aligned

	public function header_footer_max_width_fix() {
		$spacings = get_option('generate_spacing_settings');
		if(!isset($spacings['header_left'])) {
			$spacings['header_left'] = 0;
			$spacings['header_right'] = 0;
			$spacings['footer_widget_container_right'] = 0;
			$spacings['footer_widget_container_left'] = 0;
			update_option('generate_spacing_settings', $spacings);
		}
	}
	
	// GP Fixes:	Sets max-width according to container width
	// 				for compatibility with SiteOrigin fullwidth CSS method
	// 				and other fixes based on BP settings

	public function bp_base_css_output($css) {

		$max_width = generate_get_setting('container_width').'px';
		$logo_width = generate_get_setting('logo_width').'px';

		/*
		$selectors = apply_filters('container_width_selectors', array(
		'.inside-article .entry-header',
		'.site-content-inner',
		'body.single .site-main',
		'body.single-product .site-main > article',
		'.woocommerce-archive-wrapper'
		));
		*/

		$selectors = array(
			'.site-header .header-image'
		);

		$css->set_selector(implode(', ', $selectors));
		$css->add_property('min-width', $logo_width);

		/* 
		* container_width_selectors are containers that must be limited to the desidered max width in order
		* for fullwidth css rows to work
		*/

		$selectors = apply_filters('container_width_selectors', array(
			'body:not(.so-builder) .site-content > div',
			'body.so-builder .inside-article .entry-header',
			'body .has-max-width'
		));

		$css->set_selector(implode(', ', $selectors));

		$css->add_property('max-width', $max_width);
		$css->add_property('margin-left', 'auto');
		$css->add_property('margin-right', 'auto');

		// Sets entry-content margin-top for main content according to BP settings

		$selectors = array(
			'body .entry-content:not(:first-child)',
			'body .entry-summary:not(:first-child)',
			'body .page-content:not(:first-child)',
			'.site-content-header > :first-child',
			'.site-content-header + .site-content',
			'.page:not(.home) .site-main > article > .inside-article > .entry-header',
			'.single .site-content > article > .entry-header',
			'.woocommerce-account .site-main > article > .inside-article > .entry-content:first-child',
			'.account-box',
			'.single .site-content-inner',
			'.woocommerce .woocommerce-customer-details',
			'.woocommerce .woocommerce-order-details',
			'.woocommerce .woocommerce-order-downloads',
		);

		$css->set_selector(implode(', ', $selectors));
		$css->add_property('margin-top', BP_PANEL_MARGIN_FALLBACK.'px');


		// Sets site-footer margin-top for non-sopb pages according to BP settings

		$selectors = array(
		'body:not(.so-builder) .site-footer',
		'.site-content-header > * + *'
		);

		$css->set_selector(implode(', ', $selectors));
		// $css->add_property('margin-top', round(number_format(BP_PANEL_MARGIN_FALLBACK * 1.333, 2)).'px');
		$css->add_property('margin-top', BP_PANEL_MARGIN_FALLBACK.'px');

	}

	// GP Fixes: remove header area, this was used in old GP float layout, might be removed (TBR)

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
			<div class="header-widget grid-container">
				<?php dynamic_sidebar( 'header' ); ?>
			</div>
			<?php
		endif;
	}

	// GP Fixes: Adds useful body classes

	public function bp_body_classes($classes) {

		if(isset(bp()->options['nav_is_fixed']) && bp()->options['nav_is_fixed']) $classes[] = 'nav-is-fixed';
		if(isset(bp()->options['header_on_left']) && bp()->options['header_on_left']) $classes[] = 'header-on-left';

		if(isset(bp()->options['central_logo']) && bp()->options['central_logo']) {
			$classes[] = 'central-logo';
			if(is_active_sidebar('logo-left-area') || is_active_sidebar('logo-right-area') ) {
				$classes[] = 'has-logo-area';
			}
		}

		if(get_post_meta(get_the_ID(), 'has_so_builder', true) == 1) {
			$classes[] = 'so-builder';
		}

		if(get_post_meta(get_the_id(), '_basil-no-content-margin')) {
			$classes[] = 'no-content-margin-css';
		}

		if(function_exists('siteorigin_panels_setting')) {
			$settings = siteorigin_panels_setting();
			if($settings['margin-bottom'] == 0) {
				$classes[] = 'padded-rows';
			}
		}

		if(class_exists('sauce_lib')) {
			if(sl_()->is_archive()) {
		    $classes[] = 'is-archive';
		  }
		}

		// we add this class because otherwise WooCommerce CSS rules will overwrite bp styles
		$classes[] = 'woocommerce-block-theme-has-button-styles';

		return $classes;

	}

	// GP Fixes: Adds pinch to zoom lock

	public function bp_meta_viewport($string) {
		return '<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">';
	}

	// GP Fixes: removes 'archive' word on some archives

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

	// GP Fixes: entry-header might show up empty if "hide title" option is selected

	function fix_generate_show_entry_header($show) {
		$show_option = generate_show_title();
		if(get_post_type() == 'page' && !$show_option) {
			$show = false;
		}
		return $show;
	}

	// GP Fixes: remove page header according to BP customs setting

	public function bp_remove_page_header() {
		if(isset(bp()->options['page_header_global']) && bp()->options['page_header_global']) return;
		remove_action( 'generate_after_header', 'generate_featured_page_header', 10 );
		if(is_singular() && get_post_type() != 'post') {
			remove_action( 'generate_before_content', 'generate_featured_page_header_inside_single', 10);
		}
	}

	// GP Fixes: nav search string text

	public function bp_nav_search($html_output) {
		$text = apply_filters('bp_nav_search_placeholder_text', __('Search...', 'basilpress'));
		$html_output = str_replace('class="search-field"', 'class="search-field" placeholder="'.$text.'" ', $html_output);
		$html_output = str_replace('</form>', '<a class="close-search bp-search-item"></a></form>', $html_output);
		return $html_output;
	}

	// GP Fixes: show BP loader

	public function bp_loader() {
		echo "<div class='bp-loader'><div class='bp-spinner'></div></div>";
	}

	// GP Fixes: 	removes sidebar in woocommerce pages if active
	//				bp_sidebar_override filter can be used to correct layout in other situations

	public function disable_sidebar( $layout ) {
		// Keep sidebar only for single posts
		if ((function_exists( 'is_woocommerce' ) && is_woocommerce())
				|| (!is_category() && !is_home() && !is_author() && !is_tag() && !is_singular('post'))) {
				$layout = 'no-sidebar';
		}
		// Or else, set the regular layout
		return apply_filters('bp_sidebar_override', $layout);
	}

	/*
	* Primary Navigation
	*/

	// Primary Navigation: open navigation wrapper

	public function bp_header_nav_wrap_open() {
		echo $this->bp_elem_open('div', array('header-nav-wrap'));
	}

	// Primary Navigation: close navigation wrapper

	public function bp_header_nav_wrap_close() {
		echo $this->bp_elem_close('div');
	}

	// Primary Navigation: 	open main-nav wrapper
	// 						This way it's possible to add a secondary menu which will open in the hamburger
	// 						F.eg.: add_action("generate_after_primary_menu", "my_another_menu", 10);
	
	public function wrap_main_nav_open() {
		echo "<div id='main-nav-wrap' class='main-nav-wrap'>";
	}

	// Primary Navigation: 	close main-nav wrapper

	public function wrap_main_nav_close() {
		echo "</div><!-- #main-nav-wrap -->";
	}

	/**
	* SiteOrigin Compat
	*/

	// SiteOrigin Compat: Fix Widget Margin as function of Row Margin

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

	// SiteOrigin Compat: Fix Row Padding if margin-bottom is zero, which is used to set padding mode
	
	public function custom_panels_data($panels_data, $post_id) {

		$settings = siteorigin_panels_setting();

		if($settings['margin-bottom'] == 0 && isset($panels_data['grids'])) {
			foreach($panels_data['grids'] as &$grid) {
				
				// We must always respect custom set paddings!

				if(isset($grid['style']['padding']) && !empty($grid['style']['padding'])) {
					
					// If there's a custom padding set,
					// we always need to override the GP-compatibility css rule
					$grid['style']['padding'] = $grid['style']['padding'].' !important';
					
				}

				if(isset($grid['style']['mobile_padding']) && !empty($grid['style']['mobile_padding'])) {
					
					// If there's a custom mobile padding set,
					// we always need to override the GP-compatibility css rule
					$grid['style']['padding'] = $grid['style']['padding'].' !important';
					
				}

				// If there's no custom padding set, then we set out automatic rules

				if(!isset($grid['style']['padding'])) {

					// If there's no padding set,
					// we apply the general padding for added layout rythm and consistency
					$grid['style']['padding'] = BP_PANEL_MARGIN_FALLBACK.'px 0 '.BP_PANEL_MARGIN_FALLBACK.'px 0';
					
					// If row is stretched and side to side,
					// we need to override the GP-compatibility css rule
					if((isset($grid['style']['s2s_row']) && $grid['style']['s2s_row'] == 1) && $grid['style']['row_stretch'] == 'full-width-stretch') {

						$grid['style']['padding'] = BP_PANEL_MARGIN_FALLBACK.'px 0 '.BP_PANEL_MARGIN_FALLBACK.'px 0 !important';

					}

				}

			}
		}

		return $panels_data;

	}

	// SiteOrigin Compat: Add custom row classes

	public function sopb_add_row_classes($classes, $row) {

		if(isset($row['layout']['s2s_row']) && !empty($row['layout']['s2s_row'])) {
			$classes[] = 'side-to-side';
		}
		
		return $classes;
	
	}

	// SiteOrigin Compat: Add custom row options

	public function sopb_add_row_options($fields) {

		$fields['s2s_row'] = array(
			'name'        => __( 'Side to side row', 'basilpress' ),
			'type'        => 'checkbox',
			'priority'    => 11,
			'group'    => 'layout',
			'default'     => false,
			'description' => __( 'Removes right and left padding when content is stretched.', 'basilpress' ),
		);
		
		return $fields;
	
	}

	/*
	* Color Pickers
	*/

	// Color Pickers: set color defaults according to GP customizer settings
	// NOTE: $colors should become a class property to avoid recalculations

	public function color_pickers_default() {

	  $options = bp()->options;
		$colors = array();


	  if(isset($options['global_colors']) && !empty($options['global_colors'])) {

			// Adding GP theme defined colors to a simple array
	    foreach($options['global_colors'] as $data) {
	      $colors[] = $data['color'];
	    }

	  }

	  // Printing stuff in WP Admin (it's just wp-admin right?)
		if(!doing_action('acf/input/admin_footer')) { ?>
	  <script>
		  jQuery(document).ready(function($) {
				if(typeof $.wp == 'undefined') return;
		    $.wp.wpColorPicker.prototype.options = {
		      palettes: <?php echo json_encode($colors); ?>
		    };
		  });
		</script>
	<?php
		} else { ?>
		<script type="text/javascript">
			(function($) {

				acf.add_filter('color_picker_args', function( args, $field ){

				// do something to args
				args.palettes = <?php echo json_encode($colors); ?>

				// return
				return args;

				});

			})(jQuery);
		</script>
	<?php }

 	}

	/**
	* Pagenavi Integration
	*/

	// Pagenavi: remove gp nav first

	public function remove_gp_nav() {
		if(!function_exists('wp_pagenavi')) return;
	  if (!is_single() || is_archive() || is_author()) {
	      add_filter( 'generate_show_post_navigation', '__return_false' , 9999);
	  }
	}

	// Pagenavi: show pagenavi when needed

	public function pagenavi_integration() {
		if(!function_exists('wp_pagenavi')) return;
		if(!is_author()) {
		wp_pagenavi();
		}
	}

	/*
	* BP Structure
	*/

	// BP structure: add read more button

	public function bp_archive_post_link() {
		if(sl_()->is_archive()) {
		echo "<a class='button text-center' href='".get_the_permalink()."' title='".get_the_title()."'>".__("Read more", 'basilpress')."</a>";
		}
	}

	// BP structure: open site content inner wrapper for layout consistency in different templates

	public function site_content_inner_open() {
		if(is_home() || is_category() || is_tag() || is_search() || is_author() || (is_singular() && generate_get_layout() == 'right-sidebar')) {
			echo $this->bp_elem_open('div', array('site-content-inner has-max-width'));
		}
	}

	// BP structure: close site-content-inner wrapper

	public function site_content_inner_close() {
		if(is_home() || is_category() || is_tag() || is_search() || is_author() || (is_singular() && generate_get_layout() == 'right-sidebar')) {
			// just need to add this generate_before_footer
			echo "</div><!-- close .site-content-inner -->";
		}
	}

	// BP structure: open basil-archive-wrap wrapper that contains only posts items

	public function basil_archive_open() {
		if(is_home() || is_archive() || is_search()) {
			echo $this->bp_elem_open('div', array('basil-archive-wrap', 'basil-post-wrap'));
		}
	}

	// BP structure: close basil-archive-wrap wrapper

	public function basil_archive_close() {
		if(is_home() || is_archive() || is_search()) {
			echo "</div><!-- .basil-post-wrap -->";
		}
	}

	// BP Structure: archive post header wrapper

	public function bp_archive_postheader_open() {
		if(sl_()->is_archive()) {
		echo bp()->view->bp_elem_open('div', array('post-header'));
		}
	}

	public function bp_archive_header_close() {
		if(sl_()->is_archive()) {
		echo bp()->view->bp_elem_close('div');
		}
	}

	// BP Structure:	Adds term list or date according to post type
	//					NOTE: event type integrates with Sito.Express Simple Events

	public function bp_term_list() {

		$type = get_post_type();
	
		if(!sl_()->is_archive() && !is_singular()) return;
	
		if($type == 'post') {
			echo bp_get_the_term_list(get_the_ID(), 'category', '<div class="terms-list">', ', ', '</div>', 1);
		} else
		if($type == 'event') {
	
			$next_date = explode(' ', date_i18n('d F', strtotime(get_post_meta(get_the_ID(), 'next_date', true))));
			$after_next_date = (get_post_meta(get_the_ID(), 'after_next_date', true)) ? explode(' ', date_i18n('d F', strtotime(get_post_meta(get_the_ID(), 'after_next_date', true)))) : false;
			$class = ($next_date && $after_next_date) ? 'more-dates' : 'single-date';
		
			echo "<div class='event-dates $class'>";
				if($next_date) {
				echo "<div class='event-date'>";
					echo "<h1><span class='event-day'>".$next_date[0]." </span>";
					echo "<span  class='event-month'>".$next_date[1]."</span></h1>";
				echo "</div>";
				}
				if($after_next_date) {
				echo "<div class='event-date'>";
					echo "<h1><span class='event-day'>".$after_next_date[0]." </span>";
					echo "<span  class='event-month'>".$after_next_date[1]."</span></h1>";
				echo "</div>";
				}
			echo "</div>";
			echo '<div class="terms-list">'.__('Event', 'basilpress').'</div>';
	
		} else
		if($type == 'product') {
			echo bp_get_the_term_list(get_the_ID(), 'product_cat', '<div class="terms-list">', ', ', '</div>', 1);
		}
	
	}

	/*
	* BP Layout Methods
	*/

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
