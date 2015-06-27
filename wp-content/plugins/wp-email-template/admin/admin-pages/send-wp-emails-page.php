<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------
WP Email Template Send WP Emails Page

TABLE OF CONTENTS

- var menu_slug
- var page_data

- __construct()
- page_init()
- page_data()
- add_admin_menu()
- tabs_include()
- admin_settings_page()

-----------------------------------------------------------------------------------*/

class WP_Email_Template_Send_WP_Emails_Page extends WP_Email_Tempate_Admin_UI
{	
	/**
	 * @var string
	 */
	private $menu_slug = 'send_wp_emails';
	
	/**
	 * @var array
	 */
	private $page_data;
	
	/*-----------------------------------------------------------------------------------*/
	/* __construct() */
	/* Settings Constructor */
	/*-----------------------------------------------------------------------------------*/
	public function __construct() {
		$this->page_init();
		$this->tabs_include();
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* page_init() */
	/* Page Init */
	/*-----------------------------------------------------------------------------------*/
	public function page_init() {
		
		add_filter( $this->plugin_name . '_add_admin_menu', array( $this, 'add_admin_menu' ) );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* page_data() */
	/* Get Page Data */
	/*-----------------------------------------------------------------------------------*/
	public function page_data() {
		
		$page_data = array(
			'type'				=> 'submenu',
			'parent_slug'		=> 'wp_email_template',
			'page_title'		=> __( 'Send WP Emails', 'wp_email_template' ),
			'menu_title'		=> __( 'Send WP Emails', 'wp_email_template' ),
			'capability'		=> 'manage_options',
			'menu_slug'			=> $this->menu_slug,
			'function'			=> 'wp_email_template_send_wp_emails_page_show',
			'admin_url'			=> 'admin.php',
			'callback_function' => '',
			'script_function' 	=> '',
			'view_doc'			=> '',
		);
		
		if ( $this->page_data ) return $this->page_data;
		return $this->page_data = $page_data;
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* add_admin_menu() */
	/* Add This page to menu on left sidebar */
	/*-----------------------------------------------------------------------------------*/
	public function add_admin_menu( $admin_menu ) {
		
		if ( ! is_array( $admin_menu ) ) $admin_menu = array();
		$admin_menu[] = $this->page_data();
		
		return $admin_menu;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* tabs_include() */
	/* Include all tabs into this page
	/*-----------------------------------------------------------------------------------*/
	public function tabs_include() {
		
		include_once( $this->admin_plugin_dir() . '/tabs/send-wp-emails/general-settings-tab.php' );
		include_once( $this->admin_plugin_dir() . '/tabs/send-wp-emails/smtp-tab.php' );
		include_once( $this->admin_plugin_dir() . '/tabs/send-wp-emails/gmail-tab.php' );
		include_once( $this->admin_plugin_dir() . '/tabs/send-wp-emails/mandrill-tab.php' );
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* admin_settings_page() */
	/* Show Settings Page */
	/*-----------------------------------------------------------------------------------*/
	public function admin_settings_page() {
		global $wp_email_template_admin_init;
		
		$wp_email_template_admin_init->admin_settings_page( $this->page_data() );
	}
	
}

global $wp_email_template_send_wp_emails_page;
$wp_email_template_send_wp_emails_page = new WP_Email_Template_Send_WP_Emails_Page();

/** 
 * wp_email_template_send_wp_emails_page_show()
 * Define the callback function to show page content
 */
function wp_email_template_send_wp_emails_page_show() {
	global $wp_email_template_send_wp_emails_page;
	$wp_email_template_send_wp_emails_page->admin_settings_page();
}

?>