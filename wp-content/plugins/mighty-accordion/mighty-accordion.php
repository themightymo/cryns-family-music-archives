<?php
/*
Plugin Name: Mighty Accordion
Plugin URI: http://www.themightymo.com/
Description: Adds accordion functionality to themes
Version: 0.1
Author: Toby Cryns
Author URI: http://www.themightymo.com
License: This plugin is owned by Toby Cryns.
*/


add_action( 'wp_print_scripts', 'add_mighty_scripts' );

function add_mighty_scripts() {
	if( !is_admin() ) {
		wp_enqueue_script('jquery'); 
		wp_register_script('mighty-jquery-ui-core', get_bloginfo('wpurl') . '/wp-content/plugins/mighty-accordion/js/jquery-ui-1.8.11.custom.min.js', array( 'jquery' ) );
		wp_enqueue_script('mighty-jquery-ui-core', array( 'jquery' )); 
		wp_register_script('accordion-script', get_bloginfo('wpurl') . '/wp-content/plugins/mighty-accordion/js/mightyaccordion.js', array( 'jquery', 'mighty-jquery-ui-core' ) );
		wp_enqueue_script('accordion-script', array( 'jquery', 'mighty-jquery-ui-core' ) );
	}
}