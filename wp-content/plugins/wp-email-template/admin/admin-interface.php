<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------
A3rev Plugin Admin Interface

TABLE OF CONTENTS

- __construct()
- get_success_message()
- get_error_message()
- get_reset_message()
- admin_includes()
- get_font_weights()
- get_border_styles()
- admin_script_load()
- admin_css_load()
- get_settings_default()
- get_settings()
- save_settings()
- reset_settings()
- settings_get_option()
- admin_forms()
- admin_stripslashes()
- generate_border_css()
- generate_border_style_css()
- generate_border_corner_css()
- generate_shadow_css()

-----------------------------------------------------------------------------------*/

class WP_Email_Template_Admin_Interface extends WP_Email_Tempate_Admin_UI
{
	
	/*-----------------------------------------------------------------------------------*/
	/* Admin Interface Constructor */
	/*-----------------------------------------------------------------------------------*/
	public function __construct() {
		
		$this->admin_includes();
		
		add_action( 'init', array( $this, 'init_scripts' ) );
		add_action( 'init', array( $this, 'init_styles' ) );
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* Init scripts */
	/*-----------------------------------------------------------------------------------*/
	public function init_scripts() {
		$admin_pages = $this->admin_pages();
		
		if ( is_admin() && isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], $admin_pages ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_script_load' ) );
			do_action( $this->plugin_name . '_init_scripts' );
		}
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* Init styles */
	/*-----------------------------------------------------------------------------------*/
	public function init_styles() {
		$admin_pages = $this->admin_pages();
		
		if ( is_admin() && isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], $admin_pages ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_css_load' ) );
			do_action( $this->plugin_name . '_init_styles' );
		}
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* admin_script_load */
	/*-----------------------------------------------------------------------------------*/

	public function admin_script_load() {
		
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		
		wp_register_script( 'chosen', $this->admin_plugin_url() . '/assets/js/chosen/chosen.jquery' . $suffix . '.js', array( 'jquery' ), true, false );
		wp_register_script( 'a3rev-chosen-new', $this->admin_plugin_url() . '/assets/js/chosen/chosen.jquery' . $suffix . '.js', array( 'jquery' ), true, false );
		wp_register_script( 'a3rev-style-checkboxes', $this->admin_plugin_url() . '/assets/js/iphone-style-checkboxes.js', array('jquery'), true, false );
		
		wp_register_script( 'a3rev-admin-ui-script', $this->admin_plugin_url() . '/assets/js/admin-ui-script.js', array('jquery'), true, true );
		wp_register_script( 'a3rev-typography-preview', $this->admin_plugin_url() . '/assets/js/a3rev-typography-preview.js',  array('jquery'), false, true );
		wp_register_script( 'a3rev-settings-preview', $this->admin_plugin_url() . '/assets/js/a3rev-settings-preview.js',  array('jquery'), false, true );
		wp_register_script( 'jquery-tiptip', $this->admin_plugin_url() . '/assets/js/tipTip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), true, true );
		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'chosen' );
		wp_enqueue_script( 'a3rev-chosen-new' );
		wp_enqueue_script( 'a3rev-style-checkboxes' );
		wp_enqueue_script( 'a3rev-admin-ui-script' );
		wp_enqueue_script( 'a3rev-typography-preview' );
		wp_enqueue_script( 'a3rev-settings-preview' );
		wp_enqueue_script( 'jquery-tiptip' );

	} // End admin_script_load()
	
	
	/*-----------------------------------------------------------------------------------*/
	/* admin_css_load */
	/*-----------------------------------------------------------------------------------*/

	public function admin_css_load () {
		global $wp_version;
		
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		
		wp_enqueue_style( 'a3rev-admin-ui-style', $this->admin_plugin_url() . '/assets/css/admin-ui-style' . $suffix . '.css' );
		
		if ( version_compare( $wp_version, '3.8', '>=' ) ) {
			wp_enqueue_style( 'a3rev-admin-flat-ui-style', $this->admin_plugin_url() . '/assets/css/admin-flat-ui-style' . $suffix . '.css' );
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'a3rev-chosen-new-style', $this->admin_plugin_url() . '/assets/js/chosen/chosen' . $suffix . '.css' );
		wp_enqueue_style( 'a3rev-tiptip-style', $this->admin_plugin_url() . '/assets/js/tipTip/tipTip.css' );
		
	} // End admin_css_load()
	
	/*-----------------------------------------------------------------------------------*/
	/* get_success_message */
	/*-----------------------------------------------------------------------------------*/
	public function get_success_message( $message = '' ) {
		if ( trim( $message ) == '' ) $message = __( 'Settings successfully saved.' , 'wp_email_template' ); 
		return '<div class="updated" id=""><p>' . $message . '</p></div>';
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* get_error_message */
	/*-----------------------------------------------------------------------------------*/
	public function get_error_message( $message = '' ) {
		if ( trim( $message ) == '' ) $message = __( 'Error: Settings can not save.' , 'wp_email_template' ); 
		return '<div class="error" id=""><p>' . $message . '</p></div>';
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* get_reset_message */
	/*-----------------------------------------------------------------------------------*/
	public function get_reset_message( $message = '' ) {
		if ( trim( $message ) == '' ) $message = __( 'Settings successfully reseted.' , 'wp_email_template' ); 
		return '<div class="updated" id=""><p>' . $message . '</p></div>';
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* admin_includes */
	/* Include required core files used in admin UI.
	/*-----------------------------------------------------------------------------------*/
	public function admin_includes() {
		// Includes Font Face Lib
		include_once( 'includes/fonts_face.php' );
		
		// Includes Uploader Lib
		include_once( 'includes/uploader/class-uploader.php' );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* Get Font Weights */
	/*-----------------------------------------------------------------------------------*/
	public function get_font_weights() {
		$font_weights = array (
			'300'				=> __( 'Thin', 'wp_email_template' ),
			'300 italic'		=> __( 'Thin/Italic', 'wp_email_template' ),
			'normal'			=> __( 'Normal', 'wp_email_template' ),
			'italic'			=> __( 'Italic', 'wp_email_template' ),
			'bold'				=> __( 'Bold', 'wp_email_template' ),
			'bold italic'		=> __( 'Bold/Italic', 'wp_email_template' ),
		);
		return apply_filters( $this->plugin_name . '_font_weights', $font_weights );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* Get Border Styles */
	/*-----------------------------------------------------------------------------------*/
	public function get_border_styles() {
		$border_styles = array (
			'solid'				=> __( 'Solid', 'wp_email_template' ),
			'double'			=> __( 'Double', 'wp_email_template' ),
			'dashed'			=> __( 'Dashed', 'wp_email_template' ),
			'dotted'			=> __( 'Dotted', 'wp_email_template' ),
			'groove'			=> __( 'Groove', 'wp_email_template' ),
			'ridge'				=> __( 'Ridge', 'wp_email_template' ),
			'inset'				=> __( 'Inset', 'wp_email_template' ),
			'outset'			=> __( 'Outset', 'wp_email_template' ),
		);
		return apply_filters( $this->plugin_name . '_border_styles', $border_styles );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* Get Settings Default Function - get_settings_default */
	/* Just called for when option values is an array and it's in single option name for all settings
	/*-----------------------------------------------------------------------------------*/

	public function get_settings_default( $options, $option_name = '' ) {
		
		$default_settings = array();
		
		if ( !is_array( $options ) || count( $options ) < 1 ) return $default_settings;
		
		foreach ( $options as $value ) {
			if ( ! isset( $value['type'] ) ) continue;
			if ( in_array( $value['type'], array( 'heading' ) ) ) continue;
			if ( ! isset( $value['id'] ) || trim( $value['id'] ) == '' ) continue;
			if ( ! isset( $value['default'] ) ) $value['default'] = '';
			
			switch ( $value['type'] ) {
				
				// Array textfields
				case 'array_textfields' :
					if ( !isset( $value['ids'] ) || !is_array( $value['ids'] ) || count( $value['ids'] ) < 1 ) break;
					
					foreach ( $value['ids'] as $text_field ) {
						if ( ! isset( $text_field['id'] ) || trim( $text_field['id'] ) == '' ) continue;
						if ( ! isset( $text_field['default'] ) ) $text_field['default'] = '';
						
						// Do not include when it's separate option
						if ( isset( $text_field['separate_option'] ) && $text_field['separate_option'] != false ) continue;
						
						// Remove [, ] characters from id argument
						if ( strstr( $text_field['id'], '[' ) ) {
							parse_str( esc_attr( $text_field['id'] ), $option_array );
				
							// Option name is first key
							$option_keys = array_keys( $option_array );
							$first_key = current( $option_keys );
								
							$id_attribute		= $first_key;
						} else {
							$id_attribute		= esc_attr( $text_field['id'] );
						}
						
						$default_settings[$id_attribute] = $text_field['default'];
					}
					
				break;
				
				default :
					// Do not include when it's separate option
					if ( isset( $value['separate_option'] ) && $value['separate_option'] != false ) continue;
					
					// Remove [, ] characters from id argument
					if ( strstr( $value['id'], '[' ) ) {
						parse_str( esc_attr( $value['id'] ), $option_array );
			
						// Option name is first key
						$option_keys = array_keys( $option_array );
						$first_key = current( $option_keys );
							
						$id_attribute		= $first_key;
					} else {
						$id_attribute		= esc_attr( $value['id'] );
					}
					
					$default_settings[$id_attribute] = $value['default'];
				
				break;
			}
		}
		
		if ( trim( $option_name ) != '' ) $default_settings = apply_filters( $this->plugin_name . '_' . $option_name . '_default_settings' , $default_settings );
		if ( ! is_array( $default_settings ) ) $default_settings = array();
		
		return $default_settings;
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* Get Settings Function - get_settings */
	/*-----------------------------------------------------------------------------------*/

	public function get_settings( $options, $option_name = '' ) {
				
		if ( !is_array( $options ) || count( $options ) < 1 ) return;
		
		$new_settings = array(); $new_single_setting = ''; // :)
		
		// Get settings for option values is an array and it's in single option name for all settings
		if ( trim( $option_name ) != '' ) {
			global $$option_name;
			
			$default_settings = $this->get_settings_default( $options, $option_name );
			
			$current_settings = get_option( $option_name );
			if ( ! is_array( $current_settings ) ) $current_settings = array();
			$current_settings = array_merge( $default_settings, $current_settings );
			
			$current_settings = array_map( array( $this, 'admin_stripslashes' ), $current_settings );
			$current_settings = apply_filters( $this->plugin_name . '_' . $option_name . '_get_settings' , $current_settings );
			
			$$option_name = $current_settings;
			
		}
		
		// Get settings for option value is stored as a record or it's spearate option
		foreach ( $options as $value ) {
			if ( ! isset( $value['type'] ) ) continue;
			if ( in_array( $value['type'], array( 'heading' ) ) ) continue;
			if ( ! isset( $value['id'] ) || trim( $value['id'] ) == '' ) continue;
			if ( ! isset( $value['default'] ) ) $value['default'] = '';
			
			// For way it has an option name
			if ( ! isset( $value['separate_option'] ) ) $value['separate_option'] = false;
			
			// Remove [, ] characters from id argument
			if ( strstr( $value['id'], '[' ) ) {
				parse_str( esc_attr( $value['id'] ), $option_array );
	
				// Option name is first key
				$option_keys = array_keys( $option_array );
				$first_key = current( $option_keys );
					
				$id_attribute		= $first_key;
			} else {
				$id_attribute		= esc_attr( $value['id'] );
			}
			
			if ( trim( $option_name ) == '' || $value['separate_option'] != false ) {
				global $$id_attribute;
				
				$current_setting = get_option( $id_attribute, $value['default'] );
				
				switch ( $value['type'] ) {
				
					// Array textfields
					case 'wp_editor' :
						if ( is_array( $current_setting ) )
							$current_setting = array_map( array( $this, 'stripslashes' ), $current_setting );
						elseif ( ! is_null( $current_setting ) )
							$current_setting = stripslashes( $current_setting );
					break;
					
					default:
				
						if ( is_array( $current_setting ) )
							$current_setting = array_map( array( $this, 'admin_stripslashes' ), $current_setting );
						elseif ( ! is_null( $current_setting ) )
							$current_setting = esc_attr( stripslashes( $current_setting ) );
					break;
				}
				
				$current_setting = apply_filters( $this->plugin_name . '_' . $id_attribute . '_get_setting' , $current_setting );
				
				$$id_attribute = $current_setting;
			}
		}
		
		// :)
		if ( ! isset( $this->is_free_plugin ) || ! $this->is_free_plugin ) {
			$fs = array( 0 => 'c', 1 => 'p', 2 => 'h', 3 => 'i', 4 => 'e', 5 => 'n', 6 => 'k', 7 => '_' );
			$cs = array( 0 => 'U', 1 => 'g', 2 => 'p', 3 => 'r', 4 => 'd', 5 => 'a', 6 => 'e', 7 => '_' );
			$check_settings_save = true;
			if ( isset( $this->class_name ) && ! class_exists( $this->class_name . $cs[7] . $cs[0] . $cs[2] . $cs[1] . $cs[3] . $cs[5] . $cs[4] . $cs[6] ) ) {
				$check_settings_save = false;
			}
			if ( ! function_exists( $this->plugin_name . $fs[7] . $fs[0] . $fs[2] . $fs[4] . $fs[0] . $fs[6] . $fs[7] . $fs[1] . $fs[3] . $fs[5] ) ) {
				$check_settings_save = false;
			}
			if ( ! $check_settings_save ) {

				if ( trim( $option_name ) != '' ) {
					update_option( $option_name, $new_settings );
					$$option_name = $new_settings;
				}
				
				foreach ( $options as $value ) {
					if ( ! isset( $value['type'] ) ) continue;
					if ( in_array( $value['type'], array( 'heading' ) ) ) continue;
					if ( ! isset( $value['id'] ) || trim( $value['id'] ) == '' ) continue;
					if ( ! isset( $value['default'] ) ) $value['default'] = '';
					if ( ! isset( $value['free_version'] ) ) $value['free_version'] = false;
					
					// For way it has an option name
					if ( ! isset( $value['separate_option'] ) ) $value['separate_option'] = false;
					
					// Remove [, ] characters from id argument
					if ( strstr( $value['id'], '[' ) ) {
						parse_str( esc_attr( $value['id'] ), $option_array );
			
						// Option name is first key
						$option_keys = array_keys( $option_array );
						$first_key = current( $option_keys );
							
						$id_attribute		= $first_key;
					} else {
						$id_attribute		= esc_attr( $value['id'] );
					}
					
					if ( trim( $option_name ) == '' || $value['separate_option'] != false ) {
						update_option( $id_attribute,  $new_single_setting );
						$$id_attribute = $new_single_setting;
					}
				}
			}
		}
				
		return true;
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* Save Settings Function - save_settings */
	/*-----------------------------------------------------------------------------------*/

	public function save_settings( $options, $option_name = '' ) {
				
		if ( !is_array( $options ) || count( $options ) < 1 ) return;
		
		if ( empty( $_POST ) ) return false;
		
		$update_options = array();
		$update_separate_options = array();
		//var_dump($_POST);
		
		// Get settings for option value is stored as a record or it's spearate option
		foreach ( $options as $value ) {
			if ( ! isset( $value['type'] ) ) continue;
			if ( in_array( $value['type'], array( 'heading' ) ) ) continue;
			if ( ! isset( $value['id'] ) || trim( $value['id'] ) == '' ) continue;
			if ( ! isset( $value['default'] ) ) $value['default'] = '';
			
			// For way it has an option name
			if ( ! isset( $value['separate_option'] ) ) $value['separate_option'] = false;
			
			// Remove [, ] characters from id argument
			$key = false;
			if ( strstr( $value['id'], '[' ) ) {
				parse_str( esc_attr( $value['id'] ), $option_array );
	
				// Option name is first key
				$option_keys = array_keys( $option_array );
				$first_key = current( $option_keys );
					
				$id_attribute		= $first_key;
				
				$key = key( $option_array[ $id_attribute ] );
			} else {
				$id_attribute		= esc_attr( $value['id'] );
			}
			
			// Get the option name
			$option_value = null;
			
			switch ( $value['type'] ) {
	
				// Checkbox type
				case 'checkbox' :
				case 'onoff_checkbox' :
				case 'switcher_checkbox' :
				
					if ( ! isset( $value['checked_value'] ) ) $value['checked_value'] = 1;
					if ( ! isset( $value['unchecked_value'] ) ) $value['unchecked_value'] = 0;
					
					if ( trim( $option_name ) == '' || $value['separate_option'] != false ) {
						if ( $key != false ) {
							if ( isset( $_POST[ $id_attribute ][ $key ] ) ) {
								$option_value = $value['checked_value'];
							} else {
								$option_value = $value['unchecked_value'];
							}	
						} else {
							if ( isset( $_POST[ $id_attribute ] ) ) {
								$option_value = $value['checked_value'];
							} else {
								$option_value = $value['unchecked_value'];
							}
						}
							
					} else {
						if ( $key != false ) {
							if ( isset( $_POST[ $option_name ][ $id_attribute ][ $key ] ) ) {
								$option_value = $value['checked_value'];
							} else {
								$option_value = $value['unchecked_value'];
							}	
						} else {
							if ( isset( $_POST[ $option_name ][ $id_attribute ] ) ) {
								$option_value = $value['checked_value'];
							} else {
								$option_value = $value['unchecked_value'];
							}
						}
					}
	
				break;
				
				// Array textfields
				case 'array_textfields' :
					if ( !isset( $value['ids'] ) || !is_array( $value['ids'] ) || count( $value['ids'] ) < 1 ) break;
					
					foreach ( $value['ids'] as $text_field ) {
						if ( ! isset( $text_field['id'] ) || trim( $text_field['id'] ) == '' ) continue;
						if ( ! isset( $text_field['default'] ) ) $text_field['default'] = '';
						
						// Remove [, ] characters from id argument
						$key = false;
						if ( strstr( $text_field['id'], '[' ) ) {
							parse_str( esc_attr( $text_field['id'] ), $option_array );
				
							// Option name is first key
							$option_keys = array_keys( $option_array );
							$first_key = current( $option_keys );
								
							$id_attribute		= $first_key;
							
							$key = key( $option_array[ $id_attribute ] );
						} else {
							$id_attribute		= esc_attr( $text_field['id'] );
						}
						
						// Get the option name
						$option_value = null;
						
						if ( trim( $option_name ) == '' || $value['separate_option'] != false ) {
							if ( $key != false ) {
								if ( isset( $_POST[ $id_attribute ][ $key ] ) ) {
									$option_value = $_POST[ $id_attribute ][ $key ];
								} else {
									$option_value = '';
								}	
							} else {
								if ( isset( $_POST[ $id_attribute ] ) ) {
									$option_value = $_POST[ $id_attribute ];
								} else {
									$option_value = '';
								}
							}
								
						} else {
							if ( $key != false ) {
								if ( isset( $_POST[ $option_name ][ $id_attribute ][ $key ] ) ) {
									$option_value = $_POST[ $option_name ][ $id_attribute ][ $key ];
								} else {
									$option_value = '';
								}	
							} else {
								if ( isset( $_POST[ $option_name ][ $id_attribute ] ) ) {
									$option_value = $_POST[ $option_name ][ $id_attribute ];
								} else {
									$option_value = '';
								}
							}
						}
						
						if ( strstr( $text_field['id'], '[' ) ) {
							// Set keys and value
	    					$key = key( $option_array[ $id_attribute ] );
			
							$update_options[ $id_attribute ][ $key ] = $option_value;
							
							if ( trim( $option_name ) != '' && $value['separate_option'] != false ) {
								$update_separate_options[ $id_attribute ][ $key ] = $option_value;
							}
							
						} else {
							$update_options[ $id_attribute ] = $option_value;
							
							if ( trim( $option_name ) != '' && $value['separate_option'] != false ) {
								$update_separate_options[ $id_attribute ] = $option_value;
							}
						}
					}
					
				break;
	
				// Other types
				default :
					if ( trim( $option_name ) == '' || $value['separate_option'] != false ) {
						if ( $key != false ) {
							if ( isset( $_POST[ $id_attribute ][ $key ] ) ) {
								$option_value = $_POST[ $id_attribute ][ $key ];
							} else {
								$option_value = '';
							}	
						} else {
							if ( isset( $_POST[ $id_attribute ] ) ) {
								$option_value = $_POST[ $id_attribute ];
							} else {
								$option_value = '';
							}
						}
							
					} else {
						if ( $key != false ) {
							if ( isset( $_POST[ $option_name ][ $id_attribute ][ $key ] ) ) {
								$option_value = $_POST[ $option_name ][ $id_attribute ][ $key ];
							} else {
								$option_value = '';
							}	
						} else {
							if ( isset( $_POST[ $option_name ][ $id_attribute ] ) ) {
								$option_value = $_POST[ $option_name ][ $id_attribute ];
							} else {
								$option_value = '';
							}
						}
					}

					// Just for Color type
					if ( 'color' == $value['type'] && '' == trim( $option_value ) ) {
						$option_value = 'transparent';
					}
	
				break;
	
			}
			
			if ( !in_array( $value['type'], array( 'array_textfields' ) ) ) {
				if ( strstr( $value['id'], '[' ) ) {
					// Set keys and value
					$key = key( $option_array[ $id_attribute ] );
	
					$update_options[ $id_attribute ][ $key ] = $option_value;
					
					if ( trim( $option_name ) != '' && $value['separate_option'] != false ) {
						$update_separate_options[ $id_attribute ][ $key ] = $option_value;
					}
					
				} else {
					$update_options[ $id_attribute ] = $option_value;
					
					if ( trim( $option_name ) != '' && $value['separate_option'] != false ) {
						$update_separate_options[ $id_attribute ] = $option_value;
					}
				}
			}
			
		}
		
		// Save settings for option values is an array and it's in single option name for all settings
		if ( trim( $option_name ) != '' ) {
			update_option( $option_name, $update_options );
		}
		
		// Save options if each option save in a row
		if ( count( $update_options ) > 0 && trim( $option_name ) == '' ) {
			foreach ( $update_options as $name => $value ) {
				update_option( $name, $value );
			}
		}
		
		// Save separate options
		if ( count( $update_separate_options ) > 0 ) {
			foreach ( $update_separate_options as $name => $value ) {
				update_option( $name, $value );
			}
		}
				
		return true;
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* Reset Function - reset_settings */
	/*-----------------------------------------------------------------------------------*/

	public function reset_settings( $options, $option_name = '', $reset = false, $free_version = false ) {
		
		if ( !is_array( $options ) || count( $options ) < 1 ) return;
		
		// Update settings default for option values is an array and it's in single option name for all settings
		if ( trim( $option_name ) != '' ) {

			$default_settings = $this->get_settings_default( $options, $option_name );

			$current_settings = get_option( $option_name );
			if ( ! is_array( $current_settings ) ) $current_settings = array();
			$current_settings = array_merge( $default_settings, $current_settings );

			if ( $reset && !$free_version ) {
				update_option( $option_name, $default_settings );
			} else {
				if ( $free_version ) {
					foreach ( $options as $value ) {
						if ( ! isset( $value['type'] ) ) continue;
						if ( in_array( $value['type'], array( 'heading' ) ) ) continue;
						if ( ! isset( $value['id'] ) || trim( $value['id'] ) == '' ) continue;
						
						switch ( $value['type'] ) {
				
							// Array textfields
							case 'array_textfields' :
								if ( !isset( $value['ids'] ) || !is_array( $value['ids'] ) || count( $value['ids'] ) < 1 ) break;
								
								foreach ( $value['ids'] as $text_field ) {
									if ( ! isset( $text_field['id'] ) || trim( $text_field['id'] ) == '' ) continue;
									if ( ! isset( $text_field['default'] ) ) $text_field['default'] = '';
									if ( ! isset( $text_field['free_version'] ) ) {
										if ( ! isset( $value['free_version'] ) ) 
											$text_field['free_version'] = false;
										else
											$text_field['free_version'] = $value['free_version'];
									}
									if ( $text_field['free_version'] ) unset( $default_settings[ $text_field['id']] );
								}
								
							break;
							
							default :
								if ( ! isset( $value['default'] ) ) $value['default'] = '';
								if ( ! isset( $value['free_version'] ) ) $value['free_version'] = false;
								if ( $value['free_version'] ) unset( $default_settings[ $value['id']] );
							
							break;
						}
					}
					
					$current_settings = array_merge( $current_settings, $default_settings );
					update_option( $option_name, $current_settings );
				} else {
					update_option( $option_name, $current_settings );
				}
			}
			
		}
		
		// Update settings default for option value is stored as a record or it's spearate option
		foreach ( $options as $value ) {
			if ( ! isset( $value['type'] ) ) continue;
			if ( in_array( $value['type'], array( 'heading' ) ) ) continue;
			if ( ! isset( $value['id'] ) || trim( $value['id'] ) == '' ) continue;
			if ( ! isset( $value['default'] ) ) $value['default'] = '';
			if ( ! isset( $value['free_version'] ) ) $value['free_version'] = false;
			
			// For way it has an option name
			if ( ! isset( $value['separate_option'] ) ) $value['separate_option'] = false;
			
			switch ( $value['type'] ) {
				
				// Array textfields
				case 'array_textfields' :
					if ( !isset( $value['ids'] ) || !is_array( $value['ids'] ) || count( $value['ids'] ) < 1 ) break;
								
					foreach ( $value['ids'] as $text_field ) {
						if ( ! isset( $text_field['id'] ) || trim( $text_field['id'] ) == '' ) continue;
						if ( ! isset( $text_field['default'] ) ) $text_field['default'] = '';
						if ( ! isset( $text_field['free_version'] ) ) {
							if ( ! isset( $value['free_version'] ) ) 
								$text_field['free_version'] = false;
							else
								$text_field['free_version'] = $value['free_version'];
						}
						
						// Remove [, ] characters from id argument
						if ( strstr( $text_field['id'], '[' ) ) {
							parse_str( esc_attr( $text_field['id'] ), $option_array );
				
							// Option name is first key
							$option_keys = array_keys( $option_array );
							$first_key = current( $option_keys );
								
							$id_attribute		= $first_key;
						} else {
							$id_attribute		= esc_attr( $text_field['id'] );
						}
						
						if ( trim( $option_name ) == '' || $value['separate_option'] != false ) {
							if ( $reset && $text_field['free_version'] && !$free_version ) {
								update_option( $id_attribute,  $text_field['default'] );
							} elseif ( $reset && !$text_field['free_version'] ) {
								update_option( $id_attribute,  $text_field['default'] );
							} else {
								add_option( $id_attribute,  $text_field['default'] );
							}
						}
					}
								
				break;
							
				default :
					// Remove [, ] characters from id argument
					if ( strstr( $value['id'], '[' ) ) {
						parse_str( esc_attr( $value['id'] ), $option_array );
			
						// Option name is first key
						$option_keys = array_keys( $option_array );
						$first_key = current( $option_keys );
							
						$id_attribute		= $first_key;
					} else {
						$id_attribute		= esc_attr( $value['id'] );
					}
					
					if ( trim( $option_name ) == '' || $value['separate_option'] != false ) {
						if ( $reset && $value['free_version'] && !$free_version ) {
							update_option( $id_attribute,  $value['default'] );
						} elseif ( $reset && !$value['free_version'] ) {
							update_option( $id_attribute,  $value['default'] );
						} else {
							add_option( $id_attribute,  $value['default'] );
						}
					}
							
				break;
			}
			
		}
				
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* Get Option Value Function - settings_get_option */
	/* Just called for when each option has an option value for settings
	/*-----------------------------------------------------------------------------------*/
	
	public function settings_get_option( $option_name, $default = '' ) {
		// Array value
		if ( strstr( $option_name, '[' ) ) {
	
			parse_str( $option_name, $option_array );
	
			// Option name is first key
			$option_keys = array_keys( $option_array );
			$option_name = current( $option_keys );
	
			// Get value
			$option_values = get_option( $option_name, '' );
	
			$key = key( $option_array[ $option_name ] );
	
			if ( isset( $option_values[ $key ] ) )
				$option_value = $option_values[ $key ];
			else
				$option_value = null;
	
		// Single value
		} else {
			$option_value = get_option( $option_name, null );
		}
	
		if ( is_array( $option_value ) )
			$option_value = array_map( 'stripslashes', $option_value );
		elseif ( ! is_null( $option_value ) )
			$option_value = stripslashes( $option_value );
	
		return $option_value === null ? $default : $option_value;
	}
	
	/**
	 * Output admin fields.
	 *
	 *
	 * @access public
	 * @param array $options : Opens array to output
	 * @param text $form_key : It's unique key for form to get correct save and reset action for this form
	 * @param text $option_name : Save all settings as array into database for a single option name
	 * @param array $form_messages : { 'success_message' => '', 'error_message' => '', 'reset_message' => '' }
	 * @return void
	 * ========================================================================
	 * Option Array Structure :
	 * type					=> heading | text | email | number | password | color | textarea | select | multiselect | radio | onoff_radio | checkbox | onoff_checkbox 
	 *						   | switcher_checkbox | image_size | single_select_page | typography | border | border_styles | border_corner | box_shadow 
	 *						   | slider | upload | wp_editor | array_textfields | 
	 *
	 * id					=> text
	 * name					=> text
	 * free_version			=> true | false : if Yes then when save settings with $free_version = true, it does reset this option
	 * class				=> text
	 * css					=> text
	 * default				=> text : apply for other types
	 *						   array( 'width' => '125', 'height' => '125', 'crop' => 1 ) : apply image_size only
	 *						   array( 'size' => '9px', 'face' => 'Arial', 'style' => 'normal', 'color' => '#515151' ) : apply for typography only 
	 *						   array( 'width' => '1px', 'style' => 'normal', 'color' => '#515151', 'corner' => 'rounded' | 'square' , 'top_left_corner' => 3, 
	 *									'top_right_corner' => 3, 'bottom_left_corner' => 3, 'bottom_right_corner' => 3 ) : apply for border only
	  *						   array( 'width' => '1px', 'style' => 'normal', 'color' => '#515151' ) : apply for border_styles only
	 *						   array( 'corner' => 'rounded' | 'square' , 'top_left_corner' => 3, 'top_right_corner' => 3, 'bottom_left_corner' => 3, 
	 *									'bottom_right_corner' => 3 ) : apply for border_corner only
	 *						   array( 'enable' => 1|0, 'h_shadow' => '5px' , 'v_shadow' => '5px', 'blur' => '2px' , 'spread' => '2px', 'color' => '#515151', 
	 *									'inset' => '' | 'insert' ) : apply for box_shadow only
	 *
	 * desc					=> text
	 * desc_tip				=> text
	 * separate_option		=> true | false
	 * custom_attributes	=> array
	 * view_doc				=> allowed html code : apply for heading only
	 * placeholder			=> text : apply for input, email, number, password, textarea, select, multiselect and single_select_page
	 * hide_if_checked		=> true | false : apply for checkbox only
	 * show_if_checked		=> true | false : apply for checkbox only
	 * checkboxgroup		=> start | end : apply for checkbox only
	 * checked_value		=> text : apply for checkbox, onoff_checkbox, switcher_checkbox only ( it's value set to database when checkbox is checked )
	 * unchecked_value		=> text : apply for checkbox, onoff_checkbox, switcher_checkbox only ( it's value set to database when checkbox is unchecked )
	 * checked_label		=> text : apply for onoff_checkbox, switcher_checkbox only ( set it to show the text instead ON word default )
	 * unchecked_label		=> text : apply for onoff_checkbox, switcher_checkbox only ( set it to show the text instead OFF word default  )
	 * options				=> array : apply for select, multiselect, radio types
	 *
	 * onoff_options		=> array : apply for onoff_radio only
	 *						   ---------------- example ---------------------
	 *							array( 
	 *								array(  'val' 				=> 1,
	 *										'text' 				=> 'Top',
	 *										'checked_label' 	=> 'ON',
	 *										'unchecked_value'	=> 'OFF' ),
	 *
	 *								array(  'val' 				=> 2,
	 *										'text' 				=> 'Bottom',
	 *										'checked_label' 	=> 'ON',
	 *										'unchecked_value'	=> 'OFF' ),
	 *							)
	 *							---------------------------------------------
	 *
	 * args					=> array : apply for single_select_page only
	 * min					=> number : apply for slider, border, border_corner types only
	 * max					=> number : apply for slider, border, border_corner types only
	 * increment			=> number : apply for slider, border, border_corner types only
	 * textarea_rows		=> number : apply for wp_editor type only
	 *
	 * ids					=> array : apply for array_textfields only
	 *						   ---------------- example ---------------------
	 *							array( 
	 *								array(  'id' 		=> 'box_margin_top',
	 *										'name' 		=> 'Top',
	 *										'class' 	=> '',
	 *										'css'		=> 'width:40px;',
	 *										'default'	=> '10px' ),
	 *
	 *								array(  'id' 		=> 'box_margin_top',
	 *										'name' 		=> 'Top',
	 *										'class' 	=> '',
	 *										'css'		=> 'width:40px;',
	 *										'default'	=> '10px' ),
	 *							)
	 *							---------------------------------------------
	 *
	 */
	 
	public function admin_forms( $options, $form_key, $option_name = '', $form_messages = array() ) {
		global $wp_email_template_fonts_face, $wp_email_template_uploader, $current_subtab;
		
		$new_settings = array(); $new_single_setting = ''; // :)
		$admin_message = '';
		
		if ( isset( $_POST['form_name_action'] ) && $_POST['form_name_action'] == $form_key ) {
			
			do_action( $this->plugin_name . '_before_settings_save_reset' );
			do_action( $this->plugin_name . '-' . trim( $form_key ) . '_before_settings_save' );
			
			// Save settings action
			if ( isset( $_POST['bt_save_settings'] ) ) {
				$this->save_settings( $options, $option_name );
				$admin_message = $this->get_success_message( ( isset( $form_messages['success_message'] ) ) ? $form_messages['success_message'] : ''  );
			} 
			// Reset settings action
			elseif ( isset( $_POST['bt_reset_settings'] ) ) {
				$this->reset_settings( $options, $option_name, true );
				$admin_message = $this->get_success_message( ( isset( $form_messages['reset_message'] ) ) ? $form_messages['reset_message'] : ''  );
			}
			
			do_action( $this->plugin_name . '-' . trim( $form_key ) . '_after_settings_save' );
			do_action( $this->plugin_name . '_after_settings_save_reset' );
		}
		do_action( $this->plugin_name . '-' . trim( $form_key ) . '_settings_init' );
		
		$option_values = array();
		if ( trim( $option_name ) != '' ) {
			$option_values = get_option( $option_name, array() );
			if ( is_array( $option_values ) )
				$option_values = array_map( array( $this, 'admin_stripslashes' ), $option_values );
			else
				$option_values = array();
			
			$default_settings = $this->get_settings_default( $options, $option_name );
			
			$option_values = array_merge($default_settings, $option_values);
		}
						
		if ( !is_array( $options ) || count( $options ) < 1 ) return '';
		?>
        
        <?php echo $admin_message; ?>
		<div class="a3rev_panel_container" style="visibility:hidden; height:0; overflow:hidden;" >
        <form action="" method="post">
		<?php do_action( $this->plugin_name . '-' . trim( $form_key ) . '_settings_start' ); ?>
		<?php
		$count_heading = 0;
		$end_heading_id = false;
		
		foreach ( $options as $value ) {
			if ( ! isset( $value['type'] ) ) continue;
			if ( ! isset( $value['id'] ) ) $value['id'] = '';
			if ( ! isset( $value['name'] ) ) $value['name'] = '';
			if ( ! isset( $value['class'] ) ) $value['class'] = '';
			if ( ! isset( $value['css'] ) ) $value['css'] = '';
			if ( ! isset( $value['default'] ) ) $value['default'] = '';
			if ( ! isset( $value['desc'] ) ) $value['desc'] = '';
			if ( ! isset( $value['desc_tip'] ) ) $value['desc_tip'] = false;
			if ( ! isset( $value['placeholder'] ) ) $value['placeholder'] = '';
			
			// For way it has an option name
			if ( ! isset( $value['separate_option'] ) ) $value['separate_option'] = false;
	
			// Custom attribute handling
			$custom_attributes = array();
	
			if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) )
				foreach ( $value['custom_attributes'] as $attribute => $attribute_value )
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
	
			// Description handling
			if ( $value['desc_tip'] === true ) {
				$description = '';
				$tip = $value['desc'];
			} elseif ( ! empty( $value['desc_tip'] ) ) {
				$description = $value['desc'];
				$tip = $value['desc_tip'];
			} elseif ( ! empty( $value['desc'] ) ) {
				$description = $value['desc'];
				$tip = '';
			} else {
				$description = $tip = '';
			}
	
			if ( $description && in_array( $value['type'], array( 'textarea', 'radio', 'onoff_radio', 'typography', 'border', 'border_styles', 'border_corner', 'box_shadow', 'array_textfields', 'wp_editor', 'upload' ) ) ) {
				$description = '<div class="desc" style="margin-bottom:5px;">' . wp_kses_post( $description ) . '</div>';
			} elseif ( $description ) {
				$description = '<span class="description" style="margin-left:5px;">' . wp_kses_post( $description ) . '</span>';
			}
			
			/**
			 * Add Default value into description and description tip if it has shortcode :
			 * [default_value] 				: apply for normal types
			 *
			 * [default_value_width] 		: apply for image_size type
			 * [default_value_height] 		: apply for image_size type
			 *
			 * [default_value_size]			: apply for typography type
			 * [default_value_face]			: apply for typography type
			 * [default_value_style]		: apply for typography, border, border_styles types
			 * [default_value_color]		: apply for typography, border, border_styles types
			 *
			 * [default_value_width]		: apply for border, border_styles types
			 * [default_value_rounded_value]: apply for border, border_corner types
			 * [default_value_top_left_corner]: apply for border, border_corner types
			 * [default_value_top_right_corner]: apply for border, border_corner types
			 * [default_value_bottom_left_corner]: apply for border, border_corner types
			 * [default_value_bottom_right_corner]: apply for border, border_corner types
			 */
			if ( $value['type'] == 'image_size' ) {
				if ( ! is_array( $value['default'] ) ) $value['default'] = array();
				if ( ! isset( $value['default']['width'] ) ) $value['default']['width'] = '';
				if ( ! isset( $value['default']['height'] ) ) $value['default']['height'] = '';
				if ( ! isset( $value['default']['crop'] ) ) $value['default']['crop'] = 1;
				
				$description = str_replace( '[default_value_width]', $value['default']['width'], $description );
				$description = str_replace( '[default_value_height]', $value['default']['height'], $description );
			} elseif ( $value['type'] == 'typography' ) {
				if ( ! is_array( $value['default'] ) ) $value['default'] = array();
				if ( ! isset( $value['default']['size'] ) ) $value['default']['size'] = '';
				if ( ! isset( $value['default']['face'] ) ) $value['default']['face'] = '';
				if ( ! isset( $value['default']['style'] ) ) $value['default']['style'] = '';
				if ( ! isset( $value['default']['color'] ) || trim( $value['default']['color'] ) == '' ) $value['default']['color'] = '#515151';
				
				$description = str_replace( '[default_value_size]', $value['default']['size'], $description );
				$description = str_replace( '[default_value_face]', $value['default']['face'], $description );
				$description = str_replace( '[default_value_style]', $value['default']['style'], $description );
				$description = str_replace( '[default_value_color]', $value['default']['color'], $description );
			} elseif ( in_array( $value['type'], array( 'border', 'border_styles', 'border_corner' ) ) ) {
				if ( ! is_array( $value['default'] ) ) $value['default'] = array();
				
				if ( ! isset( $value['default']['width'] ) ) $value['default']['width'] = '';
				if ( ! isset( $value['default']['style'] ) ) $value['default']['style'] = '';
				if ( ! isset( $value['default']['color'] ) || trim( $value['default']['color'] ) == '' ) $value['default']['color'] = '#515151';
					
				if ( ! isset( $value['default']['corner'] ) ) $value['default']['corner'] = 'rounded';
				if ( ! isset( $value['default']['rounded_value'] ) ) $value['default']['rounded_value'] = '';
				if ( ! isset( $value['default']['top_left_corner'] ) ) $value['default']['top_left_corner'] = $value['default']['rounded_value'];
				if ( ! isset( $value['default']['top_right_corner'] ) ) $value['default']['top_right_corner'] = $value['default']['rounded_value'];
				if ( ! isset( $value['default']['bottom_left_corner'] ) ) $value['default']['bottom_left_corner'] = $value['default']['rounded_value'];
				if ( ! isset( $value['default']['bottom_right_corner'] ) ) $value['default']['bottom_right_corner'] = $value['default']['rounded_value'];
				
				$description = str_replace( '[default_value_width]', $value['default']['width'], $description );
				$description = str_replace( '[default_value_style]', $value['default']['style'], $description );
				$description = str_replace( '[default_value_color]', $value['default']['color'], $description );
				$description = str_replace( '[default_value_rounded_value]', $value['default']['rounded_value'], $description );
				$description = str_replace( '[default_value_top_left_corner]', $value['default']['top_left_corner'], $description );
				$description = str_replace( '[default_value_top_right_corner]', $value['default']['top_right_corner'], $description );
				$description = str_replace( '[default_value_bottom_left_corner]', $value['default']['bottom_left_corner'], $description );
				$description = str_replace( '[default_value_bottom_right_corner]', $value['default']['bottom_right_corner'], $description );
			} elseif ( $value['type'] == 'box_shadow' ) {
				if ( ! is_array( $value['default'] ) ) $value['default'] = array();
				if ( ! isset( $value['default']['enable'] ) || trim( $value['default']['enable'] ) == '' ) $value['default']['enable'] = 0;
				if ( ! isset( $value['default']['color'] ) || trim( $value['default']['color'] ) == '' ) $value['default']['color'] = '#515151';
				if ( ! isset( $value['default']['h_shadow'] ) || trim( $value['default']['h_shadow'] ) == '' ) $value['default']['h_shadow'] = '0px';
				if ( ! isset( $value['default']['v_shadow'] ) || trim( $value['default']['v_shadow'] ) == '' ) $value['default']['v_shadow'] = '0px';
				if ( ! isset( $value['default']['blur'] ) || trim( $value['default']['blur'] ) == '' ) $value['default']['blur'] = '0px';
				if ( ! isset( $value['default']['spread'] ) || trim( $value['default']['spread'] ) == '' ) $value['default']['spread'] = '0px';
				if ( ! isset( $value['default']['inset'] ) || trim( $value['default']['inset'] ) == '' ) $value['default']['inset'] = '';
				
				$description = str_replace( '[default_value_color]', $value['default']['color'], $description );
				$description = str_replace( '[default_value_h_shadow]', $value['default']['h_shadow'], $description );
				$description = str_replace( '[default_value_v_shadow]', $value['default']['v_shadow'], $description );
				$description = str_replace( '[default_value_blur]', $value['default']['blur'], $description );
				$description = str_replace( '[default_value_spread]', $value['default']['spread'], $description );
				
			} elseif ( $value['type'] != 'multiselect' ) {
				$description = str_replace( '[default_value]', $value['default'], $description );
			}
	
			if ( $tip && in_array( $value['type'], array( 'checkbox' ) ) ) {
	
				$tip = '<p class="description">' . $tip . '</p>';
	
			} elseif ( $tip ) {
	
				$tip = '<div class="help_tip a3-plugin-ui-icon a3-plugin-ui-help-icon" data-tip="' . esc_attr( $tip ) . '"></div>';
	
			}
			
			// Remove [, ] characters from id argument
			$key = false;
			if ( strstr( $value['id'], '[' ) ) {
				parse_str( esc_attr( $value['id'] ), $option_array );
	
				// Option name is first key
				$option_keys = array_keys( $option_array );
				$first_key = current( $option_keys );
					
				$id_attribute		= $first_key;
				
				$key = key( $option_array[ $id_attribute ] );
			} else {
				$id_attribute		= esc_attr( $value['id'] );
			}
			
			// Get option value when option name is not parse or when it's spearate option
			if ( trim( $option_name ) == '' || $value['separate_option'] != false ) {
				$option_value		= $this->settings_get_option( $value['id'], $value['default'] );
			}
			// Get option value when it's an element from option array 
			else {
				if ( $key != false ) {
					$option_value 		= ( isset( $option_values[ $id_attribute ][ $key ] ) ) ? $option_values[ $id_attribute ][ $key ] : $value['default'];
				} else {
					$option_value 		= ( isset( $option_values[ $id_attribute ] ) ) ? $option_values[ $id_attribute ] : $value['default'];
				}
			}
					
			// Generate name and id attributes
			if ( trim( $option_name ) == '' ) {
				$name_attribute		= esc_attr( $value['id'] );
			} elseif ( $value['separate_option'] != false ) {
				$name_attribute		= esc_attr( $value['id'] );
				$id_attribute		= esc_attr( $option_name ) . '_' . $id_attribute;
			} else {
				// Array value
				if ( strstr( $value['id'], '[' ) ) {
					$name_attribute	= esc_attr( $option_name ) . '[' . $id_attribute . ']' . str_replace( $id_attribute . '[', '[', esc_attr( $value['id'] ) );
				} else {
					$name_attribute	= esc_attr( $option_name ) . '[' . esc_attr( $value['id'] ) . ']';
				}
				$id_attribute		= esc_attr( $option_name ) . '_' . $id_attribute;
			}
	
			// Switch based on type
			switch( $value['type'] ) {
	
				// Heading
				case 'heading':
					
					$count_heading++;
					if ( $count_heading > 1 )  {
						if ( trim( $end_heading_id ) != '' ) do_action( $this->plugin_name . '_settings_' . sanitize_title( $end_heading_id ) . '_end' );
						echo '</table>' . "\n\n";
						echo '</div>' . "\n\n";
						if ( trim( $end_heading_id ) != '' ) do_action( $this->plugin_name . '_settings_' . sanitize_title( $end_heading_id ) . '_after' );
					}
					if ( ! empty( $value['id'] ) )
						$end_heading_id = $value['id'];
					else
						$end_heading_id = '';
						
					$view_doc = ( isset( $value['view_doc'] ) ) ? $value['view_doc'] : '';
					
					if ( ! empty( $value['id'] ) ) do_action( $this->plugin_name . '_settings_' . sanitize_title( $value['id'] ) . '_before' );
					
					echo '<div id="'. esc_attr( $value['id'] ) . '" class="a3rev_panel_inner '. esc_attr( $value['class'] ) .'" style="'. esc_attr( $value['css'] ) .'">' . "\n\n";
					if ( stristr( $value['class'], 'pro_feature_fields' ) !== false && ! empty( $value['id'] ) ) $this->upgrade_top_message( true, sanitize_title( $value['id'] ) );
					elseif ( stristr( $value['class'], 'pro_feature_fields' ) !== false ) $this->upgrade_top_message( true );
					
					echo ( ! empty( $value['name'] ) ) ? '<h3>'. esc_html( $value['name'] ) .' '. $view_doc .'</h3>' : '';
					if ( ! empty( $value['desc'] ) ) echo wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) );
					echo '<table class="form-table">' . "\n\n";
					
					if ( ! empty( $value['id'] ) ) do_action( $this->plugin_name . '_settings_' . sanitize_title( $value['id'] ) . '_start' );
				break;
	
				// Standard text inputs and subtypes like 'number'
				case 'text':
				case 'email':
				case 'number':
				case 'password' :
	
					$type 			= $value['type'];
	
					?><tr valign="top">
						<th scope="row" class="titledesc">
                        	<?php echo $tip; ?>
							<label for="<?php echo $id_attribute; ?>"><?php echo esc_html( $value['name'] ); ?></label>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<input
								name="<?php echo $name_attribute; ?>"
								id="<?php echo $id_attribute; ?>"
								type="<?php echo esc_attr( $type ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								value="<?php echo esc_attr( $option_value ); ?>"
								class="a3rev-ui-<?php echo sanitize_title( $value['type'] ) ?> <?php echo esc_attr( $value['class'] ); ?>"
                                placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								/> <?php echo $description; ?>
						</td>
					</tr><?php
				break;
				
				// Color
				case 'color' :
					
					if ( trim( $value['default'] ) == '' ) $value['default'] = '#515151';
					$default_color = ' data-default-color="' . esc_attr( $value['default'] ) . '"';
					if ( '' == trim( $option_value ) ) $option_value = 'transparent';

					?><tr valign="top">
						<th scope="row" class="titledesc">
                        	<?php echo $tip; ?>
							<label for="<?php echo $id_attribute; ?>"><?php echo esc_html( $value['name'] ); ?></label>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<input
								name="<?php echo $name_attribute; ?>"
								id="<?php echo $id_attribute; ?>"
								type="text"
								value="<?php echo esc_attr( $option_value ); ?>"
								class="a3rev-color-picker"
								<?php echo $default_color; ?>
								/> <?php echo $description; ?>
						</td>
					</tr><?php
					
				break;
	
				// Textarea
				case 'textarea':
	
					?><tr valign="top">
						<th scope="row" class="titledesc">
                        	<?php echo $tip; ?>
							<label for="<?php echo $id_attribute; ?>"><?php echo esc_html( $value['name'] ); ?></label>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<?php echo $description; ?>
	
							<textarea
								name="<?php echo $name_attribute; ?>"
								id="<?php echo $id_attribute; ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="a3rev-ui-<?php echo sanitize_title( $value['type'] ) ?> <?php echo esc_attr( $value['class'] ); ?>"
                                placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								><?php echo esc_textarea( $option_value );  ?></textarea>
						</td>
					</tr><?php
				break;
	
				// Select boxes
				case 'select' :
				case 'multiselect' :
				
					if ( trim( $value['class'] ) == '' ) $value['class'] = 'chzn-select';
					if ( ! isset( $value['options'] ) ) $value['options'] = array();
		
					?><tr valign="top">
						<th scope="row" class="titledesc">
                        	<?php echo $tip; ?>
							<label for="<?php echo $id_attribute; ?>"><?php echo esc_html( $value['name'] ); ?></label>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<select
								name="<?php echo $name_attribute; ?><?php if ( $value['type'] == 'multiselect' ) echo '[]'; ?>"
								id="<?php echo $id_attribute; ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="a3rev-ui-<?php echo sanitize_title( $value['type'] ) ?> <?php echo esc_attr( $value['class'] ); ?>"
								data-placeholder="<?php echo esc_html( $value['placeholder'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								<?php if ( $value['type'] == 'multiselect' ) echo 'multiple="multiple"'; ?>
								>
								<?php
								if ( is_array( $value['options'] ) && count( $value['options'] ) > 0 ) {
									foreach ( $value['options'] as $key => $val ) {
										?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php
	
											if ( is_array( $option_value ) )
												selected( in_array( $key, $option_value ), true );
											else
												selected( $option_value, $key );
	
										?>><?php echo $val ?></option>
										<?php
									}
								}
								?>
						   </select> <?php echo $description; ?>
						</td>
					</tr><?php
				break;
	
				// Radio inputs
				case 'radio' :
				
					if ( ! isset( $value['options'] ) ) $value['options'] = array();
	
					?><tr valign="top">
						<th scope="row" class="titledesc">
                        	<?php echo $tip; ?>
							<label for="<?php echo $id_attribute; ?>"><?php echo esc_html( $value['name'] ); ?></label>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<fieldset>
								<?php echo $description; ?>
								<ul>
								<?php
								if ( is_array( $value['options'] ) && count( $value['options'] ) > 0 ) {
									foreach ( $value['options'] as $val => $text ) {
										?>
										<li>
											<label><input
												name="<?php echo $name_attribute; ?>"
												value="<?php echo $val; ?>"
												type="radio"
												style="<?php echo esc_attr( $value['css'] ); ?>"
												class="a3rev-ui-<?php echo sanitize_title( $value['type'] ) ?> <?php echo esc_attr( $value['class'] ); ?>"
												<?php echo implode( ' ', $custom_attributes ); ?>
												<?php checked( $val, $option_value ); ?>
												/> <span class="description" style="margin-left:5px;"><?php echo $text ?></span></label>
										</li>
										<?php
									}
								}
								?>
								</ul>
							</fieldset>
						</td>
					</tr><?php
				break;
				
				// OnOff Radio inputs
				case 'onoff_radio' :
				
					if ( ! isset( $value['onoff_options'] ) ) $value['onoff_options'] = array();
	
					?><tr valign="top">
						<th scope="row" class="titledesc">
                        	<?php echo $tip; ?>
							<label for="<?php echo $id_attribute; ?>"><?php echo esc_html( $value['name'] ); ?></label>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<fieldset>
								<?php echo $description; ?>
								<ul>
								<?php
								if ( is_array( $value['onoff_options'] ) && count( $value['onoff_options'] ) > 0 ) {
									foreach ( $value['onoff_options'] as $i_option ) {
										if ( ! isset( $i_option['checked_label'] ) ) $i_option['checked_label'] = __( 'ON', 'wp_email_template' );
										if ( ! isset( $i_option['unchecked_label'] ) ) $i_option['unchecked_label'] = __( 'OFF', 'wp_email_template' );
										if ( ! isset( $i_option['val'] ) ) $i_option['val'] = 1;
										if ( ! isset( $i_option['text'] ) ) $i_option['text'] = '';
										?>
										<li>
                                            <input
                                                name="<?php echo $name_attribute; ?>"
                                                <?php if ( $i_option['val'] == $option_value ) echo ' checkbox-disabled="true" ' ; ?>
                                                class="a3rev-ui-onoff_radio <?php echo esc_attr( $value['class'] ); ?>"
                                                checked_label="<?php echo esc_html( $i_option['checked_label'] ); ?>"
                                                unchecked_label="<?php echo esc_html( $i_option['unchecked_label'] ); ?>"
                                                type="radio"
                                                value="<?php echo esc_attr( stripslashes( $i_option['val'] ) ); ?>"
                                                <?php checked( esc_attr( stripslashes( $i_option['val'] ) ), $option_value ); ?>
                                                <?php echo implode( ' ', $custom_attributes ); ?>
                                                /> <span class="description" style="margin-left:5px;"><?php echo $i_option['text'] ?></span>
										</li>
										<?php
									}
								}
								?>
								</ul>
							</fieldset>
						</td>
					</tr><?php
				break;
	
				// Checkbox input
				case 'checkbox' :
		
					if ( ! isset( $value['checked_value'] ) ) $value['checked_value'] = 1;
					if ( ! isset( $value['hide_if_checked'] ) ) $value['hide_if_checked'] = false;
					if ( ! isset( $value['show_if_checked'] ) ) $value['show_if_checked'] = false;
	
					if ( ! isset( $value['checkboxgroup'] ) || ( isset( $value['checkboxgroup'] ) && $value['checkboxgroup'] == 'start' ) ) {
						?>
						<tr valign="top" class="<?php
							if ( $value['hide_if_checked'] == 'yes' || $value['show_if_checked']=='yes') echo 'hidden_option';
							if ( $value['hide_if_checked'] == 'option' ) echo 'hide_options_if_checked';
							if ( $value['show_if_checked'] == 'option' ) echo 'show_options_if_checked';
						?>">
						<th scope="row" class="titledesc">
                        	<label for="<?php echo $id_attribute; ?>"><?php echo esc_html( $value['name'] ); ?></label>
                        </th>
						<td class="forminp forminp-checkbox">
							<fieldset>
						<?php
					} else {
						?>
						<fieldset class="<?php
							if ( $value['hide_if_checked'] == 'yes' || $value['show_if_checked'] == 'yes') echo 'hidden_option';
							if ( $value['hide_if_checked'] == 'option') echo 'hide_options_if_checked';
							if ( $value['show_if_checked'] == 'option') echo 'show_options_if_checked';
						?>">
						<?php
					}
	
					?>
						<legend class="screen-reader-text"><span><?php echo esc_html( $value['name'] ) ?></span></legend>
	
						<label for="<?php echo $id_attribute; ?>">
						<input
							name="<?php echo $name_attribute; ?>"
							id="<?php echo $id_attribute; ?>"
							type="checkbox"
							value="<?php echo esc_attr( stripslashes( $value['checked_value'] ) ); ?>"
							<?php checked( $option_value, esc_attr( stripslashes( $value['checked_value'] ) ) ); ?>
							<?php echo implode( ' ', $custom_attributes ); ?>
						/> <?php echo $description; ?></label> <?php echo $tip; ?>
					<?php
	
					if ( ! isset( $value['checkboxgroup'] ) || ( isset( $value['checkboxgroup'] ) && $value['checkboxgroup'] == 'end' ) ) {
						?>
							</fieldset>
						</td>
						</tr>
						<?php
					} else {
						?>
						</fieldset>
						<?php
					}
	
				break;
				
				// OnOff Checkbox input
				case 'onoff_checkbox' :
				
					if ( ! isset( $value['checked_value'] ) ) $value['checked_value'] = 1;
					if ( ! isset( $value['checked_label'] ) ) $value['checked_label'] = __( 'ON', 'wp_email_template' );
					if ( ! isset( $value['unchecked_label'] ) ) $value['unchecked_label'] = __( 'OFF', 'wp_email_template' );
		
					?><tr valign="top">
						<th scope="row" class="titledesc">
                        	<?php echo $tip; ?>
							<label for="<?php echo $id_attribute; ?>"><?php echo esc_html( $value['name'] ); ?></label>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<input
								name="<?php echo $name_attribute; ?>"
                                id="<?php echo $id_attribute; ?>"
								class="a3rev-ui-onoff_checkbox <?php echo esc_attr( $value['class'] ); ?>"
                                checked_label="<?php echo esc_html( $value['checked_label'] ); ?>"
                                unchecked_label="<?php echo esc_html( $value['unchecked_label'] ); ?>"
                                type="checkbox"
								value="<?php echo esc_attr( stripslashes( $value['checked_value'] ) ); ?>"
								<?php checked( $option_value, esc_attr( stripslashes( $value['checked_value'] ) ) ); ?>
								<?php echo implode( ' ', $custom_attributes ); ?>
								/> <?php echo $description; ?>
                        </td>
					</tr><?php
	
				break;
				
				// Switcher Checkbox input
				case 'switcher_checkbox' :
				
					if ( ! isset( $value['checked_value'] ) ) $value['checked_value'] = 1;
					if ( ! isset( $value['checked_label'] ) ) $value['checked_label'] = __( 'ON', 'wp_email_template' );
					if ( ! isset( $value['unchecked_label'] ) ) $value['unchecked_label'] = __( 'OFF', 'wp_email_template' );
		
					?><tr valign="top">
						<th scope="row" class="titledesc">
                        	<?php echo $tip; ?>
							<label for="<?php echo $id_attribute; ?>"><?php echo esc_html( $value['name'] ); ?></label>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<input
								name="<?php echo $name_attribute; ?>"
                                id="<?php echo $id_attribute; ?>"
								class="a3rev-ui-onoff_checkbox <?php echo esc_attr( $value['class'] ); ?>"
                                checked_label="<?php echo esc_html( $value['checked_label'] ); ?>"
                                unchecked_label="<?php echo esc_html( $value['unchecked_label'] ); ?>"
                                type="checkbox"
								value="<?php echo esc_attr( stripslashes( $value['checked_value'] ) ); ?>"
								<?php checked( $option_value, esc_attr( stripslashes( $value['checked_value'] ) ) ); ?>
								<?php echo implode( ' ', $custom_attributes ); ?>
								/> <?php echo $description; ?>
                        </td>
					</tr><?php
	
				break;
	
				// Image size settings
				case 'image_size' :
	
					if ( trim( $option_name ) == '' || $value['separate_option'] != false ) {
						$width 	= $this->settings_get_option( $value['id'] . '[width]', $value['default']['width'] );
						$height = $this->settings_get_option( $value['id'] . '[height]', $value['default']['height'] );
						$crop 	= checked( 1, $this->settings_get_option( $value['id'] . '[crop]', $value['default']['crop'] ), false );
					} else {
						$width 	= $option_value['width'];
						$height = $option_value['height'];
						$crop 	= checked( 1, $option_value['crop'], false );
					}
	
					?><tr valign="top">
						<th scope="row" class="titledesc"><?php echo $tip; ?><?php echo esc_html( $value['name'] ) ?></th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
	
							<label><?php _e( 'Width', 'wp_email_template' ); ?> <input name="<?php echo $name_attribute; ?>[width]" id="<?php echo $id_attribute; ?>-width" type="text" class="a3rev-ui-<?php echo sanitize_title( $value['type'] ) ?>-width" value="<?php echo $width; ?>" /></label>
	
							<label><?php _e( 'Height', 'wp_email_template' ); ?> <input name="<?php echo $name_attribute; ?>[height]" id="<?php echo $id_attribute; ?>-height" type="text" class="a3rev-ui-<?php echo sanitize_title( $value['type'] ) ?>-height" value="<?php echo $height; ?>" /></label>
	
							<label><?php _e( 'Hard Crop', 'wp_email_template' ); ?> <input name="<?php echo $name_attribute; ?>[crop]" id="<?php echo $id_attribute; ?>-crop" type="checkbox" class="a3rev-ui-<?php echo sanitize_title( $value['type'] ) ?>-crop" <?php echo $crop; ?> /></label>
	
							</td>
					</tr><?php
				break;
	
				// Single page selects
				case 'single_select_page' :
	
					if ( trim( $value['class'] ) == '' ) $value['class'] = 'chzn-select-deselect';
					
					$args = array( 'name'				=> $name_attribute,
								   'id'					=> $id_attribute,
								   'sort_column' 		=> 'menu_order',
								   'sort_order'			=> 'ASC',
								   'show_option_none' 	=> ' ',
								   'class'				=> 'a3rev-ui-' . sanitize_title( $value['type'] ) . ' ' . $value['class'],
								   'echo' 				=> false,
								   'selected'			=> absint( $option_value )
								   );
	
					if( isset( $value['args'] ) )
						$args = wp_parse_args( $value['args'], $args );
	
					?><tr valign="top">
						<th scope="row" class="titledesc"><?php echo $tip; ?><?php echo esc_html( $value['name'] ) ?></th>
						<td class="forminp">
							<?php echo str_replace(' id=', " data-placeholder='" . esc_html( $value['placeholder'] ) .  "' style='" . $value['css'] . "' class='" . $value['class'] . "' id=", wp_dropdown_pages( $args ) ); ?> <?php echo $description; ?>
						</td>
					</tr><?php
				break;
				
				// Font Control
				case 'typography':
				
					$default_color = ' data-default-color="' . esc_attr( $value['default']['color'] ) . '"';
					
					if ( trim( $option_name ) == '' || $value['separate_option'] != false ) {
						$size	= $this->settings_get_option( $value['id'] . '[size]', $value['default']['size'] );
						$face	= $this->settings_get_option( $value['id'] . '[face]', $value['default']['face'] );
						$style	= $this->settings_get_option( $value['id'] . '[style]', $value['default']['style'] );
						$color	= $this->settings_get_option( $value['id'] . '[color]', $value['default']['color'] );
					} else {
						$size	= $option_value['size'];
						$face	= $option_value['face'];
						$style	= $option_value['style'];
						$color	= $option_value['color'];
					}
				
					?><tr valign="top">
						<th scope="row" class="titledesc"><?php echo $tip; ?><?php echo esc_html( $value['name'] ) ?></th>
						<td class="forminp">
                        	<?php echo $description; ?>
                            <div class="a3rev-ui-<?php echo sanitize_title( $value['type'] ) ?>-control">
                        	<!-- Font Size -->
							<select
								name="<?php echo $name_attribute; ?>[size]"
                                id="<?php echo $id_attribute; ?>-size"
								class="a3rev-ui-<?php echo sanitize_title( $value['type'] ) ?>-size chzn-select"
								>
								<?php
									for ( $i = 6; $i <= 70; $i++ ) {
										?>
										<option value="<?php echo esc_attr( $i ); ?>px" <?php
												selected( $size, $i.'px' );
										?>><?php echo esc_attr( $i ); ?>px</option>
										<?php
									}
								?>
						   </select> 
                           
                           <!-- Font Face -->
							<select
								name="<?php echo $name_attribute; ?>[face]"
                                id="<?php echo $id_attribute; ?>-face"
								class="a3rev-ui-<?php echo sanitize_title( $value['type'] ) ?>-face chzn-select"
								>
								<optgroup label="<?php _e( '-- Default Fonts --', 'wp_email_template' ); ?>">
                                <?php
									foreach ( $wp_email_template_fonts_face->get_default_fonts() as $val => $text ) {
										?>
                                        <option value="<?php echo esc_attr( $val ); ?>" <?php
												selected( esc_attr( $val ), esc_attr( $face ) );
										?>><?php echo esc_attr( $text ); ?></option>
                                        <?php
									}
								?>
                                </optgroup>
                                <optgroup label="<?php _e( '-- Google Fonts --', 'wp_email_template' ); ?>">
                                <?php
									foreach ( $wp_email_template_fonts_face->get_google_fonts() as $font ) {
										?>
                                        <option value="<?php echo esc_attr( $font['name'] ); ?>" <?php
												selected( esc_attr( $font['name'] ), esc_attr( $face ) );
										?>><?php echo esc_attr( $font['name'] ); ?></option>
                                        <?php
									}
								?>
                                </optgroup>
						   </select> 
                           
                           <!-- Font Weight -->
                           <select
								name="<?php echo $name_attribute; ?>[style]"
                                id="<?php echo $id_attribute; ?>-style"
								class="a3rev-ui-<?php echo sanitize_title( $value['type'] ) ?>-style chzn-select"
								>
								<?php
									foreach ( $this->get_font_weights() as $val => $text ) {
										?>
										<option value="<?php echo esc_attr( $val ); ?>" <?php
												selected( esc_attr( $val ), esc_attr( $style ) );
										?>><?php echo esc_attr( $text ); ?></option>
                                        <?php
									}
								?>
						   </select>
                           
                           <!-- Font Color -->
                           <input
								name="<?php echo $name_attribute; ?>[color]"
								id="<?php echo $id_attribute; ?>-color"
								type="text"
								value="<?php echo esc_attr( $color ); ?>"
								class="a3rev-ui-<?php echo sanitize_title( $value['type'] ) ?>-color a3rev-color-picker"
								<?php echo $default_color; ?>
								/> 
                                
                           <!-- Preview Button -->
                           <div class="a3rev-ui-typography-preview"><a href="#" class="a3rev-ui-typography-preview-button button submit-button" title="<?php _e( 'Preview your customized typography settings', 'wp_email_template'); ?>"><span>&nbsp;</span></a></div>
                           
                           </div>
                           
						</td>
					</tr><?php

				break;
				
				// Border Styles & Corner Control
				case 'border':
				
					if ( ! is_array( $value['default'] ) ) $value['default'] = array();
					
					// For Border Styles
					$default_color = ' data-default-color="' . esc_attr( $value['default']['color'] ) . '"';
					
					if ( trim( $option_name ) == '' || $value['separate_option'] != false ) {
						$width	= $this->settings_get_option( $value['id'] . '[width]', $value['default']['width'] );
						$style	= $this->settings_get_option( $value['id'] . '[style]', $value['default']['style'] );
						$color	= $this->settings_get_option( $value['id'] . '[color]', $value['default']['color'] );
					} else {
						$width	= $option_value['width'];
						$style	= $option_value['style'];
						$color	= $option_value['color'];
					}
					
					// For Border Corner
					if ( ! isset( $value['min'] ) ) $value['min'] = 0;
					if ( ! isset( $value['max'] ) ) $value['max'] = 100;
					if ( ! isset( $value['increment'] ) ) $value['increment'] = 1;
					
					if ( trim( $option_name ) != '' && $value['separate_option'] != false ) {
						$corner					= $this->settings_get_option( $value['id'] . '[corner]', $value['default']['corner'] );
						
						if ( ! isset( $value['default']['rounded_value'] ) ) $value['default']['rounded_value'] = 3;
						$rounded_value			= $this->settings_get_option( $value['id'] . '[rounded_value]', $value['default']['rounded_value'] );
						
						if ( ! isset( $value['default']['top_left_corner'] ) ) $value['default']['top_left_corner'] = 3;
						$top_left_corner		= $this->settings_get_option( $value['id'] . '[top_left_corner]', $value['default']['top_left_corner'] );
						
						if ( ! isset( $value['default']['top_right_corner'] ) ) $value['default']['top_right_corner'] = 3;
						$top_right_corner		= $this->settings_get_option( $value['id'] . '[top_right_corner]', $value['default']['top_right_corner'] );
						
						if ( ! isset( $value['default']['bottom_left_corner'] ) ) $value['default']['bottom_left_corner'] = 3;
						$bottom_left_corner		= $this->settings_get_option( $value['id'] . '[bottom_left_corner]', $value['default']['bottom_left_corner'] );
						
						if ( ! isset( $value['default']['bottom_right_corner'] ) ) $value['default']['bottom_right_corner'] = 3;
						$bottom_right_corner	= $this->settings_get_option( $value['id'] . '[bottom_right_corner]', $value['default']['bottom_right_corner'] );
					} else {
						if ( ! isset( $option_value['corner'] ) ) $option_value['corner'] = '';
						$corner					= $option_value['corner'];
						
						if ( ! isset( $option_value['rounded_value'] ) ) $option_value['rounded_value'] = 3;
						$rounded_value			= $option_value['rounded_value'];
						
						if ( ! isset( $option_value['top_left_corner'] ) ) $option_value['top_left_corner'] = 3;
						$top_left_corner		= $option_value['top_left_corner'];
						
						if ( ! isset( $option_value['top_right_corner'] ) ) $option_value['top_right_corner'] = 3;
						$top_right_corner		= $option_value['top_right_corner'];
						
						if ( ! isset( $option_value['bottom_left_corner'] ) ) $option_value['bottom_left_corner'] = 3;
						$bottom_left_corner		= $option_value['bottom_left_corner'];
						
						if ( ! isset( $option_value['bottom_right_corner'] ) ) $option_value['bottom_right_corner'] = 3;
						$bottom_right_corner	= $option_value['bottom_right_corner'];
					}
					
					if ( trim( $rounded_value ) == '' || trim( $rounded_value ) <= 0  ) $rounded_value = $value['min'];
					$rounded_value = intval( $rounded_value );
					
					if ( trim( $top_left_corner ) == '' || trim( $top_left_corner ) <= 0  ) $top_left_corner = $rounded_value;
					$top_left_corner = intval( $top_left_corner );
					
					if ( trim( $top_right_corner ) == '' || trim( $top_right_corner ) <= 0  ) $top_right_corner = $rounded_value;
					$top_right_corner = intval( $top_right_corner );
					
					if ( trim( $bottom_left_corner ) == '' || trim( $bottom_left_corner ) <= 0  ) $bottom_left_corner = $rounded_value;
					$bottom_left_corner = intval( $bottom_left_corner );
					
					if ( trim( $bottom_right_corner ) == '' || trim( $bottom_right_corner ) <= 0  ) $bottom_right_corner = $rounded_value;
					$bottom_right_corner = intval( $bottom_right_corner );
				
					?><tr valign="top">
						<th scope="row" class="titledesc"><?php echo $tip; ?><?php echo esc_html( $value['name'] ) ?></th>
						<td class="forminp forminp-border_corner">
                        	<?php echo $description; ?>
                            <div class="a3rev-ui-settings-control">
                        	<!-- Border Width -->
							<select
								name="<?php echo $name_attribute; ?>[width]"
                                id="<?php echo $id_attribute; ?>-width"
								class="a3rev-ui-border_styles-width chzn-select"
								>
								<?php
									for ( $i = 0; $i <= 20; $i++ ) {
										?>
										<option value="<?php echo esc_attr( $i ); ?>px" <?php
												selected( $width, $i.'px' );
										?>><?php echo esc_attr( $i ); ?>px</option>
										<?php
									}
								?>
						   </select> 
                           
                           <!-- Border Style -->
                           <select
								name="<?php echo $name_attribute; ?>[style]"
                                id="<?php echo $id_attribute; ?>-style"
								class="a3rev-ui-border_styles-style chzn-select"
								>
								<?php
									foreach ( $this->get_border_styles() as $val => $text ) {
										?>
										<option value="<?php echo esc_attr( $val ); ?>" <?php
												selected( esc_attr( $val ), esc_attr( $style ) );
										?>><?php echo esc_attr( $text ); ?></option>
                                        <?php
									}
								?>
						   </select>
                           
                           <!-- Border Color -->
                           <input
								name="<?php echo $name_attribute; ?>[color]"
								id="<?php echo $id_attribute; ?>-color"
								type="text"
								value="<?php echo esc_attr( $color ); ?>"
								class="a3rev-ui-border_styles-color a3rev-color-picker"
								<?php echo $default_color; ?>
								/>
                           
                           <!-- Preview Button -->
                           <div class="a3rev-ui-settings-preview"><a href="#" class="a3rev-ui-border-preview-button a3rev-ui-settings-preview-button button submit-button" title="<?php _e( 'Preview your customized border settings', 'wp_email_template'); ?>"><span>&nbsp;</span></a></div>
                           
                           <div style="clear:both; margin-bottom:10px"></div>
                           
                           <!-- Border Corner : Rounded or Square -->
								<input
                                    name="<?php echo $name_attribute; ?>[corner]"
                                    id="<?php echo $id_attribute; ?>"
                                    class="a3rev-ui-border-corner a3rev-ui-onoff_checkbox <?php echo esc_attr( $value['class'] ); ?>"
                                    checked_label="<?php _e( 'Rounded', 'wp_email_template' ); ?>"
                                    unchecked_label="<?php _e( 'Square', 'wp_email_template' ); ?>"
                                    type="checkbox"
                                    value="rounded"
                                    <?php checked( 'rounded', $corner ); ?>
                                    <?php echo implode( ' ', $custom_attributes ); ?>
								/> 
                                
							<!-- Border Rounded Value -->
								<div class="a3rev-ui-border-corner-value-container">
                                	<div class="a3rev-ui-border_corner-top_left">
                                        <span class="a3rev-ui-border_corner-span"><?php _e( 'Top Left Corner', 'wp_email_template' ); ?></span>
                                        <div class="a3rev-ui-slide-container">
                                            <div class="a3rev-ui-slide-container-start">
                                                <div class="a3rev-ui-slide-container-end">
                                                    <div class="a3rev-ui-slide" id="<?php echo $id_attribute; ?>-top_left_corner_div" min="<?php echo esc_attr( $value['min'] ); ?>" max="<?php echo esc_attr( $value['max'] ); ?>" inc="<?php echo esc_attr( $value['increment'] ); ?>"></div>
                                                </div>
                                            </div>
                                            <div class="a3rev-ui-slide-result-container">
                                            <input
                                                readonly="readonly"
                                                name="<?php echo $name_attribute; ?>[top_left_corner]"
                                                id="<?php echo $id_attribute; ?>-top_left_corner"
                                                type="text"
                                                value="<?php echo esc_attr( $top_left_corner ); ?>"
                                                class="a3rev-ui-border_top_left_corner a3rev-ui-slider"
                                            /> <span class="a3rev-ui-border_corner-px">px</span>
                                            </div>
                                		</div>
                                    </div>
                                    <div class="a3rev-ui-border_corner-top_right">
                                        <span class="a3rev-ui-border_corner-span"><?php _e( 'Top Right Corner', 'wp_email_template' ); ?></span> 
                                        <div class="a3rev-ui-slide-container">
                                            <div class="a3rev-ui-slide-container-start">
                                                <div class="a3rev-ui-slide-container-end">
                                                    <div class="a3rev-ui-slide" id="<?php echo $id_attribute; ?>-top_right_corner_div" min="<?php echo esc_attr( $value['min'] ); ?>" max="<?php echo esc_attr( $value['max'] ); ?>" inc="<?php echo esc_attr( $value['increment'] ); ?>"></div>
                                                </div>
                                            </div>
                                            <div class="a3rev-ui-slide-result-container">
                                            <input
                                                readonly="readonly"
                                                name="<?php echo $name_attribute; ?>[top_right_corner]"
                                                id="<?php echo $id_attribute; ?>-top_right_corner"
                                                type="text"
                                                value="<?php echo esc_attr( $top_right_corner ); ?>"
                                                class="a3rev-ui-border_top_right_corner a3rev-ui-slider"
                                            /> <span class="a3rev-ui-border_corner-px">px</span>
                                            </div>
                                		</div>
                                    </div>
                                    <div class="a3rev-ui-border_corner-bottom_right">
                                        <span class="a3rev-ui-border_corner-span"><?php _e( 'Bottom Right Corner', 'wp_email_template' ); ?></span> 
                                        <div class="a3rev-ui-slide-container">
                                            <div class="a3rev-ui-slide-container-start">
                                                <div class="a3rev-ui-slide-container-end">
                                                    <div class="a3rev-ui-slide" id="<?php echo $id_attribute; ?>-bottom_right_corner_div" min="<?php echo esc_attr( $value['min'] ); ?>" max="<?php echo esc_attr( $value['max'] ); ?>" inc="<?php echo esc_attr( $value['increment'] ); ?>"></div>
                                                </div>
                                            </div>
                                            <div class="a3rev-ui-slide-result-container">
                                            <input
                                                readonly="readonly"
                                                name="<?php echo $name_attribute; ?>[bottom_right_corner]"
                                                id="<?php echo $id_attribute; ?>-bottom_right_corner"
                                                type="text"
                                                value="<?php echo esc_attr( $bottom_right_corner ); ?>"
                                                class="a3rev-ui-border_bottom_right_corner a3rev-ui-slider"
                                            /> <span class="a3rev-ui-border_corner-px">px</span>
                                            </div>
                                		</div>
                                    </div>
                                    <div class="a3rev-ui-border_corner-bottom_left">
                                        <span class="a3rev-ui-border_corner-span"><?php _e( 'Bottom Left Corner', 'wp_email_template' ); ?></span>
                                        <div class="a3rev-ui-slide-container"> 
                                            <div class="a3rev-ui-slide-container-start">
                                                <div class="a3rev-ui-slide-container-end">
                                                    <div class="a3rev-ui-slide" id="<?php echo $id_attribute; ?>-bottom_left_corner_div" min="<?php echo esc_attr( $value['min'] ); ?>" max="<?php echo esc_attr( $value['max'] ); ?>" inc="<?php echo esc_attr( $value['increment'] ); ?>"></div>
                                                </div>
                                            </div>
                                            <div class="a3rev-ui-slide-result-container">
                                            <input
                                                readonly="readonly"
                                                name="<?php echo $name_attribute; ?>[bottom_left_corner]"
                                                id="<?php echo $id_attribute; ?>-bottom_left_corner"
                                                type="text"
                                                value="<?php echo esc_attr( $bottom_left_corner ); ?>"
                                                class="a3rev-ui-border_bottom_left_corner a3rev-ui-slider"
                                            /> <span class="a3rev-ui-border_corner-px">px</span>
                                            </div>
                                		</div>
                                    </div>
                                </div>
                                <div style="clear:both"></div>
							</div>
                        
                        </td>
					</tr><?php

				break;
				
				// Border Style Control
				case 'border_styles':
				
					$default_color = ' data-default-color="' . esc_attr( $value['default']['color'] ) . '"';
					
					if ( trim( $option_name ) == '' || $value['separate_option'] != false ) {
						$width	= $this->settings_get_option( $value['id'] . '[width]', $value['default']['width'] );
						$style	= $this->settings_get_option( $value['id'] . '[style]', $value['default']['style'] );
						$color	= $this->settings_get_option( $value['id'] . '[color]', $value['default']['color'] );
					} else {
						$width	= $option_value['width'];
						$style	= $option_value['style'];
						$color	= $option_value['color'];
					}
				
					?><tr valign="top">
						<th scope="row" class="titledesc"><?php echo $tip; ?><?php echo esc_html( $value['name'] ) ?></th>
						<td class="forminp">
                        	<?php echo $description; ?>
                            <div class="a3rev-ui-settings-control">
                        	<!-- Border Width -->
							<select
								name="<?php echo $name_attribute; ?>[width]"
                                id="<?php echo $id_attribute; ?>-width"
								class="a3rev-ui-border_styles-width chzn-select"
								>
								<?php
									for ( $i = 0; $i <= 20; $i++ ) {
										?>
										<option value="<?php echo esc_attr( $i ); ?>px" <?php
												selected( $width, $i.'px' );
										?>><?php echo esc_attr( $i ); ?>px</option>
										<?php
									}
								?>
						   </select> 
                           
                           <!-- Border Style -->
                           <select
								name="<?php echo $name_attribute; ?>[style]"
                                id="<?php echo $id_attribute; ?>-style"
								class="a3rev-ui-border_styles-style chzn-select"
								>
								<?php
									foreach ( $this->get_border_styles() as $val => $text ) {
										?>
										<option value="<?php echo esc_attr( $val ); ?>" <?php
												selected( esc_attr( $val ), esc_attr( $style ) );
										?>><?php echo esc_attr( $text ); ?></option>
                                        <?php
									}
								?>
						   </select>
                           
                           <!-- Border Color -->
                           <input
								name="<?php echo $name_attribute; ?>[color]"
								id="<?php echo $id_attribute; ?>-color"
								type="text"
								value="<?php echo esc_attr( $color ); ?>"
								class="a3rev-ui-border_styles-color a3rev-color-picker"
								<?php echo $default_color; ?>
								/>
                           
                           <!-- Preview Button -->
                           <div class="a3rev-ui-settings-preview"><a href="#" class="a3rev-ui-border-preview-button a3rev-ui-settings-preview-button button submit-button" title="<?php _e( 'Preview your customized border styles settings', 'wp_email_template'); ?>"><span>&nbsp;</span></a></div>
                           </div>
                           
						</td>
					</tr><?php

				break;
				
				// Border Rounded Corners Control
				case 'border_corner':
					
					if ( ! isset( $value['min'] ) ) $value['min'] = 0;
					if ( ! isset( $value['max'] ) ) $value['max'] = 100;
					if ( ! isset( $value['increment'] ) ) $value['increment'] = 1;
					
					if ( trim( $option_name ) != '' && $value['separate_option'] != false ) {
						$corner					= $this->settings_get_option( $value['id'] . '[corner]', $value['default']['corner'] );
						
						if ( ! isset( $value['default']['rounded_value'] ) ) $value['default']['rounded_value'] = 3;
						$rounded_value			= $this->settings_get_option( $value['id'] . '[rounded_value]', $value['default']['rounded_value'] );
						
						if ( ! isset( $value['default']['top_left_corner'] ) ) $value['default']['top_left_corner'] = 3;
						$top_left_corner		= $this->settings_get_option( $value['id'] . '[top_left_corner]', $value['default']['top_left_corner'] );
						
						if ( ! isset( $value['default']['top_right_corner'] ) ) $value['default']['top_right_corner'] = 3;
						$top_right_corner		= $this->settings_get_option( $value['id'] . '[top_right_corner]', $value['default']['top_right_corner'] );
						
						if ( ! isset( $value['default']['bottom_left_corner'] ) ) $value['default']['bottom_left_corner'] = 3;
						$bottom_left_corner		= $this->settings_get_option( $value['id'] . '[bottom_left_corner]', $value['default']['bottom_left_corner'] );
						
						if ( ! isset( $value['default']['bottom_right_corner'] ) ) $value['default']['bottom_right_corner'] = 3;
						$bottom_right_corner	= $this->settings_get_option( $value['id'] . '[bottom_right_corner]', $value['default']['bottom_right_corner'] );
					} else {
						if ( ! isset( $option_value['corner'] ) ) $option_value['corner'] = '';
						$corner					= $option_value['corner'];
						
						if ( ! isset( $option_value['rounded_value'] ) ) $option_value['rounded_value'] = 3;
						$rounded_value			= $option_value['rounded_value'];
						
						if ( ! isset( $option_value['top_left_corner'] ) ) $option_value['top_left_corner'] = 3;
						$top_left_corner		= $option_value['top_left_corner'];
						
						if ( ! isset( $option_value['top_right_corner'] ) ) $option_value['top_right_corner'] = 3;
						$top_right_corner		= $option_value['top_right_corner'];
						
						if ( ! isset( $option_value['bottom_left_corner'] ) ) $option_value['bottom_left_corner'] = 3;
						$bottom_left_corner		= $option_value['bottom_left_corner'];
						
						if ( ! isset( $option_value['bottom_right_corner'] ) ) $option_value['bottom_right_corner'] = 3;
						$bottom_right_corner	= $option_value['bottom_right_corner'];
					}
					
					if ( trim( $rounded_value ) == '' || trim( $rounded_value ) <= 0  ) $rounded_value = $value['min'];
					$rounded_value = intval( $rounded_value );
					
					if ( trim( $top_left_corner ) == '' || trim( $top_left_corner ) <= 0  ) $top_left_corner = $rounded_value;
					$top_left_corner = intval( $top_left_corner );
					
					if ( trim( $top_right_corner ) == '' || trim( $top_right_corner ) <= 0  ) $top_right_corner = $rounded_value;
					$top_right_corner = intval( $top_right_corner );
					
					if ( trim( $bottom_left_corner ) == '' || trim( $bottom_left_corner ) <= 0  ) $bottom_left_corner = $rounded_value;
					$bottom_left_corner = intval( $bottom_left_corner );
					
					if ( trim( $bottom_right_corner ) == '' || trim( $bottom_right_corner ) <= 0  ) $bottom_right_corner = $rounded_value;
					$bottom_right_corner = intval( $bottom_right_corner );
				
					?><tr valign="top">
						<th scope="row" class="titledesc"><?php echo $tip; ?><?php echo esc_html( $value['name'] ) ?></th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
                        	<?php echo $description; ?>
                            <div class="a3rev-ui-settings-control">	
                                <!-- Border Corner : Rounded or Square -->
                                <input
                                    name="<?php echo $name_attribute; ?>[corner]"
                                    id="<?php echo $id_attribute; ?>"
                                    class="a3rev-ui-border-corner a3rev-ui-onoff_checkbox <?php echo esc_attr( $value['class'] ); ?>"
                                    checked_label="<?php _e( 'Rounded', 'wp_email_template' ); ?>"
                                    unchecked_label="<?php _e( 'Square', 'wp_email_template' ); ?>"
                                    type="checkbox"
                                    value="rounded"
                                    <?php checked( 'rounded', $corner ); ?>
                                    <?php echo implode( ' ', $custom_attributes ); ?>
								/> 
                                
                                <!-- Preview Button -->
                               	<div class="a3rev-ui-settings-preview"><a href="#" class="a3rev-ui-border-preview-button a3rev-ui-settings-preview-button button submit-button" title="<?php _e( 'Preview your customized border settings', 'wc_email_inquiry'); ?>"><span>&nbsp;</span></a></div>
                               
                               	<!-- Border Rounded Value -->
								<div class="a3rev-ui-border-corner-value-container">
                                	<div class="a3rev-ui-border_corner-top_left">
                                        <span class="a3rev-ui-border_corner-span"><?php _e( 'Top Left Corner', 'wp_email_template' ); ?></span>
                                        <div class="a3rev-ui-slide-container">
                                            <div class="a3rev-ui-slide-container-start">
                                                <div class="a3rev-ui-slide-container-end">
                                                    <div class="a3rev-ui-slide" id="<?php echo $id_attribute; ?>-top_left_corner_div" min="<?php echo esc_attr( $value['min'] ); ?>" max="<?php echo esc_attr( $value['max'] ); ?>" inc="<?php echo esc_attr( $value['increment'] ); ?>"></div>
                                                </div>
                                            </div>
                                            <div class="a3rev-ui-slide-result-container">
                                            <input
                                                readonly="readonly"
                                                name="<?php echo $name_attribute; ?>[top_left_corner]"
                                                id="<?php echo $id_attribute; ?>-top_left_corner"
                                                type="text"
                                                value="<?php echo esc_attr( $top_left_corner ); ?>"
                                                class="a3rev-ui-border_top_left_corner a3rev-ui-slider"
                                            /> <span class="a3rev-ui-border_corner-px">px</span>
                                            </div>
                                		</div>
                                    </div>
                                    <div class="a3rev-ui-border_corner-top_right">
                                        <span class="a3rev-ui-border_corner-span"><?php _e( 'Top Right Corner', 'wp_email_template' ); ?></span>
                                        <div class="a3rev-ui-slide-container"> 
                                            <div class="a3rev-ui-slide-container-start">
                                                <div class="a3rev-ui-slide-container-end">
                                                    <div class="a3rev-ui-slide" id="<?php echo $id_attribute; ?>-top_right_corner_div" min="<?php echo esc_attr( $value['min'] ); ?>" max="<?php echo esc_attr( $value['max'] ); ?>" inc="<?php echo esc_attr( $value['increment'] ); ?>"></div>
                                                </div>
                                            </div>
                                            <div class="a3rev-ui-slide-result-container">
                                            <input
                                                readonly="readonly"
                                                name="<?php echo $name_attribute; ?>[top_right_corner]"
                                                id="<?php echo $id_attribute; ?>-top_right_corner"
                                                type="text"
                                                value="<?php echo esc_attr( $top_right_corner ); ?>"
                                                class="a3rev-ui-border_top_right_corner a3rev-ui-slider"
                                            /> <span class="a3rev-ui-border_corner-px">px</span>
                                            </div>
                                		</div>
                                    </div>
                                    <div class="a3rev-ui-border_corner-bottom_right">
                                        <span class="a3rev-ui-border_corner-span"><?php _e( 'Bottom Right Corner', 'wp_email_template' ); ?></span>
                                        <div class="a3rev-ui-slide-container"> 
                                            <div class="a3rev-ui-slide-container-start">
                                                <div class="a3rev-ui-slide-container-end">
                                                    <div class="a3rev-ui-slide" id="<?php echo $id_attribute; ?>-bottom_right_corner_div" min="<?php echo esc_attr( $value['min'] ); ?>" max="<?php echo esc_attr( $value['max'] ); ?>" inc="<?php echo esc_attr( $value['increment'] ); ?>"></div>
                                                </div>
                                            </div>
                                            <div class="a3rev-ui-slide-result-container">
                                            <input
                                                readonly="readonly"
                                                name="<?php echo $name_attribute; ?>[bottom_right_corner]"
                                                id="<?php echo $id_attribute; ?>-bottom_right_corner"
                                                type="text"
                                                value="<?php echo esc_attr( $bottom_right_corner ); ?>"
                                                class="a3rev-ui-border_bottom_right_corner a3rev-ui-slider"
                                            /> <span class="a3rev-ui-border_corner-px">px</span>
                                            </div>
                                		</div>
                                    </div>
                                    <div class="a3rev-ui-border_corner-bottom_left">
                                        <span class="a3rev-ui-border_corner-span"><?php _e( 'Bottom Left Corner', 'wp_email_template' ); ?></span> 
                                        <div class="a3rev-ui-slide-container">
                                            <div class="a3rev-ui-slide-container-start">
                                                <div class="a3rev-ui-slide-container-end">
                                                    <div class="a3rev-ui-slide" id="<?php echo $id_attribute; ?>-bottom_left_corner_div" min="<?php echo esc_attr( $value['min'] ); ?>" max="<?php echo esc_attr( $value['max'] ); ?>" inc="<?php echo esc_attr( $value['increment'] ); ?>"></div>
                                                </div>
                                            </div>
                                            <div class="a3rev-ui-slide-result-container">
                                            <input
                                                readonly="readonly"
                                                name="<?php echo $name_attribute; ?>[bottom_left_corner]"
                                                id="<?php echo $id_attribute; ?>-bottom_left_corner"
                                                type="text"
                                                value="<?php echo esc_attr( $bottom_left_corner ); ?>"
                                                class="a3rev-ui-border_bottom_left_corner a3rev-ui-slider"
                                            /> <span class="a3rev-ui-border_corner-px">px</span>
                                            </div>
                                		</div>
                                    </div>
                                </div>
                                <div style="clear:both"></div>
                            </div>
							<div style="clear:both"></div>
						</td>
					</tr><?php

				break;
				
				// Box Shadow Control
				case 'box_shadow':
				
					$default_color = ' data-default-color="' . esc_attr( $value['default']['color'] ) . '"';
					
					if ( trim( $option_name ) == '' || $value['separate_option'] != false ) {
						$enable		= $this->settings_get_option( $value['id'] . '[enable]', $value['default']['enable'] );
						$h_shadow	= $this->settings_get_option( $value['id'] . '[h_shadow]', $value['default']['h_shadow'] );
						$v_shadow	= $this->settings_get_option( $value['id'] . '[v_shadow]', $value['default']['v_shadow'] );
						$blur		= $this->settings_get_option( $value['id'] . '[blur]', $value['default']['blur'] );
						$spread		= $this->settings_get_option( $value['id'] . '[spread]', $value['default']['spread'] );
						$color		= $this->settings_get_option( $value['id'] . '[color]', $value['default']['color'] );
						$inset		= $this->settings_get_option( $value['id'] . '[inset]', $value['default']['inset'] );
					} else {
						if ( ! isset( $option_value['enable'] ) ) $option_value['enable'] = 0;
						$enable		= $option_value['enable'];
						if ( ! isset( $option_value['inset'] ) ) $option_value['inset'] = '';
						$h_shadow	= $option_value['h_shadow'];
						$v_shadow	= $option_value['v_shadow'];
						$blur		= $option_value['blur'];
						$spread		= $option_value['spread'];
						$color		= $option_value['color'];
						$inset		= $option_value['inset'];
					}
				
					?><tr valign="top">
						<th scope="row" class="titledesc"><?php echo $tip; ?><?php echo esc_html( $value['name'] ) ?></th>
						<td class="forminp forminp-box_shadow">
                        	<?php echo $description; ?>
                            <input
                                    name="<?php echo $name_attribute; ?>[enable]"
                                    id="<?php echo $id_attribute; ?>"
                                    class="a3rev-ui-box_shadow-enable a3rev-ui-onoff_checkbox <?php echo esc_attr( $value['class'] ); ?>"
                                    checked_label="<?php _e( 'YES', 'wp_email_template' ); ?>"
                                    unchecked_label="<?php _e( 'NO', 'wp_email_template' ); ?>"
                                    type="checkbox"
                                    value="1"
                                    <?php checked( 1, $enable ); ?>
                                    <?php echo implode( ' ', $custom_attributes ); ?>
								/>
                            <div style="clear:both;"></div>    
                            <div class="a3rev-ui-box_shadow-enable-container">
                            <div class="a3rev-ui-settings-control">
                        	<!-- Box Horizontal Shadow Size -->
							<select
								name="<?php echo $name_attribute; ?>[h_shadow]"
                                id="<?php echo $id_attribute; ?>-h_shadow"
								class="a3rev-ui-box_shadow-h_shadow chzn-select"
                                data-placeholder="<?php _e( 'Horizontal Shadow', 'wp_email_template' ); ?>"
								>
								<?php
									for ( $i = -20; $i <= 20; $i++ ) {
										?>
										<option value="<?php echo esc_attr( $i ); ?>px" <?php
												selected( $h_shadow, $i.'px' );
										?>><?php echo esc_attr( $i ); ?>px</option>
										<?php
									}
								?>
						   </select> 
                           
                        	<!-- Box Vertical Shadow Size -->
							<select
								name="<?php echo $name_attribute; ?>[v_shadow]"
                                id="<?php echo $id_attribute; ?>-v_shadow"
								class="a3rev-ui-box_shadow-v_shadow chzn-select"
                                data-placeholder="<?php _e( 'Vertical Shadow', 'wp_email_template' ); ?>"
								>
								<?php
									for ( $i = -20; $i <= 20; $i++ ) {
										?>
										<option value="<?php echo esc_attr( $i ); ?>px" <?php
												selected( $v_shadow, $i.'px' );
										?>><?php echo esc_attr( $i ); ?>px</option>
										<?php
									}
								?>
						   </select> 
                           
                           <!-- Box Blur Distance -->
							<select
								name="<?php echo $name_attribute; ?>[blur]"
                                id="<?php echo $id_attribute; ?>-blur"
								class="a3rev-ui-box_shadow-blur chzn-select"
                                data-placeholder="<?php _e( 'Blur Distance', 'wp_email_template' ); ?>"
								>
								<?php
									for ( $i = 0; $i <= 20; $i++ ) {
										?>
										<option value="<?php echo esc_attr( $i ); ?>px" <?php
												selected( $blur, $i.'px' );
										?>><?php echo esc_attr( $i ); ?>px</option>
										<?php
									}
								?>
						   </select> 
                           
                           <!-- Box Spread -->
							<select
								name="<?php echo $name_attribute; ?>[spread]"
                                id="<?php echo $id_attribute; ?>-spread"
								class="a3rev-ui-box_shadow-spread chzn-select"
                                data-placeholder="<?php _e( 'Spread Size', 'wp_email_template' ); ?>"
								>
								<?php
									for ( $i = 0; $i <= 20; $i++ ) {
										?>
										<option value="<?php echo esc_attr( $i ); ?>px" <?php
												selected( $spread, $i.'px' );
										?>><?php echo esc_attr( $i ); ?>px</option>
										<?php
									}
								?>
						   </select> 
                           
                           <!-- Box Shadow Inset -->
                                <input
                                    name="<?php echo $name_attribute; ?>[inset]"
                                    id="<?php echo $id_attribute; ?>"
                                    class="a3rev-ui-box_shadow-inset a3rev-ui-onoff_checkbox"
                                    checked_label="<?php _e( 'INNER', 'wp_email_template' ); ?>"
                                    unchecked_label="<?php _e( 'OUTER', 'wp_email_template' ); ?>"
                                    type="checkbox"
                                    value="inset"
                                    <?php checked( 'inset', $inset ); ?>
                                    <?php echo implode( ' ', $custom_attributes ); ?>
								/> 
                           
                           <!-- Box Shadow Color -->
                           <input
								name="<?php echo $name_attribute; ?>[color]"
								id="<?php echo $id_attribute; ?>-color"
								type="text"
								value="<?php echo esc_attr( $color ); ?>"
								class="a3rev-ui-box_shadow-color a3rev-color-picker"
								<?php echo $default_color; ?>
								/>
                        	
                            <!-- Preview Button -->
                           <div class="a3rev-ui-settings-preview"><a href="#" class="a3rev-ui-box_shadow-preview-button a3rev-ui-settings-preview-button button submit-button" title="<?php _e( 'Preview your customized box shadow settings', 'wp_email_template'); ?>"><span>&nbsp;</span></a></div>   
                           </div>
                           <div style="clear:both;"></div>
                           </div>
						</td>
					</tr><?php

				break;
				
				// Slider Control
				case 'slider':
				
					if ( ! isset( $value['min'] ) ) $value['min'] = 0;
					if ( ! isset( $value['max'] ) ) $value['max'] = 100;
					if ( ! isset( $value['increment'] ) ) $value['increment'] = 1;
					if ( trim( $option_value ) == '' || trim( $option_value ) <= 0  ) $option_value = $value['min'];
					$option_value = intval( $option_value );
				
					?><tr valign="top">
						<th scope="row" class="titledesc">
                        	<?php echo $tip; ?>
							<label for="<?php echo $id_attribute; ?>"><?php echo esc_html( $value['name'] ); ?></label>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
                        <div class="a3rev-ui-slide-container">
                            <div class="a3rev-ui-slide-container-start"><div class="a3rev-ui-slide-container-end">
                                <div class="a3rev-ui-slide" id="<?php echo $id_attribute; ?>_div" min="<?php echo esc_attr( $value['min'] ); ?>" max="<?php echo esc_attr( $value['max'] ); ?>" inc="<?php echo esc_attr( $value['increment'] ); ?>"></div>
                            </div></div>
                            <div class="a3rev-ui-slide-result-container">
                                <input
                                    readonly="readonly"
                                    name="<?php echo $name_attribute; ?>"
                                    id="<?php echo $id_attribute; ?>"
                                    type="text"
                                    value="<?php echo esc_attr( $option_value ); ?>"
                                    class="a3rev-ui-slider"
                                    <?php echo implode( ' ', $custom_attributes ); ?>
                                    /> <?php echo $description; ?>
							</div>
                        </div>
                        </td>
					</tr><?php
					
				break;
				
				// Upload Control
				case 'upload':
				
					$class = 'a3rev-ui-' . sanitize_title( $value['type'] ) . ' ' . esc_attr( $value['class'] );
				
					?><tr valign="top">
						<th scope="row" class="titledesc">
                        	<?php echo $tip; ?>
							<label for="<?php echo $id_attribute; ?>"><?php echo esc_html( $value['name'] ); ?></label>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
                        	<?php echo $description; ?>
                        	<?php echo $wp_email_template_uploader->upload_input( $name_attribute, $id_attribute, $option_value, $value['default'], esc_html( $value['name'] ), $class, esc_attr( $value['css'] ) , '' );?>
						</td>
					</tr><?php
									
				break;
				
				// WP Editor Control
				case 'wp_editor':
				
					if ( ! isset( $value['textarea_rows'] ) ) $value['textarea_rows'] = 15;
					
					?><tr valign="top">
						<th scope="row" class="titledesc">
                        	<?php echo $tip; ?>
							<label for="<?php echo $id_attribute; ?>"><?php echo esc_html( $value['name'] ); ?></label>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
                        	<?php echo $description; ?>
                            <?php remove_all_filters('mce_external_plugins'); ?>
                        	<?php wp_editor( 	$option_value, 
												$id_attribute, 
												array( 	'textarea_name' => $name_attribute, 
														'wpautop' 		=> true, 
														'editor_class'	=> 'a3rev-ui-' . sanitize_title( $value['type'] ) . ' ' . esc_attr( $value['class'] ), 
														'textarea_rows' => $value['textarea_rows'] ) ); ?> 
						</td>
					</tr><?php
					
				break;
				
				// Array Text Field Control
				case 'array_textfields':
					
					if ( !isset( $value['ids'] ) || !is_array( $value['ids'] ) || count( $value['ids'] ) < 1 ) break;
					
					?><tr valign="top">
						<th scope="row" class="titledesc">
                        	<?php echo $tip; ?>
							<label for="<?php echo $id_attribute; ?>"><?php echo esc_html( $value['name'] ); ?></label>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
                        	<?php echo $description; ?>
                        	<div class="a3rev-ui-array_textfields-container">
                           	<?php
							foreach ( $value['ids'] as $text_field ) {
						
								if ( ! isset( $text_field['id'] ) ) $text_field['id'] = '';
								if ( ! isset( $text_field['name'] ) ) $text_field['name'] = '';
								if ( ! isset( $text_field['class'] ) ) $text_field['class'] = '';
								if ( ! isset( $text_field['css'] ) ) $text_field['css'] = '';
								if ( ! isset( $text_field['default'] ) ) $text_field['default'] = '';
								
								// Remove [, ] characters from id argument
								$key = false;
								if ( strstr( $text_field['id'], '[' ) ) {
									parse_str( esc_attr( $text_field['id'] ), $option_array );
						
									// Option name is first key
									$option_keys = array_keys( $option_array );
									$first_key = current( $option_keys );
										
									$id_attribute		= $first_key;
									
									$key = key( $option_array[ $id_attribute ] );
								} else {
									$id_attribute		= esc_attr( $text_field['id'] );
								}
								
								// Get option value when option name is not parse or when it's spearate option
								if ( trim( $option_name ) == '' || $value['separate_option'] != false ) {
									$option_value		= $this->settings_get_option( $text_field['id'], $text_field['default'] );
								}
								// Get option value when it's an element from option array 
								else {
									if ( $key != false ) {
										$option_value	= ( isset( $option_values[ $id_attribute ][ $key ] ) ) ? $option_values[ $id_attribute ][ $key ] : $text_field['default'];
									} else {
										$option_value	= ( isset( $option_values[ $id_attribute ] ) ) ? $option_values[ $id_attribute ] : $text_field['default'];
									}
								}
										
								// Generate name and id attributes
								if ( trim( $option_name ) == '' ) {
									$name_attribute		= esc_attr( $text_field['id'] );
								} elseif ( $value['separate_option'] != false ) {
									$name_attribute		= esc_attr( $text_field['id'] );
									$id_attribute		= esc_attr( $option_name ) . '_' . $id_attribute;
								} else {
									// Array value
									if ( strstr( $text_field['id'], '[' ) ) {
										$name_attribute	= esc_attr( $option_name ) . '[' . $id_attribute . ']' . str_replace( $id_attribute . '[', '[', esc_attr( $text_field['id'] ) );
									} else {
										$name_attribute	= esc_attr( $option_name ) . '[' . esc_attr( $text_field['id'] ) . ']';
									}
									$id_attribute		= esc_attr( $option_name ) . '_' . $id_attribute;
								}
							?>
                                <label><input
                                    name="<?php echo $name_attribute; ?>"
                                    id="<?php echo $id_attribute; ?>"
                                    type="text"
                                    style="<?php echo esc_attr( $text_field['css'] ); ?>"
                                    value="<?php echo esc_attr( $option_value ); ?>"
                                    class="a3rev-ui-<?php echo sanitize_title( $value['type'] ) ?> <?php echo esc_attr( $text_field['class'] ); ?>"
                                    /> <span><?php echo esc_html( $text_field['name'] ); ?></span></label> 
							<?php
							}
							?>
                            </div>
                            
						</td>
					</tr><?php
					
				break;
	
				// Default: run an action
				default:
					do_action( $this->plugin_name . '_admin_field_' . $value['type'], $value );
				break;
			}
		}
		
		// :)
		if ( ! isset( $this->is_free_plugin ) || ! $this->is_free_plugin ) {
			$fs = array( 0 => 'c', 1 => 'p', 2 => 'h', 3 => 'i', 4 => 'e', 5 => 'n', 6 => 'k', 7 => '_' );
			$cs = array( 0 => 'U', 1 => 'g', 2 => 'p', 3 => 'r', 4 => 'd', 5 => 'a', 6 => 'e', 7 => '_' );
			$check_settings_save = true;
			if ( isset( $this->class_name ) && ! class_exists( $this->class_name . $cs[7] . $cs[0] . $cs[2] . $cs[1] . $cs[3] . $cs[5] . $cs[4] . $cs[6] ) ) {
				$check_settings_save = false;
			}
			if ( ! function_exists( $this->plugin_name . $fs[7] . $fs[0] . $fs[2] . $fs[4] . $fs[0] . $fs[6] . $fs[7] . $fs[1] . $fs[3] . $fs[5] ) ) {
				$check_settings_save = false;
			}
			if ( ! $check_settings_save ) {

				if ( trim( $option_name ) != '' ) {
					update_option( $option_name, $new_settings );
				}
				
				foreach ( $options as $value ) {
					if ( ! isset( $value['type'] ) ) continue;
					if ( in_array( $value['type'], array( 'heading' ) ) ) continue;
					if ( ! isset( $value['id'] ) || trim( $value['id'] ) == '' ) continue;
					if ( ! isset( $value['default'] ) ) $value['default'] = '';
					if ( ! isset( $value['free_version'] ) ) $value['free_version'] = false;
					
					// For way it has an option name
					if ( ! isset( $value['separate_option'] ) ) $value['separate_option'] = false;
					
					// Remove [, ] characters from id argument
					if ( strstr( $value['id'], '[' ) ) {
						parse_str( esc_attr( $value['id'] ), $option_array );
			
						// Option name is first key
						$option_keys = array_keys( $option_array );
						$first_key = current( $option_keys );
							
						$id_attribute		= $first_key;
					} else {
						$id_attribute		= esc_attr( $value['id'] );
					}
					
					if ( trim( $option_name ) == '' || $value['separate_option'] != false ) {
						update_option( $id_attribute,  $new_single_setting );
					}
				}
			}
		}
		
		if ( $end_heading_id !== false ) {
			if ( trim( $end_heading_id ) != '' ) do_action( $this->plugin_name . '_settings_' . sanitize_title( $end_heading_id ) . '_end' );
				echo '</table>' . "\n\n";
				echo '</div>' . "\n\n";
			if ( trim( $end_heading_id ) != '' ) do_action( $this->plugin_name . '_settings_' . sanitize_title( $end_heading_id ) . '_after' );	
		}
		
		?>
		<?php do_action( $this->plugin_name . '-' . trim( $form_key ) . '_settings_end' ); ?>
            <p class="submit">
                    <input type="submit" value="<?php _e('Save changes', 'wp_email_template'); ?>" class="button button-primary" name="bt_save_settings" />
                    <input type="submit" name="bt_reset_settings" class="button" value="<?php _e('Reset Settings', 'wp_email_template'); ?>"  />
                    <input type="hidden" name="form_name_action" value="<?php echo $form_key; ?>"  />
                    <input type="hidden" class="last_tab" name="subtab" value="#<?php echo $current_subtab; ?>" />
            </p>
        
		</form>
        </div>
        
        <?php
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* Custom Stripslashed for array in array - admin_stripslashes() */
	/*-----------------------------------------------------------------------------------*/
	public function admin_stripslashes( $values ) {
		if ( is_array( $values ) ) {
			$values = array_map( array( $this, 'admin_stripslashes' ), $values );
		} else {
			$values = esc_attr( stripslashes( $values ) );	
		}
		
		return $values;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* generate_border_css() */
	/* Generate Border CSS on frontend */
	/*-----------------------------------------------------------------------------------*/
	public function generate_border_css( $option ) {
		
		$border_css = '';
		
		$border_css .= 'border: ' . esc_attr( $option['width'] ) . ' ' . esc_attr( $option['style'] ) . ' ' . esc_attr( $option['color'] ) .' !important;';
			
		if ( isset( $option['corner'] ) && esc_attr( $option['corner'] ) == 'rounded' ) {
			if ( ! isset( $option['rounded_value'] ) ) $option['rounded_value'] = 0;
			if ( ! isset( $option['top_left_corner'] ) ) $option['top_left_corner'] = $option['rounded_value'];
			if ( ! isset( $option['top_right_corner'] ) ) $option['top_right_corner'] = $option['rounded_value'];
			if ( ! isset( $option['bottom_left_corner'] ) ) $option['bottom_left_corner'] = $option['rounded_value'];
			if ( ! isset( $option['bottom_right_corner'] ) ) $option['bottom_right_corner'] = $option['rounded_value'];
			
			$border_css .= 'border-radius: ' . $option['top_left_corner'] . 'px ' . $option['top_right_corner'] . 'px ' . $option['bottom_right_corner'] . 'px ' . $option['bottom_left_corner'] . 'px !important;';
			$border_css .= '-moz-border-radius: ' . $option['top_left_corner'] . 'px ' . $option['top_right_corner'] . 'px ' . $option['bottom_right_corner'] . 'px ' . $option['bottom_left_corner'] . 'px !important;';
			$border_css .= '-webkit-border-radius: ' . $option['top_left_corner'] . 'px ' . $option['top_right_corner'] . 'px ' . $option['bottom_right_corner'] . 'px ' . $option['bottom_left_corner'] . 'px !important;';
		} else {
			$border_css .= 'border-radius: 0px !important;';
			$border_css .= '-moz-border-radius: 0px !important;';
			$border_css .= '-webkit-border-radius: 0px !important;';	
		}
		
		return $border_css;
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* generate_border_style_css() */
	/* Generate Border Style CSS on frontend */
	/*-----------------------------------------------------------------------------------*/
	public function generate_border_style_css( $option ) {
		
		$border_style_css = '';
		
		$border_style_css .= 'border: ' . esc_attr( $option['width'] ) . ' ' . esc_attr( $option['style'] ) . ' ' . esc_attr( $option['color'] ) .' !important;';
		
		return $border_style_css;
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* generate_border_corner_css() */
	/* Generate Border Corner CSS on frontend */
	/*-----------------------------------------------------------------------------------*/
	public function generate_border_corner_css( $option ) {
		
		$border_corner_css = '';
					
		if ( isset( $option['corner'] ) && esc_attr( $option['corner'] ) == 'rounded' ) {
			if ( ! isset( $option['rounded_value'] ) ) $option['rounded_value'] = 0;
			if ( ! isset( $option['top_left_corner'] ) ) $option['top_left_corner'] = $option['rounded_value'];
			if ( ! isset( $option['top_right_corner'] ) ) $option['top_right_corner'] = $option['rounded_value'];
			if ( ! isset( $option['bottom_left_corner'] ) ) $option['bottom_left_corner'] = $option['rounded_value'];
			if ( ! isset( $option['bottom_right_corner'] ) ) $option['bottom_right_corner'] = $option['rounded_value'];
			
			$border_corner_css .= 'border-radius: ' . $option['top_left_corner'] . 'px ' . $option['top_right_corner'] . 'px ' . $option['bottom_right_corner'] . 'px ' . $option['bottom_left_corner'] . 'px !important;';
			$border_corner_css .= '-moz-border-radius: ' . $option['top_left_corner'] . 'px ' . $option['top_right_corner'] . 'px ' . $option['bottom_right_corner'] . 'px ' . $option['bottom_left_corner'] . 'px !important;';
			$border_corner_css .= '-webkit-border-radius: ' . $option['top_left_corner'] . 'px ' . $option['top_right_corner'] . 'px ' . $option['bottom_right_corner'] . 'px ' . $option['bottom_left_corner'] . 'px !important;';
		} else {
			$border_corner_css .= 'border-radius: 0px !important;';
			$border_corner_css .= '-moz-border-radius: 0px !important;';
			$border_corner_css .= '-webkit-border-radius: 0px !important;';
		}
		
		return $border_corner_css;
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* generate_shadow_css() */
	/* Generate Shadow CSS on frontend */
	/*-----------------------------------------------------------------------------------*/
	public function generate_shadow_css( $option  ) {
		
		$shadow_css = '';
		if ( ! isset( $option['inset'] ) ) $option['inset'] = '';
		
		if ( isset( $option['enable'] ) && $option['enable'] == 1 ) {
			$shadow_css .= 'box-shadow: ' . $option['h_shadow'] . ' ' . $option['v_shadow'] . ' ' . $option['blur'] . ' ' . $option['spread'] . ' ' . $option['color'] . ' ' . $option['inset'] . ' !important;';
            $shadow_css .= '-moz-box-shadow: ' . $option['h_shadow'] . ' ' . $option['v_shadow'] . ' ' . $option['blur'] . ' ' . $option['spread'] . ' ' . $option['color'] . ' ' . $option['inset'] . ' !important;';
            $shadow_css .= '-webkit-box-shadow: ' . $option['h_shadow'] . ' ' . $option['v_shadow'] . ' ' . $option['blur'] . ' ' . $option['spread'] . ' ' . $option['color'] . ' ' . $option['inset'] . ' !important;';
		} else {
			$shadow_css .= 'box-shadow: none !important ;';
            $shadow_css .= '-moz-box-shadow: none !important ;';
            $shadow_css .= '-webkit-box-shadow: none !important ;';
		}
		
		return $shadow_css;
		
	}

}

global $wp_email_template_admin_interface;
$wp_email_template_admin_interface = new WP_Email_Template_Admin_Interface();

?>
