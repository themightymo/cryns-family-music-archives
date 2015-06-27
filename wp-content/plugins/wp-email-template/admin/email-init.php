<?php
function wp_email_template_install(){
	update_option('a3rev_wp_email_template_version', '1.2.0');
	update_option('a3rev_wp_email_template_lite_version', '1.2.0');

	// Set Settings Default from Admin Init
	global $wp_email_template_admin_init;
	$wp_email_template_admin_init->set_default_settings();

	update_option('a3rev_wp_email_just_installed', true);
}

update_option('a3rev_wp_email_template_plugin', 'wp_email_template');

/**
 * Load languages file
 */
function wp_email_template_init() {
	if ( get_option('a3rev_wp_email_just_installed') ) {
		delete_option('a3rev_wp_email_just_installed');
		wp_redirect( admin_url( 'admin.php?page=wp_email_template', 'relative' ) );
		exit;
	}
	load_plugin_textdomain( 'wp_email_template', false, WP_EMAIL_TEMPLATE_FOLDER.'/languages' );
}
// Add language
add_action('init', 'wp_email_template_init');

// Add custom style to dashboard
add_action( 'admin_enqueue_scripts', array( 'WP_Email_Template_Hook_Filter', 'a3_wp_admin' ) );

// Add extra link on left of Deactivate link on Plugin manager page
add_action('plugin_action_links_'.WP_EMAIL_TEMPLATE_NAME, array('WP_Email_Template_Hook_Filter', 'settings_plugin_links') );

// Add admin sidebar menu css
add_action( 'admin_enqueue_scripts', array( 'WP_Email_Template_Hook_Filter', 'admin_sidebar_menu_css' ) );

// Add text on right of Visit the plugin on Plugin manager page
add_filter( 'plugin_row_meta', array('WP_Email_Template_Hook_Filter', 'plugin_extra_links'), 10, 2 );


	// Need to call Admin Init to show Admin UI
	global $wp_email_template_admin_init;
	$wp_email_template_admin_init->init();

	// Add upgrade notice to Dashboard pages
	add_filter( $wp_email_template_admin_init->plugin_name . '_plugin_extension', array( 'WP_Email_Template_Hook_Filter', 'plugin_extension' ) );

	$admin_pages = $wp_email_template_admin_init->admin_pages();
	if ( is_array( $admin_pages ) && count( $admin_pages ) > 0 ) {
		foreach ( $admin_pages as $admin_page ) {
			add_action( $wp_email_template_admin_init->plugin_name . '-' . $admin_page . '_tab_start', array( 'WP_Email_Template_Hook_Filter', 'plugin_extension_start' ) );
			add_action( $wp_email_template_admin_init->plugin_name . '-' . $admin_page . '_tab_end', array( 'WP_Email_Template_Hook_Filter', 'plugin_extension_end' ) );
		}
	}

	add_action('wp_ajax_preview_wp_email_template', array('WP_Email_Template_Hook_Filter', 'preview_wp_email_template') );
	add_action('wp_ajax_nopriv_preview_wp_email_template', array('WP_Email_Template_Hook_Filter', 'preview_wp_email_template') );

	// Add marker at start of email template header from woocommerce
	add_action('woocommerce_email_header', array('WP_Email_Template_Hook_Filter', 'woo_email_header_marker_start'), 1 );

	// Add marker at end of email template header from woocommerce
	add_action('woocommerce_email_header', array('WP_Email_Template_Hook_Filter', 'woo_email_header_marker_end'), 100 );

	// Add marker at start of email template footer from woocommerce
	add_action('woocommerce_email_footer', array('WP_Email_Template_Hook_Filter', 'woo_email_footer_marker_start'), 1 );

	// Add marker at end of email template footer from woocommerce
	add_action('woocommerce_email_footer', array('WP_Email_Template_Hook_Filter', 'woo_email_footer_marker_end'), 100 );

	// Apply the email template to wp_mail of wordpress
	add_filter('wp_mail_content_type', array('WP_Email_Template_Hook_Filter', 'set_content_type'), 20);
	add_filter('wp_mail', array('WP_Email_Template_Hook_Filter', 'change_wp_mail'), 20);

// Check upgrade functions
add_action('plugins_loaded', 'a3rev_wp_email_template_lite_upgrade_plugin');
function a3rev_wp_email_template_lite_upgrade_plugin () {

	if(version_compare(get_option('a3rev_wp_email_template_version'), '1.0.4') === -1){
		$wp_email_template_settings = get_option('wp_email_template_settings');
		if (isset($wp_email_template_settings['header_image']))
			update_option('wp_email_template_header_image', $wp_email_template_settings['header_image']);
		update_option('a3rev_wp_email_template_version', '1.0.4');
	}

	//Upgrade to version 1.0.8
	if ( version_compare( get_option( 'a3rev_wp_email_template_version'), '1.0.8' ) === -1 ) {
		$wp_email_template_settings = get_option( 'wp_email_template_settings' );

		$wp_email_template_general = array(
			'header_image'					=> get_option('wp_email_template_header_image', '' ),
			'background_colour'				=> $wp_email_template_settings['background_colour'],
			'deactivate_pattern_background'	=> $wp_email_template_settings['deactivate_pattern_background'],
			'apply_for_woo_emails'			=> $wp_email_template_settings['apply_for_woo_emails'],
			'show_plugin_url'				=> $wp_email_template_settings['show_plugin_url'],
		);
		update_option( 'wp_email_template_general', $wp_email_template_general );

		$wp_email_template_style = array(
			'base_colour'					=> $wp_email_template_settings['base_colour'],
			'header_font'					=> array(
					'size'		=> $wp_email_template_settings['header_text_size'],
					'face'		=> $wp_email_template_settings['header_font'],
					'style'		=> $wp_email_template_settings['header_text_style'],
					'color'		=> $wp_email_template_settings['header_text_colour'],
				),
			'content_background_colour'		=> $wp_email_template_settings['content_background_colour'],
			'content_font'					=> array(
					'size'		=> $wp_email_template_settings['content_text_size'],
					'face'		=> $wp_email_template_settings['content_font'],
					'style'		=> $wp_email_template_settings['content_text_style'],
					'color'		=> $wp_email_template_settings['content_text_colour'],
				),
			'content_link_colour'			=> $wp_email_template_settings['content_link_colour'],
			'email_footer'					=> $wp_email_template_settings['email_footer'],
		);
		update_option( 'wp_email_template_style', $wp_email_template_style );

		$wp_email_template_social_media = array(
			'email_facebook'				=> $wp_email_template_settings['email_facebook'],
			'email_twitter'					=> $wp_email_template_settings['email_twitter'],
			'email_linkedIn'				=> $wp_email_template_settings['email_linkedIn'],
			'email_pinterest'				=> $wp_email_template_settings['email_pinterest'],
			'email_googleplus'				=> $wp_email_template_settings['email_googleplus'],
			'facebook_icon'					=> WP_EMAIL_TEMPLATE_IMAGES_URL.'/icon_facebook.png',
			'twitter_icon'					=> WP_EMAIL_TEMPLATE_IMAGES_URL.'/icon_twitter.png',
			'linkedIn_icon'					=> WP_EMAIL_TEMPLATE_IMAGES_URL.'/icon_linkedin.png',
			'pinterest_icon'				=> WP_EMAIL_TEMPLATE_IMAGES_URL.'/icon_pinterest.png',
			'googleplus_icon'				=> WP_EMAIL_TEMPLATE_IMAGES_URL.'/icon_googleplus.png',
		);
		update_option( 'wp_email_template_social_media', $wp_email_template_social_media );

		update_option( 'a3rev_wp_email_template_version', '1.0.8' );
	}

	//Upgrade to version 1.1.0
	if ( version_compare( get_option( 'a3rev_wp_email_template_lite_version'), '1.1.0' ) === -1 ) {
		update_option( 'a3rev_wp_email_template_lite_version', '1.1.0' );

		$wp_email_template_style = get_option( 'wp_email_template_style' );
		if ( isset( $wp_email_template_style['email_footer'] ) )
			update_option( 'wp_email_template_email_footer', $wp_email_template_style['email_footer'] );
	}

	if ( version_compare( get_option( 'a3rev_wp_email_template_lite_version'), '1.2.0' ) === -1 ) {
		include( WP_EMAIL_TEMPLATE_DIR. '/includes/updates/wp-email-update-1.2.0.php' );
		update_option( 'a3rev_wp_email_template_lite_version', '1.2.0' );

		global $wp_email_template_admin_init;
		$wp_email_template_admin_init->set_default_settings();
	}

	update_option('a3rev_wp_email_template_version', '1.2.0');
	update_option('a3rev_wp_email_template_lite_version', '1.2.0');
}
?>
