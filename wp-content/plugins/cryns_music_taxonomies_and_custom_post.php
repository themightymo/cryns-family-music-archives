<?php 
/*
Plugin Name: Cryns Music Taxonomies and Custom Post Type
Plugin URI: http://www.themightymo.com/
Description: Creates the "Audio File" custom post type and all audio file custom taxonomies.  It also adds audio file meta data to the front end (filters the_content).  This plugin depends on the "Custom Field Template" plugin.
Version: 0.2
Author: Toby Cryns
Author URI: http://www.themightymo.com
License: This plugin is owned by Toby Cryns.
*/

// Create the "Audio File" custom post type
add_action('init', 'codex_custom_init');
function codex_custom_init() 
{
  $labels = array(
    'name' => _x('Audio File', 'post type general name'),
    'singular_name' => _x('Audio File', 'post type singular name'),
    'add_new' => _x('Add New', 'cryns_audio_file'),
    'add_new_item' => __('Add New Audio File'),
    'edit_item' => __('Edit Audio File'),
    'new_item' => __('New Audio File'),
    'view_item' => __('View Audio File'),
    'search_items' => __('Search Audio Files'),
    'not_found' =>  __('No Audio Files found'),
    'not_found_in_trash' => __('No Audio Files found in Trash'), 
    'parent_item_colon' => '',
    'menu_name' => 'Audio Files'

  );
  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' => array('slug'=>'songs'),
    'capability_type' => 'post',
    'has_archive' => true, 
    'hierarchical' => false,
    'menu_position' => 3,
	'slug' => 'music-file',
    'supports' => array('title','editor','custom-fields','author','excerpt','comments')
  ); 
  register_post_type('cryns_audio_file',$args);
}

//hook into the init action and call create_cryns_audio_files_taxonomies when it fires
add_action( 'init', 'create_cryns_audio_files_taxonomies', 0 );

//create the taxonomies that will hook onto the Audio File custom post type
function create_cryns_audio_files_taxonomies() 
{
  // Add new taxonomy, make it hierarchical (like categories)
  $labels = array(
    'name' => _x( 'Artist', 'taxonomy general name' ),
    'singular_name' => _x( 'Artist', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Artists' ),
    'all_items' => __( 'All Artists' ),
    'parent_item' => __( 'Parent Artist' ),
    'parent_item_colon' => __( 'Parent Artist:' ),
    'edit_item' => __( 'Edit Artist' ), 
    'update_item' => __( 'Update Artist' ),
    'add_new_item' => __( 'Add New Artist' ),
    'new_item_name' => __( 'New Artist Name' ),
    'menu_name' => __( 'Artist' ),
  ); 	

  register_taxonomy('cryns_artist',array('cryns_audio_file'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'artist' ),
  ));
  
  // Add new taxonomy, make it hierarchical (like categories)
  $labels = array(
    'name' => _x( 'Album Title', 'taxonomy general name' ),
    'singular_name' => _x( 'Album Title', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Album Titles' ),
    'all_items' => __( 'All Album Titles' ),
    'parent_item' => __( 'Parent Album Titles' ),
    'parent_item_colon' => __( 'Parent Album Titles:' ),
    'edit_item' => __( 'Edit Album Title' ), 
    'update_item' => __( 'Update Album Title' ),
    'add_new_item' => __( 'Add New Album Title' ),
    'new_item_name' => __( 'New Album Title Name' ),
    'menu_name' => __( 'Album Title' ),
  ); 	

  register_taxonomy('cryns_album_title',array('cryns_audio_file'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'album-title' ),
  ));
  
  // Add new taxonomy, make it hierarchical (like categories)
  $labels = array(
    'name' => _x( 'Songwriter', 'taxonomy general name' ),
    'singular_name' => _x( 'Songwriter', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Songwriters' ),
    'all_items' => __( 'All Songwriters' ),
    'parent_item' => __( 'Parent Songwriters' ),
    'parent_item_colon' => __( 'Parent Songwriters:' ),
    'edit_item' => __( 'Edit Songwriter' ), 
    'update_item' => __( 'Update Songwriter' ),
    'add_new_item' => __( 'Add New Songwriter' ),
    'new_item_name' => __( 'New Songwriter Name' ),
    'menu_name' => __( 'Songwriters' ),
  ); 	

  register_taxonomy('cryns_written_by',array('cryns_audio_file'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'written-by' ),
  ));
  
  // Add new taxonomy, make it hierarchical (like categories)
  $labels = array(
    'name' => _x( 'Producers', 'taxonomy general name' ),
    'singular_name' => _x( 'Producers', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Producers' ),
    'all_items' => __( 'All Producers' ),
    'parent_item' => __( 'Parent Producers' ),
    'parent_item_colon' => __( 'Parent Producers:' ),
    'edit_item' => __( 'Edit Producer' ), 
    'update_item' => __( 'Update Producer' ),
    'add_new_item' => __( 'Add New Producer' ),
    'new_item_name' => __( 'New Producer Name' ),
    'menu_name' => __( 'Producer' ),
  ); 	

  register_taxonomy('cryns_producer',array('cryns_audio_file'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'producer' ),
  ));
  
  // Add new taxonomy, make it hierarchical (like categories)
  $labels = array(
    'name' => _x( 'Engineers', 'taxonomy general name' ),
    'singular_name' => _x( 'Engineers', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Engineers' ),
    'all_items' => __( 'All Engineers' ),
    'parent_item' => __( 'Parent Engineers' ),
    'parent_item_colon' => __( 'Parent Engineers:' ),
    'edit_item' => __( 'Edit Engineer' ), 
    'update_item' => __( 'Update Engineer' ),
    'add_new_item' => __( 'Add New Engineer' ),
    'new_item_name' => __( 'New Engineer Name' ),
    'menu_name' => __( 'Engineer' ),
  ); 	

  register_taxonomy('cryns_engineer',array('cryns_audio_file'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'engineer' ),
  ));
    
  // Add new taxonomy, make it hierarchical (like categories)
  $labels = array(
    'name' => _x( 'Genres', 'taxonomy general name' ),
    'singular_name' => _x( 'Genre', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Genres' ),
    'all_items' => __( 'All Genres' ),
    'parent_item' => __( 'Parent Genres' ),
    'parent_item_colon' => __( 'Parent Genres:' ),
    'edit_item' => __( 'Edit Genre' ), 
    'update_item' => __( 'Update Genre' ),
    'add_new_item' => __( 'Add New Genre' ),
    'new_item_name' => __( 'New Genre Name' ),
    'menu_name' => __( 'Genres' ),
  ); 	

  register_taxonomy('cryns_genre',array('cryns_audio_file'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'genre' ),
  ));
  
  // Add new taxonomy, make it hierarchical (like categories)
  $labels = array(
    'name' => _x( 'Musicians', 'taxonomy general name' ),
    'singular_name' => _x( 'Musician', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Musicians' ),
    'all_items' => __( 'All Musicians' ),
    'parent_item' => __( 'Parent Musicians' ),
    'parent_item_colon' => __( 'Parent Musicians:' ),
    'edit_item' => __( 'Edit Musician' ), 
    'update_item' => __( 'Update Musician' ),
    'add_new_item' => __( 'Add New Musician' ),
    'new_item_name' => __( 'New Musician Name' ),
    'menu_name' => __( 'Musicians' ),
  ); 	

  register_taxonomy('cryns_musicians',array('cryns_audio_file'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'musicians' ),
  ));
  

  // Add new taxonomy, NOT hierarchical (like tags)
  $labels = array(
    'name' => _x( 'Release Year', 'taxonomy general name' ),
    'singular_name' => _x( 'Release Year', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Release Years' ),
    'popular_items' => __( 'Popular Release Years' ),
    'all_items' => __( 'All Release Years' ),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __( 'Edit Release Year' ), 
    'update_item' => __( 'Update Release Year' ),
    'add_new_item' => __( 'Add New Release Year' ),
    'new_item_name' => __( 'New Release Year' ),
    'separate_items_with_commas' => __( 'Separate Release Years with commas' ),
    'add_or_remove_items' => __( 'Add or remove Release Years' ),
    'choose_from_most_used' => __( 'Choose from the most used Release Years' ),
    'menu_name' => __( 'Release Year' ),
  ); 

  register_taxonomy('cryns_release_year','cryns_audio_file',array(
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'release-year' ),
  ));
  
  // Add new taxonomy, NOT hierarchical (like tags)
  $labels = array(
    'name' => _x( 'Track Number', 'taxonomy general name' ),
    'singular_name' => _x( 'Track Number', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Track Numbers' ),
    'popular_items' => __( 'Popular Track Numbers' ),
    'all_items' => __( 'All Track Numbers' ),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __( 'Edit Track Number' ), 
    'update_item' => __( 'Update Track Number' ),
    'add_new_item' => __( 'Add New Track Number' ),
    'new_item_name' => __( 'New Track Number' ),
    'separate_items_with_commas' => __( 'Ex: 01 or 02 or 13' ),
    'add_or_remove_items' => __( 'Add or remove Track Numbers' ),
    'choose_from_most_used' => __( 'Choose from the most used Track Numbers' ),
    'menu_name' => __( 'Track Number' ),
  ); 

  register_taxonomy('cryns_track_number','cryns_audio_file',array(
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'track-number' ),
  ));
}

/**
 * Include cryns_audio_file post type in Jetpack Related Posts
 */
function allow_my_post_types($allowed_post_types) {
    $allowed_post_types[] = 'cryns_audio_file';
    return $allowed_post_types;
}
add_filter( 'rest_api_allowed_post_types', 'allow_my_post_types' );

/**
 * Change the “Related” headline at the top of the Related Posts section
 **/
function jetpackme_related_posts_headline( $headline ) {
$headline = sprintf(
            '<h3 class="jp-relatedposts-headline"><em>%s</em></h3>',
            esc_html( 'Related songs:' )
            );
return $headline;
}
add_filter( 'jetpack_relatedposts_filter_headline', 'jetpackme_related_posts_headline' );

// Add mp3 player to single post view
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

// Add total audio posts to footer
function display_audio_post_count () {
	$count_posts = wp_count_posts( 'cryns_audio_file' )->publish;
	echo '<div style="color:#fff; text-align:center;">Total Songs Posted: ' . $count_posts . '</div>';
}
add_action('wp_footer', 'display_audio_post_count'); 