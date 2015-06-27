<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------
WP Email Teplate Send WP Emails Mandrill Provider Settings

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

class WP_ET_Mandrill_Provider_Settings extends WP_Email_Tempate_Admin_UI
{
	
	/**
	 * @var string
	 */
	private $parent_tab = 'mandrill';
	
	/**
	 * @var array
	 */
	private $subtab_data;
	
	/**
	 * @var string
	 * You must change to correct option name that you are working
	 */
	public $option_name = 'wp_et_mandrill_provider_configuration';
	
	/**
	 * @var string
	 * You must change to correct form key that you are working
	 */
	public $form_key = 'wp_et_mandrill_provider_configuration';
	
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
				'success_message'	=> __( 'Mandrill Configuration successfully saved.', 'wp_email_template' ),
				'error_message'		=> __( 'Error: Mandrill Configuration can not save.', 'wp_email_template' ),
				'reset_message'		=> __( 'Mandrill Configuration successfully reseted.', 'wp_email_template' ),
			);
		
		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_end', array( $this, 'include_script' ) );
			
		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );
		
		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_init' , array( $this, 'after_save_settings' ) );
						
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
	/* after_save_settings()
	/* Process when clean on deletion option is un selected */
	/*-----------------------------------------------------------------------------------*/
	public function after_save_settings() {
		if ( isset( $_POST['bt_save_settings'] ) ) {
			$settings_array = get_option( $this->option_name, array() );
			if ( isset( $settings_array['mandrill_connect_type'] ) ) {
				global $wp_et_send_wp_emails;
				if ( $settings_array['mandrill_connect_type'] == 'api' ) {
					// check api key
					$api_key_valid = $wp_et_send_wp_emails->check_mandrill_api_key( trim( $settings_array['api_key'] ) );
					if ( $api_key_valid ) {
						update_option( 'wp_et_mandrill_api_key_valid', 1 );
					} else {
						delete_option( 'wp_et_mandrill_api_key_valid');
					}
				} else {
					// check api key
					$api_key_valid = $wp_et_send_wp_emails->check_mandrill_api_key( trim( $settings_array['smtp_password'] ) );
					if ( $api_key_valid ) {
						update_option( 'wp_et_mandrill_api_key_valid', 1 );
					} else {
						delete_option( 'wp_et_mandrill_api_key_valid');
					}
				}
			}
		}
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
			'name'				=> 'mandrill',
			'label'				=> __( 'Mandrill', 'wp_email_template' ),
			'callback_function'	=> 'wp_et_mandrill_provider_settings_form',
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
		
		if ( get_option( 'wp_et_mandrill_api_key_valid' , 0 ) != 1 )
			echo $wp_email_template_admin_interface->get_error_message(  __( "Your API key is invalid", 'wp_email_template' ) );
		
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
            	'name' 		=> __( 'Madrill Credentials', 'wp_email_template' ),
				'desc'		=> sprintf( __( 'Send up to 12,000 emails a month for free <a href="%s" target="_blank">with Mandrill</a>. Register an account and generate the API Key or SMTP creds and enter those here.', 'wp_email_template' ), 'http://mandrill.com/' ),
                'type' 		=> 'heading',
           	),
			array(  
				'name' 		=> __( 'Connect Type', 'wp_email_template' ),
				'class'		=> 'mandrill_connect_type',
				'id' 		=> 'mandrill_connect_type',
				'type' 		=> 'switcher_checkbox',
				'default'	=> 'api',
				'checked_value'		=> 'api',
				'unchecked_value'	=> 'smtp',
				'checked_label'		=> __( 'API', 'wp_email_template' ),
				'unchecked_label' 	=> __( 'SMTP', 'wp_email_template' ),
			),
			
			array(
				'class'		=> 'mandrill_api_connect_container',
                'type' 		=> 'heading',
           	),
			array(  
				'name' 		=> '',
				'id' 		=> 'api_key',
				'type' 		=> 'text',
				'default'	=> '',
				'placeholder'	=> __( 'enter API key', 'wp_email_template' ),
			),
			array(  
				'name' 		=> __( 'Track Opens', 'wp_email_template' ),
				'id' 		=> 'enable_track_opens',
				'type' 		=> 'onoff_checkbox',
				'default' 	=> '1',
				'checked_value'		=> '1',
				'unchecked_value'	=> '0',
				'checked_label'		=> __( 'ON', 'wp_email_template' ),
				'unchecked_label' 	=> __( 'OFF', 'wp_email_template' ),
			),
			array(  
				'name' 		=> __( 'Track Clicks', 'wp_email_template' ),
				'id' 		=> 'enable_track_clicks',
				'type' 		=> 'onoff_checkbox',
				'default' 	=> '1',
				'checked_value'		=> '1',
				'unchecked_value'	=> '0',
				'checked_label'		=> __( 'ON', 'wp_email_template' ),
				'unchecked_label' 	=> __( 'OFF', 'wp_email_template' ),
			),
			
			array(
				'class'		=> 'mandrill_smtp_connect_container',
                'type' 		=> 'heading',
           	),
			array(  
				'name' 		=> __( 'Host', 'wp_email_template' ),
				'id' 		=> 'smtp_host',
				'type' 		=> 'text',
				'default'	=> 'smtp.mandrillapp.com',
				'placeholder'	=> 'smtp.mandrillapp.com',
			),
			array(  
				'name' 		=> __( 'Port', 'wp_email_template' ),
				'id' 		=> 'smtp_port',
				'style'		=> 'width:100px;',	
				'type' 		=> 'text',
				'default'	=> '587',
				'placeholder'	=> '587',
			),
			array(  
				'name' 		=> __( 'SMTP Username', 'wp_email_template' ),
				'id' 		=> 'smtp_username',
				'type' 		=> 'text',
				'default'	=> ''
			),
			array(  
				'name' 		=> __( 'SMTP Password', 'wp_email_template' ),
				'id' 		=> 'smtp_password',
				'type' 		=> 'password',
				'default'	=> '',
				'placeholder'	=> __( 'any valid API key', 'wp_email_template' ),
			),
        ));
	}
	
	public function include_script() {
	?>
<script>
(function($) {
$(document).ready(function() {
	if ( $("input.mandrill_connect_type:checked").val() == 'api') {
		$(".mandrill_api_connect_container").css( {'visibility': 'visible', 'height' : 'auto', 'overflow' : 'inherit'} );
		$(".mandrill_smtp_connect_container").css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden'} );
	} else {
		$(".mandrill_api_connect_container").css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden'} );
		$(".mandrill_smtp_connect_container").css( {'visibility': 'visible', 'height' : 'auto', 'overflow' : 'inherit'} );
	}
	
	$(document).on( "a3rev-ui-onoff_checkbox-switch", '.mandrill_connect_type', function( event, value, status ) {
		$(".mandrill_api_connect_container").hide().css( {'visibility': 'visible', 'height' : 'auto', 'overflow' : 'inherit'} );
		$(".mandrill_smtp_connect_container").hide().css( {'visibility': 'visible', 'height' : 'auto', 'overflow' : 'inherit'} );
		if ( status == 'true' ) {
			$(".mandrill_api_connect_container").slideDown();
			$(".mandrill_smtp_connect_container").slideUp();
		} else {
			$(".mandrill_api_connect_container").slideUp();
			$(".mandrill_smtp_connect_container").slideDown();
		}
	});
});
})(jQuery);
</script>
    <?php	
	}
}

global $wp_et_mandrill_provider_settings;
$wp_et_mandrill_provider_settings = new WP_ET_Mandrill_Provider_Settings();

/** 
 * wp_et_mandrill_provider_settings_form()
 * Define the callback function to show subtab content
 */
function wp_et_mandrill_provider_settings_form() {
	global $wp_et_mandrill_provider_settings;
	$wp_et_mandrill_provider_settings->settings_form();
}

?>