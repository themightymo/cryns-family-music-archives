<?php
/**
 * Jetpack Compatibility File
 * See: http://jetpack.me/
 *
 * @package AppPresser Theme
 * @since   0.0.1
 */

class AppPresser_Jetpack {

	/**
	 * AppPresser_Jetpack hooks
	 * @since 1.0.6
	 */
	public function hooks() {
		return array( array( 'after_setup_theme', 'jetpack_setup' ) );
	}

	/**
	 * Add theme support for Infinite Scroll.
	 * @see http://jetpack.me/support/infinite-scroll/
	 * @since 0.0.1
	 */
	public function jetpack_setup() {
		add_theme_support( 'infinite-scroll', array(
			'container' => 'content',
			'footer'    => 'page',
		) );
	}
}
