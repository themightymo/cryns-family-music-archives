<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package AppPresser Theme
 * @since   0.0.1
 */

class AppPresser_Tags {

	public static $title    = 'no';
	public static $has_logo = 'no';

	/**
	 * AppPresser_Tags hooks
	 * @since 1.0.6
	 */
	public function hooks() {
		return array(
			array( 'edit_category', 'flush_cats' ),
			array( 'save_post', 'flush_cats' ),
		);
	}

	/**
	 * Display navigation to next/previous pages when applicable
	 * @since 0.0.1
	 */
	public static function content_nav( $nav_id ) {
		global $wp_query, $post;

		// Don't print empty markup on single pages if there's nowhere to navigate.
		if ( is_single() ) {
			$previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
			$next = get_adjacent_post( false, '', false );

			if ( ! $next && ! $previous )
				return;
		}

		// Don't print empty markup in archives if there's only one page.
		if ( $wp_query->max_num_pages < 2 && ( is_home() || is_archive() || is_search() ) )
			return;

		$nav_class = ( is_single() ) ? 'navigation-post' : 'navigation-paging';

		?>
		<nav role="navigation" id="<?php echo esc_attr( $nav_id ); ?>" class="<?php echo $nav_class; ?>">
			<h1 class="screen-reader-text"><?php _e( 'Post navigation', 'apptheme' ); ?></h1>

		<?php if ( is_single() ) : // navigation links for single posts ?>

			<ul class="pager">
			<?php previous_post_link( '<li class="previous">%link</li>', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'apptheme' ) . '</span> Previous' ); ?>
			<?php next_post_link( '<li class="next">%link</li>', 'Next <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'apptheme' ) . '</span>' ); ?>
			</ul>

		<?php elseif ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages ?>

			<ul class="pager">

			<?php if ( get_next_posts_link() ) : ?>
			<li class="previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'apptheme' ) ); ?></li>

			<?php endif; ?>

			<?php if ( get_previous_posts_link() ) : ?>
			<li class="next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'apptheme' ) ); ?></li>
			<?php endif; ?>

			</ul>

		<?php endif; ?>

		</nav><!-- #<?php echo esc_html( $nav_id ); ?> -->
		<?php
	}

	/**
	 * Template for comments and pingbacks.
	 *
	 * Used as a callback by wp_list_comments() for displaying the comments.
	 * @since  0.0.1
	 */
	public static function comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;

		if ( 'pingback' == $comment->comment_type || 'trackback' == $comment->comment_type ) : ?>

		<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
			<div class="comment-body">
				<?php _e( 'Pingback:', 'apptheme' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( 'Edit', 'apptheme' ), '<span class="edit-link">', '</span>' ); ?>
			</div>

		<?php else : ?>

		<li id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?>>
			<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
				<footer class="comment-meta">
					<div class="comment-author vcard">
						<?php if ( 0 != $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
						<?php printf( __( '%s <span class="says">says:</span>', 'apptheme' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
					</div><!-- .comment-author -->

					<div class="comment-metadata">
						<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
							<time datetime="<?php comment_time( 'c' ); ?>">
								<?php printf( _x( '%1$s at %2$s', '1: date, 2: time', 'apptheme' ), get_comment_date(), get_comment_time() ); ?>
							</time>
						</a>
						<?php edit_comment_link( __( 'Edit', 'apptheme' ), '<span class="edit-link">', '</span>' ); ?>
					</div><!-- .comment-metadata -->

					<?php if ( '0' == $comment->comment_approved ) : ?>
					<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'apptheme' ); ?></p>
					<?php endif; ?>
				</footer><!-- .comment-meta -->

				<div class="comment-content">
					<?php comment_text(); ?>
				</div><!-- .comment-content -->

				<div class="reply">
					<?php comment_reply_link( array_merge( $args, array( 'add_below' => 'div-comment', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
				</div><!-- .reply -->
			</article><!-- .comment-body -->

		<?php
		endif;
	}

	/**
	 * Prints the attached image with a link to the next attached image.
	 * @since  0.0.1
	 */
	public static function attached_image() {
		$post                = get_post();
		$attachment_size     = apply_filters( 'appp_attachment_size', array( 1200, 1200 ) );
		$next_attachment_url = wp_get_attachment_url();

		/**
		 * Grab the IDs of all the image attachments in a gallery so we can get the URL
		 * of the next adjacent image in a gallery, or the first image (if we're
		 * looking at the last image in a gallery), or, in a gallery of one, just the
		 * link to that image file.
		 */
		$attachments = array_values( get_children( array(
			'post_parent'    => $post->post_parent,
			'post_status'    => 'inherit',
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'order'          => 'ASC',
			'orderby'        => 'menu_order ID'
		) ) );

		// If there is more than 1 attachment in a gallery...
		if ( count( $attachments ) > 1 ) {
			foreach ( $attachments as $k => $attachment ) {
				if ( $attachment->ID == $post->ID )
					break;
			}
			$k++;

			// get the URL of the next image attachment...
			if ( isset( $attachments[ $k ] ) )
				$next_attachment_url = get_attachment_link( $attachments[ $k ]->ID );

			// or get the URL of the first image attachment.
			else
				$next_attachment_url = get_attachment_link( $attachments[0]->ID );
		}

		printf( '<a href="%1$s" title="%2$s" rel="attachment">%3$s</a>',
			esc_url( $next_attachment_url ),
			the_title_attribute( array( 'echo' => false ) ),
			wp_get_attachment_image( $post->ID, $attachment_size )
		);
	}

	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 * @since  0.0.1
	 */
	public static function posted_on() {
		printf( __( 'Posted on <a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a><span class="byline"> by <span class="author vcard"><a class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</a></span></span>', 'apptheme' ),
			esc_url( get_permalink() ),
			esc_attr( get_the_time() ),
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() ),
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_attr( sprintf( __( 'View all posts by %s', 'apptheme' ), get_the_author() ) ),
			get_the_author()
		);
	}

	/**
	 * Returns true if a blog has more than 1 category
	 * @since  0.0.1
	 */
	public static function categorized_blog() {
		if ( false === ( $all_the_cool_cats = get_transient( 'all_the_cool_cats' ) ) ) {
			// Create an array of all the categories that are attached to posts
			$all_the_cool_cats = get_categories( array(
				'hide_empty' => 1,
			) );

			// Count the number of categories that are attached to the posts
			$all_the_cool_cats = count( $all_the_cool_cats );

			set_transient( 'all_the_cool_cats', $all_the_cool_cats, HOUR_IN_SECONDS );
		}

		if ( '1' != $all_the_cool_cats ) {
			// This blog has more than 1 category so return true
			return true;
		} else {
			// This blog has only 1 category so return false
			return false;
		}
	}

	/**
	 * Checks if logo has been added via the customizer
	 * @since  0.0.1
	 */
	public static function has_logo() {
		if ( self::$has_logo !== 'no' )
			return self::$has_logo;

		self::$has_logo = get_theme_mod( 'appp_logo' );

		return self::$has_logo;
	}

	/**
	 * Gets page header area
	 * @since  0.0.1
	 */
	public static function title_header() {
		$object = get_queried_object();
		$description = isset( $object->taxonomy )
			? term_description( $object->term_id, $object->taxonomy )
			: false;

		if ( ! self::has_logo() && ! $description )
			return;

		if ( is_home() || is_front_page() )
			return;

		?>
		<header class="entry-header">
			<?php

			if ( self::has_logo() ) {
				printf( '<h1 class="page-title">%s</h1>', self::get_title( false ) );
			}

			if ( $description ) {

				$tax = $object->taxonomy == 'post_tag' ? 'tag' : $object->taxonomy;

				// add a filter and show our taxonomy term description
				echo apply_filters( "{$tax}_archive_meta", sprintf( '<div class="taxonomy-description">%s</div>', $description ) );
			}

			?>
		</header><!-- .page-header -->
		<?php
	}

	/**
	 * Gets current page title
	 * @since  0.0.1
	 * @param  boolean $echo Whether to echo the title
	 * @return string        Title
	 */
	public static function get_title( $echo = true ) {
		if ( self::$title !== 'no' ) {
			if ( $echo )
				echo self::$title;
			return self::$title;
		}

		$object = get_queried_object();

		self::$title = '';
		if ( is_category() ) :
			self::$title = sprintf( __( '%s', 'apptheme' ), '<span>' . single_cat_title( '', false ) . '</span>' );

		elseif ( is_tag() ) :
			self::$title = sprintf( __( '%s', 'apptheme' ), '<span>' . single_tag_title( '', false ) . '</span>' );

		elseif ( isset( $object->taxonomy ) ) :
			self::$title = sprintf( __( '%s', 'apptheme' ), '<span>' . single_term_title( '', false ) . '</span>' );

		elseif ( is_author() ) :
			/* Queue the first post, that way we know
			 * what author we're dealing with (if that is the case).
			*/
			the_post();
			self::$title = sprintf( __( '%s', 'apptheme' ), '<span class="vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" title="' . esc_attr( get_the_author() ) . '" rel="me">' . get_the_author() . '</a></span>' );
			/* Since we called the_post() above, we need to
			 * rewind the loop back to the beginning that way
			 * we can run the loop properly, in full.
			 */
			rewind_posts();

		elseif ( is_day() ) :
			self::$title = sprintf( __( '%s', 'apptheme' ), '<span>' . get_the_date() . '</span>' );

		elseif ( is_month() ) :
			self::$title = sprintf( __( '%s', 'apptheme' ), '<span>' . get_the_date( 'F Y' ) . '</span>' );

		elseif ( is_year() ) :
			self::$title = sprintf( __( '%s', 'apptheme' ), '<span>' . get_the_date( 'Y' ) . '</span>' );

		elseif ( is_404() ) :
			self::$title = __( 'Missing', 'apptheme' );

		elseif ( is_search() ) :
			self::$title = sprintf( __( 'Search: %s', 'apptheme' ), '<span>' . get_search_query() . '</span>' );

		elseif ( is_singular() || is_page() ) :
			self::$title = single_post_title( '', false );

		else :
			self::$title = get_bloginfo( 'name' );
		endif;

		if ( $echo )
			echo self::$title;

		return self::$title;
	}

	/**
	 * Flush out the transients used in categorized_blog
	 * @since  0.0.1
	 */
	public function flush_cats() {
		// Like, beat it. Dig?
		delete_transient( 'all_the_cool_cats' );
	}

	/**
	 * Utility method that attempts to get an attachment's ID by it's url
	 * @since  0.0.1
	 * @param  string $img_url Attachment url
	 * @return mixed           Attachment ID or false
	 */
	public static function image_id_from_url( $img_url ) {
		global $wpdb;

		// Get just the file name
		if ( strpos( $img_url, '/' ) )
			$img_url = end( explode( '/', $img_url ) );

		// And search for a fuzzy match of the file name
		$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid LIKE '%%%s%%' LIMIT 1;", $img_url ) );

		// If we found an attachement ID, return it
		if ( !empty( $attachment ) && is_array( $attachment ) )
			return $attachment[0];

		// No luck
		return false;
	}


}

function appp_content_nav( $nav_id ) {
	return AppPresser_Tags::content_nav( $nav_id );
}

function appp_comment( $comment, $args, $depth ) {
	return AppPresser_Tags::comment( $comment, $args, $depth );
}

function appp_the_attached_image() {
	return AppPresser_Tags::attached_image();
}

function appp_posted_on() {
	return AppPresser_Tags::posted_on();
}

function appp_categorized_blog() {
	return AppPresser_Tags::categorized_blog();
}

function appp_has_logo() {
	return AppPresser_Tags::has_logo();
}

function appp_get_title( $echo = true ) {
	return AppPresser_Tags::get_title( $echo );
}

function appp_title_header() {
	return AppPresser_Tags::title_header();
}
