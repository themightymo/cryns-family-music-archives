<?php
if(basename($_SERVER['SCRIPT_FILENAME']) == "wordfence.class.php"):
    exit;
endif;
class IWP_WORDFENCE extends IWP_MMB_Core
{
    function __construct()
    {
        parent::__construct();
    }
	
	/*
	 * Load the Load Previousa Scan Results from WordFence
	 */
	 function load() {
	 	if($this->_checkWordFence()) {
	 		
	 		if(wfUtils::isScanRunning()){
	 			return array('scan'=>'yes');
	 		} else {
	 			return wordfence::ajax_loadIssues_callback();
	 		}
	 	} else {
	 		return array('warning'=>"Word Fence plugin is not activated");
	 	}
	 }
	 
	 /*
	 * Start the new scan on WordFence
	 */
	 function scan() {
	 	if($this->_checkWordFence()) {
	 		return wordfence::ajax_scan_callback();
	 	} else {
	 		return array('error'=>"Word Fence plugin is not activated", 'error_code' => 'wordfence_plugin_is_not_activated');
	 	}
	 }
	 
	 /*
	  * Private function, Will return the wordfence is load or not
	  */
	 private function _checkWordFence() {
	 	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	 	if ( is_plugin_active( 'wordfence/wordfence.php' ) ) {
	 		@include_once(WP_PLUGIN_DIR . '/wordfence/wordfence.php');
	 		if (class_exists('wordfence')) {
		    	return true;
			} else {
				return false;
			}
	 	} else {
	 		return false;
	 	}
	 	
		
		
	 }
    
}