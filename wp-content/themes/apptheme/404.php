<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package AppPresser Theme
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<?php appp_title_header(); ?>
		<div id="content" class="site-content" role="main">

			<article id="post-0" class="post not-found">

				<div class="entry-content">
					<p><?php _e( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'apptheme' ); ?></p>

					<?php get_search_form(); ?>

					<?php the_widget( 'WP_Widget_Recent_Posts' ); ?>

					<?php if ( appp_categorized_blog() ) : // Only show the widget if site has multiple categories. ?>
					<div class="widget widget_categories">
						<h2 class="widgettitle"><?php _e( 'Most Used Categories', 'apptheme' ); ?></h2>
						<ul>
						<?php
							wp_list_categories( array(
								'orderby'    => 'count',
								'order'      => 'DESC',
								'show_count' => 1,
								'title_li'   => '',
								'number'     => 10,
							) );
						?>
						</ul>
					</div><!-- .widget -->
					<?php endif; ?>

					<?php
					/* translators: %1$s: smiley */
					$archive_content = '<p>' . sprintf( __( 'Try looking in the monthly archives. %1$s', 'apptheme' ), convert_smilies( ':)' ) ) . '</p>';
					the_widget( 'WP_Widget_Archives', 'dropdown=1', "after_title=</h2>$archive_content" );
					?>

					<?php the_widget( 'WP_Widget_Tag_Cloud' ); ?>

				</div><!-- .entry-content -->
			</article><!-- #post-0 .post .not-found -->

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
