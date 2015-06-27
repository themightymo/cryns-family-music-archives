<?php
/**
 * WP Email Template Hook Filter
 *
 * Table Of Contents
 *
 * woo_email_header_marker_start()
 * woo_email_header_marker_end()
 * woo_email_footer_marker_start()
 * woo_email_footer_marker_end()
 * preview_wp_email_template()
 * set_content_type()
 * change_wp_mail()
 * a3_wp_admin()
 * admin_sidebar_menu_css()
 * plugin_extra_links()
 * settings_plugin_links()
 */
class WP_Email_Template_Hook_Filter
{

	public static function woo_email_header_marker_start($email_heading='') {
		global $wp_email_template_general;

		if (isset($wp_email_template_general['apply_for_woo_emails']) && $wp_email_template_general['apply_for_woo_emails'] == 'yes') {
			ob_start();
			echo '<!--WOO_EMAIL_TEMPLATE_HEADER_START-->';
		}
	}

	public static function woo_email_header_marker_end($email_heading='') {
		global $wp_email_template_general;

		if (isset($wp_email_template_general['apply_for_woo_emails']) && $wp_email_template_general['apply_for_woo_emails'] == 'yes') {
			echo '<!--WOO_EMAIL_TEMPLATE_HEADER_END-->';
			ob_get_clean();
			$header = WP_Email_Template_Functions::email_header($email_heading);

			if (isset($_REQUEST['preview_woocommerce_mail']) && $_REQUEST['preview_woocommerce_mail'] == 'true') {
				$template_notice = WP_Email_Template_Functions::apply_email_template_notice( __('Attention! You have selected to apply your WP Email Template to all WooCommerce Emails. Go to Settings in your WordPress admin sidebar > Email Template to customize this template or to reactivate the WooCommerce Email Template.', 'wp_email_template') );
				$header = str_replace('<!--EMAIL_TEMPLATE_NOTICE-->', $template_notice, $header);
			}


			echo $header;
		}
	}

	public static function woo_email_footer_marker_start() {
		global $wp_email_template_general;

		if (isset($wp_email_template_general['apply_for_woo_emails']) && $wp_email_template_general['apply_for_woo_emails'] == 'yes') {
			ob_start();
			echo '<!--WOO_EMAIL_TEMPLATE_FOOTER_START-->';
		}
	}

	public static function woo_email_footer_marker_end() {
		global $wp_email_template_general;

		if (isset($wp_email_template_general['apply_for_woo_emails']) && $wp_email_template_general['apply_for_woo_emails'] == 'yes') {
			echo '<!--WOO_EMAIL_TEMPLATE_FOOTER_END-->';
			ob_get_clean();
			echo WP_Email_Template_Functions::email_footer();
		}
		echo '<!--NO_USE_EMAIL_TEMPLATE-->';
	}

	public static function style_inline_h1_tag( $styles ) {
		global $wp_email_template_fonts_face, $wp_email_template_general;

		if (isset($wp_email_template_general['apply_for_woo_emails']) && $wp_email_template_general['apply_for_woo_emails'] == 'yes') {

			$styles = array();
			$h1_font     = 'font:italic 26px Century Gothic, sans-serif !important; color: #000000 !important;';
			$styles['font'] = trim( $h1_font );

		}

		return $styles;
	}

	public static function style_inline_h2_tag( $styles ) {
		global $wp_email_template_fonts_face, $wp_email_template_general;

		if (isset($wp_email_template_general['apply_for_woo_emails']) && $wp_email_template_general['apply_for_woo_emails'] == 'yes') {

			$styles = array();
			$h2_font     = 'font:italic 20px Century Gothic, sans-serif !important; color: #000000 !important;';
			$styles['font'] = trim( $h2_font );

		}

		return $styles;
	}

	public static function style_inline_h3_tag( $styles ) {
		global $wp_email_template_fonts_face, $wp_email_template_general;

		if (isset($wp_email_template_general['apply_for_woo_emails']) && $wp_email_template_general['apply_for_woo_emails'] == 'yes') {

			$styles = array();
			$h3_font     = 'font:italic 18px Century Gothic, sans-serif !important; color: #000000 !important;';
			$styles['font'] = trim( $h3_font );

		}

		return $styles;
	}

	public static function remove_style_inline_woocommerce_tag( $styles ) {
		global $wp_email_template_general;

		if (isset($wp_email_template_general['apply_for_woo_emails']) && $wp_email_template_general['apply_for_woo_emails'] == 'yes') {
			$styles = array();
		}

		return $styles;
	}

	public static function preview_wp_email_template() {
		check_ajax_referer( 'preview_wp_email_template', 'security' );

		$email_heading = __('Email preview', 'wp_email_template');

		echo WP_Email_Template_Hook_Filter::preview_wp_email_content( $email_heading );

		die();
	}

	public static function preview_wp_email_content( $email_heading ) {

		$message = '<h2>'.__('WordPress Email sit amet', 'wp_email_template').'</h2>';

		$message.= wpautop(__('Ut ut est qui euismod parum. Dolor veniam tation nihil assum mazim. Possim fiant habent decima et claritatem. Erat me usus gothica laoreet consequat. Clari facer litterarum aliquam insitam dolor.

Gothica minim lectores demonstraverunt ut soluta. Sequitur quam exerci veniam aliquip litterarum. Lius videntur nisl facilisis claritatem nunc. Praesent in iusto me tincidunt iusto. Dolore lectores sed putamus exerci est. ', 'wp_email_template') );

		return WP_Email_Template_Functions::email_content($email_heading, $message);

	}

	public static function set_content_type($content_type='') {
		if ( stristr( $content_type, 'multipart') !== false ) {
			$content_type = 'multipart/alternative';
		} else {
			$content_type = 'text/html';
		}
		return $content_type;
	}

	public static function change_wp_mail($email_data=array()) {
		$email_heading = $email_data['subject'] ;
		if ( isset( $email_data['message'] ) && stristr( $email_data['message'], '<!--NO_USE_EMAIL_TEMPLATE-->' ) === false ) {
			$email_data['message'] = WP_Email_Template_Functions::email_content($email_heading, $email_data['message']);
		} elseif ( isset( $email_data['html'] ) && stristr( $email_data['html'], '<!--NO_USE_EMAIL_TEMPLATE-->' ) === false ) {
			$email_data['html'] = WP_Email_Template_Functions::email_content($email_heading, $email_data['html']);
		}

		return $email_data;
	}

	public static function a3_wp_admin() {
		wp_enqueue_style( 'a3rev-wp-admin-style', WP_EMAIL_TEMPLATE_CSS_URL . '/a3_wp_admin.css' );
	}

	public static function admin_sidebar_menu_css() {
		wp_enqueue_style( 'a3rev-wp-et-admin-sidebar-menu-style', WP_EMAIL_TEMPLATE_CSS_URL . '/admin_sidebar_menu.css' );
	}

	public static function plugin_extra_links($links, $plugin_name) {
		if ( $plugin_name != WP_EMAIL_TEMPLATE_NAME) {
			return $links;
		}
		$links[] = '<a href="http://docs.a3rev.com/user-guides/wordpress/wp-email-template/" target="_blank">'.__('Documentation', 'wp_email_template').'</a>';
		$links[] = '<a href="http://wordpress.org/support/plugin/wp-email-template" target="_blank">'.__('Support', 'wp_email_template').'</a>';
		return $links;
	}

	public static function settings_plugin_links($actions) {
		$actions = array_merge( array( 'settings' => '<a href="admin.php?page=wp_email_template">' . __( 'Settings', 'wp_email_template' ) . '</a>' ), $actions );

		return $actions;
	}

	public static function plugin_extension_start() {
		global $wp_email_template_admin_init;

		$wp_email_template_admin_init->plugin_extension_start();
	}

	public static function plugin_extension_end() {
		global $wp_email_template_admin_init;

		$wp_email_template_admin_init->plugin_extension_end();
	}

	public static function plugin_extension() {
		$html = '';
		$html .= '<a href="http://a3rev.com/shop/" target="_blank" style="float:right;margin-top:5px; margin-left:10px;" ><div class="a3-plugin-ui-icon a3-plugin-ui-a3-rev-logo"></div></a>';
		$html .= '<h3>'.__('Upgrade to WP Email Template Pro', 'wp_email_template').'</h3>';
		$html .= '<p>'.__("<strong>NOTE:</strong> All the functions inside the Yellow border on the plugins admin panel are extra functionality that is activated by upgrading to the Pro version", 'wp_email_template').':</p>';
		$html .= '<p>';
		$html .= '<h3 style="margin-bottom:5px;">* <a href="'.WP_EMAIL_TEMPLATE_AUTHOR_URI.'" target="_blank">'.__('WP Email Template Pro', 'wp_email_template').'</a></h3>';

		$html .= '<div><strong>'.__('Features', 'wp_email_template').':</strong></div>';
		$html .= '<p>';
		$html .= '<ul style="padding-left:10px;">';
		$html .= '<li>1. '.__("Create a fully customized responsive email template in not days but just minutes.", 'wp_email_template').'</li>';
		$html .= '<li>2. '.__("Web Developers - wow your web clients with an email template that matches their site.", 'wp_email_template').'</li>';
		$html .= '<li>3. '.__('Site owners (even complete novices) with the Pro Version it will take you just a few minutes to be wowing your users with your uniquely styled and branded emails that they get from your site.', 'wp_email_template').'</li>';
		$html .= '<li>4. '.__('Lifetime License Fee - no ongoing payments.', 'wp_email_template').'</li>';
		$html .= '<li>5. '.__('Lifetime Pro License support from developers.', 'wp_email_template').'</li>';
		$html .= '</ul>';
		$html .= '</p>';
		$html .= '<h3>'.__('View this plugins', 'wp_email_template').' <a href="'.WP_EMAIL_TEMPLATE_DOCS_URI.'" target="_blank">'.__('documentation', 'wp_email_template').'</a></h3>';
		$html .= '<h3>'.__('Lite Version plugins', 'wp_email_template').' <a href="http://wordpress.org/support/plugin/wp-email-template/" target="_blank">'.__('support forum', 'wp_email_template').'</a></h3>';

		$html .= '<h3>'.__('More a3rev Quality WordPress Plugins', 'wp_email_template').'</h3>';
		$html .= '<p>';
		$html .= '<ul style="padding-left:10px;">';
		$html .= '<li>* <a href="https://wordpress.org/plugins/a3-lazy-load/" target="_blank">'.__('a3 Lazy Load', 'wp_email_template').'</a> &nbsp;&nbsp;&nbsp; <sup>*</sup>'.__( 'New Plugin' , 'wp_email_template' ).'</li>';
		$html .= '<li>* <a href="https://wordpress.org/plugins/a3-portfolio/" target="_blank">'.__('a3 Portfolio', 'wp_email_template').'</a> &nbsp;&nbsp;&nbsp; <sup>*</sup>'.__( 'New Plugin' , 'wp_email_template' ).'</li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/a3-responsive-slider/" target="_blank">'.__('a3 Responsive Slider', 'wp_email_template').'</a>&nbsp;&nbsp;&nbsp; ('.__( 'Just released', 'wp_email_template' ).')</li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/contact-us-page-contact-people/" target="_blank">'.__('Contact Us page - Contact People', 'wp_email_template').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/page-views-count/" target="_blank">'.__('Page View Count', 'wp_email_template').'</a></li>';
		$html .= '</ul>';
		$html .= '</p>';
		$html .= '<p>'.__("View all", 'wp_email_template').' <a href="http://profiles.wordpress.org/a3rev/" target="_blank">'.__("19 a3rev plugins", 'wp_email_template').'</a> '.__('on the WordPress repository', 'wp_email_template').'</p>';
		return $html;
	}
}
?>