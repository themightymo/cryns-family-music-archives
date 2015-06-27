<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------
WP Email Teplate General Settings

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

class WP_Email_Template_Style_Header_Image_Settings extends WP_Email_Tempate_Admin_UI
{

	/**
	 * @var string
	 */
	private $parent_tab = 'style-header-image';

	/**
	 * @var array
	 */
	private $subtab_data;

	/**
	 * @var string
	 * You must change to correct option name that you are working
	 */
	public $option_name = 'wp_email_template_style_header_image';

	/**
	 * @var string
	 * You must change to correct form key that you are working
	 */
	public $form_key = 'wp_email_template_style_header_image';

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
				'success_message'	=> __( 'Settings successfully saved.', 'wp_email_template' ),
				'error_message'		=> __( 'Error: General Settings can not save.', 'wp_email_template' ),
				'reset_message'		=> __( 'Settings successfully reseted.', 'wp_email_template' ),
			);

		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );

		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_init' , array( $this, 'clean_on_deletion' ) );

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
	/* clean_on_deletion()
	/* Process when clean on deletion option is un selected */
	/*-----------------------------------------------------------------------------------*/
	public function clean_on_deletion() {
		if ( ( isset( $_POST['bt_save_settings'] ) || isset( $_POST['bt_reset_settings'] ) ) && get_option( 'wp_email_template_clean_on_deletion' ) == 0  )  {
			$uninstallable_plugins = (array) get_option('uninstall_plugins');
			unset($uninstallable_plugins[WP_EMAIL_TEMPLATE_NAME]);
			update_option('uninstall_plugins', $uninstallable_plugins);
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
			'name'				=> 'header-image',
			'label'				=> __( 'Header Image', 'wp_email_template' ),
			'callback_function'	=> 'wp_email_template_style_header_image_settings_form',
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
            	'name' 		=> __( 'Header Image Container', 'wp_email_template' ),
                'type' 		=> 'heading',
           	),
			array(
				'name' 		=> __( 'Header Image', 'wp_email_template' ),
				'desc' 		=> __( "Maximum image width supported is 600px", 'wp_email_template' ),
				'id' 		=> 'header_image',
				'type' 		=> 'upload',
			),
			array(
				'name' 		=> __( 'Header Image Alignment', 'wp_email_template' ),
				'id' 		=> 'header_image_alignment',
				'type' 		=> 'select',
				'default'	=> 'left',
				'options'	=> array(
					'none'		=> __( 'None', 'wp_email_template' ),
					'left'	=> __( 'Left', 'wp_email_template' ),
					'right'		=> __( 'Right', 'wp_email_template' ),
					'center'	=> __( 'Center', 'wp_email_template' ),
				),
				'css' 		=> 'width:160px;',
			),

			array(
            	'name' 		=> __( 'Header Image Container', 'wp_email_template' ),
                'type' 		=> 'heading',
           	),
			array(
				'name' 		=> __( 'Container Border Margin', 'wp_email_template' ),
				'desc'		=> __( 'Margin or space outside of the Image container border in px.', 'wp_email_template' ),
				'id' 		=> 'header_image_margin',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(
											'id' 		=> 'header_image_margin_top',
	 										'name' 		=> __( 'Top', 'wp_email_template' ),
	 										'css'		=> 'width:40px;',
	 										'default'	=> 0 ),
	 								array(  'id' 		=> 'header_image_margin_bottom',
	 										'name' 		=> __( 'Bottom', 'wp_email_template' ),
	 										'css'		=> 'width:40px;',
	 										'default'	=> 20 ),
									array(
											'id' 		=> 'header_image_margin_left',
	 										'name' 		=> __( 'Left', 'wp_email_template' ),
	 										'css'		=> 'width:40px;',
	 										'default'	=> 0 ),
									array(
											'id' 		=> 'header_image_margin_right',
	 										'name' 		=> __( 'Right', 'wp_email_template' ),
	 										'css'		=> 'width:40px;',
	 										'default'	=> 0 ),
	 							)
			),
			array(
				'name' 		=> __( 'Container Border Padding', 'wp_email_template' ),
				'desc'		=> __( 'Padding or space between the border and the image.', 'wp_email_template' ),
				'id' 		=> 'header_image_padding',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(
											'id' 		=> 'header_image_padding_top',
	 										'name' 		=> __( 'Top', 'wp_email_template' ),
	 										'css'		=> 'width:40px;',
	 										'default'	=> 0 ),
	 								array(  'id' 		=> 'header_image_padding_bottom',
	 										'name' 		=> __( 'Bottom', 'wp_email_template' ),
	 										'css'		=> 'width:40px;',
	 										'default'	=> 0 ),
									array(
											'id' 		=> 'header_image_padding_left',
	 										'name' 		=> __( 'Left', 'wp_email_template' ),
	 										'css'		=> 'width:40px;',
	 										'default'	=> 0 ),
									array(
											'id' 		=> 'header_image_padding_right',
	 										'name' 		=> __( 'Right', 'wp_email_template' ),
	 										'css'		=> 'width:40px;',
	 										'default'	=> 0 ),
	 							)
			),
			array(
				'name' 		=> __( 'Container Background Colour', 'wp_email_template' ),
				'desc' 		=> __( "Header background colour. Default", 'wp_email_template' ) . ' [default_value]',
				'id' 		=> 'header_image_background_color',
				'type' 		=> 'color',
				'default'	=> '#ffffff',
			),

			array(
            	'name' 		=> __( 'Image Container Borders', 'wp_email_template' ),
				'desc'		=> __( 'Please note all versions of Microsoft Outlook do not support borders. Most other Email Clients and all mobile email including Windows phone do.', 'wp_email_template' ) . '</a>',
                'type' 		=> 'heading',
           	),
			array(
				'name' 		=> __( 'Border Top', 'wp_email_template' ),
				'id' 		=> 'header_image_border_top',
				'type' 		=> 'border_styles',
				'default'	=> array( 'width' => '0px', 'style' => 'solid', 'color' => '#ffffff' )
			),

			array(
				'name' 		=> __( 'Border Bottom', 'wp_email_template' ),
				'id' 		=> 'header_image_border_bottom',
				'type' 		=> 'border_styles',
				'default'	=> array( 'width' => '0px', 'style' => 'solid', 'color' => '#ffffff' )
			),

			array(
				'name' 		=> __( 'Border Left', 'wp_email_template' ),
				'id' 		=> 'header_image_border_left',
				'type' 		=> 'border_styles',
				'default'	=> array( 'width' => '0px', 'style' => 'solid', 'color' => '#ffffff' )
			),

			array(
				'name' 		=> __( 'Border Right', 'wp_email_template' ),
				'id' 		=> 'header_image_border_right',
				'type' 		=> 'border_styles',
				'default'	=> array( 'width' => '0px', 'style' => 'solid', 'color' => '#ffffff' )
			),

			array(
				'name' 		=> __( 'Border Corners', 'wp_email_template' ),
				'id' 		=> 'header_image_border_corner',
				'type' 		=> 'border_corner',
				'default'	=> array( 'rounded_value' => 0 )
			),
        ));
	}
}

global $wp_email_template_style_header_image_settings;
$wp_email_template_style_header_image_settings = new WP_Email_Template_Style_Header_Image_Settings();

/**
 * wp_email_template_style_header_image_settings_form()
 * Define the callback function to show subtab content
 */
function wp_email_template_style_header_image_settings_form() {
	global $wp_email_template_style_header_image_settings;
	$wp_email_template_style_header_image_settings->settings_form();
}

?>