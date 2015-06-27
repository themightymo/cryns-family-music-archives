<?php
/**
 * Uninstall file as per WP 2.7+
 *
 * @author Ade WALKER  (email : info@studiograsshopper.ch)
 * @copyright Copyright 2010-2013
 * @package custom_taxonomies_menu_widget
 * @version 1.3.1
 *
 *
 * @since 1.0
 */

/**
 * Prevent direct access to this file 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( _( 'Sorry, you are not allowed to access this file directly.' ) );
}

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}