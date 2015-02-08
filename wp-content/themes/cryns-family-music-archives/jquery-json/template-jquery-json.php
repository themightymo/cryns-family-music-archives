<?php
/**
 * Template Name: jquery-json
 *
 * @package cryns-family-music-archive
 */
 
 get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
		
			<?php while ( have_posts() ) : the_post(); ?>

				<p><a href="http://music.cryns.dev/wp-json/posts/?type=cryns_audio_file">http://music.cryns.dev/wp-json/posts/?type=cryns_audio_file</a></p>
			
				<p>See <a href="http://developer.wordpress.com/docs/api/1/get/sites/%24site/posts/">the documentation</a>.</p>
				
				<hr />
				
				<h1><a href="" class="title"></a></h1>
				<div id="single-audio-content"></div>

			<?php endwhile; // end of the loop. ?>
			
			
		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>

	
	
