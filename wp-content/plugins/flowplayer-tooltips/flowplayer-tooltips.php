<?php
/*
Plugin Name: jQuery Flowplayer Tooltips
Plugin URI: http://www.themightymo.com/
Description: Simply enables jquery Tooltips functionality using http://flowplayer.org/tools/
Version: 1.0
Author: Toby Cryns
Author URI: http://www.themightymo.com
License: This plugin is owned by Toby Cryns.
*/


add_action( 'wp_print_scripts', 'add_flowplayer_tooltips_scripts' );

function add_flowplayer_tooltips_scripts() {
	if( !is_admin() ) {
		wp_enqueue_script('jquery'); 
		wp_register_script('mighty-tool-tips', get_bloginfo('wpurl') . '/wp-content/plugins/flowplayer-tooltips/js/jquery.tools.min.js', array( 'jquery' ) );
		wp_enqueue_script('mighty-tool-tips', array( 'jquery' )); 
	}
}


?>