<?php
/************************************************************
 * This plugin was modified by Revmakx						*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
/*************************************************************
 * 
 * core.class.php
 * 
 * Upgrade Plugins
 * 
 * 
 * Copyright (c) 2011 Prelovac Media
 * www.prelovac.com
 **************************************************************/
 
class IWP_MMB_Core extends IWP_MMB_Helper
{
    var $name;
    var $slug;
    var $settings;
    var $remote_client;
    var $comment_instance;
    var $plugin_instance;
    var $theme_instance;
    var $wp_instance;
    var $post_instance;
    var $stats_instance;
    var $search_instance;
    var $links_instance;
    var $user_instance;
    var $backup_instance;
	var $wordfence_instance;
    var $installer_instance;
    var $iwp_mmb_multisite;
    var $network_admin_install;
	
	var $backup_repository_instance;
	var $optimize_instance;
	
    private $action_call;
    private $action_params;
    private $iwp_mmb_pre_init_actions;
    private $iwp_mmb_pre_init_filters;
    private $iwp_mmb_init_actions;
    
    
    function __construct()
    {
        global $iwp_mmb_plugin_dir, $wpmu_version, $blog_id, $_iwp_mmb_plugin_actions, $_iwp_mmb_item_filter;
        
		$_iwp_mmb_plugin_actions = array();
        $this->name     = 'Manage Multiple Blogs';
        $this->slug     = 'manage-multiple-blogs';
		$this->action_call = null;
		$this->action_params = null;
		
		
        $this->settings = get_option($this->slug);
        if (!$this->settings) {
            $this->settings = array(
                'blogs' => array(),
                'current_blog' => array(
                    'type' => null
                )
            );
            $this->save_options();
        }
		if ( function_exists('is_multisite') ) {
            if ( is_multisite() ) {
                $this->iwp_mmb_multisite = $blog_id;
                $this->network_admin_install = get_option('iwp_client_network_admin_install');
            }
        } else if (!empty($wpmu_version)) {
            $this->iwp_mmb_multisite = $blog_id;
            $this->network_admin_install = get_option('iwp_client_network_admin_install');
        } else {
			$this->iwp_mmb_multisite = false;
			$this->network_admin_install = null;
		}
		
		// admin notices
		if ( !get_option('iwp_client_public_key') ){
			if( $this->iwp_mmb_multisite ){
				if( is_network_admin() && $this->network_admin_install == '1'){
					add_action('network_admin_notices', array( &$this, 'network_admin_notice' ));
				} else if( $this->network_admin_install != '1' ){
					//$parent_key = $this->get_parent_blog_option('iwp_client_public_key');//IWP commented to show notice to all subsites of network
					//if(empty($parent_key))//IWP commented to show notice to all subsites of network
						add_action('admin_notices', array( &$this, 'admin_notice' ));
				}
			} else {
				add_action('admin_notices', array( &$this, 'admin_notice' ));
			}
		}
		
		// default filters
		//$this->iwp_mmb_pre_init_filters['get_stats']['iwp_mmb_stats_filter'][] = array('IWP_MMB_Stats', 'pre_init_stats'); // called with class name, use global $iwp_mmb_core inside the function instead of $this
		$this->iwp_mmb_pre_init_filters['get_stats']['iwp_mmb_stats_filter'][] = 'iwp_mmb_pre_init_stats';
		
		$_iwp_mmb_item_filter['pre_init_stats'] = array( 'core_update', 'hit_counter', 'comments', 'backups', 'posts', 'drafts', 'scheduled' );
		$_iwp_mmb_item_filter['get'] = array( 'updates', 'errors','plugins_status','themes_status' );
		
		$this->iwp_mmb_pre_init_actions = array(
			'backup_req' => 'iwp_mmb_get_backup_req',
		);
		
		$this->iwp_mmb_init_actions = array(
			'do_upgrade' => 'iwp_mmb_do_upgrade',
			'get_stats' => 'iwp_mmb_stats_get',
			'remove_site' => 'iwp_mmb_remove_site',
			'backup_clone' => 'iwp_mmb_backup_now',
			'restore' => 'iwp_mmb_restore_now',
			'optimize_tables' => 'iwp_mmb_optimize_tables',
			'check_wp_version' => 'iwp_mmb_wp_checkversion',
			'create_post' => 'iwp_mmb_post_create',
			'update_client' => 'iwp_mmb_update_client_plugin',
			
			'change_comment_status' => 'iwp_mmb_change_comment_status',
			'change_post_status' => 'iwp_mmb_change_post_status',
			'get_comment_stats' => 'iwp_mmb_comment_stats_get',
			
			'get_links' => 'iwp_mmb_get_links',
			'add_link' => 'iwp_mmb_add_link',
			'delete_link' => 'iwp_mmb_delete_link',
			'delete_links' => 'iwp_mmb_delete_links',
			
			'create_post' => 'iwp_mmb_post_create',
			'change_post_status' => 'iwp_mmb_change_post_status',
			'get_posts' => 'iwp_mmb_get_posts',
			'delete_post' => 'iwp_mmb_delete_post',
			'delete_posts' => 'iwp_mmb_delete_posts',
			'edit_posts' => 'iwp_mmb_edit_posts',
			'get_pages' => 'iwp_mmb_get_pages',
			'delete_page' => 'iwp_mmb_delete_page',
			
			'install_addon' => 'iwp_mmb_install_addon',
			'add_link' => 'iwp_mmb_add_link',
			'add_user' => 'iwp_mmb_add_user',
			'email_backup' => 'iwp_mmb_email_backup',
			'check_backup_compat' => 'iwp_mmb_check_backup_compat',
			'scheduled_backup' => 'iwp_mmb_scheduled_backup',
			'run_task' => 'iwp_mmb_run_task_now',
			'delete_schedule_task' => 'iwp_mmb_delete_task_now',
			'execute_php_code' => 'iwp_mmb_execute_php_code',
			'delete_backup' => 'iwp_mmb_delete_backup',
			'remote_backup_now' => 'iwp_mmb_remote_backup_now',
			'set_notifications' => 'iwp_mmb_set_notifications',
			'clean_orphan_backups' => 'iwp_mmb_clean_orphan_backups',
			'get_users' => 'iwp_mmb_get_users',
			'edit_users' => 'iwp_mmb_edit_users',
			'get_plugins_themes' => 'iwp_mmb_get_plugins_themes',
			'edit_plugins_themes' => 'iwp_mmb_edit_plugins_themes',
			'get_comments' => 'iwp_mmb_get_comments',
			'action_comment' => 'iwp_mmb_action_comment',
			'bulk_action_comments' => 'iwp_mmb_bulk_action_comments',
			'replyto_comment' => 'iwp_mmb_reply_comment',
			'client_brand' => 'iwp_mmb_client_brand',
			'set_alerts' => 'iwp_mmb_set_alerts',
			'maintenance' => 'iwp_mmb_maintenance_mode',
			
			'wp_optimize' => 'iwp_mmb_wp_optimize',
			
			'backup_repository' => 'iwp_mmb_backup_repository',
			'trigger_backup_multi' => 'iwp_mmb_trigger_check',
			'get_all_links'         => 'iwp_mmb_get_all_links',
            'update_broken_link'    => 'iwp_mmb_update_broken_link',
            'unlink_broken_link'    => 'iwp_mmb_unlink_broken_link',
            'markasnot_broken_link' => 'iwp_mmb_markasnot_broken_link',
            'dismiss_broken_link' => 'iwp_mmb_dismiss_broken_link',
            'undismiss_broken_link' => 'iwp_mmb_undismiss_broken_link',
            'bulk_actions_processor' => 'iwp_mmb_bulk_actions_processor',

            'file_editor_upload'    => 'iwp_mmb_file_editor_upload',

            'put_redirect_url'      =>  'iwp_mmb_gwmt_redirect_url',
            'put_redirect_url_again'=>  'iwp_mmb_gwmt_redirect_url_again',
			'wordfence_scan' => 'iwp_mmb_wordfence_scan',
			'wordfence_load' => 'iwp_mmb_wordfence_load',
			'backup_test_site' => 'iwp_mmb_backup_test_site',
			'ithemes_security_load' => 'iwp_mmb_ithemes_security_load',
			'get_seo_info' => 'iwp_mmb_yoast_get_seo_info',
			'save_seo_info' => 'iwp_mmb_yoast_save_seo_info'
		);
		
		add_action('rightnow_end', array( &$this, 'add_right_now_info' ));       
		add_action('admin_init', array(&$this,'admin_actions'));   
		add_action('init', array( &$this, 'iwp_mmb_remote_action'), 9999);
		add_action('setup_theme', 'iwp_mmb_parse_request');
		add_action('set_auth_cookie', array( &$this, 'iwp_mmb_set_auth_cookie'));
		add_action('set_logged_in_cookie', array( &$this, 'iwp_mmb_set_logged_in_cookie'));
		
    }
    
	function iwp_mmb_remote_action(){
		if($this->action_call != null){
			$params = isset($this->action_params) && $this->action_params != null ? $this->action_params : array();
			call_user_func($this->action_call, $params);
		}
	}
	
	function register_action_params( $action = false, $params = array() ){
		
		if(isset($this->iwp_mmb_pre_init_actions[$action]) && function_exists($this->iwp_mmb_pre_init_actions[$action])){
			call_user_func($this->iwp_mmb_pre_init_actions[$action], $params);
		}
		
		if(isset($this->iwp_mmb_init_actions[$action]) && function_exists($this->iwp_mmb_init_actions[$action])){
			$this->action_call = $this->iwp_mmb_init_actions[$action];
			$this->action_params = $params;
			
			if( isset($this->iwp_mmb_pre_init_filters[$action]) && !empty($this->iwp_mmb_pre_init_filters[$action])){
				global $iwp_mmb_filters;
				
				foreach($this->iwp_mmb_pre_init_filters[$action] as $_name => $_functions){
					if(!empty($_functions)){
						$data = array();
						
						foreach($_functions as $_k => $_callback){
							if(is_array($_callback) && method_exists($_callback[0], $_callback[1]) ){
								$data = call_user_func( $_callback, $params );
							} elseif (is_string($_callback) && function_exists( $_callback )){
								$data = call_user_func( $_callback, $params );
							}
							$iwp_mmb_filters[$_name] = isset($iwp_mmb_filters[$_name]) && !empty($iwp_mmb_filters[$_name]) ? array_merge($iwp_mmb_filters[$_name], $data) : $data;
							add_filter( $_name, create_function( '$a' , 'global $iwp_mmb_filters; return array_merge($a, $iwp_mmb_filters["'.$_name.'"]);') );
						}
					}
					
				}
			}
			return true;
		} 
		return false;
	}
	
    /**
     * Add notice to network admin dashboard for security reasons    
     * 
     */
    function network_admin_notice()
    {
        echo '<div class="error" style="text-align: center;"><p style="font-size: 14px; font-weight: bold; color:#c00;">Attention !</p>
		<p>The InfiniteWP client plugin has to be activated on individual sites. Kindly deactivate the plugin from the network admin dashboard and activate them from the individual dashboards.</p></div>';
    }
	
		
	/**
     * Add notice to admin dashboard for security reasons    
     * 
     */
    function admin_notice()
    {
       /* IWP */
		if(defined('MULTISITE') && MULTISITE == true){	
			global $blog_id;			
			$user_id_from_email = get_user_id_from_string( get_blog_option($blog_id, 'admin_email'));
			$details = get_userdata($user_id_from_email);
			$username = $details->user_login;			
		}
		else{
			$current_user = wp_get_current_user(); 
			$username = $current_user->data->user_login;
		}	
		
		$iwp_client_activate_key = get_option('iwp_client_activate_key');
		
		//check BWP 
		$bwp = get_option("bit51_bwps");
		$notice_display_URL=admin_url();
		if(!empty($bwp))
		{
			//$bwpArray = @unserialize($bwp);
			if($bwp['hb_enabled']==1)
			$notice_display_URL = get_option('home');
		}
		
		$notice_display_URL = rtrim($notice_display_URL, '/').'/';
		
		
		echo '<div class="updated" style="text-align: center;"><p style="color: green; font-size: 14px; font-weight: bold;">Add this site to IWP Admin panel</p><p>
		<table border="0" align="center" cellpadding="5">';
		if(!empty($iwp_client_activate_key)){
			echo '<tr><td align="right">WP-ADMIN URL:</td><td align="left"><strong>'.$notice_display_URL.'</strong></td></tr>
			<tr><td align="right">ADMIN USERNAME:</td><td align="left"><strong>'.$username.'</strong> (or any admin id)</td></tr>
            <tr><td align="right">ACTIVATION KEY:</td><td align="left"><strong>'.$iwp_client_activate_key.'</strong></td></tr>
            <tr id="copy_at_once"><td align="right">To quick add, copy this</td><td align="left" style="position:relative;"><input type="text" style="width:295px;" class="read_creds" readonly value="'.$notice_display_URL.'|^|'.$username.'|^|'.$iwp_client_activate_key.'" /></td></tr>
            <tr class="only_flash"><td></td><td align="left" style="position:relative;"><div id="copy_details" style="background:#008000;display: inline-block;padding: 4px 10px;border-radius: 5px;color:#fff;font-weight:600;cursor:pointer;">Copy details</div><span class="copy_message" style="display:none;margin-left:10px;color:#008000;">Copied :)</span></td></tr>

            <script type="text/javascript">
                var hasFlash = function() {
                    return (typeof navigator.plugins == "undefined" || navigator.plugins.length == 0) ? !!(new ActiveXObject("ShockwaveFlash.ShockwaveFlash")) : navigator.plugins["Shockwave Flash"];
                };
                var onhoverMsg = "<span class=\"aftercopy_instruction\" style=\"position: absolute;top: 32px;left:20px;background:#fff;border:1px solid #000;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;padding:2px;margin:2px;text-align:center;\">Paste this in any field in the Add Website dialogue in the InfiniteWP admin panel.</span>";
                if(typeof hasFlash() != "undefined"){
                    var client = new ZeroClipboard( jQuery("#copy_details") );
                    client.on( "ready", function(event) {
                        // console.log( "movie is loaded" );

                        client.on( "copy", function(event) {
                            event.clipboardData.setData("text/plain", jQuery(".read_creds").val());
                        } );

                        client.on( "aftercopy", function(event) {
                            // console.log("Copied text to clipboard: " + event.data["text/plain"]);
                            jQuery(".copy_message").show();
                            setTimeout(\'jQuery(".copy_message").hide();\',1000);
                        } );
                    } );

                    client.on( "error", function(event) {
                        ZeroClipboard.destroy();
                    } );
                    jQuery("#copy_at_once").hide();
                    jQuery("#copy_details").mouseenter(function(){jQuery(onhoverMsg).appendTo(jQuery(this).parent());}).mouseleave(function(){jQuery(".aftercopy_instruction").remove();});

                }else{
                    jQuery(".only_flash").remove();
                    jQuery(".read_creds").click(function(){jQuery(this).select();});
                    jQuery(".read_creds").mouseenter(function(e){jQuery(onhoverMsg).appendTo(jQuery(this).parent());}).mouseleave(function(){jQuery(".aftercopy_instruction").remove();});
                }
            </script>';
		}
		else{
			echo '<tr><td align="center">Please deactivate and then activate InfiniteWP Client plugin.</td></tr>';
		}		
		
		echo '</table>
	  	</p></div>';		
		
    }
    
    /**
     * Add an item into the Right Now Dashboard widget 
     * to inform that the blog can be managed remotely
     * 
     */
    function add_right_now_info()
    {
        echo '<div class="iwp_mmb-slave-info">
            <p>This site can be managed remotely.</p>
        </div>';
    }
    
    /**
     * Get parent blog options
     * 
     */
    private function get_parent_blog_option( $option_name = '' )
    {
		global $wpdb;
		$option = $wpdb->get_var( $wpdb->prepare( "SELECT `option_value` FROM {$wpdb->base_prefix}options WHERE option_name = %s LIMIT 1", $option_name ) );
        return $option;
    }
    
	
	/**
     * Gets an instance of the WP_Optimize class
     * 
     */
    function wp_optimize_instance()
    {
        if (!isset($this->optimize_instance)) {
            $this->optimize_instance = new IWP_MMB_Optimize();
        }
        
        return $this->optimize_instance;
    }
    

    /**
     * Gets an instance of the WP_BrokenLinks class
     * 
     */
    function wp_blc_get_blinks()
    {
        global $iwp_mmb_plugin_dir;
	require_once("$iwp_mmb_plugin_dir/addons/brokenlinks/brokenlinks.class.php");
        if (!isset($this->blc_get_blinks)) {
            $this->blc_get_blinks = new IWP_MMB_BLC();
        }
        
        return $this->blc_get_blinks;
    }
    

    /**
     * Gets an instance of the WP_BrokenLinks class
     * 
     */
    function wp_google_webmasters_crawls()
    {
        global $iwp_mmb_plugin_dir;
        require_once("$iwp_mmb_plugin_dir/addons/google_webmasters/google_webmasters.class.php");
		if (!isset($this->get_google_webmasters_crawls)) {
            $this->get_google_webmasters_crawls = new IWP_MMB_GWMT();
        }
        
        return $this->get_google_webmasters_crawls;
    }
    
    /**
     * Gets an instance of the fileEditor class
     * 
     */
    function wp_get_file_editor()
    {
        global $iwp_mmb_plugin_dir;
        require_once("$iwp_mmb_plugin_dir/addons/file_editor/file_editor.class.php");
		if (!isset($this->get_file_editor)) {
            $this->get_file_editor = new IWP_MMB_fileEditor();
        }
        
        return $this->get_file_editor;
    }
    
   
    /**
     * Gets an instance of the yoastWpSeo class
     * 
     */
    function wp_get_yoast_seo()
    {
        global $iwp_mmb_plugin_dir;
        require_once("$iwp_mmb_plugin_dir/addons/yoast_wp_seo/yoast_wp_seo.class.php");
		if (!isset($this->get_yoast_seo)) {
            $this->get_yoast_seo = new IWP_MMB_YWPSEO();
        }
        
        return $this->get_yoast_seo;
    }
    

    /**
     * Gets an instance of the Comment class
     * 
     */
    function get_comment_instance()
    {
        if (!isset($this->comment_instance)) {
            $this->comment_instance = new IWP_MMB_Comment();
        }
        
        return $this->comment_instance;
    }
    
    /**
     * Gets an instance of the Plugin class
     * 
     */
    function get_plugin_instance()
    {
        if (!isset($this->plugin_instance)) {
            $this->plugin_instance = new IWP_MMB_Plugin();
        }
        
        return $this->plugin_instance;
    }
    
    /**
     * Gets an instance of the Theme class
     * 
     */
    function get_theme_instance()
    {
        if (!isset($this->theme_instance)) {
            $this->theme_instance = new IWP_MMB_Theme();
        }
        
        return $this->theme_instance;
    }
    
    
    /**
     * Gets an instance of IWP_MMB_Post class
     * 
     */
    function get_post_instance()
    {
        if (!isset($this->post_instance)) {
            $this->post_instance = new IWP_MMB_Post();
        }
        
        return $this->post_instance;
    }
    
    /**
     * Gets an instance of Blogroll class
     * 
     */
    function get_blogroll_instance()
    {
        if (!isset($this->blogroll_instance)) {
            $this->blogroll_instance = new IWP_MMB_Blogroll();
        }
        
        return $this->blogroll_instance;
    }
    
    
    
    /**
     * Gets an instance of the WP class
     * 
     */
    function get_wp_instance()
    {
        if (!isset($this->wp_instance)) {
            $this->wp_instance = new IWP_MMB_WP();
        }
        
        return $this->wp_instance;
    }
    
    /**
     * Gets an instance of User
     * 
     */
    function get_user_instance()
    {
        if (!isset($this->user_instance)) {
            $this->user_instance = new IWP_MMB_User();
        }
        
        return $this->user_instance;
    }
    
    /**
     * Gets an instance of stats class
     * 
     */
    function get_stats_instance()
    {
        if (!isset($this->stats_instance)) {
            $this->stats_instance = new IWP_MMB_Stats();
        }
        return $this->stats_instance;
    }
    /**
     * Gets an instance of search class
     * 
     */
    function get_search_instance()
    {
        if (!isset($this->search_instance)) {
            $this->search_instance = new IWP_MMB_Search();
        }
        //return $this->search_instance;
        return $this->search_instance;
    }
    /**
     * Gets an instance of stats class
     *
     */
    function get_backup_instance($mechanism='')
    {
		require_once($GLOBALS['iwp_mmb_plugin_dir']."/backup.class.singlecall.php");
		require_once($GLOBALS['iwp_mmb_plugin_dir']."/backup.class.multicall.php");
		//$mechanism = 'multiCall';
        if (!isset($this->backup_instance)) {
			if($mechanism == 'singleCall' || $mechanism == ''){
				$this->backup_instance = new IWP_MMB_Backup_Singlecall();
			}
			elseif($mechanism == 'multiCall'){
				$this->backup_instance = new IWP_MMB_Backup_Multicall();
			}
			else{
				iwp_mmb_response(array('error' => 'mechanism not found'), true);
				//return false;
			}
        }
        
        return $this->backup_instance;
    }

	function get_backup_repository_instance()
    {
        require_once($GLOBALS['iwp_mmb_plugin_dir']."/backup.class.singlecall.php");
		require_once($GLOBALS['iwp_mmb_plugin_dir']."/backup.class.multicall.php");
		if (!isset($this->backup_repository_instance)) {
            $this->backup_repository_instance = new IWP_MMB_Backup_Repository();
        }
        
        return $this->backup_repository_instance;
    }
    
    /**
     * Gets an instance of links class
     *
     */
    function get_link_instance()
    {
        if (!isset($this->link_instance)) {
            $this->link_instance = new IWP_MMB_Link();
        }
        
        return $this->link_instance;
    }
    
    function get_installer_instance()
    {
        if (!isset($this->installer_instance)) {
            $this->installer_instance = new IWP_MMB_Installer();
        }
        return $this->installer_instance;
    }
	
	/*
	 * Get an instance of WordFence 
	 */
	 function get_wordfence_instance()
	 {
	 	if (!isset($this->wordfence_instance)) {
            $this->wordfence_instance = new IWP_WORDFENCE();
        }
        return $this->wordfence_instance;
	 }
	
	
    /**
     * Plugin install callback function
     * Check PHP version
     */
    function install() {
		
        global $wpdb, $_wp_using_ext_object_cache, $current_user;
        $_wp_using_ext_object_cache = false;

        //delete plugin options, just in case
        if ($this->iwp_mmb_multisite != false) {
			$network_blogs = $wpdb->get_results("select `blog_id`, `site_id` from `{$wpdb->blogs}`");
			if(!empty($network_blogs)){
				if( is_network_admin() ){
					update_option('iwp_client_network_admin_install', 1);
					foreach($network_blogs as $details){
						if($details->site_id == $details->blog_id)
							update_blog_option($details->blog_id, 'iwp_client_network_admin_install', 1);
						else 
							update_blog_option($details->blog_id, 'iwp_client_network_admin_install', -1);
							
						delete_blog_option($blog_id, 'iwp_client_nossl_key');
						delete_blog_option($blog_id, 'iwp_client_public_key');
						delete_blog_option($blog_id, 'iwp_client_action_message_id');
					}
				} else {
					update_option('iwp_client_network_admin_install', -1);
					delete_option('iwp_client_nossl_key');
					delete_option('iwp_client_public_key');
					delete_option('iwp_client_action_message_id');
				}
			}
        } else {
            delete_option('iwp_client_nossl_key');
            delete_option('iwp_client_public_key');
            delete_option('iwp_client_action_message_id');
        }
        
        //delete_option('iwp_client_backup_tasks');
        delete_option('iwp_client_notifications');
        delete_option('iwp_client_brand');
        delete_option('iwp_client_pageview_alerts');
		
		add_option('iwp_client_activate_key', sha1( rand(1, 99999). uniqid('', true) . get_option('siteurl') ) );
        
    }
    
    /**
     * Saves the (modified) options into the database
     * 
     */
    function save_options()
    {
        if (get_option($this->slug)) {
            update_option($this->slug, $this->settings);
        } else {
            add_option($this->slug, $this->settings);
        }
    }
    
    /**
     * Deletes options for communication with IWP Admin panel
     * 
     */
    function uninstall( $deactivate = false )
    {
        global $current_user, $wpdb, $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = false;
        
        if ($this->iwp_mmb_multisite != false) {
			$network_blogs = $wpdb->get_col("select `blog_id` from `{$wpdb->blogs}`");
			if(!empty($network_blogs)){
				if( is_network_admin() ){
					if( $deactivate ) {
						delete_option('iwp_client_network_admin_install');
						foreach($network_blogs as $blog_id){
							delete_blog_option($blog_id, 'iwp_client_network_admin_install');
							delete_blog_option($blog_id, 'iwp_client_nossl_key');
							delete_blog_option($blog_id, 'iwp_client_public_key');
							delete_blog_option($blog_id, 'iwp_client_action_message_id');
							delete_blog_option($blog_id, 'iwp_client_maintenace_mode');
						}
					}
				} else {
					if( $deactivate )
						delete_option('iwp_client_network_admin_install');
						
					delete_option('iwp_client_nossl_key');
					delete_option('iwp_client_public_key');
					delete_option('iwp_client_action_message_id');
				}
			}
        } else {
			delete_option('iwp_client_nossl_key');
            delete_option('iwp_client_public_key');
            delete_option('iwp_client_action_message_id');
        }
        
        //Delete options
		delete_option('iwp_client_maintenace_mode');
        //delete_option('iwp_client_backup_tasks');
        wp_clear_scheduled_hook('iwp_client_backup_tasks');
        delete_option('iwp_client_notifications');
        wp_clear_scheduled_hook('iwp_client_notifications');        
        delete_option('iwp_client_brand');
        delete_option('iwp_client_pageview_alerts');
		
		delete_option('iwp_client_activate_key');
    }
    
    
    /**
     * Constructs a url (for ajax purpose)
     * 
     * @param mixed $base_page
     */
    function construct_url($params = array(), $base_page = 'index.php')
    {
        $url = "$base_page?_wpnonce=" . wp_create_nonce($this->slug);
        foreach ($params as $key => $value) {
            $url .= "&$key=$value";
        }
        
        return $url;
    }
    
    /**
     * Client update
     * 
     */
    function update_client_plugin($params)
    {
		
        extract($params);
        if ($download_url) {
            @include_once ABSPATH . 'wp-admin/includes/file.php';
			@include_once ABSPATH . 'wp-admin/includes/plugin.php';
            @include_once ABSPATH . 'wp-admin/includes/misc.php';
            @include_once ABSPATH . 'wp-admin/includes/template.php';
            @include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            @include_once ABSPATH . 'wp-admin/includes/screen.php';
            
            if (!$this->is_server_writable()) {
                return array(
                    'error' => 'Failed. please add FTP details for automatic upgrades.', 'error_code' => 'automatic_upgrade_failed_add_ftp_details'
                );
            }
            
            ob_start();
            @unlink(dirname(__FILE__));
            $upgrader = new Plugin_Upgrader();
            $result   = $upgrader->run(array(
                'package' => $download_url,
                'destination' => WP_PLUGIN_DIR,
                'clear_destination' => true,
                'clear_working' => true,
                'hook_extra' => array(
                    'plugin' => 'iwp-client/init.php'
                )
            ));
            ob_end_clean();
			@wp_update_plugins();
			
			//iwp_mmb_create_backup_table();
			
			//add_action( 'plugins_loaded', 'iwp_mmb_create_backup_table' );
			
			/*global $wpdb;
			
				
			$IWP_MMB_BACKUP_TABLE_VERSION = '1.0';
			if (get_site_option( 'iwp_backup_table_version' ) != $IWP_MMB_BACKUP_TABLE_VERSION) {
				
				$table_name = $wpdb->base_prefix . "iwp_backup_status"; 
								
				$sql = "
						CREATE TABLE IF NOT EXISTS $table_name (
						  `ID` int(11) NOT NULL AUTO_INCREMENT,
						  `historyID` int(11) NOT NULL,
						  `taskName` varchar(255) NOT NULL,
						  `action` varchar(50) NOT NULL,
						  `type` varchar(50) NOT NULL,
						  `category` varchar(50) NOT NULL,
						  `stage` varchar(255) NOT NULL,
						  `status` varchar(255) NOT NULL,
						  `finalStatus` varchar(50) DEFAULT NULL,
						  `statusMsg` varchar(255) NOT NULL,
						  `requestParams` text NOT NULL,
						  `responseParams` longtext,
						  `taskResults` text,
						  `startTime` int(11) DEFAULT NULL,
						  `endTime` int(11) NOT NULL,
						  PRIMARY KEY (`ID`)
						) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
						";
					
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $sql );
				
				add_option( "iwp_backup_table_version", $IWP_MMB_BACKUP_TABLE_VERSION);
			}*/
			// import iwp_client_backup_tasks option table array to row data.
		
			
            if (is_wp_error($result) || !$result) {
                return array(
                    'error' => 'InfiniteWP Client plugin could not be updated.', 'error_code' => 'client_plugin_could_not_be_updated'
                );
            } else {			
                return array(
                    'success' => 'InfiniteWP Client plugin successfully updated.'
                );
            }
        }
        return array(
            'error' => 'Bad download path for client installation file.', 'error_code' => 'client_plugin_bad_download_path'
        );
    }
    
    /**
     * Automatically logs in when called from IWP Admin panel
     * 
     */
    function automatic_login()
    {
		$where      = isset($_GET['iwp_goto']) ? $_GET['iwp_goto'] : false;
        $username   = isset($_GET['username']) ? $_GET['username'] : '';
        $auto_login = isset($_GET['auto_login']) ? $_GET['auto_login'] : 0;
        $_SERVER['HTTP_REFERER']='';
		if( !function_exists('is_user_logged_in') )
			include_once( ABSPATH.'wp-includes/pluggable.php' );
		
		if (( $auto_login && strlen(trim($username)) && !is_user_logged_in() ) || (isset($this->iwp_mmb_multisite) && $this->iwp_mmb_multisite )) {
			$signature  = base64_decode($_GET['signature']);
            $message_id = trim($_GET['message_id']);
            
            $auth = $this->authenticate_message($where . $message_id, $signature, $message_id);
			if ($auth === true) {
				
				if (!headers_sent())
					header('P3P: CP="CAO PSA OUR"');
				
				if(!defined('IWP_MMB_USER_LOGIN'))
					define('IWP_MMB_USER_LOGIN', true);
				
				$siteurl = function_exists('get_site_option') ? get_site_option( 'siteurl' ) : get_option('siteurl');
				$user = $this->iwp_mmb_get_user_info($username);
				wp_set_current_user($user->ID);
				
				if(!defined('COOKIEHASH') || (isset($this->iwp_mmb_multisite) && $this->iwp_mmb_multisite) )
					wp_cookie_constants();
				
				wp_set_auth_cookie($user->ID);
				@iwp_mmb_client_header();
				
				//if((isset($this->iwp_mmb_multisite) && $this->iwp_mmb_multisite ) || isset($_REQUEST['iwpredirect'])){//comment makes force redirect, which fix bug https dashboard
					if(function_exists('wp_safe_redirect') && function_exists('admin_url')){
						wp_safe_redirect(admin_url($where));
						exit();
					}
				//}
			} else {
                wp_die($auth['error']);
            }
        } elseif( is_user_logged_in() ) {
			@iwp_mmb_client_header();
			if(isset($_REQUEST['iwpredirect'])){
				if(function_exists('wp_safe_redirect') && function_exists('admin_url')){
					wp_safe_redirect(admin_url($where));
					exit();
				}
			}
		}
    }
    
	function iwp_mmb_set_auth_cookie( $auth_cookie ){
		if(!defined('IWP_MMB_USER_LOGIN'))
			return false;
		
		if( !defined('COOKIEHASH') )
			wp_cookie_constants();
			
		$_COOKIE['wordpress_'.COOKIEHASH] = $auth_cookie;
		
	}
	function iwp_mmb_set_logged_in_cookie( $logged_in_cookie ){
		if(!defined('IWP_MMB_USER_LOGIN'))
			return false;
	
		if( !defined('COOKIEHASH') )
			wp_cookie_constants();
			
		$_COOKIE['wordpress_logged_in_'.COOKIEHASH] = $logged_in_cookie;
	}
		
    function admin_actions(){
    	add_filter('all_plugins', array($this, 'client_replace'));
    }
    
    function client_replace($all_plugins){
    	$replace = get_option("iwp_client_brand");
    	if(is_array($replace)){
    		if($replace['name'] || $replace['desc'] || $replace['author'] || $replace['author_url']){
    			$all_plugins['iwp-client/init.php']['Name'] = $replace['name'];
    			$all_plugins['iwp-client/init.php']['Title'] = $replace['name'];
    			$all_plugins['iwp-client/init.php']['Description'] = $replace['desc'];
    			$all_plugins['iwp-client/init.php']['AuthorURI'] = $replace['author_url'];
    			$all_plugins['iwp-client/init.php']['Author'] = $replace['author'];
    			$all_plugins['iwp-client/init.php']['AuthorName'] = $replace['author'];
    			$all_plugins['iwp-client/init.php']['PluginURI'] = '';
    		}
    		
    		if($replace['hide']){
    			if (!function_exists('get_plugins')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        	}
          $activated_plugins = get_option('active_plugins');
          if (!$activated_plugins)
                $activated_plugins = array();
          if(in_array('iwp-client/init.php',$activated_plugins))
           	unset($all_plugins['iwp-client/init.php']);   	
    		}
    	}
		    	  	
    	return $all_plugins;
    }
    
   
}
?>