<?php
/**
 * The main template file.
 *
 * @package AppPresser Theme
 */

get_header();
appp_title_header(); ?>

<div id="content" class="site-content" role="main">	

	<?php if ( have_posts() ) : ?>
	
		<?php if( apptheme_get_list_type() ) : ?>
		
			<div class="<?php echo apptheme_get_list_type() ?>">
			
				<?php apptheme_get_slider(); ?>
				
			    <ul>
				
					<?php while ( have_posts() ) : the_post(); ?>
					
						<?php get_template_part( 'content', apptheme_get_list_type() ); ?>
					
					<?php endwhile; ?>
		
			    </ul>
			</div>
					
		<?php else : ?>
	
				<?php while ( have_posts() ) : the_post(); ?>
			
					<?php
						/* Include the Post-Format-specific template for the content.
						 * If you want to overload this in a child theme then include a file
						 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
						 */
						get_template_part( 'content', get_post_format() );
					?>
			
				<?php endwhile; ?>
				
		<?php endif; ?>
			
		<?php appp_content_nav( 'nav-below' ); ?>
		
	<?php endif; ?>

</div><!-- #content -->

<?php get_footer(); ?>