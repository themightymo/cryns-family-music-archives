<?php
/**
 * The template for displaying archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package cryns-family-music-archive
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php $mp3Files = array (); //Store the audio file id in an array ?>
			
			<?php if ( have_posts() ) : ?>
	
				<header class="page-header">
					<?php
						the_archive_title( '<h1 class="page-title">', '</h1>' );
						the_archive_description( '<div class="taxonomy-description">', '</div>' );
					?>
				</header><!-- .page-header -->
	
				<?php /* Start the Loop */ ?>
				<?php while ( have_posts() ) : the_post(); ?>
	
					<div><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div>
					<?php
					global $post;
						if ( wp_get_attachment_url( get_post_meta($post->ID, 'Audio File', true) ) ) {
						//$mp3 = 'hello'; 
						/*$attr = array(
							'src'		=> $mp3,
							'loop'		=> '',
							'autoplay'	=> '',
							'preload'	=> 'none'
						);
						
						//Add the mp3 id to the end of the array
						$mp3ID = get_post_meta($post->ID, 'Audio File', true); 
						$mp3Files[] = $mp3ID;*/

					}
					?>
	
	
				<?php endwhile; ?>
	
				<?php the_posts_navigation(); ?>
	
			<?php else : ?>
	
				<?php get_template_part( 'content', 'none' ); ?>
	
			<?php endif; ?>
			
			<?php 
			/*$myvar = '';
			
			for ($index = 0; $index < count($mp3Files); $index++){  
				$myvar .= $mp3Files[$index];
				$myvar .= ',';
			}
			
			echo do_shortcode('[playlist ids="' . $myvar . '"]');
			
			
			$mp3 = wp_get_attachment_url(get_post_meta($post->ID, 'Audio File', true)); 
			$attr = array(
			'src'      => $mp3,
			'loop'     => '',
			'autoplay' => '',
			'preload' => 'none'
			);
			echo wp_playlist_shortcode( $attr );*/
			?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
