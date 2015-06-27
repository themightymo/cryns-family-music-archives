<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------
WP Email Template Style Settings

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

class WP_Email_Template_Style_Fonts_Settings extends WP_Email_Tempate_Admin_UI
{

	/**
	 * @var string
	 */
	private $parent_tab = 'style-fonts';

	/**
	 * @var array
	 */
	private $subtab_data;

	/**
	 * @var string
	 * You must change to correct option name that you are working
	 */
	public $option_name = 'wp_email_template_style_fonts';

	/**
	 * @var string
	 * You must change to correct form key that you are working
	 */
	public $form_key = 'wp_email_template_style_fonts';

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
				'success_message'	=> __( 'WP Email Template Style successfully saved.', 'wp_email_template' ),
				'error_message'		=> __( 'Error: WP Email Template Style  can not save.', 'wp_email_template' ),
				'reset_message'		=> __( 'WP Email Template Style  successfully reseted.', 'wp_email_template' ),
			);

		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );

		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_init' , array( $this, 'reset_default_settings' ) );

		add_action( $this->plugin_name . '_get_all_settings' , array( $this, 'get_settings' ) );

		add_action( $this->plugin_name . '-'. $this->form_key.'_settings_start', array( $this, 'pro_fields_before' ) );
		add_action( $this->plugin_name . '-'. $this->form_key.'_settings_end', array( $this, 'pro_fields_after' ) );
	}

	/*-----------------------------------------------------------------------------------*/
	/* subtab_init()
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
			'name'				=> 'style-fonts',
			'label'				=> __( 'Fonts', 'wp_email_template' ),
			'callback_function'	=> 'wp_email_template_style_fonts_settings_form',
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
            	'name' 		=> __( 'Email Fonts', 'wp_email_template' ),
				'desc'		=> __( "<strong>Important!</strong> The a3rev dynamic font editors give you the choice of 16 Default or Web safe fonts plus 364 Google fonts. The 16 Web safe fonts work in all email clients but be aware that Google fonts don't. Google fonts are fetched by &lt;link&gt; from Google. Gmail and Microsoft Outlook remove all &lt;link&gt; tags and hence default to one of the Web safe fonts. Interestingly iOS, Android Gmail and Windows mobile don't and Google fonts show beautifully. Go figure the weird and wonderful world of HTM email template design.", 'wp_email_template' ),
                'type' 		=> 'heading',
           	),

           	array(
            	'name' 		=> __( 'Title Fonts Style', 'wp_email_template' ),
				'desc'		=> __( 'The styles below will be applied to all Title fonts (H tag) in the email content.  Note some plugins that generate emails may have a H tag style that will override the template Title styles you set here.', 'wp_email_template' ),
                'type' 		=> 'heading',
           	),
			array(
				'name' 		=> __( 'H1 Font Style', 'wp_email_template' ),
				'id' 		=> 'h1_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '26px', 'face' => 'Century Gothic, sans-serif', 'style' => 'italic', 'color' => '#0A0A0A' )
			),
			array(
				'name' 		=> __( 'H2 Font Style', 'wp_email_template' ),
				'id' 		=> 'h2_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '20px', 'face' => 'Century Gothic, sans-serif', 'style' => 'italic', 'color' => '#0A0A0A' )
			),
			array(
				'name' 		=> __( 'H3 Font Style', 'wp_email_template' ),
				'id' 		=> 'h3_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '18px', 'face' => 'Century Gothic, sans-serif', 'style' => 'italic', 'color' => '#0A0A0A' )
			),
			array(
				'name' 		=> __( 'H4 Font Style', 'wp_email_template' ),
				'id' 		=> 'h4_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '16px', 'face' => 'Century Gothic, sans-serif', 'style' => 'italic', 'color' => '#0A0A0A' )
			),
			array(
				'name' 		=> __( 'H5 Font Style', 'wp_email_template' ),
				'id' 		=> 'h5_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '14px', 'face' => 'Century Gothic, sans-serif', 'style' => 'italic', 'color' => '#0A0A0A' )
			),
			array(
				'name' 		=> __( 'H6 Font Style', 'wp_email_template' ),
				'id' 		=> 'h6_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'face' => 'Century Gothic, sans-serif', 'style' => 'italic', 'color' => '#0A0A0A' )
			),

        ));
	}
}

global $wp_email_template_style_fonts_settings;
$wp_email_template_style_fonts_settings = new WP_Email_Template_Style_Fonts_Settings();

/**
 * wp_email_template_style_fonts_settings_form()
 * Define the callback function to show subtab content
 */
function wp_email_template_style_fonts_settings_form() {
	global $wp_email_template_style_fonts_settings;
	$wp_email_template_style_fonts_settings->settings_form();
}

?>