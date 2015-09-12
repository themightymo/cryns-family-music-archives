<?php

require_once( 'inc/classes/AppPresser_Theme_Setup.php' );

// Only include if it doesn't already exist in a child theme.
if ( ! class_exists( 'AppPresser_Theme_Settings' ) ) {

/**
 * AppPresser Theme Settings Setup
 *
 * @package AppPresser Theme
 * @since   0.0.1
 */
class AppPresser_Theme_Settings extends AppPresser_Admin_Settings {

	private static $done = null;
	private $updater     = null;

	/**
	 * Setup AppPresser_Theme_Settings
	 * @since 1.0.1
	 */
	public function __construct() {
		if ( null !== self::$done )
			return;

		add_action( 'after_setup_theme', array( $this, 'updater' ) );
		// Add it late to be below extension options
		add_action( 'apppresser_add_settings', array( $this, 'theme_options' ), 50 );
		// Add the customizer link
		add_action( 'apppresser_tab_buttons_appp-theme', array( $this, 'customizer_link' ) );

		self::$done = true;
	}

	/**
	 * Add the updater to the theme
	 * @since  0.0.1
	 */
	public function updater() {
		if ( null !== $this->updater )
			return $this->updater;

		$this->updater = appp_theme_updater_add( AppPresser_Theme_Setup::THEME_SLUG, AppPresser_Theme_Setup::APPP_KEY, array(
			'item_name' => AppPresser_Theme_Setup::THEME_NAME,
			'version'   => AppPresser_Theme_Setup::VERSION,
		) );

		return $this->updater;
	}

	/**
	 * Adds a checkbox to disable the theme's ajax page loading on the AppPresser Core plugin's settings page
	 * @since  0.0.1
	 * @param  object $appp The AppPresser_Admin_Settings instance
	 */
	public function theme_options( $appp ) {
		// $appp->add_setting_tab( __( 'AppPresser Theme Settings', 'apptheme' ), 'appp-theme' );
		$appp->add_setting_label( __( 'Theme Settings', 'apptheme' ), array(
			// 'tab' => 'appp-theme',
			// 'helptext' => __( 'These is the options for your installed App Aware theme.', 'apptheme' ),
			// 'description' => __( 'These is the options for your installed App Aware theme.', 'apptheme' ),
		) );
		$appp->add_setting( AppPresser_Theme_Setup::APPP_KEY, __( 'AppPresser Theme License Key', 'apptheme' ), array( 'type' => 'license_key', 'helptext' => __( 'Adding a license key enables automatic updates.', 'apptheme' ) ) );
		$appp->add_setting( 'disable_theme_ajax', __( 'Disable dynamic page loading', 'apptheme' ), array(
			'type' => 'checkbox',
			// 'tab' => 'appp-theme',
			'helptext' => __( 'The AppPresser theme relies heavily on ajax to avoid page refreshes. Many WordPress plugins are not compatible with ajax, so disabling may help resolve some issues.', 'apptheme' ),
		) );

	}

	/**
	 * Add a link to the theme customizer to the settings page
	 * @since  0.0.1
	 */
	public function customizer_link() {
		echo '<a class="button-secondary" href="'. admin_url( 'customize.php' ) .'">'. __( 'Theme Customizer', 'apptheme' ) .'</a>';
	}

}

$GLOBALS['AppPresser_Theme_Settings'] = new AppPresser_Theme_Settings();

} // end class_exists check
