<?php
/*
Plugin Name: Magic Admin
Plugin URI: 
Description: A custom dashboard management plugin.
Version: 0.0.1
Author: Blind Tigers
Author URI: blindtigers.com
*/
/*  
	Copyright 2011 Blind Tigers

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
//Do a PHP version check, require 5.2 or newer
if(version_compare(PHP_VERSION, '5.2.0', '<'))
{
	//Silently deactivate plugin, keeps admin usable
	deactivate_plugins(plugin_basename(__FILE__), true);
	//Spit out die messages
	wp_die(sprintf(__('Your PHP version is too old, please upgrade to a newer version. Your version is %s, this plugin requires %s', 'bt_magic'), phpversion(), '5.2.0'));
}
//Include admin base class
if(!class_exists('mtekk_admin'))
{
	require_once(dirname(__FILE__) . '/includes/mtekk_admin_class.php');
}
/**
 * The administrative interface class 
 */
class MagicAdmin extends mtekk_admin
{
	protected $version = '0.0.1';
	protected $full_name = 'Magic Admin Settings';
	protected $short_name = 'Magic Admin';
	protected $access_level = 'manage_options';
	protected $identifier = 'bt_magic';
	protected $unique_prefix = 'btma';
	protected $plugin_basename = '';
	protected $opt = array(
				'hmessage' => 'Need some help? Call or text Toby at 612-293-8629.  Or <a href="mailto:toby@themightymo.com">email</a>.',
				'bshow_message' => true,
				'stitle_0' => '',
				'simg_url_0' => '',
				'slink_url_0' => '',
				'stitle_1' => '',
				'simg_url_1' => '',
				'slink_url_1' => '',
				'stitle_2' => '',
				'simg_url_2' => '',
				'slink_url_2' => '',
				'stitle_3' => '',
				'simg_url_3' => '',
				'slink_url_3' => '',
				'stitle_4' => '',
				'simg_url_4' => '',
				'slink_url_4' => '',
				'stitle_5' => '',
				'simg_url_5' => '',
				'slink_url_5' => '',
				'stitle_6' => '',
				'simg_url_6' => '',
				'slink_url_6' => '',
				'stitle_7' => '',
				'simg_url_7' => '',
				'slink_url_7' => ''
	);
	/**
	 * __construct()
	 * 
	 * Class default constructor
	 */
	function __construct()
	{
		//We set the plugin basename here, could manually set it, but this is for demonstration purposes
		$this->plugin_basename = plugin_basename(__FILE__);
		add_action('admin_menu', array($this, 'kill_admin_menu_items'));
		add_action('wp_dashboard_setup', array($this, 'kill_dashboard_widgets'));
		//We're going to make sure we load the parent's constructor
		parent::__construct();
	}
	/**
	 * admin initialisation callback function
	 * 
	 * is bound to wpordpress action 'admin_init' on instantiation
	 * 
	 * @return void
	 */
	function init()
	{
		//We're going to make sure we run the parent's version of this function as well
		parent::init();
		//We can not synchronize our database options untill after the parent init runs (the reset routine must run first if it needs to)
		$this->opt = get_option($this->unique_prefix . '_options');
		//Add javascript enqeueing callback
		//add_action('wp_print_scripts', array($this, 'javascript'));
		if(!current_user_can($this->access_level))
		{
			global $pagenow, $wp_actions;
			//var_dump($menu);
			if($pagenow === 'index.php')
			{
					add_action('admin_footer', array($this, 'admin_footer'));	
					add_action('adminmenu', array($this, 'draw_admin_page'));
			}
			add_filter('favorite_actions', array($this, 'fav_act'));
			add_action('wp_print_scripts', array($this, 'admin_head'));
			//add_action('load-index.php', array($this, 'draw_admin_page'));
			//remove_all_actions('load-' . $pagenow, '999999999');
			//$this->draw_admin_page();
		}
	}
	function kill_dashboard_widgets()
	{
		//Globalize the metaboxes array, this holds all the widgets for wp-admin
		global $wp_meta_boxes;
		if(!current_user_can($this->access_level))
		{
			//Remove the quickpress widget
			$wp_meta_boxes['dashboard'] = array();
		}
	}
	function fav_act()
	{
		return array();
	}
	function kill_admin_menu_items()
	{
	  global $menu;
		if(!current_user_can($this->access_level))
		{
			$menu = array();
		}
	}
	/**
	 * security
	 * 
	 * Makes sure the current user can manage options to proceed
	 */
	function security()
	{
		//If the user can not manage options we will die on them
		if(!current_user_can($this->access_level))
		{
			wp_die(__('Insufficient privileges to proceed.', $this->identifier));
		}
	}
	/**
	 * Upgrades input options array, sets to $this->opt
	 * 
	 * @param array $opts
	 * @param string $version the version of the passed in options
	 */
	function opts_upgrade($opts, $version)
	{
		//If our version is not the same as in the db, time to update
		if($version !== $this->version)
		{
			//Upgrading from 0.2.x
			if(version_compare($version, '0.3.0', '<'))
			{
				$opts['short_url'] = false;
			}
			//Save the passed in opts to the object's option array
			$this->opt = $opts;
		}
	}
	function draw_admin_page()
	{
		$output = '<div id="magic-admin">';
		for($i = 0; $i < 8; $i++)
		{
			if($this->opt['slink_url_' . $i])
			{
				$output .= '<a class="magic-button" href="' . $this->opt['slink_url_' . $i] . '"><img src="' . $this->opt['simg_url_' . $i] . '" alt="" /><br />' . $this->opt['stitle_' . $i] . '</a>';
			}
		}
		if($this->opt['bshow_message'])
		{
			$output .= '<div class="clear"></div><div id="magic-support">' . $this->opt['hmessage'] . '<div class="clear"></div></div>';
		}
		$output .= '</div>';
		return $output;
	}
	/**
	 * javascript
	 *
	 * Enqueues JS dependencies (jquery) for the tabs
	 * 
	 * @see admin_init()
	 * @return void
	 */
	function javascript()
	{

	}
	/**
	 * get help text
	 * 
	 * @return string
	 */
	protected function _get_help_text()
	{
		return sprintf(__('Tips for the settings are located below select options. Please refer to the %sdocumentation%s for more information.', 'bt_magic'), 
			'<a title="' . __('Go to the Relatively Perfect online documentation', 'bt_magic') . '" href="http://urlhere">', '</a>');
	}
	/**
	 * admin_head
	 *
	 * Adds in the JavaScript and CSS for the tabs in the adminsitrative 
	 * interface
	 * 
	 */
	function admin_styles()
	{
		wp_enqueue_style('mtekk_admin_tabs');
	}
	function admin_scripts()
	{
		//Enqueue ui-tabs
		wp_enqueue_script('jquery-ui-tabs');
		//Enqueue the admin tabs javascript
		wp_enqueue_script('mtekk_admin_tabs');
		//Load the translations for the tabs
		wp_localize_script('mtekk_admin_tabs', 'objectL10n', array(
			'mtad_import' => __('Import', $this->identifier),
			'mtad_export' => __('Export', $this->identifier),
			'mtad_reset' => __('Reset', $this->identifier),
		));
	}
	function admin_head()
	{	
		if(!current_user_can($this->access_level))
		{		
		/* First, let's get rid of the Help menu, update nag, Personal Options section */
				echo "\n" . '<style type="text/css" media="screen">#your-profile { display: none; } .update-nag, #screen-meta, .color-option, .show-admin-bar { display: none !important; }a  {color: #5f5f5f;font-family: sans-serif;}#magic-admin {margin: 10px auto;width: 960px;}.magic-button {float: left;	margin: 0 0 0 20px;padding: 5px 0;text-align: center;text-decoration: none;text-transform: uppercase;	width: auto;	}a:hover.magic-button {background: #f9f9f9;}.magic-button img {	border: none;	}	#magic-support {	background: #f9f9f9;color: #5f5f5f;font-family: sans-serif;font-size: 12px;	line-height: 30px;margin: 10px 0 10px 20px;padding: 5px 10px;}#magic-support a:hover {text-decoration: none;	}#magic-support img {float: left; margin: 0 10px 0 0;}.clear {clear: both;}</style>';
				echo "\n" . '<script type="text/javascript">jQuery(document).ready(function($) { $(\'form#your-profile > h3:first\').hide();  $(\'form#your-profile > table:first\').hide(); $(\'form#your-profile\').show(); });</script>' . "\n";
		}
	}
	function admin_footer()
	{
		?><script type="text/javascript">jQuery(document).ready(function($){
			$("#wpbody").css("margin" , "0px");
			$("#wpbody").css("height" , "0px");
			$("#wpbody").css("width" , "100%");
			$("#adminmenu").remove();
			$(".wrap").empty().append('<?php echo $this->draw_admin_page();?>');
			})</script><?php
	}
	/**
	 * admin_page
	 * 
	 * The administrative page for Relatively Perfect
	 * 
	 */
	function admin_page()
	{
		global $wp_taxonomies;
		$this->security();
		$this->version_check(get_option($this->unique_prefix . '_version'));
		?>
		<div class="wrap"><h2><?php _e('Relatively Perfect Settings', 'bt_magic'); ?></h2>		
		<p<?php if($this->_has_contextual_help): ?> class="hide-if-js"<?php endif; ?>><?php 
			print $this->_get_help_text();			 
		?></p>
		<form action="options-general.php?page=bt_magic" method="post" id="<?php echo $this->unique_prefix;?>-options">
			<?php settings_fields($this->unique_prefix . '_options');?>
			<div id="hasadmintabs">
			<fieldset id="general" class="<?php echo $this->unique_prefix;?>_options">
				<h3><?php _e('General', 'bt_magic'); ?></h3>
				<table class="form-table">
					<?php
						for($i = 0; $i < 8; $i++)
						{
							$this->input_text(sprintf(__('Title %s', 'bt_magic'), $i), 'stitle_'.$i, '32', false, sprintf(__('Title for button %s', 'bt_magic'), $i));
							$this->input_text(sprintf(__('Image URL %s', 'bt_magic'), $i), 'simg_url_'.$i, '32', false, sprintf(__('Image for button %s', 'bt_magic'), $i));
							$this->input_text(sprintf(__('Link URL %s', 'bt_magic'), $i), 'slink_url_'.$i, '32', false, sprintf(__('Destination URL for button %s', 'bt_magic'), $i));
						}
					?>
				</table>
			</fieldset>
			<fieldset id="message" class="<?php echo $this->unique_prefix;?>_options">
				<h3><?php _e('Message', 'bt_magic'); ?></h3>
				<table class="form-table">
					<?php
						$this->input_check(__('Show Message', 'bt_magic'), 'bshow_message', __('Show message to the users', 'bt_magic'));
						$this->textbox(__('Message', 'bt_magic'), 'hmessage', '3', false,  __('Message to display on the user landing page.', 'bt_magic'));
					?>
				</table>
			</fieldset>
			</div>
			<p class="submit"><input type="submit" class="button-primary" name="<?php echo $this->unique_prefix;?>_admin_options" value="<?php esc_attr_e('Save Changes') ?>" /></p>
		</form>
		<?php $this->import_form(); ?>
		</div>
		<?php
	}
}
//Let's make an instance of our object takes care of everything
$MagicAdmin = new MagicAdmin;