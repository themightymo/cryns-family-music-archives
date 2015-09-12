<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package AppPresser Theme
 * @since   0.0.1
 */

class AppPresser_Extra_Functionality {

	/**
	 * AppPresser_Extra_Functionality hooks
	 * @since 1.0.6
	 */
	public function hooks() {
		return array(
			array( 'wp_page_menu_args', 'page_menu_args' ),
			array( 'attachment_link', 'enhanced_image_navigation', 10, 2 ),
			array( 'wp_title', 'wp_title', 10, 2 ),
			array( 'wp_head', 'viewport_meta' )
		);
	}

	/**
	 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
	 * @since  0.0.1
	 */
	function page_menu_args( $args ) {
		$args['show_home'] = true;
		return $args;
	}
	/**
	 * Filter in a link to a content ID attribute for the next/previous image links on image attachment pages
	 * @since  0.0.1
	 */
	function enhanced_image_navigation( $url, $id ) {
		if ( ! is_attachment() && ! wp_attachment_is_image( $id ) )
			return $url;

		$image = get_post( $id );
		if ( ! empty( $image->post_parent ) && $image->post_parent != $id )
			$url .= '#main';

		return $url;
	}

	/**
	 * Filters wp_title to print a neat <title> tag based on what is being viewed.
	 * @since  0.0.1
	 */
	function wp_title( $title, $sep ) {
		global $page, $paged;

		if ( is_feed() )
			return $title;

		// Add the blog name
		$title .= get_bloginfo( 'name' );

		// Add the blog description for the home/front page.
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) )
			$title .= " $sep $site_description";

		// Add a page number if necessary:
		if ( $paged >= 2 || $page >= 2 )
			$title .= " $sep " . sprintf( __( 'Page %s', 'apptheme' ), max( $paged, $page ) );

		return $title;
	}

	/**
	 * Adds viewport meta tag
	 * @since  0.0.1
	 */
	function viewport_meta() {
		?>
<!-- Sets initial viewport load and disables zooming  -->
<meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no">
		<?php
	}

}