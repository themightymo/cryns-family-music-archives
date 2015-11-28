<?php
/**
 * @package AppPresser Theme
 */
?>

<div id="post-<?php the_ID(); ?>" class="post">

 	<div class="item-content">

		<a class="<?php echo apply_filters('appp_transition_left', $classname ); ?>" href="<?php the_permalink(); ?>">
		

			<div class="item-inner">
			
			  <div class="item-title"><?php the_title(); ?></div>
			
			  <div class="item-text"><?php the_excerpt(); ?></div>
			  
			</div>
		  
		</a>
	
 	</div>
	
</div><!-- #post-## -->
