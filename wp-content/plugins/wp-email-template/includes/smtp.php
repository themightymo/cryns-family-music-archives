<?php
/**
 * WP Email Template SMTP Class
 *
 * Table Of Contents
 *
 * phpmailer_init()
 * godaddy_phpmailer_init()
 */
class WP_Email_Template_SMTP_Class
{
	public $smtp_host = '';
	public $smtp_port = 25;
	public $smtp_encrypt_type = 'none';
	public $enable_smtp_authentication = 'yes';
	public $smtp_username = '';
	public $smtp_password = '';
	
	public function phpmailer_init( $phpmailer ) {
		// Start filter $phpmailer of wordpress
		
		// Validate smtp host is not blank
		if ( $this->smtp_host == '' ) return;
		
		$phpmailer->Mailer = 'smtp';
		
		// Set the SMTPSecure value
		$phpmailer->SMTPSecure = ( $this->smtp_encrypt_type == 'none' ) ? '' : $this->smtp_encrypt_type;
					
		$phpmailer->Host = $this->smtp_host;
		
		if ( $this->smtp_port == '' ) $this->smtp_port = 25;
		$phpmailer->Port = $this->smtp_port;
		
		$phpmailer->From     = apply_filters( 'wp_mail_from'     , get_option('admin_email') );
		$phpmailer->FromName = apply_filters( 'wp_mail_from_name', get_option('blogname')  );
					
		// If SMTP Authentication is enable
		if ( $this->enable_smtp_authentication == 'yes' ) {
			$phpmailer->SMTPAuth = TRUE;
			$phpmailer->Username = $this->smtp_username;
			$phpmailer->Password = $this->smtp_password;
			//$phpmailer->From     = $this->smtp_username;
		}
		
		// Support for other plugin can filter $phpmailer again
		$phpmailer = apply_filters( 'wp_email_template_phpmailer_custom', $phpmailer );
		
	}
	
	public function godaddy_phpmailer_init( $phpmailer ) {
		// Start filter $phpmailer of wordpress
		
		// Validate smtp host is not blank
		if ( $this->smtp_host == '' ) return;
		
		// set it's SMTP mail
		$phpmailer->IsSMTP();
					
		$phpmailer->Host = $this->smtp_host;
		
		if ( $this->smtp_port == '' ) $this->smtp_port = 25;
		$phpmailer->Port = $this->smtp_port;
		
		$phpmailer->FromName = apply_filters( 'wp_mail_from_name', get_option('blogname')  );
					
		// Support for other plugin can filter $phpmailer again
		$phpmailer = apply_filters( 'wp_email_template_phpmailer_custom', $phpmailer );
		
	}
}

global $wp_et_smtp_class;
$wp_et_smtp_class = new WP_Email_Template_SMTP_Class();
?>