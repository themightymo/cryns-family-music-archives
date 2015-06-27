<?php
/*
Plugin Name: Custom Taxonomies Menu Widget
Plugin URI: http://www.studiograsshopper.ch/custom-taxonomies-menu-widget/
Version: 1.3.1
Author: Ade Walker, Studiograsshopper
Author URI: http://www.studiograsshopper.ch
Description: Creates a simple menu of your custom taxonomies and their associated terms, ideal for sidebars. Highly customisable via widget control panel.
*/

/*  Copyright 2010-2013  Ade WALKER  (email : info@studiograsshopper.ch) */

/*	License information
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License 2 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

The license for this software can be found here: 
http://www.gnu.org/licenses/gpl-2.0.html
*/

/* 	About Version History info:
Bug fix:	means that something was broken and has been fixed
Enhance:	means code has been improved either for better optimisation, code organisation, compatibility with wider use cases, etc
Feature:	means new functionality has been added
*/

/* Version History

1.3.1
Bug fix: Tweaked admin CSS to fix new-look WP 3.8 Dashboard styles

1.3
Enhance: Added new control panel options for Terms Handling, which determine how the plugin should treat new terms added to a taxonomy since the last time the widget options were Saved by the user
Enhance: Major re-write to integrate options and to treat new terms added to a taxonomy since last Save
Enhance: SGR_CTMW_DOMAIN deprecated
Bug fix: Changed textdomain to string 'sgr-ctmw', no longer Constant
Bug fix: Improved sanitisation/validation when widget options are saved
	
1.2.2
Bug fix: Fixed PHP data type error
	
1.2.1
Bug fix: Fixed PHP error on upgrade with $known_terms returning NULL.
	
1.2
Bug fix: Now ignores taxonomies with no terms
Bug fix: Fixed missing internationalisation for strings in the widget form
Bug fix: Enqueues admin CSS properly now
Enhance: Upped minimum WP version requirement to 3.2 - upgrade!
Enhance: Widget sub-class now uses PHP5 constructor
Enhance: Widget control form made wider, less scrolling required for long taxonomy checklist
Enhance: Added activation hook function for WP version check, deprecated SGR_CTMW_WP_VERSION_REQ constant
Enhance: sgr_ctmw_wp_version_check() deprecated
Enhance: sgr_ctmw_admin_notices() deprecated
Enhance: Added SGR_CTMW_HOME for plugin's homepage url on studiograsshopper
Enhance: Plugin files reorganised, sgr-ctmw-admin-core.php no longer used
Feature: 'hide_empty' options added, to allow display of empty terms in the menu
Feature: New terms are now automatically added to menu, and 'checked' in the widget form
	
1.1.1
Bug fix: Removed debug code from sgr_ctmw_wp_version_check()
	
1.1
Feature: Added option to hide Taxonomy title
Feature: Added option to select whether or not to display terms as a hierarchy	

1.0
Feature: First public release

*/



/* ******************** DO NOT edit below this line! ******************** */


/***** Prevent direct access to the plugin *****/
if ( ! defined( 'ABSPATH' ) ) {
	exit( _( 'Sorry, you are not allowed to access this page directly.' ) );
}



register_activation_hook( __FILE__, 'sgr_ctmw_activation' );
/**
 * This function runs on plugin activation.
 * It checks to make sure that the minimum WP version is installed
 *
 * @uses network_admin_url(), as this fallbacks to admin_url() if no multisite
 *
 * @since 1.2
 */
function sgr_ctmw_activation() {

	$wp_version_required = '3.8';
	
	$wp_valid = version_compare( get_bloginfo( "version" ), $wp_version_required, '>=' );
	
	if ( ! $wp_valid ) {
        
        deactivate_plugins( plugin_basename( __FILE__ ) ); /** Deactivate ourself */
		
		wp_die( sprintf( __('Sorry, this version of the Custom Taxonomies Menu Widget plugin requires WordPress %s or greater. <br /><a href="%s">Go back to the Dashboard > Plugins screen</a>.' ), $wp_version_required, network_admin_url() . '/plugins.php' ) );
	}
}


add_action( 'plugins_loaded', 'sgr_ctmw_init' );
/**
 * Initialise plugin
 *
 * - Defines constants
 * - Sets internationalisation global variable
 * - Loads plugin files
 * - Initialises all action/filter hooks needed
 *
 * Note: hooked to 'plugins_loaded' because admin_init or init runs too late
 * for widgets_init, which is needed by the register_widget() function
 *
 * @since 1.2
 * @updated 1.3.1
 */
function sgr_ctmw_init() {

	// Define constants
	define( 'SGR_CTMW_URL',				plugins_url( 'custom-taxonomies-menu-widget' ) );
	define( 'SGR_CTMW_DIR',				plugin_dir_path( __FILE__ ) );
	define( 'SGR_CTMW_VER',				'1.3.1' );
	define( 'SGR_CTMW_FILE_NAME',		'custom-taxonomies-menu-widget/sgr-custom-taxonomies-menu-widget.php' );
	define( 'SGR_CTMW_HOME',			'http://www.studiograsshopper.ch/custom-taxonomies-menu-widget/' );


	// Internationalisation functionality
	global $sgr_ctmw_text_loaded;
	$sgr_ctmw_text_loaded = false;


	// Include files
	require_once( SGR_CTMW_DIR . '/includes/sgr-ctmw-class-widget.php' );


	// Action hooks and filters
	if( is_admin() ) {
		// Admin - Adds additional links in main Plugins page
		add_filter( 'plugin_row_meta', 'sgr_ctmw_plugin_meta', 10, 2 );

		// Admin - Loads CSS for widget form
		add_action( 'admin_enqueue_scripts', 'sgr_ctmw_loadcss_admin_head', 100 );
	}
}


/**
 * Function to load textdomain for Internationalisation functionality
 *
 * Loads textdomain if $sgr_ctmw_text_loaded is false,
 * called by SGR_Widget_Custom_Taxonomies_Menu::form()
 *
 * Note: .mo file should be named as per the text domain, ie sgr-custom-taxonomies-menu-widget-xx_XX.mo
 * and placed in the CTMW plugin's languages folder, where xx_XX is the language code, eg fr_FR for French etc.
 *
 * @since 1.0
 *
 * @uses load_plugin_textdomain()
 * @global $sgr_ctmw_text_loaded bool defined in sgr-custom-taxonomies-menu-widget.php
 * @return null if $sgr_ctmw_text_loaded is true, or loads textdomain
 */
function sgr_ctmw_load_textdomain() {
	
	global $sgr_ctmw_text_loaded;
   	
	// If textdomain is already loaded, do nothing
	if( $sgr_ctmw_text_loaded ) {
   		return;
   	}
	
	// Textdomain isn't already loaded, let's load it
   	load_plugin_textdomain( 'sgr-ctmw', false, dirname( plugin_basename(__FILE__) ) . '/languages' );
   	
	// Change variable to prevent loading textdomain again
	$sgr_ctmw_text_loaded = true;
}


/**
 * Function to load Admin CSS
 *
 * Hooked to 'admin_enqueue_scripts' - only loads on widgets.php
 *
 * @since 1.0
 *
 * @global $pagenow - admin page name
 */
function sgr_ctmw_loadcss_admin_head() {

	global $pagenow;
	
	if( $pagenow == 'widgets.php' ) {
	
		//wp_enqueue_style( $handle, $src, $deps, $ver, $media );
		wp_enqueue_style( 'ctmw-admin', SGR_CTMW_URL . '/includes/sgr-ctmw-ui-admin.css', array(), SGR_CTMW_VER );
	}
}


/**
 * Display Plugin Meta Links in main Plugin page in Dashboard
 *
 * Adds additional meta links in the plugin's info section in main Plugins Settings page
 *
 * Hooked to 'plugin_row_meta filter' so only works for WP 2.8+
 *
 * @since 1.0
 * @updated 1.3
 *
 * @param array $links Default links for each plugin row
 * @param string $file plugins.php filehook
 *
 * @return array $links Array of customised links shown in plugin row after activation
 */
function sgr_ctmw_plugin_meta($links, $file) {
 
	// Check we're only adding links to this plugin
	if( $file == SGR_CTMW_FILE_NAME ) {
	
		// Create CTMW links
		$config_link = sprintf( '<a href="%s" target="_blank">%s</a>', SGR_CTMW_HOME, __( 'Configuration Guide', 'sgr-ctmw' ) );
		
		$faq_link = sprintf( '<a href="%sfaq/" target="_blank">%s</a>', SGR_CTMW_HOME, __( 'FAQ', 'sgr-ctmw' ) );
		
		return array_merge(
			$links,
			array( $config_link, $faq_link )
		);
	}
 
	return $links;
}