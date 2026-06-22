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
function cfma_get_audio_file_id( $post_id = null ) {
	global $post;

	$post_id = $post_id ?: $post->ID;
	return get_field( 'audio_file', $post_id ) ?: get_post_meta( $post_id, 'Audio File', true );
}

function cfma_get_attachment_duration( $attachment_id ) {
    $metadata = wp_get_attachment_metadata( $attachment_id );

    if ( ! empty( $metadata['length_formatted'] ) ) {
        return $metadata['length_formatted'];
    }

    if ( ! empty( $metadata['length'] ) ) {
        $length = (int) $metadata['length'];
        return sprintf( '%d:%02d', floor( $length / 60 ), $length % 60 );
    }

    return '--:--';
}

function cfma_get_playlist_art_url( $attachment_id, $size = 'thumbnail' ) {
    $image = wp_get_attachment_image_src( get_post_thumbnail_id( $attachment_id ), $size );
    if ( $image ) {
        return $image[0];
    }

    $image = wp_get_attachment_image_src( $attachment_id, $size );
    if ( $image ) {
        return $image[0];
    }

    return '';
}

function cfma_get_audio_attachment_ids_from_playlist_attrs( $attr ) {
    global $post;

    $ids = [];

    if ( ! empty( $attr['ids'] ) ) {
        $ids = array_filter( array_map( 'absint', explode( ',', $attr['ids'] ) ) );
    } elseif ( ! empty( $attr['include'] ) ) {
        $ids = array_filter( array_map( 'absint', explode( ',', $attr['include'] ) ) );
    }

    if ( $ids ) {
        return $ids;
    }

    $post_id = $post ? $post->ID : 0;
    if ( ! $post_id ) {
        return [];
    }

    $attachments = get_posts( [
        'post_type'      => 'attachment',
        'post_mime_type' => 'audio',
        'post_parent'    => $post_id,
        'post_status'    => 'inherit',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
        'fields'         => 'ids',
    ] );

    return array_map( 'absint', $attachments );
}

function cfma_get_audio_attachment_track( $attachment_id, $index ) {
    $attachment = get_post( $attachment_id );
    if ( ! $attachment || 0 !== strpos( (string) $attachment->post_mime_type, 'audio/' ) ) {
        return null;
    }

    $parent_id = (int) $attachment->post_parent;
    $artist_terms = $parent_id ? get_the_terms( $parent_id, 'cryns_artist' ) : [];
    $album_terms = $parent_id ? get_the_terms( $parent_id, 'cryns_album_title' ) : [];
    $release_terms = $parent_id ? get_the_terms( $parent_id, 'cryns_release_year' ) : [];
    $audio_url = wp_get_attachment_url( $attachment_id );

    if ( ! $audio_url ) {
        return null;
    }

    return [
        'id'       => $attachment_id,
        'url'      => $audio_url,
        'mime'     => get_post_mime_type( $attachment_id ) ?: 'audio/mpeg',
        'title'    => get_the_title( $attachment_id ),
        'artist'   => ( ! empty( $artist_terms ) && ! is_wp_error( $artist_terms ) ) ? wp_list_pluck( $artist_terms, 'name' ) : [],
        'album'    => ( ! empty( $album_terms ) && ! is_wp_error( $album_terms ) ) ? $album_terms[0]->name : '',
        'release'  => ( ! empty( $release_terms ) && ! is_wp_error( $release_terms ) ) ? $release_terms[0]->name : '',
        'duration' => cfma_get_attachment_duration( $attachment_id ),
        'art'      => cfma_get_playlist_art_url( $attachment_id ),
        'number'   => $index + 1,
    ];
}

function cfma_render_audio_playlist( $attachment_ids, $title = '' ) {
    cfma_enqueue_audio_player_script();

    $tracks = [];

    foreach ( $attachment_ids as $index => $attachment_id ) {
        $track = cfma_get_audio_attachment_track( $attachment_id, $index );
        if ( $track ) {
            $tracks[] = $track;
        }
    }

    if ( ! $tracks ) {
        return '';
    }

    $playlist_id = wp_unique_id( 'cfma-playlist-' );
    $track_count = count( $tracks );
    $title = $title ?: get_the_title();
    $first_track = $tracks[0];
    $artists = array_filter( array_unique( array_merge( ...array_map( function ( $track ) {
        return $track['artist'];
    }, $tracks ) ) ) );
    $subtitle_parts = [
        sprintf( _n( '%s track', '%s tracks', $track_count ), number_format_i18n( $track_count ) ),
    ];

    if ( $artists ) {
        $subtitle_parts[] = implode( ', ', array_slice( $artists, 0, 3 ) );
    }

    $playlist_json = wp_json_encode( $tracks );

    ob_start();
    ?>
    <section id="<?php echo esc_attr( $playlist_id ); ?>" class="cfma-playlist-player" data-cfma-playlist data-cfma-tracks="<?php echo esc_attr( $playlist_json ); ?>">
        <div class="cfma-playlist-card">
            <header class="cfma-playlist-hero">
                <div class="cfma-playlist-cover" aria-hidden="true">
                    <?php if ( $first_track['art'] ) : ?>
                        <img src="<?php echo esc_url( $first_track['art'] ); ?>" alt="">
                    <?php else : ?>
                        <span>CFMA</span>
                    <?php endif; ?>
                </div>
                <div class="cfma-playlist-summary">
                    <span class="cfma-playlist-pill">Audio Playlist</span>
                    <h2><?php echo esc_html( $title ); ?></h2>
                    <p><?php echo esc_html( implode( ' / ', $subtitle_parts ) ); ?></p>
                    <button type="button" class="cfma-playlist-primary" data-cfma-playlist-play>
                        <span aria-hidden="true">&#9658;</span>
                        <span>Play</span>
                    </button>
                </div>
            </header>

            <div class="cfma-inline-player">
                <div class="cfma-playlist-buttons">
                    <button type="button" data-cfma-prev aria-label="Previous track">Prev</button>
                    <button type="button" class="cfma-playlist-play-button" data-cfma-play aria-label="Play playlist"><span aria-hidden="true">&#9658;</span></button>
                    <button type="button" data-cfma-next aria-label="Next track">Next</button>
                </div>
                <div class="cfma-playlist-progress">
                    <span data-cfma-current>00:00</span>
                    <input type="range" min="0" max="100" value="0" step="0.1" data-cfma-seek aria-label="Playlist progress">
                    <span data-cfma-duration><?php echo esc_html( $first_track['duration'] ); ?></span>
                </div>
                <div class="cfma-playlist-volume">
                    <button type="button" data-cfma-mute aria-label="Mute playlist">Mute</button>
                    <input type="range" min="0" max="1" value="0.8" step="0.01" data-cfma-volume aria-label="Playlist volume">
                </div>
            </div>

            <div class="cfma-track-list" role="list">
                <div class="cfma-track-heading" aria-hidden="true">
                    <span>#</span>
                    <span>Title</span>
                    <span>Release</span>
                    <span>Duration</span>
                </div>
                <?php foreach ( $tracks as $index => $track ) : ?>
                    <button type="button" class="cfma-track-row" data-cfma-track-index="<?php echo esc_attr( $index ); ?>" role="listitem">
                        <span class="cfma-track-number"><?php echo esc_html( $track['number'] ); ?></span>
                        <span class="cfma-track-title-wrap">
                            <span class="cfma-track-thumb" aria-hidden="true">
                                <?php if ( $track['art'] ) : ?>
                                    <img src="<?php echo esc_url( $track['art'] ); ?>" alt="">
                                <?php else : ?>
                                    <span>&#9834;</span>
                                <?php endif; ?>
                            </span>
                            <span>
                                <strong><?php echo esc_html( $track['title'] ); ?></strong>
                                <em><?php echo esc_html( $track['artist'] ? implode( ', ', $track['artist'] ) : ( $track['album'] ?: get_bloginfo( 'name' ) ) ); ?></em>
                            </span>
                        </span>
                        <span class="cfma-track-release"><?php echo esc_html( $track['release'] ?: '-' ); ?></span>
                        <span class="cfma-track-duration"><?php echo esc_html( $track['duration'] ); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="cfma-playlist-bar" aria-live="polite">
            <div class="cfma-now-playing">
                <span class="cfma-now-thumb" aria-hidden="true"></span>
                <span>
                    <strong data-cfma-now-title><?php echo esc_html( $first_track['title'] ); ?></strong>
                    <em data-cfma-now-artist><?php echo esc_html( $first_track['artist'] ? implode( ', ', $first_track['artist'] ) : get_bloginfo( 'name' ) ); ?></em>
                </span>
            </div>
            <div class="cfma-playlist-controls">
                <div class="cfma-playlist-buttons">
                    <button type="button" data-cfma-prev aria-label="Previous track">Prev</button>
                    <button type="button" class="cfma-playlist-play-button" data-cfma-play aria-label="Play playlist"><span aria-hidden="true">&#9658;</span></button>
                    <button type="button" data-cfma-next aria-label="Next track">Next</button>
                </div>
                <div class="cfma-playlist-progress">
                    <span data-cfma-current>00:00</span>
                    <input type="range" min="0" max="100" value="0" step="0.1" data-cfma-seek aria-label="Playlist progress">
                    <span data-cfma-duration><?php echo esc_html( $first_track['duration'] ); ?></span>
                </div>
            </div>
            <div class="cfma-playlist-volume">
                <button type="button" data-cfma-mute aria-label="Mute playlist">Mute</button>
                <input type="range" min="0" max="1" value="0.8" step="0.01" data-cfma-volume aria-label="Playlist volume">
            </div>
            <audio preload="metadata" data-cfma-audio></audio>
        </div>
    </section>
    <?php

    return ob_get_clean();
}

function cfma_replace_wordpress_audio_playlist( $output, $attr ) {
    $type = empty( $attr['type'] ) ? 'audio' : $attr['type'];
    if ( 'audio' !== $type ) {
        return $output;
    }

    $custom = cfma_render_audio_playlist( cfma_get_audio_attachment_ids_from_playlist_attrs( $attr ) );

    return $custom ?: $output;
}
add_filter( 'post_playlist', 'cfma_replace_wordpress_audio_playlist', 10, 2 );

function return_audio_player() {
	global $post;
	$audio_id = cfma_get_audio_file_id( $post->ID );

	if ( $audio_id ) {
        $audio_url = wp_get_attachment_url( $audio_id );
        $mime_type = get_post_mime_type( $audio_id ) ?: 'audio/mpeg';

        if ( is_singular( 'cryns_audio_file' ) ) {
            return '<div class="cfma-single-player" data-cfma-player>
                <div class="cfma-single-player-shell">
                    <div class="cfma-single-player-actions">
                        <button type="button" class="cfma-player-btn cfma-player-skip" data-cfma-skip="-15" aria-label="Skip back 15 seconds">
                            <svg aria-hidden="true" fill="none" height="32" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="32" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.5 2v6h6M2.66 15.57a10 10 0 1 0 .57-8.38"></path>
                                <text fill="currentColor" font-size="7" font-weight="700" stroke="none" text-anchor="middle" x="12" y="17">15</text>
                            </svg>
                        </button>
                        <button type="button" class="cfma-player-btn cfma-player-play" data-cfma-play aria-label="Play audio">
                            <svg class="cfma-play-svg" aria-hidden="true" fill="currentColor" height="32" viewBox="0 0 24 24" width="32" xmlns="http://www.w3.org/2000/svg"><path d="M8 5v14l11-7z"></path></svg>
                            <svg class="cfma-pause-svg" aria-hidden="true" fill="currentColor" height="32" viewBox="0 0 24 24" width="32" xmlns="http://www.w3.org/2000/svg"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"></path></svg>
                        </button>
                        <button type="button" class="cfma-player-btn cfma-player-skip" data-cfma-skip="15" aria-label="Skip forward 15 seconds">
                            <svg aria-hidden="true" fill="none" height="32" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="32" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38"></path>
                                <text fill="currentColor" font-size="7" font-weight="700" stroke="none" text-anchor="middle" x="12" y="17">15</text>
                            </svg>
                        </button>
                    </div>
                    <div class="cfma-single-player-divider" aria-hidden="true"></div>
                    <div class="cfma-single-player-progress">
                        <span class="cfma-player-time" data-cfma-current>00:00</span>
                        <input class="cfma-player-range cfma-player-seek" data-cfma-seek type="range" min="0" max="100" value="0" step="0.1" aria-label="Audio progress">
                        <span class="cfma-player-time" data-cfma-duration>00:00</span>
                        <button type="button" class="cfma-player-btn cfma-player-volume" data-cfma-mute aria-label="Mute audio">
                            <svg aria-hidden="true" fill="currentColor" height="28" viewBox="0 0 24 24" width="28" xmlns="http://www.w3.org/2000/svg"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"></path></svg>
                        </button>
                        <a class="cfma-player-menu" href="' . esc_url( $audio_url ) . '" download aria-label="Download audio">
                            <svg aria-hidden="true" fill="none" height="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                        </a>
                    </div>
                </div>
                <audio preload="metadata" data-cfma-audio>
                    <source type="' . esc_attr( $mime_type ) . '" src="' . esc_url( $audio_url ) . '">
                </audio>
            </div>';
        }

        return '<audio class="wp-audio-shortcode" id="" preload="none" style="width: 100%;" controls="controls"><source type="' . esc_attr( $mime_type ) . '" src="' . esc_url( $audio_url ) . '"></audio>';
	} else {
        // No single ACF/legacy file — try attached audio files and render as a playlist.
        $attachments = get_posts( [
            'post_type'      => 'attachment',
            'post_mime_type' => 'audio',
            'post_parent'    => $post->ID,
            'post_status'    => 'inherit',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
        ] );
        if ( $attachments ) {
            $ids = implode( ',', wp_list_pluck( $attachments, 'ID' ) );
            return do_shortcode( '[playlist ids="' . $ids . '"]' );
        }
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
    wp_register_style('media-player-styles', plugins_url('/media-player-style.css', __FILE__), '', filemtime( plugin_dir_path( __FILE__ ) . 'media-player-style.css' ));
    wp_enqueue_style ( 'media-player-styles' );
}
add_action('wp_enqueue_scripts', 'media_player_styles');

function cfma_enqueue_audio_player_script() {
    if ( ! wp_script_is( 'cfma-single-audio-player', 'registered' ) ) {
        wp_register_script( 'cfma-single-audio-player', plugins_url( '/js/single-audio-player.js', __FILE__ ), [], filemtime( plugin_dir_path( __FILE__ ) . 'js/single-audio-player.js' ), true );
    }

    wp_enqueue_script( 'cfma-single-audio-player' );
}

function cfma_enqueue_single_audio_player_script() {
    if ( is_singular( 'cryns_audio_file' ) ) {
        cfma_enqueue_audio_player_script();
    }
}
add_action( 'wp_enqueue_scripts', 'cfma_enqueue_single_audio_player_script' );


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

    $musicians = get_terms( [
        'taxonomy'   => 'cryns_musicians',
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => 500,
    ] );

    $written_by = get_terms( [
        'taxonomy'   => 'cryns_written_by',
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => 500,
    ] );

    // Register with no src so SiteGround's JS combiner can't swallow the file.
    // The script content is inlined directly via wp_add_inline_script.
    cfma_enqueue_audio_player_script();

    wp_register_script( 'cfma-song-filter', false, [], false, true );
    wp_enqueue_script( 'cfma-song-filter' );

    wp_localize_script( 'cfma-song-filter', 'cfmaFilter', [
        'feedUrl' => rest_url( 'custom/v1/mixed-feed' ),
        'perPage' => 20,
    ] );

    wp_add_inline_script(
        'cfma-song-filter',
        file_get_contents( plugin_dir_path( __FILE__ ) . 'js/song-filter.js' )
    );

    ob_start();
    ?>
    <div id="cfma-filter-wrap">
        <div class="cfma-layout">

            <aside class="cfma-sidebar">
                <button type="button" class="cfma-mobile-filter-toggle" aria-expanded="false" aria-controls="cfma-filter-controls">
                    <span>Filters</span>
                    <span id="cfma-mobile-filter-count" class="cfma-mobile-filter-count" hidden></span>
                </button>

                <div id="cfma-filter-controls" class="cfma-filter-controls">
                    <div>
                        <p class="cfma-filter-label">Select an Artist:</p>
                        <input type="text" id="cfma-artist-search" class="cfma-filter-search" placeholder="Search artists…" autocomplete="off">
                        <div id="cfma-artist-checkboxes" class="cfma-checkbox-group">
                            <?php foreach ( (array) $artists as $term ) : ?>
                            <label class="cfma-checkbox-label">
                                <input type="checkbox" class="cfma-artist-cb" value="<?php echo esc_attr( $term->term_id ); ?>">
                                <?php echo esc_html( $term->name ); ?> (<?php echo esc_html( $term->count ); ?>)
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div>
                        <p class="cfma-filter-label">Select an Album:</p>
                        <input type="text" id="cfma-album-search" class="cfma-filter-search" placeholder="Search albums…" autocomplete="off">
                        <div id="cfma-album-checkboxes" class="cfma-checkbox-group">
                            <?php foreach ( (array) $albums as $term ) : ?>
                            <label class="cfma-checkbox-label">
                                <input type="checkbox" class="cfma-album-cb" value="<?php echo esc_attr( $term->term_id ); ?>">
                                <?php echo esc_html( $term->name ); ?> (<?php echo esc_html( $term->count ); ?>)
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div>
                        <p class="cfma-filter-label">Musicians:</p>
                        <input type="text" id="cfma-musicians-search" class="cfma-filter-search" placeholder="Search musicians…" autocomplete="off">
                        <div id="cfma-musicians-checkboxes" class="cfma-checkbox-group">
                            <?php foreach ( (array) $musicians as $term ) : ?>
                            <label class="cfma-checkbox-label">
                                <input type="checkbox" class="cfma-musicians-cb" value="<?php echo esc_attr( $term->term_id ); ?>">
                                <?php echo esc_html( $term->name ); ?> (<?php echo esc_html( $term->count ); ?>)
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div>
                        <p class="cfma-filter-label">Written by:</p>
                        <input type="text" id="cfma-written-by-search" class="cfma-filter-search" placeholder="Search songwriters…" autocomplete="off">
                        <div id="cfma-written-by-checkboxes" class="cfma-checkbox-group">
                            <?php foreach ( (array) $written_by as $term ) : ?>
                            <label class="cfma-checkbox-label">
                                <input type="checkbox" class="cfma-written-by-cb" value="<?php echo esc_attr( $term->term_id ); ?>">
                                <?php echo esc_html( $term->name ); ?> (<?php echo esc_html( $term->count ); ?>)
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="cfma-main">
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

        </div>
    </div>
    <?php
    return ob_get_clean();
}










// Mixed feed: cryns_audio_file + post, ordered by date, with pagination.
add_action( 'rest_api_init', function () {
    register_rest_route( 'custom/v1', '/mixed-feed', [
        'methods'             => 'GET',
        'callback'            => 'cfma_mixed_feed_endpoint',
        'permission_callback' => '__return_true',
        'args'                => [
            'per_page'           => [ 'default' => 20, 'sanitize_callback' => 'absint' ],
            'page'               => [ 'default' => 1,  'sanitize_callback' => 'absint' ],
            'cryns_artist'       => [ 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ],
            'cryns_album_title'  => [ 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ],
            'cryns_musicians'    => [ 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ],
            'cryns_written_by'   => [ 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ],
        ],
    ] );
} );

function cfma_mixed_feed_endpoint( WP_REST_Request $request ) {
    $per_page   = max( 1, $request->get_param( 'per_page' ) );
    $page       = max( 1, $request->get_param( 'page' ) );
    $artist     = $request->get_param( 'cryns_artist' );
    $album      = $request->get_param( 'cryns_album_title' );
    $musicians  = $request->get_param( 'cryns_musicians' );
    $written_by = $request->get_param( 'cryns_written_by' );

    $args = [
        'post_type'      => $artist || $album || $musicians || $written_by ? [ 'cryns_audio_file' ] : [ 'cryns_audio_file', 'post' ],
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];

    if ( $artist ) {
        $args['tax_query'][] = [
            'taxonomy' => 'cryns_artist',
            'field'    => 'term_id',
            'terms'    => array_map( 'intval', explode( ',', $artist ) ),
        ];
    }
    if ( $album ) {
        $args['tax_query'][] = [
            'taxonomy' => 'cryns_album_title',
            'field'    => 'term_id',
            'terms'    => array_map( 'intval', explode( ',', $album ) ),
        ];
    }
    if ( $musicians ) {
        $args['tax_query'][] = [
            'taxonomy' => 'cryns_musicians',
            'field'    => 'term_id',
            'terms'    => array_map( 'intval', explode( ',', $musicians ) ),
        ];
    }
    if ( $written_by ) {
        $args['tax_query'][] = [
            'taxonomy' => 'cryns_written_by',
            'field'    => 'term_id',
            'terms'    => array_map( 'intval', explode( ',', $written_by ) ),
        ];
    }

    $query = new WP_Query( $args );
    $items = [];

    foreach ( $query->posts as $post ) {
        $item = [
            'id'          => $post->ID,
            'type'        => $post->post_type,
            'title'       => [ 'rendered' => get_the_title( $post->ID ) ],
            'link'        => get_permalink( $post->ID ),
            'audio_file'  => null,
            'has_playlist' => false,
        ];

        if ( 'cryns_audio_file' === $post->post_type ) {
            $file_id = get_field( 'audio_file', $post->ID ) ?: get_post_meta( $post->ID, 'Audio File', true );
            if ( ! $file_id ) {
                // Fall back to the first attached audio file.
                $attached = get_posts( [
                    'post_type'      => 'attachment',
                    'post_mime_type' => 'audio',
                    'post_parent'    => $post->ID,
                    'post_status'    => 'inherit',
                    'posts_per_page' => 1,
                    'orderby'        => 'menu_order title',
                    'order'          => 'ASC',
                ] );
                if ( $attached ) {
                    $file_id = $attached[0]->ID;
                    $item['has_playlist'] = true;
                }
            }
            if ( $file_id ) {
                $item['audio_file'] = [
                    'id'   => (int) $file_id,
                    'url'  => wp_get_attachment_url( $file_id ),
                    'mime' => get_post_mime_type( $file_id ) ?: 'audio/mpeg',
                ];
            }
        }

        $items[] = $item;
    }

    $response = new WP_REST_Response( $items, 200 );
    $response->header( 'X-WP-Total',      $query->found_posts );
    $response->header( 'X-WP-TotalPages', $query->max_num_pages ?: 1 );

    return $response;
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
