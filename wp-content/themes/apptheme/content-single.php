<?php
/**
 * @package AppPresser Theme
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content">
		<h1 class="entry-title"><?php echo $title = apply_filters( 'appp_single_post_title', the_title(null,null,false) ); ?></h1>

		<?php

		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'large' );
		}
		the_content();

		wp_link_pages( array(
			'before' => '<div class="page-links">' . __( 'Pages:', 'apptheme' ),
			'after'  => '</div>',
		) );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-meta">
		<?php 

			// Post on meta
			echo '<span class="appp-posted-on">';
			appp_posted_on();
			echo '</span>';
			
		?>

		<?php edit_post_link( __( 'Edit', 'apptheme' ), '<span class="sep"> | </span><span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-meta -->
</article><!-- #post-## -->
