<?php
function parent_css_theme_style() { 
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' ); 
}
add_action( 'wp_enqueue_scripts', 'parent_css_theme_style' );

add_action( 'after_setup_theme', 'childtheme_formats', 11 );
function childtheme_formats(){
	add_theme_support( 'post-formats', array( 'audio', 'video' ) );
}