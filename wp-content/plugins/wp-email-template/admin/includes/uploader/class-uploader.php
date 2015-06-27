<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------
A3rev Plugin Uploader

TABLE OF CONTENTS

- var admin_uploader_url
- __construct()
- admin_uploader_url()
- uploader_js()
- uploader_style()
- uploader_init()
- get_silentpost()
- upload_input()
- change_button_text()
- modify_tabs()
- inside_popup()

-----------------------------------------------------------------------------------*/
class WP_Email_Template_Uploader extends WP_Email_Tempate_Admin_UI
{
	
	/**
	 * @var string
	 */
	private $admin_uploader_url;
	
	/**
	 * @var string
	 */
	private $custom_post_type_image = 'wp_email_images';
	
	/**
	 * @var string
	 */
	private $custom_post_type_name = 'Custom Image Type For Uploader';
	
	/*-----------------------------------------------------------------------------------*/
	/* Admin Uploader Constructor */
	/*-----------------------------------------------------------------------------------*/
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'init', array( $this, 'uploader_init' ) );
			add_action( 'admin_print_scripts', array( $this, 'inside_popup' ) );
			add_filter( 'gettext', array( $this, 'change_button_text' ), null, 3 );
			
			// include scripts to Admin UI Interface
			add_action( $this->plugin_name . '_init_scripts', array( $this, 'uploader_js' ) );
			
			// include styles to Admin UI Interface
			add_action( $this->plugin_name . '_init_styles', array( $this, 'uploader_style' ) );
		}
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* admin_uploader_url */
	/*-----------------------------------------------------------------------------------*/
	public function admin_uploader_url() {
		if ( $this->admin_uploader_url ) return $this->admin_uploader_url;
		return $this->admin_uploader_url = untrailingslashit( plugins_url( '/', __FILE__ ) );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* Include Uploader Script */
	/*-----------------------------------------------------------------------------------*/
	public function uploader_js () {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script( 'a3-uploader-script', $this->admin_uploader_url() . '/uploader-script.js' );
		wp_enqueue_script( 'media-upload' );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* Include Uploader Style */
	/*-----------------------------------------------------------------------------------*/
	public function uploader_style () {
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_style( 'a3-uploader-style', $this->admin_uploader_url() . '/uploader.css' );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* Uploader Init : Create Custom Post for Image */
	/*-----------------------------------------------------------------------------------*/
	public function uploader_init () {
		register_post_type( $this->custom_post_type_image, array(
			'labels' => array(
				'name' => $this->custom_post_type_name,
			),
			'public' => true,
			'show_ui' => false,
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => false,
			'supports' => array( 'title', 'editor' ),
			'query_var' => false,
			'can_export' => true,
			'show_in_nav_menus' => false
		) );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* Get Post Id of Custom Post for Image */
	/*-----------------------------------------------------------------------------------*/
	public function get_silentpost ( $option_key = '' ) {
		global $wpdb;
		$post_id = 1;
		if ( $option_key != '' ) {
			$args = array( 
				'post_parent' => '0', 
				'post_type' => $this->custom_post_type_image, 
				'post_name' => $option_key, 
				'post_status' => 'draft', 
				'comment_status' => 'closed', 
				'ping_status' => 'closed'
			);
			$my_posts = get_posts( $args );
			if ( $my_posts ) {
				foreach ($my_posts as $my_post) {
					$post_id = $my_post->ID;
					break;
				}
			} else {
				$args['post_title'] = str_replace('_', ' ', $option_key);
				$post_id = wp_insert_post( $args );
			}
		}
		return $post_id;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* Get Upload Input Field */
	/*-----------------------------------------------------------------------------------*/
	public function upload_input ( $name_attribute, $id_attribute = '', $value = '', $default_value = '', $field_name = '', $class = '', $css = '', $description = '', $post_id = 0 ) {
		$output = '';
		
		if ( $post_id == 0 ) {
			$post_id = $this->get_silentpost( $id_attribute );
		}
		
		if ( trim( $value ) == '' ) $value = trim( $default_value );
		
		$output .= '<input type="text" name="'.$name_attribute.'" id="'.$id_attribute.'" value="'.esc_attr( $value ).'" class="'.$id_attribute. ' ' .$class.' a3_upload" style="'.$css.'" rel="'.$field_name.'" /> ';
		$output .= '<input id="upload_'.$id_attribute.'" class="a3rev-ui-upload-button a3_upload_button button" type="button" value="'.__( 'Upload', 'wp_email_template' ).'" rel="'.$post_id.'" /> '.$description;
		
		$output .= '<div style="clear:both;"></div><div class="a3_screenshot" id="'.$id_attribute.'_image" style="'.( ( $value == '' ) ? 'display:none;' : 'display:block;' ).'">';

		if ( $value != '' ) {
			$remove = '<a href="javascript:(void);" class="a3_uploader_remove a3-plugin-ui-delete-icon">&nbsp;</a>';

			$image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico*)/i', $value );

			if ( $image ) {
				$output .= '<img class="a3_uploader_image" src="' . esc_url( $value ) . '" alt="" />'.$remove.'';
			} else {
				$parts = explode( "/", $value );

				for( $i = 0; $i < sizeof( $parts ); ++$i ) {
					$title = $parts[$i];
				}

				$output .= '';

				$title = __( 'View File', 'wp_email_template' );

				$output .= '<div class="a3_no_image"><span class="a3_file_link"><a href="'.esc_url( $value ).'" target="_blank" rel="a3_external">'.$title.'</a></span>'.$remove.'</div>';

			}
		}

		$output .= '</div>';

		return $output;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* Change the Button text on image popup */
	/*-----------------------------------------------------------------------------------*/
	public function change_button_text( $translation, $original, $domain ) {
	    if ( isset( $_REQUEST['type'] ) ) { return $translation; }
	    
	    if ( is_admin() && $original === 'Insert into Post' ) {
	    	$translation = __( 'Use this Image', 'wp_email_template' );
			if ( isset( $_REQUEST['title'] ) && $_REQUEST['title'] != '' ) { $translation =__( 'Use as', 'wp_email_template' ).' '.esc_attr( $_REQUEST['title'] ); }
	    }
	
	    return $translation;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* modify_tabs */
	/*-----------------------------------------------------------------------------------*/
	public function modify_tabs ( $tabs ) {
		if ( isset( $tabs['gallery'] ) ) { $tabs['gallery'] = str_replace( 'Gallery', __( 'Previously Uploaded', 'wp_email_template' ), $tabs['gallery'] ); }
		return $tabs;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* inside_popup */
	/*-----------------------------------------------------------------------------------*/
	public function inside_popup () {
		if ( isset( $_REQUEST['a3_uploader'] ) && $_REQUEST['a3_uploader'] == 'yes' ) {
			add_filter( 'media_upload_tabs', array( $this, 'modify_tabs' ) );
		}
	}
	
}

global $wp_email_template_uploader;
$wp_email_template_uploader = new WP_Email_Template_Uploader();

?>
