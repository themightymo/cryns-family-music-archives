<?php
class s2_admin extends s2class {
/* ===== WordPress menu registration and scripts ===== */
	/**
	Hook the menu
	*/
	function admin_menu() {
		if( file_exists(dirname(plugin_dir_path( __FILE__ ) ).'/readygraph-extension.php')) {
		global $menu_slug;
		add_menu_page(__('Subscribe2', 'subscribe2'), __('Subscribe2', 'subscribe2'), apply_filters('s2_capability', "read", 'user'),$menu_slug, NULL, S2URL . 'include/email_edit.png');
		
			$s2readygraph = add_submenu_page($menu_slug, __('Readygraph App', 'subscribe2'), __('Readygraph App', 'subscribe2'), apply_filters('s2_capability', "manage_options", 'readygraph'), $menu_slug, array(&$this, 'readygraph_menu'), S2URL . 'include/email_edit.png');

		$s2user = add_submenu_page($menu_slug, __('Your Subscriptions', 'subscribe2'), __('Your Subscriptions', 'subscribe2'), apply_filters('s2_capability', "read", 'user'), 's2', array(&$this, 'user_menu'));
		add_action("admin_print_scripts-$s2user", array(&$this, 'checkbox_form_js'));
		add_action("admin_print_styles-$s2user", array(&$this, 'user_admin_css'));
		add_action('load-' . $s2user, array(&$this, 'user_help'));

		
		//add_action("admin_print_scripts-$s2readygraph", array(&$this, 'readygraph_js'));

		$s2subscribers = add_submenu_page($menu_slug, __('Subscribers', 'subscribe2'), __('Subscribers', 'subscribe2'), apply_filters('s2_capability', "manage_options", 'manage'), 's2_tools', array(&$this, 'subscribers_menu'));
		add_action("admin_print_scripts-$s2subscribers", array(&$this, 'checkbox_form_js'));
		add_action('load-' . $s2subscribers, array(&$this, 'subscribers_help'));

		$s2settings = add_submenu_page($menu_slug, __('Settings', 'subscribe2'), __('Settings', 'subscribe2'), apply_filters('s2_capability', "manage_options", 'settings'), 's2_settings', array(&$this, 'settings_menu'));
		add_action("admin_print_scripts-$s2settings", array(&$this, 'checkbox_form_js'));
		add_action("admin_print_scripts-$s2settings", array(&$this, 'option_form_js'));
		add_filter('plugin_row_meta', array(&$this, 'plugin_links'), 10, 2);
		add_action('load-' . $s2settings, array(&$this, 'settings_help'));

		$s2mail = add_submenu_page($menu_slug, __('Send Email', 'subscribe2'), __('Send Email', 'subscribe2'), apply_filters('s2_capability', "publish_posts", 'send'), 's2_posts', array(&$this, 'write_menu'));
		add_action('load-' . $s2mail, array(&$this, 'mail_help'));
		$s2readygraph = add_submenu_page($menu_slug, __('Go Premium', 'subscribe2'), __('Go Premium', 'subscribe2'), apply_filters('s2_capability', "manage_options", 'readygraph'), 'readygraph-go-premium', array(&$this, 'readygraph_premium'));
		}
		else {
		add_menu_page(__('Subscribe2', 'subscribe2'), __('Subscribe2', 'subscribe2'), apply_filters('s2_capability', "read", 'user'), 's2', NULL, S2URL . 'include/email_edit.png');

		$s2user = add_submenu_page('s2', __('Your Subscriptions', 'subscribe2'), __('Your Subscriptions', 'subscribe2'), apply_filters('s2_capability', "read", 'user'), 's2', array(&$this, 'user_menu'), S2URL . 'include/email_edit.png');
		add_action("admin_print_scripts-$s2user", array(&$this, 'checkbox_form_js'));
		add_action("admin_print_styles-$s2user", array(&$this, 'user_admin_css'));
		add_action('load-' . $s2user, array(&$this, 'user_help'));
		
		//add_action("admin_print_scripts-$s2readygraph", array(&$this, 'readygraph_js'));

		$s2subscribers = add_submenu_page('s2', __('Subscribers', 'subscribe2'), __('Subscribers', 'subscribe2'), apply_filters('s2_capability', "manage_options", 'manage'), 's2_tools', array(&$this, 'subscribers_menu'));
		add_action("admin_print_scripts-$s2subscribers", array(&$this, 'checkbox_form_js'));
		add_action('load-' . $s2subscribers, array(&$this, 'subscribers_help'));

		$s2settings = add_submenu_page('s2', __('Settings', 'subscribe2'), __('Settings', 'subscribe2'), apply_filters('s2_capability', "manage_options", 'settings'), 's2_settings', array(&$this, 'settings_menu'));
		add_action("admin_print_scripts-$s2settings", array(&$this, 'checkbox_form_js'));
		add_action("admin_print_scripts-$s2settings", array(&$this, 'option_form_js'));
		add_filter('plugin_row_meta', array(&$this, 'plugin_links'), 10, 2);
		add_action('load-' . $s2settings, array(&$this, 'settings_help'));

		$s2mail = add_submenu_page('s2', __('Send Email', 'subscribe2'), __('Send Email', 'subscribe2'), apply_filters('s2_capability', "publish_posts", 'send'), 's2_posts', array(&$this, 'write_menu'));
		add_action('load-' . $s2mail, array(&$this, 'mail_help'));
		}
		$s2nonce = wp_hash('subscribe2');
	} // end admin_menu()

	/**
	Contextual Help
	*/
	function user_help() {
		$screen = get_current_screen();
		if ( $this->subscribe2_options['email_freq'] != 'never' ) {
			$screen->add_help_tab(array(
				'id' => 's2-user-help1',
				'title' => __('Overview', 'subscribe2'),
				'content' => '<p>' . __('From this page you can opt in or out of receiving a periodical digest style email of blog posts.', 'subscribe2') . '</p>'
			));
		} else {
			$screen->add_help_tab(array(
				'id' => 's2-user-help1',
				'title' => __('Overview', 'subscribe2'),
				'content' => '<p>' . __('From this page you can control your subscription preferences. Choose the email format you wish to receive, which categories you would like to receive notification for and depending on the site settings which authors you would like to read.', 'subscribe2') . '</p>'
			));
		}
	} // end user_help()

	function subscribers_help() {
		$screen = get_current_screen();
		$screen->add_help_tab(array(
			'id' => 's2-subscribers-help1',
			'title' => __('Overview', 'subscribe2'),
			'content' => '<p>' . __('From this page you can manage your subscribers.', 'subscribe2') . '</p>'
		));
		$screen->add_help_tab(array(
			'id' => 's2-subscribers-help2',
			'title' => __('Public Subscribers', 'subscribe2'),
			'content' => '<p>' . __('Public Subscribers are subscribers who have used the plugin form and only provided their email address.', 'subscribe2') . '</p><p>'. __('On this page public subscribers can be viewed, searched, deleted and also toggled between Confirmed and Unconfirmed status.', 'subscribe2') . '</p>'
		));
		$screen->add_help_tab(array(
			'id' => 's2-subscribers-help3',
			'title' => __('Registered Subscribers', 'subscribe2'),
			'content' => '<p>' . __('Registered Subscribers are subscribers who have registered in WordPress and have a username and password.', 'subscribe2') .
			'</p><p>'. __('Registered Subscribers have greater personal control over their subscription. They can change the format of the email and also select which categories and authors they want to receive notifications about.', 'subscribe2') .
			'</p><p>'. __('On this page registered subscribers can be viewed and searched. User accounts can be deleted from here with any posts created by those users being assigned to the currently logged in user. Bulk changes can be applied to all user settings changing their subscription email format and categories.', 'subscribe2') . '</p>'
		));
	} // end subscribers_help()

	function settings_help() {
		$screen = get_current_screen();
		$screen->add_help_tab(array(
			'id' => 's2-settings-help1',
			'title' => __('Overview', 'subscribe2'),
			'content' => '<p>' . __('From this page you can adjust the Settings for Subscribe2.', 'subscribe2') . '</p>'
		));
		$screen->add_help_tab(array(
			'id' => 's2-settings-help2',
			'title' => __('Email Settings', 'subscribe2'),
			'content' => '<p>' . __('This section allows you to specify settings that apply to the emails generated by the site.', 'subscribe2') .
			'</p><p>'. __('Emails can be sent to individual subscribers by setting the number of recipients per email to 1. A setting greater than one will group recipients together and make use of the BCC emails header. A setting of 0 sends a single email with all subscribers in one large BCC group. A setting of 1 looks less like spam email to filters but takes longer to process.', 'subscribe2').
			'</p><p>'. __('This section is also where the sender of the email on this page is chosen. You can choose Post Author or your Blogname but it is recommended to create a user account with an email address that really exists and shares the same domain name as your site (the bit after the @ should be the same as your sites web address) and then use this account.', 'subscribe2') .
			'</p><p>'. __('This page also configures the frequency of emails. This can be at the time new posts are made (per post) or periodically with an excerpt of each post made (digest). Additionally the post types (pages, private, password protected) can also be configured here.', 'subscribe2') . '</p>'
		));
		$screen->add_help_tab(array(
			'id' => 's2-settings-help3',
			'title' => __('Templates', 'subscribe2'),
			'content' => '<p>' . __('This section allows you to customise the content of your notification emails.', 'subscribe2') .
			'</p><p>'. __('There are special {KEYWORDS} that are used by Subscribe2 to place content into the final email. The template also accepts regular text and HTML as desired in the final emails.', 'subscribe2') .
			'</p><p>'. __('The {KEYWORDS} are listed on the right of the templates, note that some are for per post emails only and some are for digest emails only. Make sure the correct keywords are used based upon the Email Settings.', 'subscribe2') . '</p>'
		));
		$screen->add_help_tab(array(
			'id' => 's2-settings-help4',
			'title' => __('Registered Users', 'subscribe2'),
			'content' => '<p>' . __('This section allows settings that apply to Registered Subscribers to be configured.', 'subscribe2') .
			'</p><p>'. __('Categories can be made compulsory so emails are always sent for posts in these categories. They can also be excludes so that emails are not generated. Excluded categories take precedence over Compulsory categories.', 'subscribe2') .
			'</p><p>'. __('A set of default settings for new users can also be specified using the Auto Subscribe section. Settings specified here will be applied to any newly created user accounts while Subscribe2 is activated.', 'subscribe2') . '</p>'
		));
		$screen->add_help_tab(array(
			'id' => 's2-settings-help5',
			'title' => __('Appearance', 'subscribe2'),
			'content' => '<p>' . __('This section allows you to enable several aspect of the plugin such as Widgets and editor buttons.', 'subscribe2') .
			'</p><p>'. __('AJAX mode can be enabled that is intended to work with the shortcode link parameter so that a dialog opens in the centre of the browser rather then using the regular form.', 'subscribe2') .
			'</p><p>'. __('The email over ride check box can be set to be automatically checked for every new post and page from here to, this may be useful if you will only want to send very occasional notifications for specific posts. You can then uncheck this box just before you publish your content.', 'subscribe2') . '</p>'
		));
		$screen->add_help_tab(array(
			'id' => 's2-settings-help6',
			'title' => __('Miscellaneous', 'subscribe2'),
			'content' => '<p>' . __('This section contains a place to bar specified domains from becoming Public Subscribers and links to help and support pages.', 'subscribe2') .
			'</p><p>'. __('In the paid Subscribe2 HTML version there is also a place here to enter a license code so that updates can be accessed automatically.', 'subscribe2') .
			'</p>'
		));
	} // end settings_help()

	function mail_help() {
		$screen = get_current_screen();
		$screen->add_help_tab(array(
			'id' => 's2-send-mail-help1',
			'title' => __('Overview', 'subscribe2'),
			'content' => '<p>' . __('From this page you can send emails to the recipients in the group selected in the drop down.', 'subscribe2') .
			'</p><p>' . __('<strong>Preview</strong> will send a preview of the email to the currently logged in user. <strong>Send</strong> will send the email to the recipient list.', 'subscribe2') . '</p>'
		));
	} // end send_email_help()

	/**
	Hook for Admin Drop Down Icons
	*/
	function ozh_s2_icon() {
		return S2URL . 'include/email_edit.png';
	} // end ozh_s2_icon()

	/**
	Insert Javascript and CSS into admin_header
	*/
	function checkbox_form_js() {
		wp_register_script('s2_checkbox', S2URL . 'include/s2_checkbox' . $this->script_debug . '.js', array('jquery'), '1.3');
		wp_enqueue_script('s2_checkbox');
	} //end checkbox_form_js()

	function user_admin_css() {
		wp_register_style('s2_user_admin', S2URL . 'include/s2_user_admin.css', array(), '1.0');
		wp_enqueue_style('s2_user_admin');
	} // end user_admin_css()

	function option_form_js() {
		wp_register_script('s2_edit', S2URL . 'include/s2_edit' . $this->script_debug . '.js', array('jquery'), '1.1');
		wp_enqueue_script('s2_edit');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/smoothness/jquery-ui.css');
		wp_register_script('s2_date_time', S2URL . 'include/s2_date_time' . $this->script_debug . '.js', array('jquery-ui-datepicker'), '1.0');
		wp_enqueue_script('s2_date_time');
	} // end option_form_js()

	/**
	Enqueue jQuery for ReadyGraph
	*/
/*	function readygraph_js() {
		wp_enqueue_script('jquery');
		wp_register_script('s2_readygraph', S2URL . 'include/s2_readygraph' . $this->script_debug . '.js', array('jquery'), '1.0');
		wp_enqueue_script('s2_readygraph');
		wp_localize_script('s2_readygraph', 'objectL10n', array(
			'emailempty'  => __('Email is empty!', 'subscribe2'),
			'passwordempty' => __('Password is empty!', 'subscribe2'),
			'urlempty' => __('Site URL is empty!', 'subscribe2'),
			'passwordmatch' => __('Password is not matching!', 'subscribe2')
		) );
	} // end readygraph_js()
*/
	/**
	Adds a links directly to the settings page from the plugin page
	*/
	function plugin_links($links, $file) {
		if ( $file == S2DIR.'subscribe2.php' ) {
			$links[] = "<a href='admin.php?page=s2_settings'>" . __('Settings', 'subscribe2') . "</a>";
			$links[] = "<a href='http://plugins.trac.wordpress.org/browser/subscribe2/i18n/'>" . __('Translation Files', 'subscribe2') . "</a>";
			$links[] = "<a href='https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=2387904'><b>" . __('Donate', 'subscribe2') . "</b></a>";
		}
		return $links;
	} // end plugin_links()

	/* ===== Menus ===== */
	/**
	Our subscriber management page
	*/
	function subscribers_menu() {
		require_once(S2PATH . 'admin/subscribers.php');
	} // end subscribers_menu()

	/**
	Our ReadyGraph API page
	*/
	function readygraph_menu() {
	global $wpdb;
	$current_page = isset($_GET['ac']) ? $_GET['ac'] : '';
	switch($current_page)
	{
		case 'signup-popup':
			include(S2PATH . 'extension/readygraph/signup-popup.php');
			break;
		case 'go-premium':
			include(S2PATH . 'extension/readygraph/go-premium.php');
			break;
		case 'social-feed':
			include(S2PATH . 'extension/readygraph/social-feed.php');
			break;
		case 'site-profile':
			include(S2PATH . 'extension/readygraph/site-profile.php');
			break;
		case 'customize-emails':
			include(S2PATH . 'extension/readygraph/customize-emails.php');
			break;
		case 'deactivate-readygraph':
			include(S2PATH . 'extension/readygraph/deactivate-readygraph.php');
			break;
		case 'welcome-email':
			include(S2PATH . 'extension/readygraph/welcome-email.php');
			break;
		case 'retention-email':
			include(S2PATH . 'extension/readygraph/retention-email.php');
			break;
		case 'invitation-email':
			include(S2PATH . 'extension/readygraph/invitation-email.php');
			break;	
		case 'faq':
			include(S2PATH . 'extension/readygraph/faq.php');
			break;
		default:
			include(S2PATH . 'extension/readygraph/admin.php');
			break;
	}
	} // end readygraph_menu()
	/**
	Our Readygraph Premium Page
	*/
	function readygraph_premium() {
		include(S2PATH . 'extension/readygraph/go-premium.php');
	} // end settings_menu()
	/**
	Our settings page
	*/
	function settings_menu() {
		require_once(S2PATH . 'admin/settings.php');
	} // end settings_menu()

	/**
	Our profile menu
	*/
	function user_menu() {
		require_once(S2PATH . 'admin/your_subscriptions.php');
	} // end user_menu()

	/**
	Display the Write sub-menu
	*/
	function write_menu() {
		require_once(S2PATH . 'admin/send_email.php');
	} // end write_menu()

/* ===== Write Toolbar Button Functions ===== */
	/**
	Register our button in the QuickTags bar
	*/
	function button_init() {
		global $pagenow;
		if ( !in_array($pagenow, array('post-new.php', 'post.php', 'page-new.php', 'page.php')) ) { return; }
		if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) { return; }
		if ( 'true' == get_user_option('rich_editing') ) {
			// Hook into the rich text editor
			add_filter('mce_external_plugins', array(&$this, 'mce_plugin'));
			add_filter('mce_buttons', array(&$this, 'mce_button'));
		} else {
			wp_enqueue_script('subscribe2_button', S2URL . 'include/s2_button' . $this->script_debug . '.js', array('quicktags'), '2.0' );
		}
	} // end button_init()

	/**
	Add buttons for Rich Text Editor
	*/
	function mce_plugin($arr) {
		if ( version_compare($this->wp_release, '3.9', '<') ) {
			$path = S2URL . 'tinymce/editor_plugin3' . $this->script_debug . '.js';
		} else {
			$path = S2URL . 'tinymce/editor_plugin4' . $this->script_debug . '.js';
		}
		$arr['subscribe2'] = $path;
		return $arr;
	} // end mce_plugin()

	function mce_button($arr) {
		$arr[] = 'subscribe2';
		return $arr;
	} // end mce_button()

/* ===== widget functions ===== */
	/**
	Function to add css and js files to admin header
	*/
	function widget_s2counter_css_and_js() {
		// ensure we only add colorpicker js to widgets page
		if ( stripos($_SERVER['REQUEST_URI'], 'widgets.php' ) !== false ) {
			wp_enqueue_style('farbtastic');
			wp_enqueue_script('farbtastic');
			wp_register_script('s2_colorpicker', S2URL . 'include/s2_colorpicker' . $this->script_debug . '.js', array('farbtastic'), '1.2');
			wp_enqueue_script('s2_colorpicker');
		}
	} // end widget_s2_counter_css_and_js()

	/**
	Function to to handle activate redirect
	*/
	/*function on_plugin_activated_redirect(){
		$setting_url="admin.php?page=s2_readygraph";

		if ( get_option('s2_do_activation_redirect', false) ) {
			delete_option('s2_do_activation_redirect');
			wp_redirect($setting_url);
		}
	} // end on_plugin_activated_redirect()
*/
/* ===== meta box functions to allow per-post override ===== */
	/**
	Create meta box on write pages
	*/
	function s2_meta_init() {
		add_meta_box('subscribe2', __('Subscribe2 Notification Override', 'subscribe2' ), array(&$this, 's2_meta_box'), 'post', 'advanced');
		add_meta_box('subscribe2', __('Subscribe2 Notification Override', 'subscribe2' ), array(&$this, 's2_meta_box'), 'page', 'advanced');
	} // end s2_meta_init()

	/**
	Meta box code
	*/
	function s2_meta_box() {
		global $post_ID;
		$s2mail = get_post_meta($post_ID, '_s2mail', true);
		echo "<input type=\"hidden\" name=\"s2meta_nonce\" id=\"s2meta_nonce\" value=\"" . wp_create_nonce(wp_hash(plugin_basename(__FILE__))) . "\" />";
		echo __("Check here to disable sending of an email notification for this post/page", 'subscribe2');
		echo "&nbsp;&nbsp;<input type=\"checkbox\" name=\"s2_meta_field\" value=\"no\"";
		if ( $s2mail == 'no' || ($this->subscribe2_options['s2meta_default'] == "1" && $s2mail == "") ) {
			echo " checked=\"checked\"";
		}
		echo " />";
	} // end s2_meta_box()

	/**
	Meta box form handler
	*/
	function s2_meta_handler($post_id) {
		if ( !isset($_POST['s2meta_nonce']) || !wp_verify_nonce($_POST['s2meta_nonce'], wp_hash(plugin_basename(__FILE__))) ) { return $post_id; }

		if ( 'page' == $_POST['post_type'] ) {
			if ( !current_user_can('edit_page', $post_id) ) { return $post_id; }
		} else {
			if ( !current_user_can('edit_post', $post_id) ) { return $post_id; }
		}

		if ( isset($_POST['s2_meta_field']) && $_POST['s2_meta_field'] == 'no' ) {
			update_post_meta($post_id, '_s2mail', $_POST['s2_meta_field']);
		} else {
			update_post_meta($post_id, '_s2mail', 'yes');
		}
	} // end s2_meta_box_handler()

/* ===== WordPress menu helper functions ===== */
	/**
	Collects the signup date for all public subscribers
	*/
	function signup_date($email = '') {
		if ( '' == $email ) { return false; }

		global $wpdb;
		if ( !empty($this->signup_dates) ) {
			return $this->signup_dates[$email];
		} else {
			$results = $wpdb->get_results("SELECT email, date FROM $this->public", ARRAY_N);
			foreach ( $results as $result ) {
				$this->signup_dates[$result[0]] = $result[1];
			}
			return $this->signup_dates[$email];
		}
	} // end signup_date()

	/**
	Collects the ip address for all public subscribers
	*/
	function signup_ip($email = '') {
		if ( '' == $email ) {return false; }

		global $wpdb;
		if ( !empty($this->signup_ips) ) {
			return $this->signup_ips[$email];
		} else {
			$results = $wpdb->get_results("SELECT email, ip FROM $this->public", ARRAY_N);
			foreach ( $results as $result ) {
				$this->signup_ips[$result[0]] = $result[1];
			}
			return $this->signup_ips[$email];
		}
	} // end signup_ip()

	/**
	Export subscriber emails and other details to CSV
	*/
	function prepare_export( $subscribers ) {
		if ( empty($subscribers) ) { return; }
		$subscribers = explode(",\r\n", $subscribers);
		natcasesort($subscribers);

		$exportcsv = _x('User Email,User Type,User Name,Confirm Date,IP', 'Comma Separated Column Header names for CSV Export' , 'subscribe2');
		$all_cats = $this->all_cats(false, 'ID');

		foreach ($all_cats as $cat) {
			$exportcsv .= "," . html_entity_decode($cat->cat_name, ENT_QUOTES);
			$cat_ids[] = $cat->term_id;
		}
		$exportcsv .= "\r\n";

		if ( !function_exists('get_userdata') ) {
			require_once(ABSPATH . WPINC . '/pluggable.php');
		}

		foreach ( $subscribers as $subscriber ) {
			if ( $this->is_registered($subscriber) ) {
				$user_ID = $this->get_user_id( $subscriber );
				$user_info = get_userdata( $user_ID );

				$cats = explode(',', get_user_meta($user_ID, $this->get_usermeta_keyname('s2_subscribed'), true));
				$subscribed_cats = '';
				foreach ( $cat_ids as $cat ) {
					(in_array($cat, $cats)) ? $subscribed_cats .= ",Yes" : $subscribed_cats .= ",No";
				}

				$exportcsv .= $subscriber . ',';
				$exportcsv .= __('Registered User', 'subscribe2');
				$exportcsv .= ',' . $user_info->display_name;
				$exportcsv .= ',,' . $subscribed_cats . "\r\n";
			} else {
				if ( $this->is_public($subscriber) === '1' ) {
					$exportcsv .= $subscriber . ',' . __('Confirmed Public Subscriber', 'subscribe2') . ',,' . $this->signup_date($subscriber) . ',' . $this->signup_ip($subscriber) . "\r\n";
				} elseif ( $this->is_public($subscriber) === '0' ) {
					$exportcsv .= $subscriber . ',' . __('Unconfirmed Public Subscriber', 'subscribe2') . ',,' . $this->signup_date($subscriber) . ',' . $this->signup_ip($subscriber) . "\r\n";
				}
			}
		}

		return $exportcsv;
	} // end prepare_export()

	/**
	Display a table of categories with checkboxes
	Optionally pre-select those categories specified
	*/
	function display_category_form($selected = array(), $override = 1, $compulsory = array(), $name = 'category') {
		global $wpdb;

		if ( $override == 0 ) {
			$all_cats = $this->all_cats(true);
		} else {
			$all_cats = $this->all_cats(false);
		}

		$half = (count($all_cats) / 2);
		$i = 0;
		$j = 0;
		echo "<table style=\"width: 100%; border-collapse: separate; border-spacing: 2px; *border-collapse: expression('separate', cellSpacing = '2px');\" class=\"editform\">\r\n";
		echo "<tr><td style=\"text-align: left;\" colspan=\"2\">\r\n";
		echo "<label><input type=\"checkbox\" name=\"checkall\" value=\"checkall_" . $name . "\" /> " . __('Select / Unselect All', 'subscribe2') . "</label>\r\n";
		echo "</td></tr>\r\n";
		echo "<tr style=\"vertical-align: top;\"><td style=\"width: 50%; text-align: left;\">\r\n";
		foreach ( $all_cats as $cat ) {
			if ( $i >= $half && 0 == $j ) {
				echo "</td><td style=\"width: 50%; text-align: left;\">\r\n";
				$j++;
			}
			$catName = '';
			$parents = array_reverse( get_ancestors($cat->term_id, $cat->taxonomy) );
			if ( $parents ) {
				foreach ( $parents as $parent ) {
					$parent = get_term($parent, $cat->taxonomy);
					$catName .= $parent->name . ' &raquo; ';
				}
			}
			$catName .= $cat->name;

			if ( 0 == $j ) {
				echo "<label><input class=\"checkall_" . $name . "\" type=\"checkbox\" name=\"" . $name . "[]\" value=\"" . $cat->term_id . "\"";
				if ( in_array($cat->term_id, $selected) || in_array($cat->term_id, $compulsory) ) {
					echo " checked=\"checked\"";
				}
				if ( in_array($cat->term_id, $compulsory) && $name === 'category' ) {
					echo " DISABLED";
				}
				echo " /> <abbr title=\"" . $cat->slug . "\">" . $catName . "</abbr></label><br />\r\n";
			} else {
				echo "<label><input class=\"checkall_" . $name . "\" type=\"checkbox\" name=\"" . $name . "[]\" value=\"" . $cat->term_id . "\"";
				if ( in_array($cat->term_id, $selected) || in_array($cat->term_id, $compulsory) ) {
					echo " checked=\"checked\"";
				}
				if ( in_array($cat->term_id, $compulsory) && $name === 'category' ) {
					echo " DISABLED";
				}
				echo " /> <abbr title=\"" . $cat->slug . "\">" . $catName . "</abbr></label><br />\r\n";
			}
			$i++;
		}
		if ( !empty($compulsory) ) {
			foreach ($compulsory as $cat) {
				echo "<input type=\"hidden\" name=\"" . $name . "[]\" value=\"" . $cat . "\">\r\n";
			}
		}
		echo "</td></tr>\r\n";
		echo "</table>\r\n";
	} // end display_category_form()

	/**
	Display a table of post formats supported by the currently active theme
	*/
	function display_format_form($formats, $selected = array()) {
		$half = (count($formats[0]) / 2);
		$i = 0;
		$j = 0;
		echo "<table style=\"width: 100%; border-collapse: separate; border-spacing: 2px; *border-collapse: expression('separate', cellSpacing = '2px');\" class=\"editform\">\r\n";
		echo "<tr><td style=\"text-align: left;\" colspan=\"2\">\r\n";
		echo "<label><input type=\"checkbox\" name=\"checkall\" value=\"checkall_format\" /> " . __('Select / Unselect All', 'subscribe2') . "</label>\r\n";
		echo "</td></tr>\r\n";
		echo "<tr style=\"vertical-align: top;\"><td style=\"width: 50%; text-align: left\">\r\n";
		foreach ( $formats[0] as $format ) {
			if ( $i >= $half && 0 == $j ) {
				echo "</td><td style=\"width: 50%; text-align: left\">\r\n";
				$j++;
			}

			if ( 0 == $j ) {
				echo "<label><input class=\"checkall_format\" type=\"checkbox\" name=\"format[]\" value=\"" . $format . "\"";
				if ( in_array($format, $selected) ) {
						echo " checked=\"checked\"";
				}
				echo " /> " . ucwords($format) . "</label><br />\r\n";
			} else {
				echo "<label><input class=\"checkall_format\" type=\"checkbox\" name=\"format[]\" value=\"" . $format . "\"";
				if ( in_array($format, $selected) ) {
							echo " checked=\"checked\"";
				}
				echo " /> " . ucwords($format) . "</label><br />\r\n";
			}
			$i++;
		}
		echo "</td></tr>\r\n";
		echo "</table>\r\n";
	} // end display_format_form()

	/**
	Display a table of authors with checkboxes
	Optionally pre-select those authors specified
	*/
	function display_author_form($selected = array()) {
		$all_authors = $this->get_authors();

		$half = (count($all_authors) / 2);
		$i = 0;
		$j = 0;
		echo "<table style=\"width: 100%; border-collapse: separate; border-spacing: 2px; *border-collapse: expression('separate', cellSpacing = '2px');\" class=\"editform\">\r\n";
		echo "<tr><td style=\"text-align: left;\" colspan=\"2\">\r\n";
		echo "<label><input type=\"checkbox\" name=\"checkall\" value=\"checkall_author\" /> " . __('Select / Unselect All', 'subscribe2') . "</label>\r\n";
		echo "</td></tr>\r\n";
		echo "<tr style=\"vertical-align: top;\"><td style=\"width: 50%; test-align: left;\">\r\n";
		foreach ( $all_authors as $author ) {
			if ( $i >= $half && 0 == $j ) {
				echo "</td><td style=\"width: 50%; text-align: left;\">\r\n";
				$j++;
			}
			if ( 0 == $j ) {
				echo "<label><input class=\"checkall_author\" type=\"checkbox\" name=\"author[]\" value=\"" . $author->ID . "\"";
				if ( in_array($author->ID, $selected) ) {
						echo " checked=\"checked\"";
				}
				echo " /> " . $author->display_name . "</label><br />\r\n";
			} else {
				echo "<label><input class=\"checkall_author\" type=\"checkbox\" name=\"author[]\" value=\"" . $author->ID . "\"";
				if ( in_array($author->ID, $selected) ) {
					echo " checked=\"checked\"";
				}
				echo " /> " . $author->display_name . "</label><br />\r\n";
				$i++;
			}
		}
		echo "</td></tr>\r\n";
		echo "</table>\r\n";
	} // end display_author_form()

	/**
	Collect an array of all author level users and above
	*/
	function get_authors() {
		if ( '' == $this->all_authors ) {
			$role = array('fields' => array('ID', 'display_name'), 'role' => 'administrator');
			$administrators = get_users( $role );
			$role = array('fields' => array('ID', 'display_name'), 'role' => 'editor');
			$editors = get_users( $role );
			$role = array('fields' => array('ID', 'display_name'), 'role' => 'author');
			$authors = get_users( $role );

			$this->all_authors = array_merge($administrators, $editors, $authors);
		}
		return apply_filters('s2_authors', $this->all_authors);
	} // end get_authors()

	/**
	Display a drop-down form to select subscribers
	$selected is the option to select
	$submit is the text to use on the Submit button
	*/
	function display_subscriber_dropdown($selected = 'registered', $submit = '', $exclude = array()) {
		global $wpdb;

		$who = array('all' => __('All Users and Subscribers', 'subscribe2'),
			'public' => __('Public Subscribers', 'subscribe2'),
			'confirmed' => ' &nbsp;&nbsp;' . __('Confirmed', 'subscribe2'),
			'unconfirmed' => ' &nbsp;&nbsp;' . __('Unconfirmed', 'subscribe2'),
			'all_users' => __('All Registered Users', 'subscribe2'),
			'registered' => __('Registered Subscribers', 'subscribe2'));

		$all_cats = $this->all_cats(false);

		// count the number of subscribers
		$count['confirmed'] = $wpdb->get_var("SELECT COUNT(id) FROM $this->public WHERE active='1'");
		$count['unconfirmed'] = $wpdb->get_var("SELECT COUNT(id) FROM $this->public WHERE active='0'");
		if ( in_array('unconfirmed', $exclude) ) {
			$count['public'] = $count['confirmed'];
		} elseif ( in_array('confirmed', $exclude) ) {
			$count['public'] = $count['unconfirmed'];
		} else {
			$count['public'] = ($count['confirmed'] + $count['unconfirmed']);
		}
		if ( $this->s2_mu ) {
			$count['all_users'] = $wpdb->get_var("SELECT COUNT(meta_key) FROM $wpdb->usermeta WHERE meta_key='" . $wpdb->prefix . "capabilities'");
		} else {
			$count['all_users'] = $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->users");
		}
		if ( $this->s2_mu ) {
			$count['registered'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(b.meta_key) FROM $wpdb->usermeta AS a INNER JOIN $wpdb->usermeta AS b ON a.user_id = b.user_id WHERE a.meta_key='" . $wpdb->prefix . "capabilities' AND b.meta_key=%s AND b.meta_value <> ''", $this->get_usermeta_keyname('s2_subscribed')));
		} else {
			$count['registered'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(meta_key) FROM $wpdb->usermeta WHERE meta_key=%s AND meta_value <> ''", $this->get_usermeta_keyname('s2_subscribed')));
		}
		$count['all'] = ($count['confirmed'] + $count['unconfirmed'] + $count['all_users']);
		// get subscribers to individual categories but only if we are using per-post notifications
		if ( $this->subscribe2_options['email_freq'] == 'never' ) {
			$compulsory = explode(',', $this->subscribe2_options['compulsory']);
			if ( $this->s2_mu ) {
				foreach ( $all_cats as $cat ) {
					if ( in_array($cat->term_id, $compulsory) ) {
						$count[$cat->name] = $count['all_users'];
					} else {
						$count[$cat->name] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(a.meta_key) FROM $wpdb->usermeta AS a INNER JOIN $wpdb->usermeta AS b ON a.user_id = b.user_id WHERE a.meta_key='" . $wpdb->prefix . "capabilities' AND b.meta_key=%s", $this->get_usermeta_keyname('s2_cat') . $cat->term_id));
					}
				}
			} else {
				foreach ( $all_cats as $cat ) {
					if ( in_array($cat->term_id, $compulsory) ) {
						$count[$cat->name] = $count['all_users'];
					} else {
						$count[$cat->name] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(meta_value) FROM $wpdb->usermeta WHERE meta_key=%s", $this->get_usermeta_keyname('s2_cat') . $cat->term_id));
					}
				}
			}
		}

		// do have actually have some subscribers?
		if ( 0 == $count['confirmed'] && 0 == $count['unconfirmed'] && 0 == $count['all_users'] ) {
			// no? bail out
			return;
		}

		echo "<select name=\"what\">\r\n";
		foreach ( $who as $whom => $display ) {
			if ( in_array($whom, $exclude) ) { continue; }
			if ( 0 == $count[$whom] ) { continue; }

			echo "<option value=\"" . $whom . "\"";
			if ( $whom == $selected ) { echo " selected=\"selected\" "; }
			echo ">$display (" . ($count[$whom]) . ")</option>\r\n";
		}

		if ( $count['registered'] > 0 && $this->subscribe2_options['email_freq'] == 'never' ) {
			foreach ( $all_cats as $cat ) {
				if ( in_array($cat->term_id, $exclude) ) { continue; }
				echo "<option value=\"" . $cat->term_id . "\"";
				if ( $cat->term_id == $selected ) { echo " selected=\"selected\" "; }
				echo "> &nbsp;&nbsp;" . $cat->name . "&nbsp;(" . $count[$cat->name] . ") </option>\r\n";
			}
		}
		echo "</select>";
		if ( false !== $submit ) {
			echo "&nbsp;<input type=\"submit\" class=\"button-secondary\" value=\"$submit\" />\r\n";
		}
	} // end display_subscriber_dropdown()

	/**
	Display a drop down list of administrator level users and
	optionally include a choice for Post Author
	*/
	function admin_dropdown($inc_author = false) {
		global $wpdb;

		$args = array('fields' => array('ID', 'display_name'), 'role' => 'administrator');
		$wp_user_query = get_users( $args );
		if ( !empty($wp_user_query) ) {
			foreach ($wp_user_query as $user) {
				$admins[] = $user;
			}
		} else {
			$admins = array();
		}

		if ( $inc_author ) {
			$author[] = (object)array('ID' => 'author', 'display_name' => __('Post Author', 'subscribe2'));
			$author[] = (object)array('ID' => 'blogname', 'display_name' => html_entity_decode(get_option('blogname'), ENT_QUOTES));
			$admins = array_merge($author, $admins);
		}

		echo "<select name=\"sender\">\r\n";
		foreach ( $admins as $admin ) {
			echo "<option value=\"" . $admin->ID . "\"";
			if ( $admin->ID == $this->subscribe2_options['sender'] ) {
				echo " selected=\"selected\"";
			}
			echo ">" . $admin->display_name . "</option>\r\n";
		}
		echo "</select>\r\n";
	} // end admin_dropdown()

	/**
	Display a dropdown of choices for digest email frequency
	and give user details of timings when event is scheduled
	*/
	function display_digest_choices() {
		global $wpdb;
		$cron_file = ABSPATH . 'wp-cron.php';
		if ( !is_readable($cron_file) ) {
			echo "<strong><em style=\"color: red\">" . __('The WordPress cron functions may be disabled on this server. Digest notifications may not work.', 'subscribe2') . "</em></strong><br />\r\n";
		}
		$scheduled_time = wp_next_scheduled('s2_digest_cron');
		$offset = get_option('gmt_offset') * 60 * 60;
		$schedule = (array)wp_get_schedules();
		$schedule = array_merge(array('never' => array('interval' => 0, 'display' => __('For each Post', 'subscribe2'))), $schedule);
		$sort = array();
		foreach ( (array)$schedule as $key => $value ) {
			$sort[$key] = $value['interval'];
		}
		asort($sort);
		$schedule_sorted = array();
		foreach ( $sort as $key => $value ) {
			$schedule_sorted[$key] = $schedule[$key];
		}
		foreach ( $schedule_sorted as $key => $value ) {
			echo "<label><input type=\"radio\" name=\"email_freq\" value=\"" . $key . "\"" . checked($this->subscribe2_options['email_freq'], $key, false) . " />";
			echo " " . $value['display'] . "</label><br />\r\n";
		}
		if ( $scheduled_time ) {
			$date_format = get_option('date_format');
			$time_format = get_option('time_format');
			echo "<p>" . __('Current UTC time is', 'subscribe2') . ": \r\n";
			echo "<strong>" . date_i18n($date_format . " @ " . $time_format, false, 'gmt') . "</strong></p>\r\n";
			echo "<p>" . __('Current blog time is', 'subscribe2') . ": \r\n";
			echo "<strong>" . date_i18n($date_format . " @ " . $time_format) . "</strong></p>\r\n";
			echo "<p>" . __('Next email notification will be sent when your blog time is after', 'subscribe2') . ": \r\n";
			echo "<input type=\"hidden\" id=\"jscrondate\" value=\"" . date_i18n($date_format, $scheduled_time + $offset) . "\" />";
			echo "<input type=\"hidden\" id=\"jscrontime\" value=\"" . date_i18n($time_format, $scheduled_time + $offset) . "\" />";
			echo "<span id=\"s2cron_1\"><span id=\"s2crondate\" style=\"background-color: #FFFBCC\">" . date_i18n($date_format, $scheduled_time + $offset) . "</span>";
			echo " @ <span id=\"s2crontime\" style=\"background-color: #FFFBCC\">" . date_i18n($time_format, $scheduled_time + $offset) . "</span> ";
			echo "<a href=\"#\" onclick=\"s2_show('cron'); return false;\">" . __('Edit', 'subscribe2') . "</a></span>\n";
			echo "<span id=\"s2cron_2\">\r\n";
			echo "<input id=\"s2datepicker\" name=\"crondate\" value=\"" . date_i18n($date_format, $scheduled_time + $offset) . "\">\r\n";
			$hours = array('12:00 am', '1:00 am', '2:00 am', '3:00 am', '4:00 am', '5:00 am', '6:00 am', '7:00 am', '8:00 am', '9:00 am', '10:00 am', '11:00 am', '12:00 pm', '1:00 pm', '2:00 pm', '3:00 pm', '4:00 pm', '5:00 pm', '6:00 pm', '7:00 pm', '8:00 pm', '9:00 pm', '10:00 pm', '11:00 pm');
			$current_hour = intval(date_i18n('G', $scheduled_time + $offset));
			echo "<select name=\"crontime\">\r\n";
			foreach ( $hours as $key => $value ) {
				echo "<option value=\"" . $key . "\"";
				if ( !empty($scheduled_time) && $key === $current_hour ) {
					echo " selected=\"selected\"";
				}
				echo ">" . $value . "</option>\r\n";
			}
			echo "</select>\r\n";
			echo "<a href=\"#\" onclick=\"s2_cron_update('cron'); return false;\">". __('Update', 'subscribe2') . "</a>\n";
			echo "<a href=\"#\" onclick=\"s2_cron_revert('cron'); return false;\">". __('Revert', 'subscribe2') . "</a></span>\n";
			if ( !empty($this->subscribe2_options['last_s2cron']) ) {
				echo "<p>" . __('Attempt to resend the last Digest Notification email', 'subscribe2') . ": ";
				echo "<input type=\"submit\" class=\"button-secondary\" name=\"resend\" value=\"" . __('Resend Digest', 'subscribe2') . "\" /></p>\r\n";
			}
		} else {
			echo "<br />";
		}
	} // end display_digest_choices()

	/**
	Create and display a dropdown list of pages
	*/
	function pages_dropdown($s2page) {
		$pages = get_pages();
		if ( empty($pages) ) { return; }

		$option = '';
		foreach ( $pages as $page ) {
			$option .= "<option value=\"" . $page->ID . "\"";
			if ( $page->ID == $s2page ) {
				$option .= " selected=\"selected\"";
			}
			$option .= ">";
			$parents = array_reverse( get_ancestors($page->ID, 'page') );
			if ( $parents ) {
				foreach ( $parents as $parent ) {
					$option .= get_the_title($parent) . ' &raquo; ';
				}
			}
			$option .= $page->post_title . "</option>\r\n";
		}

		echo $option;
	} // end pages_dropdown()

	/**
	Subscribe all registered users to category selected on Admin Manage Page
	*/
	function subscribe_registered_users($emails = '', $cats = array()) {
		if ( '' == $emails || '' == $cats ) { return false; }
		global $wpdb;

		$useremails = explode(",\r\n", $emails);
		$useremails = implode(", ", array_map(array($this, 'prepare_in_data'), $useremails));

		$sql = "SELECT ID FROM $wpdb->users WHERE user_email IN ($useremails)";
		$user_IDs = $wpdb->get_col($sql);

		foreach ( $user_IDs as $user_ID ) {
			$old_cats = get_user_meta($user_ID, $this->get_usermeta_keyname('s2_subscribed'), true);
			if ( !empty($old_cats) ) {
				$old_cats = explode(',', $old_cats);
				$newcats = array_unique(array_merge($cats, $old_cats));
			} else {
				$newcats = $cats;
			}
			if ( !empty($newcats) && $newcats !== $old_cats) {
				// add subscription to these cat IDs
				foreach ( $newcats as $id ) {
					update_user_meta($user_ID, $this->get_usermeta_keyname('s2_cat') . $id, $id);
				}
				update_user_meta($user_ID, $this->get_usermeta_keyname('s2_subscribed'), implode(',', $newcats));
			}
			unset($newcats);
		}
	} // end subscribe_registered_users()

	/**
	Unsubscribe all registered users to category selected on Admin Manage Page
	*/
	function unsubscribe_registered_users($emails = '', $cats = array()) {
		if ( '' == $emails || '' == $cats ) { return false; }
		global $wpdb;

		$useremails = explode(",\r\n", $emails);
		$useremails = implode(", ", array_map(array($this, 'prepare_in_data'), $useremails));

		$sql = "SELECT ID FROM $wpdb->users WHERE user_email IN ($useremails)";
		$user_IDs = $wpdb->get_col($sql);

		foreach ( $user_IDs as $user_ID ) {
			$old_cats = explode(',', get_user_meta($user_ID, $this->get_usermeta_keyname('s2_subscribed'), true));
			$remain = array_diff($old_cats, $cats);
			if ( !empty($remain) && $remain !== $old_cats) {
				// remove subscription to these cat IDs and update s2_subscribed
				foreach ( $cats as $id ) {
					delete_user_meta($user_ID, $this->get_usermeta_keyname('s2_cat') . $id);
				}
				update_user_meta($user_ID, $this->get_usermeta_keyname('s2_subscribed'), implode(',', $remain));
			} else {
				// remove subscription to these cat IDs and update s2_subscribed to ''
				foreach ( $cats as $id ) {
					delete_user_meta($user_ID, $this->get_usermeta_keyname('s2_cat') . $id);
				}
				delete_user_meta($user_ID, $this->get_usermeta_keyname('s2_subscribed'));
			}
			unset($remain);
		}
	} // end unsubscribe_registered_users()

	/**
	Handles bulk changes to email format for Registered Subscribers
	*/
	function format_change($emails, $format) {
		if ( empty($format) ) { return; }

		global $wpdb;
		$useremails = explode(",\r\n", $emails);
		$useremails = implode(", ", array_map(array($this,'prepare_in_data'), $useremails));
		$ids = $wpdb->get_col("SELECT ID FROM $wpdb->users WHERE user_email IN ($useremails)");
		$ids = implode(',', array_map(array($this, 'prepare_in_data'), $ids));
		$sql = "UPDATE $wpdb->usermeta SET meta_value='{$format}' WHERE meta_key='" . $this->get_usermeta_keyname('s2_format') . "' AND user_id IN ($ids)";
		$wpdb->query($sql);
	} // end format_change()

	/**
	Handles bulk update to digest preferences
	*/
	function digest_change($emails, $digest) {
		if ( empty($digest) ) { return; }

		global $wpdb;
		$useremails = explode(",\r\n", $emails);
		$useremails = implode(", ", array_map(array($this, 'prepare_in_data'), $useremails));

		$sql = "SELECT ID FROM $wpdb->users WHERE user_email IN ($useremails)";
		$user_IDs = $wpdb->get_col($sql);

		if ( $digest == 'digest' ) {
			$exclude = explode(',', $this->subscribe2_options['exclude']);
			if ( !empty($exclude) ) {
				$all_cats = $this->all_cats(true, 'ID');
			} else {
				$all_cats = $this->all_cats(false, 'ID');
			}

			$cats_string = '';
			foreach ( $all_cats as $cat ) {
				('' == $cats_string) ? $cats_string = "$cat->term_id" : $cats_string .= ",$cat->term_id";
			}

			foreach ( $user_IDs as $user_ID ) {
				foreach ( $all_cats as $cat ) {
					update_user_meta($user_ID, $this->get_usermeta_keyname('s2_cat') . $cat->term_id, $cat->term_id);
				}
				update_user_meta($user_ID, $this->get_usermeta_keyname('s2_subscribed'), $cats_string);
			}
		} elseif ( $digest == '-1' ) {
			foreach ( $user_IDs as $user_ID ) {
				$cats = explode(',', get_user_meta($user_ID, $this->get_usermeta_keyname('s2_subscribed'), true));
				foreach ( $cats as $id ) {
					delete_user_meta($user_ID, $this->get_usermeta_keyname('s2_cat') . $id);
				}
				delete_user_meta($user_ID, $this->get_usermeta_keyname('s2_subscribed'));
			}
		}
	} // end digest_change()

/* ===== functions to handle addition and removal of WordPress categories ===== */
	/**
	Autosubscribe registered users to newly created categories
	if registered user has selected this option
	*/
	function new_category($new_category='') {
		if ( 'no' == $this->subscribe2_options['show_autosub'] ) { return; }

		global $wpdb;
		if ( $this->subscribe2_options['email_freq'] != 'never' ) {
			// if we are doing digests add new categories to users who are currently opted in
			$sql = $wpdb->prepare("SELECT DISTINCT user_id FROM $wpdb->usermeta WHERE meta_key=%s AND meta_value<>''", $this->get_usermeta_keyname('s2_subscribed'));
			$user_IDs = $wpdb->get_col($sql);
			foreach ( $user_IDs as $user_ID ) {
				$old_cats = get_user_meta($user_ID, $this->get_usermeta_keyname('s2_subscribed'), true);
				$old_cats = explode(',', $old_cats);
				$newcats = array_merge($old_cats, (array)$new_category);
				update_user_meta($user_ID, $this->get_usermeta_keyname('s2_cat') . $new_category, $new_category);
				update_user_meta($user_ID, $this->get_usermeta_keyname('s2_subscribed'), implode(',', $newcats));
			}
			return;
		}

		if ( 'yes' == $this->subscribe2_options['show_autosub'] ) {
			if ( $this->s2_mu ) {
				$sql = $wpdb->prepare("SELECT DISTINCT a.user_id FROM $wpdb->usermeta AS a INNER JOIN $wpdb->usermeta AS b WHERE a.user_id = b.user_id AND a.meta_key=%s AND a.meta_value='yes' AND b.meta_key=%s", $this->get_usermeta_keyname('s2_autosub'), $this->get_usermeta_keyname('s2_subscribed'));
			} else {
				$sql = $wpdb->prepare("SELECT DISTINCT user_id FROM $wpdb->usermeta WHERE $wpdb->usermeta.meta_key=%s AND $wpdb->usermeta.meta_value='yes'", $this->get_usermeta_keyname('s2_autosub'));
			}
			$user_IDs = $wpdb->get_col($sql);
			if ( '' == $user_IDs ) { return; }

			foreach ( $user_IDs as $user_ID ) {
				$old_cats = get_user_meta($user_ID, $this->get_usermeta_keyname('s2_subscribed'), true);
				if ( empty($old_cats) ) {
					$newcats = (array)$new_category;
				} else {
					$old_cats = explode(',', $old_cats);
					$newcats = array_merge($old_cats, (array)$new_category);
				}
				// add subscription to these cat IDs
				update_user_meta($user_ID, $this->get_usermeta_keyname('s2_cat') . $new_category, $new_category);
				update_user_meta($user_ID, $this->get_usermeta_keyname('s2_subscribed'), implode(',', $newcats));
			}
		} elseif ( 'exclude' == $this->subscribe2_options['show_autosub'] ) {
			$excluded_cats = explode(',', $this->subscribe2_options['exclude']);
			$excluded_cats[] = $new_category;
			$this->subscribe2_options['exclude'] = implode(',', $excluded_cats);
			update_option('subscribe2_options', $this->subscribe2_options);
		}
	} // end new_category()

	/**
	Automatically delete subscriptions to a category when it is deleted
	*/
	function delete_category($deleted_category='') {
		global $wpdb;

		if ( $this->s2_mu ) {
			$sql = $wpdb->prepare("SELECT DISTINCT a.user_id FROM $wpdb->usermeta AS a INNER JOIN $wpdb->usermeta AS b WHERE a.user_id = b.user_id AND a.meta_key=%s AND b.meta_key=%s", $this->get_usermeta_keyname('s2_cat') . $deleted_category, $this->get_usermeta_keyname('s2_subscribed'));
		} else {
			$sql = $wpdb->prepare("SELECT DISTINCT user_id FROM $wpdb->usermeta WHERE meta_key=%s", $this->get_usermeta_keyname('s2_cat') . $deleted_category);
		}
		$user_IDs = $wpdb->get_col($sql);
		if ( '' == $user_IDs ) { return; }

		foreach ( $user_IDs as $user_ID ) {
			$old_cats = explode(',', get_user_meta($user_ID, $this->get_usermeta_keyname('s2_subscribed'), true));
			if ( !is_array($old_cats) ) {
				$old_cats = array($old_cats);
			}
			// add subscription to these cat IDs
			delete_user_meta($user_ID, $this->get_usermeta_keyname('s2_cat') . $deleted_category);
			$remain = array_diff($old_cats, (array)$deleted_category);
			update_user_meta($user_ID, $this->get_usermeta_keyname('s2_subscribed'), implode(',', $remain));
		}
	} // end delete_category()

/* ===== functions to show & handle one-click subscription ===== */
	/**
	Show form for one-click subscription on user profile page
	*/
	function one_click_profile_form($user) {
		echo "<h3>" . __('Email subscription', 'subscribe2') . "</h3>\r\n";
		echo "<table class=\"form-table\">\r\n";
		echo "<tr><th scope=\"row\">" . __('Subscribe / Unsubscribe', 'subscribe2') . "</th>\r\n";
		echo "<td><label><input type=\"checkbox\" name=\"sub2-one-click-subscribe\" value=\"1\" " . checked( ! get_user_meta($user->ID, $this->get_usermeta_keyname('s2_subscribed'), true), false, false ) . " /> " . __('Receive notifications', 'subscribe2') . "</label><br />\r\n";
		echo "<span class=\"description\">" . __('Check if you want to receive email notification when new posts are published', 'subscribe2') . "</span>\r\n";
		echo "</td></tr></table>\r\n";
	} // end one_click_profile_form()

	/**
	Handle submission from profile one-click subscription
	*/
	function one_click_profile_form_save($user_ID) {
		if ( !current_user_can( 'edit_user', $user_ID ) ) {
			return false;
		}

		if ( isset( $_POST['sub2-one-click-subscribe'] ) && 1 == $_POST['sub2-one-click-subscribe'] ) {
			// Subscribe
			$this->one_click_handler($user_ID, 'subscribe');
		} else {
			// Unsubscribe
			$this->one_click_handler($user_ID, 'unsubscribe');
		}
	} // end one_click_profile_form_save()
}
?>