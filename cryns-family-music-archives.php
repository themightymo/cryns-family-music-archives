<?php 
/*
Plugin Name: Cryns Family Music Archives
Plugin URI: https://www.tobycryns.com/
Description: Creates the "Audio File" custom post type and all audio file custom taxonomies.  It also adds audio file meta data to the front end (filters the_content).  This plugin depends on the "Custom Field Template" plugin.
Version: 0.7.1
Author: Toby Cryns
Author URI: http://www.tobycryns.com
License: This plugin is owned by Toby Cryns.
*/

// Create the "Audio File" custom post type

function register_cryns_audio_file_cpt() 
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
    'show_admin_column' => true,
    'show_in_rest' => true, // <-- enables REST API access
    'rest_controller_class' => 'WP_REST_Posts_Controller', // optional
    'menu_icon' => 'dashicons-format-audio',
    'supports' => ['title','editor','custom-fields','author','excerpt','comments']
  ); 
  register_post_type('cryns_audio_file',$args);
}
add_action('init', 'register_cryns_audio_file_cpt');





add_action('rest_api_init', function () {
  register_rest_field('cryns_audio_file', 'audio_file', [
      'get_callback' => function ($post_arr) {
          $file_id = get_field('audio_file', $post_arr['id']);
          if (!$file_id) {
              return null;
          }

          return [
              'id'       => $file_id,
              'url'      => wp_get_attachment_url($file_id),
              'title'    => get_the_title($file_id),
              'mime'     => get_post_mime_type($file_id),
              'filename' => basename(get_attached_file($file_id)),
          ];
      },
      'schema' => null,
  ]);
});




// Expose all ACF endpoints
add_filter('acf/rest_api/field_settings/show_in_rest', '__return_true');


//ACF hides custom fields meta box.  This filter displays them again.  Via https://wordpress.stackexchange.com/questions/277388/how-to-fix-missing-custom-fields-after-upgrading-to-wordpress-4-8-1
add_filter('acf/settings/remove_wp_meta_box', '__return_false');

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
	'show_in_rest' => true,
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
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'album-title' ),
	  'show_in_rest' => true,
	  
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
	  'show_in_rest' => true,
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
	  'show_in_rest' => true,
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
	  'show_in_rest' => true,
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
	  'show_in_rest' => true,
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
	  'show_in_rest' => true,
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
	  'show_in_rest' => true,
  ));
  
}

/**
 * Include cryns_audio_file post type in Jetpack Rest api
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

/*
	Display the html5 audio player for the current mp3.
*/
function return_audio_player() {
	global $post;
	if ( get_field('audio_file') ) {
    return '<audio class="wp-audio-shortcode" id="" preload="none" style="width: 100%;" controls="controls"><source type="audio/mpeg" src="' . wp_get_attachment_url( get_field ( 'audio_file' ) ) . '"></audio>';
	} else if ( get_post_meta($post->ID, 'Audio File', true) ) {
    return '<audio class="wp-audio-shortcode" id="" preload="none" style="width: 100%;" controls="controls"><source type="audio/mpeg" src="' . wp_get_attachment_url( get_post_meta($post->ID, 'Audio File', true) ) . '"></audio>';
	} else {
		return "There's no single mp3 for this one.";
	}
}
function echo_audio_player() {
	global $post;
	if ( get_post_meta($post->ID, 'Audio File', true) ) {
		echo '<audio class="wp-audio-shortcode" id="audio-2383-1" preload="none" style="width: 100%;" controls="controls"><source type="audio/mpeg" src="' . wp_get_attachment_url( get_post_meta($post->ID, 'Audio File', true) ) . '"></audio>';
	} else {
		echo "There's no single mp3 for this one.";
	}
}



/*
	Display band name, release year, album, etc.
*/
function return_audio_meta() {
	global $post;
	
	
	//If the new ACF field name exists, then display that audio file, else display the old audio file.
	if ( get_post_meta($post->ID, 'audio_file', true) ) { 
		$audioFileCustomFieldName = 'audio_file';
	} else {
		$audioFileCustomFieldName = 'Audio File';
	}
	
	$return_taxonomy_info = function ( $tax, $label ) use ( $post ) {
		$terms = get_the_terms( $post->ID, $tax );
		if ( ! empty( $terms ) ) {
			$term_list = '';
			foreach ( $terms as $term ) {
				$term_list .= '<a href="' . get_term_link( $term ) . '">' . $term->name . '</a>, ';
			}
			$term_list = rtrim( $term_list, ', ' );
			return $label . $term_list;
		}
	};

	
	return 
		'<div class="audio-meta">
			<a href="' . wp_get_attachment_url( get_post_meta($post->ID, $audioFileCustomFieldName, true) ) . '" target="_blank">Download MP3 File</a>' . 
			$return_taxonomy_info ('cryns_artist', ' | Artist(s): ') .
			$return_taxonomy_info ('cryns_written_by', ' | Written By: ') .
			"| Track Number: " . get_field('track_number') . 
			get_the_term_list( get_the_ID(), 'cryns_release_year', ", Release Year: " ) . 
			$return_taxonomy_info ('cryns_musicians', ' | Musicians: ') .
			$return_taxonomy_info ('cryns_engineer', ' | Engineer(s): ') .
			$return_taxonomy_info ('cryns_producer', ' | Producer(s): ') .
			$return_taxonomy_info ('cryns_genre', ' | Genre(s): ') .
			$return_taxonomy_info ('cryns_album_title', ' | Album Title ') .
		'</div>';
}
/* 
	Add mp3 player to single post view
*/
function add_mp3_to_single_audio_posts ( $content ) {
    if ( is_singular( 'cryns_audio_file' ) && has_post_format( 'audio' ) ) { //If we're on a single Audio post, and it's using the "audio" post format, then display the player + the audio file meta
		
	    $content .= return_audio_player();
	    $content .= return_audio_meta();
	    
    } else if ( is_archive() && 'cryns_audio_file' == get_post_type() && has_post_format( 'audio' ) ) { //If we're on an archive page, only display the player - hide the audio meta.
		
	    $content .= return_audio_player();
	         
    }
    
    // Returns the content.
	return $content;
}

add_filter( 'the_content', 'add_mp3_to_single_audio_posts', 20 );



// Add total audio posts to footer
function display_audio_post_count () {
	$count_posts = wp_count_posts( 'cryns_audio_file' )->publish;
	echo '<div class="cfma-footer-meta">Total Songs Posted: ' . $count_posts . '</div>';
}
add_action('wp_footer', 'display_audio_post_count'); 


// add post-formats to post_type 'my_custom_post_type'
add_post_type_support( 'cryns_audio_file', 'post-formats' );

// Subscribe2 uses "cryns_audio_file" post type
function my_post_types($types) {
    $types[] = 'cryns_audio_file';
    return $types;
}
add_filter('s2_post_types', 'my_post_types');



/* Displays a playlist in your template.  To be used in archive.php or equivalent.  
 * cryns_audio_playlist();
 */
function cryns_audio_playlist() {
    global $wp_query;
    $queried_object = get_queried_object();

    // This is the array that will store all the audio file ids
    $audioIDs = array();

    // Loop through the current query posts
    foreach ( $wp_query->posts as $post ) {
        setup_postdata( $post );

        // Get the audio file's id, and store it in a variable (the old Custom Field Template format was "Audio File", the new ACF format is "audio_file").
        if ( get_post_meta( $post->ID, 'audio_file', true ) ) {
            $audioID = get_post_meta( $post->ID, 'audio_file', true );
        } else if ( get_post_meta( $post->ID, 'Audio File', true ) ) {
            $audioID = get_post_meta( $post->ID, 'Audio File', true );
        } else {
            $audioID = '';
        }

        // Add the audio file's id to the $audioIDs variable (array format)
        if ( $audioID ) {
            array_push( $audioIDs, $audioID );
        }
    }

    // Since the $audioIDs array is not in the correct format, we put the files in a comma-separated list and store that list in the $audioList variable
    $audioList = implode( ',', $audioIDs );

    // Display the playlist!
    echo do_shortcode( '[playlist ids="' . $audioList . '"]' );

    /* Restore original Post Data */
    wp_reset_postdata();
}

/* 
	Add Audio post format
*/
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


/* 
	Display music playlist player on archive pages.
*/
add_shortcode('cryns_audio_playlist', 'output_before_taxonomy_loop'); 
function output_before_taxonomy_loop(){
  if (is_tax()) {
    cryns_audio_playlist();
    
    // Display the artist image
    $queried_object = get_queried_object();
    $taxonomy = $queried_object->taxonomy;
    $term_id = $queried_object->term_id;
    $terms = get_field( 'artist_image', $taxonomy.'_'.$term_id);
    
    if( $terms ) {
      
      echo '<img src="'. $terms['url'] .'" />';

    } else {
      //do nothing
    }
   
  }
}

function footer_credits () {
    echo '<div class="cfma-footer-meta">Sweet ass musical search and display functionality on this site by the <a href="https://github.com/themightymo/cryns-family-music-archives" target="_blank">Cryns Family Music Archives</a> Plugin</div>';
}
add_action( 'wp_footer', 'footer_credits' );

/*
	Include custom media player styles
*/
function media_player_styles () {
    wp_register_style('media-player-styles', plugins_url('/media-player-style.css', __FILE__), '', '1.0.0');
    wp_enqueue_style ( 'media-player-styles' );
}
add_action('wp_enqueue_scripts', 'media_player_styles');


/*
 * [cfma_song_filter] shortcode
 * Renders artist + album dropdowns and an AJAX-driven song list.
 * Replaces the FacetWP filter/template block that was on the homepage.
 */
add_shortcode( 'cfma_song_filter', 'cfma_song_filter_shortcode' );

function cfma_song_filter_shortcode() {
    $artists = get_terms( [
        'taxonomy'   => 'cryns_artist',
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => 500,
    ] );

    $albums = get_terms( [
        'taxonomy'   => 'cryns_album_title',
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => 500,
    ] );

    wp_enqueue_script(
        'cfma-song-filter',
        plugins_url( '/js/song-filter.js', __FILE__ ),
        [],
        '1.0.0',
        true
    );

    wp_localize_script( 'cfma-song-filter', 'cfmaFilter', [
        'restUrl' => rest_url( 'wp/v2/cryns_audio_file' ),
        'perPage' => 20,
    ] );

    ob_start();
    ?>
    <div id="cfma-filter-wrap">
        <div class="cfma-filter-controls">
            <div>
                <label for="cfma-artist-select">Select an Artist:</label>
                <select id="cfma-artist-select" name="cfma_artist">
                    <option value="">Any Artist</option>
                    <?php foreach ( (array) $artists as $term ) : ?>
                    <option value="<?php echo esc_attr( $term->term_id ); ?>">
                        <?php echo esc_html( $term->name ); ?> (<?php echo esc_html( $term->count ); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="cfma-album-select">Select an Album:</label>
                <select id="cfma-album-select" name="cfma_album">
                    <option value="">Any Album</option>
                    <?php foreach ( (array) $albums as $term ) : ?>
                    <option value="<?php echo esc_attr( $term->term_id ); ?>">
                        <?php echo esc_html( $term->name ); ?> (<?php echo esc_html( $term->count ); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div id="cfma-selections"></div>

        <div class="cfma-results-meta">
            RESULTS (By default, the most recent songs are displayed):
            <span class="cfma-count-wrap">Total Results: <strong id="cfma-count">&mdash;</strong></span>
        </div>

        <div id="cfma-results">
            <p class="cfma-loading">Loading songs&hellip;</p>
        </div>

        <div id="cfma-pagination"></div>
    </div>
    <?php
    return ob_get_clean();
}










// via https://chatgpt.com/c/6827547b-e844-800e-92ad-94dac48a935a
add_action('rest_api_init', function () {
  register_rest_route('custom/v1', '/search-audio/', [
      'methods' => 'GET',
      'callback' => 'search_audio_files_with_acf',
      'permission_callback' => '__return_true',
      'args' => [
          's' => [
              'required' => true,
              'sanitize_callback' => 'sanitize_text_field',
          ],
      ],
  ]);
});


//via https://chatgpt.com/c/6827547b-e844-800e-92ad-94dac48a935a
function search_audio_files_with_acf($request) {
  $search = $request->get_param('s');

  $all_posts = get_posts([
      'post_type' => ['cryns_audio_file', 'attachment'],
      'post_status' => ['publish', 'inherit'],
      'posts_per_page' => -1,
  ]);

  $results = [];
  $seen_ids = [];

  foreach ($all_posts as $post) {
      $title = get_the_title($post->ID);

      // Match if "duluth" appears in title (case-insensitive)
      if (stripos($title, $search) !== false) {
          $results[] = [
              'id'    => $post->ID,
              'type'  => get_post_type($post->ID),
              'title' => $title,
              'link'  => get_permalink($post->ID),
          ];
          $seen_ids[] = $post->ID;
      }
  }

  // (Optional) Also search in the ACF audio_file field if needed
  $post_ids = get_posts([
      'post_type' => 'cryns_audio_file',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'fields' => 'ids',
  ]);

  foreach ($post_ids as $post_id) {
      if (in_array($post_id, $seen_ids)) continue;

      $audio_file = get_field('audio_file', $post_id);
      if (is_string($audio_file) && stripos($audio_file, $search) !== false) {
          $results[] = [
              'id' => $post_id,
              'type' => 'cryns_audio_file',
              'title' => get_the_title($post_id),
              'link' => get_permalink($post_id),
              'audio_file' => $audio_file,
          ];
      }
  }

  return rest_ensure_response($results);
}


