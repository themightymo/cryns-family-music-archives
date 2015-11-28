<?php
/**
 * The Template for displaying all single posts.
 *
 * @package AppPresser Theme
 */

get_header(); ?>

<?php appp_title_header(); ?>

<div id="content" class="site-content" role="main">

<?php while ( have_posts() ) : the_post(); ?>

	<?php get_template_part( 'content', 'single' ); ?>

	<?php get_template_part( 'content', 'below_post' ); ?>

<?php endwhile; // end of the loop. ?>

</div><!-- #content -->

<?php get_footer(); ?>