<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------
WP Email Template Admin Page

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

class WP_Email_Template_Admin_Page extends WP_Email_Tempate_Admin_UI
{	
	/**
	 * @var string
	 */
	private $menu_slug = 'wp_email_template';
	
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
			array(
				'type'				=> 'menu',
				'page_title'		=> __( 'WP Email', 'wp_email_template' ),
				'menu_title'		=> __( 'WP Email', 'wp_email_template' ),
				'capability'		=> 'manage_options',
				'menu_slug'			=> $this->menu_slug,
				'function'			=> 'wp_email_template_admin_page_show',
				'icon_url'			=> '',
				'position'			=> '50.2456',
				'admin_url'			=> 'admin.php',
				'callback_function' => '',
				'script_function' 	=> '',
				'view_doc'			=> '',
			),
			array(
				'type'				=> 'submenu',
				'parent_slug'		=> $this->menu_slug,
				'page_title'		=> __( 'Template', 'wp_email_template' ),
				'menu_title'		=> __( 'Template', 'wp_email_template' ),
				'capability'		=> 'manage_options',
				'menu_slug'			=> $this->menu_slug,
				'function'			=> 'wp_email_template_admin_page_show',
				'admin_url'			=> 'admin.php',
				'callback_function' => '',
				'script_function' 	=> '',
				'view_doc'			=> '',
			)
		);
		
		if ( $this->page_data ) return $this->page_data;
		return $this->page_data = $page_data;
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* add_admin_menu() */
	/* Add This page to menu on left sidebar */
	/*-----------------------------------------------------------------------------------*/
	public function add_admin_menu( $admin_menu ) {
		
		//if ( ! is_array( $admin_menu ) ) $admin_menu = array();
		//$admin_menu[] = $this->page_data();
		if ( ! is_array( $admin_menu ) ) $admin_menu = array();
		$admin_menu = array_merge( $this->page_data(), $admin_menu );
		
		return $admin_menu;
	}
	
	// fix conflict with mandrill plugin
	public function remove_mandrill_notice() {
		remove_action( 'admin_notices', array( 'wpMandrill', 'adminNotices' ) );
		global $wp_et_send_wp_emails;
		remove_action( 'admin_notices', array( $wp_et_send_wp_emails, 'wp_mail_declared' ) );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* tabs_include() */
	/* Include all tabs into this page
	/*-----------------------------------------------------------------------------------*/
	public function tabs_include() {
		
		if ( is_admin() && in_array (basename($_SERVER['PHP_SELF']), array('admin.php') ) && isset( $_GET['page'] ) && $_GET['page'] == 'wp_email_template' ) {
			require_once( ABSPATH . 'wp-includes/pluggable.php' );
			add_action('init' , array( $this, 'remove_mandrill_notice' ) );
		}
		
		include_once( $this->admin_plugin_dir() . '/tabs/admin-general-tab.php' );
		include_once( $this->admin_plugin_dir() . '/tabs/admin-style-header-image-tab.php' );
		include_once( $this->admin_plugin_dir() . '/tabs/admin-style-header-tab.php' );
		include_once( $this->admin_plugin_dir() . '/tabs/admin-style-body-tab.php' );
		include_once( $this->admin_plugin_dir() . '/tabs/admin-style-footer-tab.php' );
		include_once( $this->admin_plugin_dir() . '/tabs/admin-style-fonts-tab.php' );
		include_once( $this->admin_plugin_dir() . '/tabs/admin-social-media-tab.php' );
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* admin_settings_page() */
	/* Show Settings Page */
	/*-----------------------------------------------------------------------------------*/
	public function admin_settings_page() {
		global $wp_email_template_admin_init;
		$my_page_data = $this->page_data();
		$my_page_data = array_values( $my_page_data );
		
		$wp_email_template_admin_init->admin_settings_page( $my_page_data[1] );
	}
	
}

global $wp_email_template_admin_page;
$wp_email_template_admin_page = new WP_Email_Template_Admin_Page();

/** 
 * wp_email_template_admin_page_show()
 * Define the callback function to show page content
 */
function wp_email_template_admin_page_show() {
	global $wp_email_template_admin_page;
	$wp_email_template_admin_page->admin_settings_page();
}

?>
