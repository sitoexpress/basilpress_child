# BasilPress Changelog
This changelog will still be used to keep track of changes, as the github repo won't necessarily follow our internal release history.
* Current release: 0.6.0

## v.0.6.1
* scripts.js:     content_margin now applies integer margin + 0px when no-content-margin class is found
* scripts.js:     commented some console.log from content_margin

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
