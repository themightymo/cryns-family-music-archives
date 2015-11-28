<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package AppPresser Theme
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content">
		<?php the_content(); ?>
		<?php get_template_part( 'content', 'below_page' ); ?>
	</div><!-- .entry-content -->
	<?php edit_post_link( __( 'Edit', 'apptheme' ), '<footer class="entry-meta"><span class="edit-link">', '</span></footer>' ); ?>
</article><!-- #post-## -->
