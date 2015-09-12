<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package AppPresser Theme
 */

get_header(); ?>

<?php appp_title_header(); ?>

<div id="content" class="site-content" role="main">

<?php if ( have_posts() ) : ?>

	<?php /* Start the Loop */ ?>
	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'content', 'search' ); ?>

	<?php endwhile; ?>

	<?php appp_content_nav( 'nav-below' ); ?>

<?php else : ?>

	<?php get_template_part( 'no-results', 'search' ); ?>

<?php endif; ?>

</div><!-- #content -->

<?php get_footer(); ?>