<?php
/**
 * @package AppPresser Theme
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('post'); ?>>

	<a href="<?php the_permalink(); ?>"><?php if (has_post_thumbnail()) the_post_thumbnail('thumbnail', array('class' => 'alignleft')); ?></a>
	<h1 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h1>
	<div class="entry-content">
		<?php the_excerpt(); ?>
	</div><!-- .entry-content -->

</article><!-- #post-## -->
