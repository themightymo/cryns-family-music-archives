<?php
/**
 * @package AppPresser Theme
 */
?>

<div id="post-<?php the_ID(); ?>" class="post">

	<a class="<?php echo apply_filters('appp_transition_left', $classname ); ?>" href="<?php the_permalink(); ?>">
		<div class="card">
		    <div class="card-header"><?php the_title(); ?></div>
		    <div class="card-content">
			    <div class="card-media">
				
				  <?php if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'full' );
					} ?>
					
				</div>
		        <div class="card-content-inner"><?php the_excerpt(); ?></div>
		    </div>
		    <div class="card-footer"><?php do_action( 'appp_cardlist_footer'); ?></div>
		</div> 
	</a>
	
</div><!-- #post-## -->

