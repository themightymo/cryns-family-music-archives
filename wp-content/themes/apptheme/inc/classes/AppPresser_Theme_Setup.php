<?php
// Allows child-thems to override this class
if ( ! class_exists( 'AppPresser_Theme_Setup' ) ) {
	class AppPresser_Theme_Setup {

		/**
		 * The option key for `appp_get_setting`
		 */
		const APPP_KEY   = 'appptheme_key';

		/**
		 * The name of the child theme as it's registered in apppresser.com/extensions
		 */
		const THEME_NAME = 'AppTheme';

		/**
		 * The folder name for this theme
		 */
		const THEME_SLUG = 'apppresser';

		/**
		 * The current version of this theme
		 */
		const VERSION    = '2.0.1';
	
	}
}