<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------
WP Email Teplate Send WP Emails via Gmail SMTP Provider Settings

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

class WP_ET_Gmail_SMTP_Provider_Settings extends WP_Email_Tempate_Admin_UI
{
	
	/**
	 * @var string
	 */
	private $parent_tab = 'gmail-smtp';
	
	/**
	 * @var array
	 */
	private $subtab_data;
	
	/**
	 * @var string
	 * You must change to correct option name that you are working
	 */
	public $option_name = 'wp_et_gmail_smtp_provider_configuration';
	
	/**
	 * @var string
	 * You must change to correct form key that you are working
	 */
	public $form_key = 'wp_et_gmail_smtp_provider_configuration';
	
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
				'success_message'	=> __( 'Gmail Configuration successfully saved.', 'wp_email_template' ),
				'error_message'		=> __( 'Error: Gmail Configuration can not save.', 'wp_email_template' ),
				'reset_message'		=> __( 'Gmail Configuration successfully reseted.', 'wp_email_template' ),
			);
					
		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );
								
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
			'name'				=> 'gmail-smtp',
			'label'				=> __( 'Gmail', 'wp_email_template' ),
			'callback_function'	=> 'wp_et_gmail_smtp_provider_settings_form',
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
		
  		// Define settings			
     	$this->form_fields = apply_filters( $this->option_name . '_settings_fields', array(
		
			array(
            	'name' 		=> __( 'Gmail SMTP Credentials', 'wp_email_template' ),
				'desc'		=> sprintf( __( 'Due to the 500 email a day sending limit recommend that you open a dedicated Gmail account for this purpose. As an extra security measure we also recommend that you set up a <a href="%s" target="_blank">Google Application Specific Password</a> and use it instead of your Gmail account password.', 'wp_email_template' ), 'https://accounts.google.com/b/0/IssuedAuthSubTokens?hide_authsub=1' ),
                'type' 		=> 'heading',
           	),
			array(  
				'name' 		=> __( 'Gmail Username', 'wp_email_template' ),
				'id' 		=> 'smtp_username',
				'type' 		=> 'text',
				'default'	=> ''
			),
			array(  
				'name' 		=> __( 'Gmail Password', 'wp_email_template' ),
				'id' 		=> 'smtp_password',
				'type' 		=> 'password',
				'default'	=> '',
			),
        ));
	}
	
}

global $wp_et_gmail_smtp_provider_settings;
$wp_et_gmail_smtp_provider_settings = new WP_ET_Gmail_SMTP_Provider_Settings();

/** 
 * wp_et_gmail_smtp_provider_settings_form()
 * Define the callback function to show subtab content
 */
function wp_et_gmail_smtp_provider_settings_form() {
	global $wp_et_gmail_smtp_provider_settings;
	$wp_et_gmail_smtp_provider_settings->settings_form();
}

?>