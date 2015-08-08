<?php
function parent_css_theme_style() { 
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' ); 
}
add_action( 'wp_enqueue_scripts', 'parent_css_theme_style' );

add_action( 'after_setup_theme', 'childtheme_formats', 11 );
function childtheme_formats(){
	add_theme_support( 'post-formats', array( 'audio', 'video' ) );
}

// Add Audio File custom field to WP-Rest-API Output via https://wordpress.org/support/topic/custom-meta-data-2?replies=7
add_filter( 'json_prepare_post', function ($data, $post, $context) {
	$data['myextradata'] = array(
		'mp3URL' => wp_get_attachment_url(get_post_meta( $post['ID'], 'Audio File', true )),
	);
	return $data;
}, 10, 3 );