<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------
WP Email Template Social Media Settings

TABLE OF CONTENTS

- var parent_tab
- var subtab_data
- var option_name
- var form_key
- var position
- var form_fields
- var form_messages

- __construct()
- subtab_init()
- set_default_settings()
- get_settings()
- subtab_data()
- add_subtab()
- settings_form()
- init_form_fields()

-----------------------------------------------------------------------------------*/

class WP_Email_Template_Social_Media_Settings extends WP_Email_Tempate_Admin_UI
{
	
	/**
	 * @var string
	 */
	private $parent_tab = 'social-media';
	
	/**
	 * @var array
	 */
	private $subtab_data;
	
	/**
	 * @var string
	 * You must change to correct option name that you are working
	 */
	public $option_name = 'wp_email_template_social_media';
	
	/**
	 * @var string
	 * You must change to correct form key that you are working
	 */
	public $form_key = 'wp_email_template_social_media';
	
	/**
	 * @var string
	 * You can change the order show of this sub tab in list sub tabs
	 */
	private $position = 1;
	
	/**
	 * @var array
	 */
	public $form_fields = array();
	
	/**
	 * @var array
	 */
	public $form_messages = array();
	
	/*-----------------------------------------------------------------------------------*/
	/* __construct() */
	/* Settings Constructor */
	/*-----------------------------------------------------------------------------------*/
	public function __construct() {
		$this->init_form_fields();
		//$this->subtab_init();
		
		$this->form_messages = array(
				'success_message'	=> __( 'Social Media Settings successfully saved.', 'wp_email_template' ),
				'error_message'		=> __( 'Error: Social Media Settings can not save.', 'wp_email_template' ),
				'reset_message'		=> __( 'Social Media Settings successfully reseted.', 'wp_email_template' ),
			);
		
		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_end', array( $this, 'include_script' ) );
			
		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );
		
		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_init' , array( $this, 'reset_default_settings' ) );
		
		add_action( $this->plugin_name . '_get_all_settings' , array( $this, 'get_settings' ) );
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* subtab_init() */
	/* Sub Tab Init */
	/*-----------------------------------------------------------------------------------*/
	public function subtab_init() {
		
		add_filter( $this->plugin_name . '-' . $this->parent_tab . '_settings_subtabs_array', array( $this, 'add_subtab' ), $this->position );
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* set_default_settings()
	/* Set default settings with function called from Admin Interface */
	/*-----------------------------------------------------------------------------------*/
	public function set_default_settings() {
		global $wp_email_template_admin_interface;
		
		$wp_email_template_admin_interface->reset_settings( $this->form_fields, $this->option_name, false );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* reset_default_settings()
	/* Reset default settings with function called from Admin Interface */
	/*-----------------------------------------------------------------------------------*/
	public function reset_default_settings() {
		global $wp_email_template_admin_interface;
		
		$wp_email_template_admin_interface->reset_settings( $this->form_fields, $this->option_name, true, true );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* get_settings()
	/* Get settings with function called from Admin Interface */
	/*-----------------------------------------------------------------------------------*/
	public function get_settings() {
		global $wp_email_template_admin_interface;
		
		$wp_email_template_admin_interface->get_settings( $this->form_fields, $this->option_name );
	}
	
	/**
	 * subtab_data()
	 * Get SubTab Data
	 * =============================================
	 * array ( 
	 *		'name'				=> 'my_subtab_name'				: (required) Enter your subtab name that you want to set for this subtab
	 *		'label'				=> 'My SubTab Name'				: (required) Enter the subtab label
	 * 		'callback_function'	=> 'my_callback_function'		: (required) The callback function is called to show content of this subtab
	 * )
	 *
	 */
	public function subtab_data() {
		
		$subtab_data = array( 
			'name'				=> 'social-media',
			'label'				=> __( 'Social Media', 'wp_email_template' ),
			'callback_function'	=> 'wp_email_template_social_media_settings_form',
		);
		
		if ( $this->subtab_data ) return $this->subtab_data;
		return $this->subtab_data = $subtab_data;
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* add_subtab() */
	/* Add Subtab to Admin Init
	/*-----------------------------------------------------------------------------------*/
	public function add_subtab( $subtabs_array ) {
	
		if ( ! is_array( $subtabs_array ) ) $subtabs_array = array();
		$subtabs_array[] = $this->subtab_data();
		
		return $subtabs_array;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* settings_form() */
	/* Call the form from Admin Interface
	/*-----------------------------------------------------------------------------------*/
	public function settings_form() {
		global $wp_email_template_admin_interface;
		
		$output = '';
		$output .= $wp_email_template_admin_interface->admin_forms( $this->form_fields, $this->form_key, $this->option_name, $this->form_messages );
		
		return $output;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* init_form_fields() */
	/* Init all fields of this form */
	/*-----------------------------------------------------------------------------------*/
	public function init_form_fields() {
		$preview_wp_email_template = '';
		if ( is_admin() && in_array (basename($_SERVER['PHP_SELF']), array('admin.php') ) && isset( $_GET['page'] ) && $_GET['page'] == 'wp_email_template' ) {
			$preview_wp_email_template = wp_create_nonce("preview_wp_email_template");
		}
		
  		// Define settings			
     	$this->form_fields = apply_filters( $this->option_name . '_settings_fields', array(
		
			array(
            	'name' 		=> __( 'Live Preview', 'wp_email_template' ),
				'desc'		=> __( 'For a live preview of changes save them and then', 'wp_email_template' ) . ' <a href="' . admin_url( 'admin-ajax.php', 'relative' ) . '?action=preview_wp_email_template&security='.$preview_wp_email_template.'" target="_blank">' . __( 'Click here to preview your email template.', 'wp_email_template' ) . '</a>',
                'type' 		=> 'heading',
           	),
			
			array(
            	'name' 		=> __( 'Follow Us On', 'wp_email_template' ),
                'type' 		=> 'heading',
           	),
			array(  
				'name' 		=> __( 'Facebook URI', 'wp_email_template' ),
				'desc'		=> __( 'Enter your Facebook URL to show that linked icon in footer.', 'wp_email_template' ),
				'id' 		=> 'email_facebook',
				'type' 		=> 'text',
				'default'	=> '',
				'free_version'		=> true,
			),
			array(  
				'name' 		=> __( 'Twitter URI', 'wp_email_template' ),
				'desc'		=> __( 'Enter your Twitter URL to show that linked icon in footer.', 'wp_email_template' ),
				'id' 		=> 'email_twitter',
				'type' 		=> 'text',
				'default'	=> '',
				'free_version'		=> true,
			),
			array(  
				'name' 		=> __( 'LinkedIn URI', 'wp_email_template' ),
				'desc'		=> __( 'Enter your Linkedin URL to show that linked icon in footer.', 'wp_email_template' ),
				'id' 		=> 'email_linkedIn',
				'type' 		=> 'text',
				'default'	=> '',
				'free_version'		=> true,
			),
			array(  
				'name' 		=> __( 'Pinterest URI', 'wp_email_template' ),
				'desc'		=> __( 'Enter your Pinterest URL to show that linked icon in footer.', 'wp_email_template' ),
				'id' 		=> 'email_pinterest',
				'type' 		=> 'text',
				'default'	=> '',
				'free_version'		=> true,
			),
			array(  
				'name' 		=> __( 'Google+1 URI', 'wp_email_template' ),
				'desc'		=> __( 'Enter your Google+1 URL to show that linked icon in footer.', 'wp_email_template' ),
				'id' 		=> 'email_googleplus',
				'type' 		=> 'text',
				'default'	=> '',
				'free_version'		=> true,
			),
			
			array(
            	'name' 		=> __( 'Social Media Icons', 'wp_email_template' ),
				'class'		=> 'pro_feature_fields',
                'type' 		=> 'heading',
           	),
			array(  
				'name' 		=> __( 'Facebook Icon', 'wp_email_template' ),
				'id' 		=> 'facebook_icon',
				'type' 		=> 'upload',
				'default'	=> WP_EMAIL_TEMPLATE_IMAGES_URL.'/icon_facebook.png',
			),
			array(  
				'name' 		=> __( 'Twitter Icon', 'wp_email_template' ),
				'id' 		=> 'twitter_icon',
				'type' 		=> 'upload',
				'default'	=> WP_EMAIL_TEMPLATE_IMAGES_URL.'/icon_twitter.png',
			),
			array(  
				'name' 		=> __( 'LinkedIn Icon', 'wp_email_template' ),
				'id' 		=> 'linkedIn_icon',
				'type' 		=> 'upload',
				'default'	=> WP_EMAIL_TEMPLATE_IMAGES_URL.'/icon_linkedin.png',
			),
			array(  
				'name' 		=> __( 'Pinterest Icon', 'wp_email_template' ),
				'id' 		=> 'pinterest_icon',
				'type' 		=> 'upload',
				'default'	=> WP_EMAIL_TEMPLATE_IMAGES_URL.'/icon_pinterest.png',
			),
			array(  
				'name' 		=> __( 'Google+1 Icon', 'wp_email_template' ),
				'id' 		=> 'googleplus_icon',
				'type' 		=> 'upload',
				'default'	=> WP_EMAIL_TEMPLATE_IMAGES_URL.'/icon_googleplus.png',
			),
			
        ));
	}
	
	public function include_script() {
	?>
<style>
#a3_plugin_panel_extensions {
	position:absolute;
	bottom:50px;	
}
</style>
	<?php
	}
}

global $wp_email_template_social_media_settings;
$wp_email_template_social_media_settings = new WP_Email_Template_Social_Media_Settings();

/** 
 * wp_email_template_social_media_settings_form()
 * Define the callback function to show subtab content
 */
function wp_email_template_social_media_settings_form() {
	global $wp_email_template_social_media_settings;
	$wp_email_template_social_media_settings->settings_form();
}

?>
