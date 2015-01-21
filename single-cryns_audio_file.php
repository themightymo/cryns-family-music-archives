<?php
/**
 * The template for displaying all single posts.
 *
 * @package cryns-family-music-archive
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php while ( have_posts() ) : the_post(); ?>

			<?php get_template_part( 'content', 'single' ); ?>
			
			<?php		
			// Add all the audio meta data and audio player to the front end - Note the audio player requires the "Audio Player" plugin
				
			// Creates the variable, $myAlbumTitle
			$terms = get_the_terms ($post->id, 'cryns_album_title'); 
			if ($terms) {
				unset($myterms);
				foreach ($terms as $term) {
					$myterms[] = $term->name;
					}
											
				$myAlbumTitle = join(", ", $myterms);
			}
			 
			// Creates the variable, $myArtist
			$terms = get_the_terms ($post->id, 'cryns_artist'); 
			if ($terms) {
				unset($myterms);
				foreach ($terms as $term) {
					$myterms[] = $term->name;
					}
											
				$myArtist = join(", ", $myterms);
			}
			?>	
			
		    <?php if (get_the_term_list( get_the_ID(), 'cryns_artist', "Artist: " )) { 
				echo '<span class="audio-meta">';
				echo get_the_term_list( get_the_ID(), 'cryns_artist', "Artist: " );
				echo '</span><br />';
			}
			?>
		    
			<?php if (get_the_term_list( get_the_ID(), 'cryns_album_title', "Album Title: " )) {
				echo '<span class="audio-meta">';
				echo get_the_term_list( get_the_ID(), 'cryns_album_title', "Album Title: " ); 
				echo '</span><br />';
			}
			?>
			
		    <?php if (get_the_term_list( get_the_ID(), 'cryns_genre', "Genre(s): ", ', ' )) {
				echo '<span class="audio-meta">';
				echo get_the_term_list( get_the_ID(), 'cryns_genre', "Genre(s): ", ', ' ); 
				echo '</span><br />';
		    }
		    ?>
		    
			<?php if (get_the_term_list( get_the_ID(), 'cryns_producer', "Producer(s): ", ', ' )) {
				echo '<span class="audio-meta">';
				echo get_the_term_list( get_the_ID(), 'cryns_producer', "Producer(s): ", ', ' );
				echo '</span><br />';
			}
			?>
		    
			<?php if (get_the_term_list( get_the_ID(), 'cryns_engineer', "Engineer(s): ", ', ' )) {
				echo '<span class="audio-meta">';
				echo get_the_term_list( get_the_ID(), 'cryns_engineer', "Engineer(s): ", ', ' ); 
				echo '</span><br />';
			}?>
		    
			<?php if (get_the_term_list( get_the_ID(), 'cryns_musicians', "Musicians: ", ', ' )) {
				echo '<span class="audio-meta">';
				echo get_the_term_list( get_the_ID(), 'cryns_musicians', "Musicians: ", ', ' ); 
				echo '</span><br />';
			}
			?>
		    
			<?php if (get_the_term_list( get_the_ID(), 'cryns_release_year', "Release Year: " )) {
				echo '<span class="audio-meta">';
				echo get_the_term_list( get_the_ID(), 'cryns_release_year', "Release Year: " ); 
				echo '<br /></span>';
			}
			?>
		    
			<?php if (get_the_term_list( get_the_ID(), 'cryns_track_number', "Track Number: " )) {
				echo '<span class="audio-meta">';
				echo get_the_term_list( get_the_ID(), 'cryns_track_number', "Track Number: " ); 
				echo '<br /></span>';
			}
			?>
		    
			<?php if (get_the_term_list( get_the_ID(), 'cryns_written_by', "Written By: " )) {
				echo '<span class="audio-meta">';
				echo get_the_term_list( get_the_ID(), 'cryns_written_by', "Written By: ", ', ' );
				echo '<br /></span>';
			}
			?>
		   	
		    <?php if (get_post_meta($post->ID, 'Audio File', true)) { ?>
		    	<span class="audio-meta">
				Download: <a href="<?php echo wp_get_attachment_url(get_post_meta($post->ID, 'Audio File', true)); ?>"><?php the_title(); ?></a><br /><br />
		        </span>
		    <?php } ?>
		    
		    <?php
			$mp3 = wp_get_attachment_url(get_post_meta($post->ID, 'Audio File', true)); 
			$attr = array(
				'src'      => $mp3,
				'loop'     => '',
				'autoplay' => '',
				'preload' => 'none'
				);
			echo wp_audio_shortcode( $attr );
			?>

			<?php the_post_navigation(); ?>

			<?php
				// If comments are open or we have at least one comment, load up the comment template
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;
			?>

		<?php endwhile; // end of the loop. ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
