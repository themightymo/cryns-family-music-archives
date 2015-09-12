<?php
/**
 * @package AppPresser Theme
 */
?>

<div id="post-<?php the_ID(); ?>" class="post">

 	<div class="item-content">

		<a class="item-link" href="<?php the_permalink(); ?>">
		
			<div class="item-media">
			
			  <?php if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'thumbnail' );
				} else { ?>
					<img src="<?php echo get_stylesheet_directory_uri() . '/images/thumbnail.jpg'; ?>">
				<?php } ?>
				
			</div>
			
			<div class="item-inner">
			
			  <div class="item-title"><?php the_title(); ?></div>
			
			  <div class="item-text"><?php the_excerpt(); ?></div>
			  
			</div>
		  
		</a>
	
 	</div>
	
</div><!-- #post-## -->
