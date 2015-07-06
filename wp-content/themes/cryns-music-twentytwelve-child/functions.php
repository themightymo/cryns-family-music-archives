<?php
function parent_css_theme_style() { 
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' ); 
}
add_action( 'wp_enqueue_scripts', 'parent_css_theme_style' );

add_action( 'after_setup_theme', 'childtheme_formats', 11 );
function childtheme_formats(){
	add_theme_support( 'post-formats', array( 'audio', 'video' ) );
}

// add post-formats to post_type 'my_custom_post_type'
add_post_type_support( 'cryns_audio_file', 'post-formats' );


/**
 * Add a icon to the beginning of every post page.
 *
 * @uses is_single()
 */
function add_mp3_to_single_audio_posts ( $content ) {
    if ( is_single() && has_post_format( 'audio' ) ) {
	    global $post;
	    $mp3 = wp_get_attachment_url( get_post_meta($post->ID, 'Audio File', true) );
	    $myHTML = '<audio class="wp-audio-shortcode" id="audio-2383-1" preload="none" style="width: 100%; visibility: hidden;" controls="controls"><source type="audio/mpeg" src="' . $mp3 . '">http://music.cryns.com/wp-content/uploads/2012/03/Asshole-4-Life.mp3</a></audio>';
	    $content .= $myHTML;
    }
    // Returns the content.
	return $content;
}
add_filter( 'the_content', 'add_mp3_to_single_audio_posts', 20 );

function display_audio_post_count () {
	$count_posts = wp_count_posts( 'cryns_audio_file' )->publish;
	echo '<div style="color:#fff; text-align:center;">Total Songs Posted: ' . $count_posts . '</div>';
}
add_action('wp_footer', 'display_audio_post_count'); 