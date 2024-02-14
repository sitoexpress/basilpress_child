# BasilPress Changelog
This changelog will still be used to keep track of changes, as the github repo won't necessarily follow our internal release history.
* Current release: 1.2.0

## v.1.2.0
* misc:             many files renamed for easier maintenance / customization
* wc.js:            fixed vat_toggle not working as expected
* wc.css:           fixed basic woocommerce grid
* checkout:         user first and last name fallback to billing name if none present in WC_Customer object
* checkout:         fixed edit button not translatable
* checkout, cart:   hides shipping methods when free shipping is available
* view:             custom_panels_data rework to assure compatibility with SO PB 2.29.4
* view:             fixed woocommerce-top-widget-area not showing
* view:             added bp_sidebar_override filter && max-width wrapper now shows up on is_singular() if there's a sidebar
* view:             fix to cqoc 3.x compatibility
* view:             fix to GP quirks on page header when title is not visible
* view:             added customization to wc order emails
* view:             variable product now open a popup much cool very wow
* view:             reorder and refactoring of the file structure
* view:             added privacy policy checkboxes for user registration and checkout forms
* view:             variable product price label as "Starting from..."
* wc.css:           better styling for notices checkout
* wc.css:           general improvements over mobile layout consistency and responsiveness for cart, checkout and WPC Fly Cart css
* controller:       added is_yith_catalog_enabled() method to check if it's enabled for current use case
* controller:       added s2s_row setting for SO PB panel style in order to create side-to-side stretched rows

## v.1.0.0
* functions:        BP_WP_LOGIN_BG constant sets wp-login background
* functions:        bp_is_archive() function allow to detect more easily a wordpress archive and is customizable via the bp_is_archive filter -> moved to sauce, callable as sl_()->is_archive()
* bp.view:          is-archive class applied to body when sl_()->is_archive() is true
* bp.view:          added support to Woo Fly Cart (WPC) adding vat in fly cart
* bp.view:          added Fattura Elettronica fields in order e-mail
* Removed useless/unused image sizes
* SiteOrigin fullwidth rows now work fine using CSS method out of the box
* General improvements in BasilPress CSS to provide a consistent appearence out of the box
 * header-widget and footer-area now behave consistently and are Layout Builder dependant for background and internal padding settings
 * pages, posts, archives and likely any cpt or ctx will display consistently
* General improvements in WooCommerce CSS to provide a consistent appearence out of the box
 * huge improvements in default myaccount, cart & checkout pages
 * huge improvements in table styles
 * fully integrated with Change Quantity on Checkout for WooCommerce plugin
 * added generic svg icon for account: just add my-account class to a menu item
 * added generic svg icon to WPC Fly Cart
 * renamed bp.woocommerce.css to wc.base.css
 * added wc.custom.css
 * removed woocommerce-layout & woocommerce-smallscreen css loaded by WC
 * enhancements in buy-button UI/UX for simple products purchase
 * added 'woocommerce-block-theme-has-button-styles' to provide consistent behaviour, as BasilPress customizes these things via plain and simple css in style.custom.css
* General colors are now defined by css vars which follows Bootstrap naming convention. There can be customized in the GP's customizer.
  * --white
  * --light
  * --dark
  * --primary, --secondary
  * --accent-1, --accent-2, --accent-3
  * --warning, --info, --success, --danger


## v.0.6.3
admin.style:      added rule to prevent right sidebar in post edit show hz scroll for whatever stupid reason
bp.controller:    added central_logo mode in customizer which allows setting a central logo in the header,
                  enabling left & right logo sidebars
                  added _basil-no-content-margin meta as GP layout post option to control content margin via CSS (.no-content-magin-css class applied to body)
bp.view:          no-content-magin-css class is applied to body if _basil-no-content-margin post meta is set
                  color_pickers_default ACF compat
                  central logo compat
                  added check for $panels_data['grids']
style.css:        added .no-content-margin-css rule
                  central logo compat
scripts.js:       central logo compat
style.custom:     added SiteOrigin CSS FullWidth rules to make 100% stretched rows work with via CSS which is better, still testing the PHP counterpart
                  central logo compat
                  added buttons markup to target Sauce Lib button-menu class so that all buttons will look the same
functions:        added commented out functions to conveniently add custom/cdn fonts without always copypasting the same stuff
assets:           added fonts folder & empty fonts.css stylesheet for any @font-face we might need


## v.0.6.2
bp.view:          fixed php notice showing up if no global colors are set in GP options
bp.view:          fixed unwanted padding calculation for padding & margin max-width VS container max-width in GP
all:              mobile breakpoint is now 810px to match ipad vertical breakpoint

## v.0.6.1
* scripts.js:     content_margin now applies integer margin + 0px when no-content-margin class is found
* scripts.js:     commented some console.log from content_margin
* bp.view:        now SOPB & wp-admin color pickers will default to GP global colors

## v.0.6.0
* style.css:      style complies with Basil Advanced Menu (BAM) .bam-active class
* bp_nav:         lock_body is now applied to html as sauce lib already does
                  main-nav margin-top is now set as header-nav-wrap height which should always be consistent
* functions.php:  added BP_PANEL_MARGIN_FALLBACK settings: when using zero row margin, this will keep widget margin bottom value
* functions.php:  added BP_ADVANCED_MENU to activate widget-based menu
* bp.view:        changes to allow BAM functionality to work
* scripts.js:     changes to allow BAM functionality to work
* style.css:      changes to allow BAM functionality to work
* bp.view:        implemented BP_PANEL_MARGIN_FALLBACK in custom_widget_general_margin method
* bp.view:        .padded-rows class is applied to body when zero row margin is set in SOPB options
* bp.view:        BP_PANEL_MARGIN_FALLBACK is added as padding-top and padding-bottom when row's margin-bottom is zero

## v.0.5.5
* bp.view:  bp_remove_page_header did not work on cpts

## v.0.5.4
* style.custom.css: fixed :focus :hover issues for button custom styles

## v.0.5.3
* bp.woocommerce: quantity +/- must not be added to header/footer elements
* view:           basil_archive_open missing echo
* view:           basil_archive_open working in search
* style.css:      post image fix from tag24

## v.0.5.2
* view:       added post edit link in frontend
* controller: disables YOAST annoying ads
* controller: removes WP useless duotone SVGs
* scripts.js: bp_nav() compat with Max Mega Menu
* style.css:  bp_nav() compat with Max Mega Menu
* style.css:  site-main is now 100% by default
* style.css:  box-shadow: none added to button styling
* style.css:  removed .js selector from .bp-loader
* header.php: delete as not needed
* style.css:  added fix for woocommerce Photoswipe going below header
* view:       bp_elem_open refactoring, it is now possible to filter ids, classes, data-attr and style rules via the proper filters
* view:       archive title was removed within 'remove_gp_nav' while 'basil_archive_open' was fired twice
* view:       basil-posts-wrap now uses bp_elem_open
* scripts:    menu-open class is now applied to body
* style.css:  minor optimizations

## v.0.5
* initial code refactoring: bp.init class has been removed; bp.setup is renamed in bp_controller; bp.template is now bp_view
* fully compatible with gp 3.1.0 flex layout
* bp_view should now override any gp primary-menu position settings in the customizer so than all elements always fall within the <header>
* style.custom.css no longer applies color/background rules for the menu: the style can be defined via GP's customizer as of gpp 3.1.0
* scripts.js: fixed a bug in content_height preventing correct setup

## v.0.3.8
* scripts.js: bp-loader is now removed after 1.5s it disappears

## v.0.3.7
* bp.template.class.php: added several isset() check to avoid warnings
* bp.template.class.php: added support to wp pagenavi
* bp.template.class.php: now SOPB bottom widget margin is defined as "row_margin/SO_PANEL_MARGIN_DIVIDER" set in functions.php.
* style.custom.css: added basic wp pagenavi style
* style.custom.css: fixes in nav-related css rules to override changes made by GP 3.1.0

## v.0.3.6
* Improved a lot of general stuff about WooCommerce
* Improved a lot of general stuff about CSS
* site-header and header-nav-wrap defaults to transparent background

## v.0.3.5
* bp.style: html element overflow fixed for the 100th time
* bp.init: added rewrite rule flush fix when wpml+wc are installed and home url != site url
* bp.template: now bp_debug array needs BP_DEBUG constant set in order to print output
* bp.template: added buy-button-wrapper to woocommerce button
* bp.woocommerce.js: added add_to_cart_dynamics
* bp.woocommerce.css:  added buy-button-wrapper related CSS
* bp.custom.css:  added woocommerce button classes to general button styling rules
* shit ton of css fixes for better out of the box styling

## v.0.3.2
* bp.init: added conditional loading for bp.woocommerce.css if WC_VERSION is defined
* bp.init: removed wp_enqueue_script and wp_deregister_script for jquery and jquery migrate as they are set in sauce-lib
* bp.woocommerce.css: added color-related rules in the file
* bp.woocommerce.js:  added javascript +/- buttons for cart and checkout
* bp.scripts.js: removed ";" and used "$" instead of "jQuery" to save size
* bp.scripts.js: minor fixes for jQuery 3.x compat
* bp.scripts.js: main_nav_margin doubled when applied in order to have better spacing
* function.php: BP_VER constant now matches theme version
* bp.template.php:  generatepress header-widget area is now placed outside the inside-header div, so it goes full-width as it is
                    the way it's usually used

## v.0.3.0
* style.custom.css: removed MIE styles for footer
* styles: improved header css
          fixed nav-search which is now fully functional
          added Structural Padding section in style.custom.css
* bp_template: now posts are wrapped within .basil-post-wrap
* bp_setup: added loops folder for SO Post Loop widget with generic loop that will look like theme defined archives
* bp_init: now scripts/styles enqueue are tied to BP_VER constant defined in functions.php


## v.0.2.8
* bp.setup.class.php: bp_content_single_post() should always return $options otherwise customizer won't work
* bp.init.class.php: added loading for wp-admin bp.custom.scripts.admin.js and bp.custom.style.admin.css
* style.custom.css: more specific .main-navigation rules in order to overwrite GP defaults
* bp.scripts.js: fixed go_to_anchor not actually going to anchor
* bp.template.class.php: bp_remove_page_header now removes header image when is_singular() - aka a cpt - and keeps it when is_single() - aka a 'post'

## v.0.2.7
* style.css: started cleanup, some customizable rules have been moved to style.custom.css
* bp.setup.class.php: added support for SiteOrigin Page Builder Layouts in /view/ folder
* bp.template.class.php: added bp_meta_viewport() method to add "viewport" meta tag in header
* bp.template.class.php: added bp_content_single_post() to ensure single posts always display full content
* functions.php: added some snippets that can be useful to customize the child theme

## v.0.2.5
* Fixed content_margin & content_height may routinely change layout due to DOM mismatch over previous basilblank Theme
  -> issue may still be found due to footer being placed outside content div in GP
* Several css fixes on mobile menu CSS for better usability on Apple device

## v.0.2.3
* bp_init & bp_setup moved to singleton pattern
* added bp.custom.scripts.js and style.custom.css for simpler update on modified BP copies
* bp_template class: added remove archive word from basilblank
* bp_setup class: added CF7_LOGO shortcode for Contact Form 7 from basilblank
* header.php: updated to match generatepress >= 2.4

## v.0.2.2
* bp.scripts.js:  fixed content_margin & content_height conflict if flex-vertical-align class applied
* bp.scripts.js:  fixed content_height couldn't stop applying/removing height in specific cases
* bp.scripts.js:  content_height site_h now set on body's outerHeight(1,1) as wrogly used .site which
                  excluded footer resulting in unexpected behaviour in specific situations

## v.0.2.1
* Fixed content_margin() not working
* Added custom primary menu CSS barebone in style.css

## v.0.2
* Fixed bp_nav() submenus (js & css)
* Fixed content_height() applying wrong height when flex-column class applied
* Set .header-nav-wrap background to rgba(255,255,255,1)
* Added option to globally disable the page header

## v.0.1
* Added left-column layout
* Added fixed header layout
* General GeneratePress compatibility
* bp.scripts.js refactory from original Basil theme
