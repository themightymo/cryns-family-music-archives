<?php

/************************************************************
 * This plugin was modified by Revmakx						*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
/*************************************************************
 * 
 * backup.class.php
 * 
 * Manage Backups
 * 
 * 
 * Copyright (c) 2011 Prelovac Media
 * www.prelovac.com
 **************************************************************/
 
if(!defined('IWP_BACKUP_DIR')){
define('IWP_BACKUP_DIR', WP_CONTENT_DIR . '/infinitewp/backups');
}

if(!defined('IWP_DB_DIR')){
define('IWP_DB_DIR', IWP_BACKUP_DIR . '/iwp_db');
}

if(!defined('IWP_PCLZIP_TEMPORARY_DIR')){
define('IWP_PCLZIP_TEMPORARY_DIR', WP_CONTENT_DIR . '/infinitewp/temp/');
}

$zip_errors   = array(
    'No error',
    'No error',
    'Unexpected end of zip file',
    'A generic error in the zipfile format was detected',
    'zip was unable to allocate itself memory',
    'A severe error in the zipfile format was detected',
    'Entry too large to be split with zipsplit',
    'Invalid comment format',
    'zip -T failed or out of memory',
    'The user aborted zip prematurely',
    'zip encountered an error while using a temp file. Please check if this domain\'s account has enough disk space.',
    'Read or seek error',
    'zip has nothing to do',
    'Missing or empty zip file',
    'Error writing to a file. Please check if this domain\'s account has enough disk space.',
    'zip was unable to create a file to write to',
    'bad command line parameters',
    'no error',
    'zip could not open a specified file to read'
);
$unzip_errors = array(
    'No error',
    'One or more warning errors were encountered, but processing completed successfully anyway',
    'A generic error in the zipfile format was detected',
    'A severe error in the zipfile format was detected.',
    'unzip was unable to allocate itself memory.',
    'unzip was unable to allocate memory, or encountered an encryption error',
    'unzip was unable to allocate memory during decompression to disk',
    'unzip was unable allocate memory during in-memory decompression',
    'unused',
    'The specified zipfiles were not found',
    'Bad command line parameters',
    'No matching files were found',
    50 => 'The disk is (or was) full during extraction',
    51 => 'The end of the ZIP archive was encountered prematurely.',
    80 => 'The user aborted unzip prematurely.',
    81 => 'Testing or extraction of one or more files failed due to unsupported compression methods or unsupported decryption.',
    82 => 'No files were found due to bad decryption password(s)'
);


class IWP_MMB_Backup_Singlecall extends IWP_MMB_Core
{
    var $site_name;
    var $statuses;
    var $tasks;
    var $s3;
    var $ftp;
    var $dropbox;
    function __construct()
    {
        require_once $GLOBALS['iwp_mmb_plugin_dir'].'/pclzip.class.php';
		parent::__construct();
        $this->site_name = str_replace(array(
            "_",
            "/",
	    			"~"
        ), array(
            "",
            "-",
            "-"
        ), rtrim($this->remove_http(get_bloginfo('url')), "/"));
        $this->statuses  = array(
            'db_dump' => 1,
            'db_zip' => 2,
            'files_zip' => 3,
            'finished' => 100
        );
		
		
											
    }
    function set_resource_limit()
   	{   		   		
   		$changed = array('execution_time' => 0, 'memory_limit' => 0);
   		@ignore_user_abort(true);
		
//   		$memory_limit = trim(ini_get('memory_limit'));    
//    	$last = strtolower(substr($memory_limit, -1));
//
//	    if($last == 'g')       
//	        $memory_limit = ((int) $memory_limit)*1024;
//	    elseif($last == 'm')      
//	        $memory_limit = (int) $memory_limit;
//	    elseif($last == 'k')
//	        $memory_limit = ((int) $memory_limit)/1024;         
//        
//   		if ( $memory_limit < 384 )  {    
//			@ini_set('memory_limit', '384M');
//			$changed['memory_limit'] = 1;
//		}
		  
		@ini_set('memory_limit', -1);
		$changed['memory_limit'] = 1;
      
      if ( (int) @ini_get('max_execution_time') < 1200 ) {
     	  	@ini_set('max_execution_time', 1200);//twenty minutes
			@set_time_limit(1200); 
     		$changed['execution_time'] = 1;
     	}
     	
     	return $changed;
     	
  	}
	
    /*function get_backup_settings()
    {
        $backup_settings = get_option('iwp_client_backup_tasks');
        if (!empty($backup_settings))
            return $backup_settings;
        else
            return false;
    }*/
    
    function set_backup_task($params){
		if (!empty($params)) {
        	
			$this->statusLog($historyID, array('stage' => 'verification', 'status' => 'processing', 'statusMsg' => 'verificationInitiated'), $params);
			
			$this->set_resource_limit();
			
			$this->tasks = $this->get_this_tasks('requestParams');
						
			extract($params);			
										
			//if ($task_name == 'Backup Now') {
								
				$result  = $this->backup($args, $task_name);
				
				$backup_settings = $this->get_this_tasks();
				
				if (is_array($result) && array_key_exists('error', $result)) {
					$return = $result;
				} else {
					$return = unserialize($backup_settings['taskResults']);
				}
			//}
						
			return $return;
        }
        
        return false;
    }
    
     
function delete_task_now($task_name){
	global $wpdb;

	$table_name = $wpdb->base_prefix . "iwp_backup_status";
	
	$tasks = $this->tasks;
	//unset($tasks[$task_name]);
	
	
	$delete_query = "DELETE FROM ".$table_name." WHERE taskName = '".$task_name."' ";
	$deleteRes = $wpdb->query($delete_query);
	
	$this->update_tasks($tasks);
	$this->cleanup();
	
	return $task_name;
				
}

    /*
     * If Task Name not set then it's manual backup
     * Backup args:
     * type -> db, full
     * what -> daily, weekly, monthly
     * account_info -> ftp, amazons3, dropbox
     * exclude-> array of paths to exclude from backup
     */
    
    function backup($args, $task_name = false)
    {
		if (!$args || empty($args))
            return false;
        
        extract($args); //extract settings
          
        //Remove old backup(s)
        $removed = $this->remove_old_backups($task_name);
        if (is_array($removed) && isset($removed['error'])) {
        	//$error_message = $removed['error'];
        	return $removed;
        }
        
        $new_file_path = IWP_BACKUP_DIR;
        
        if (!file_exists($new_file_path)) {
            if (!mkdir($new_file_path, 0755, true))
                return array(
                    'error' => 'Permission denied, make sure you have write permission to wp-content folder.', 'error_code' => 'permission_denied_wpcontent_folder'
                );
        }
        
        @file_put_contents($new_file_path . '/index.php', ''); //safe
		
		//pclzip temp folder creation
		
		if(!(file_exists(IWP_PCLZIP_TEMPORARY_DIR) && is_dir(IWP_PCLZIP_TEMPORARY_DIR)))
		{
			$mkdir = @mkdir(IWP_PCLZIP_TEMPORARY_DIR, 0755, true);
			if(!$mkdir){
				return array('error' => 'Error creating database backup folder (' . IWP_PCLZIP_TEMPORARY_DIR . '). Make sure you have corrrect write permissions.');
			}
		}
		if(is_writable(IWP_PCLZIP_TEMPORARY_DIR))
		{
			@file_put_contents(IWP_PCLZIP_TEMPORARY_DIR . '/index.php', ''); //safe	
		}
		else
		{
			$chmod = chmod(IWP_PCLZIP_TEMPORARY_DIR, 777);
			if(!is_writable(IWP_PCLZIP_TEMPORARY_DIR)){
				return array('error' => IWP_PCLZIP_TEMPORARY_DIR.' directory is not writable. Please set 755 or 777 file permission and try again.');
			}
		}
           
        //Prepare .zip file name  
        $hash        = md5(time());
        $label       = $type ? $type : 'manual';
        $backup_file = $new_file_path . '/' . $this->site_name . '_' . $label . '_' . $what . '_' . date('Y-m-d') . '_' . $hash . '.zip';
        $backup_url  = content_url() . '/infinitewp/backups/' . $this->site_name . '_' . $label . '_' . $what . '_' . date('Y-m-d') . '_' . $hash . '.zip';
        
        //Optimize tables?
        if (isset($optimize_tables) && !empty($optimize_tables)) {
            $this->optimize_tables();
        }
        
	    $exclude_file_size = $this->tasks['args']['exclude_file_size'];
		$exclude_extensions = $this->tasks['args']['exclude_extensions'];
		$disable_comp = $this->tasks['args']['disable_comp'];
		$comp_level   = $disable_comp ? '-0' : '-1';
		
        //What to backup - db or full?
        if (trim($what) == 'db') {
		
			$db_backup = $this->backup_db_alone($task_name, $backup_file, $comp_level);
						
			if (is_array($db_backup) && array_key_exists('error', $db_backup)) {
                return array(
                    'error' => $db_backup['error'], 'error_code' => $db_backup['error_code']
                );
            }	

      	}
		elseif(trim($what) == 'files'){
			$content_backup = $this->backup_files_alone($task_name, $backup_file, $exclude, $include, $comp_level, $exclude_file_size, $exclude_extensions);
			if (is_array($content_backup) && array_key_exists('error', $content_backup)) {
                return array(
                    'error' => $content_backup['error'], 'error_code' => $content_backup['error_code']
                );
            }			
		}
		elseif (trim($what) == 'full') {
			$db_backup = $this->backup_db_alone($task_name, $backup_file, $comp_level);
			if (is_array($db_backup) && array_key_exists('error', $db_backup)) {
                return array(
                    'error' => $db_backup['error'], 'error_code' => $db_backup['error_code']
                );
            }	
            $content_backup = $this->backup_files_alone($task_name, $backup_file, $exclude, $include, $comp_level, $exclude_file_size, $exclude_extensions);
            if (is_array($content_backup) && array_key_exists('error', $content_backup)) {
                return array(
                    'error' => $content_backup['error'], 'error_code' => $content_backup['error_code']
                );
            }
        }
		
	
        //Update backup info
        if ($task_name) {
            //backup task (scheduled)
			
            $backup_settings = $this->tasks;
            $paths           = array();
            $size            = round(iwp_mmb_get_file_size($backup_file) / 1024, 2);
            
            if ($size > 1000) {
                $paths['size'] = round($size / 1024, 2) . " MB";//Modified by IWP //Mb => MB
            } else {
                $paths['size'] = $size . 'KB';//Modified by IWP //Kb => KB
            }
											
			$paths['backup_name'] = $backup_settings['args']['backup_name'];
			$paths['mechanism'] = 'singleCall';
			
			$paths['server'] = array(
                    'file_path' => $backup_file,
                    'file_url' => $backup_url);
			
			$paths['time'] = time();
            $paths['adminHistoryID'] = $GLOBALS['IWP_CLIENT_HISTORY_ID']; //['adminHistoryID'] = $GLOBALS['IWP_CLIENT_HISTORY_ID'];
            
				
		if (isset($backup_settings['account_info']['iwp_ftp'])) {
				
				$this->update_status($task_name,'ftp');
				
                $paths['ftp'] = basename($backup_url);

                $backup_settings['account_info']['iwp_ftp']['backup_file'] = $backup_file;
				iwp_mmb_print_flush('FTP upload: Start');
                $ftp_result                             = $this->ftp_backup($backup_settings['account_info']['iwp_ftp']);
                iwp_mmb_print_flush('FTP upload: End');
                if ($ftp_result !== true && $del_host_file) {
                    @unlink($backup_file);
                }
                
                if (is_array($ftp_result) && isset($ftp_result['error'])) {
                    return $ftp_result;
                }
				$this->update_status($task_name,'ftp', true);
				
                unset($paths['server']);			
           }
            
            if (isset($backup_settings['account_info']['iwp_amazon_s3'])) {
				
				$this->update_status($task_name,'amazon_s3');
				
                $paths['amazons3'] = basename($backup_url);
				
                $backup_settings['account_info']['iwp_amazon_s3']['backup_file'] = $backup_file;
				iwp_mmb_print_flush('Amazon S3 upload: Start');
                $amazons3_result                              = $this->amazons3_backup($backup_settings['account_info']['iwp_amazon_s3']);
				iwp_mmb_print_flush('Amazon S3 upload: End');
                if ($amazons3_result !== true && $del_host_file) {
                    @unlink($backup_file);
                }
                if (is_array($amazons3_result) && isset($amazons3_result['error'])) {
                    return $amazons3_result;
                }
             	
				$this->update_status($task_name,'amazon_s3', true);
				
            	unset($paths['server']);
            }
            
            if (isset($backup_settings['account_info']['iwp_dropbox'])) {
				
				$this->update_status($task_name,'dropbox');
				
                $paths['dropbox'] = basename($backup_url);
				
                $backup_settings['account_info']['iwp_dropbox']['backup_file'] = $backup_file;
				iwp_mmb_print_flush('Dropbox upload: Start');
                $dropbox_result                             = $this->dropbox_backup($backup_settings['account_info']['iwp_dropbox']);
				iwp_mmb_print_flush('Dropbox upload: End');
                if ($dropbox_result !== true && $del_host_file) {
                    @unlink($backup_file);
                }
                
                if (is_array($dropbox_result) && isset($dropbox_result['error'])) {
                    return $dropbox_result;
                }
				
				$this->update_status($task_name,'dropbox', true);
                unset($paths['server']);
            }
                        			
			if (isset($backup_settings['account_info']['iwp_gdrive'])) {
			
				$this->update_status($task_name,'gDrive');
				
                $paths['gDrive'] = basename($backup_url);
				
				
                $backup_settings['account_info']['iwp_gdrive']['backup_file'] = $backup_file;
				iwp_mmb_print_flush('google Drive upload: Start');
				$gdrive_result                              = $this->google_drive_backup($backup_settings['account_info']['iwp_gdrive']);
				iwp_mmb_print_flush('google Drive upload: End');
				
				if ($gdrive_result == false && $del_host_file) {
                    @unlink($backup_file);
                }
                
                if (is_array($gdrive_result) && isset($gdrive_result['error'])) {
                    return $gdrive_result;
                }
				
				$paths['gDrive'] = $gdrive_result;  				//different from other upload ; storing the gDrive backupfile ID in the paths array for delete operation
				$paths['gDriveOrgFileName'] = basename($backup_url);
				
				$this->update_status($task_name,'gDrive', true);
                unset($paths['server']);
			}
                        			
        }
        
		if ($del_host_file) {
			@unlink($backup_file);
		}
         
        $this->update_status($task_name,'finished',true, $paths);
				
        return $backup_url; 
    }
	
    function backup_db_alone($task_name, $backup_file, $comp_level){
	
		//Take database backup
		$this->update_status($task_name, 'db_dump');
		//$this->statusLog();
		$GLOBALS['fail_safe_db'] = $this->tasks['args']['fail_safe_db'];

		$db_result = $this->backup_db();
		
		if ($db_result == false) {
			return array(
				'error' => 'Failed to backup database.', 'error_code' => 'backup_database_failed'
			);
		} else if (is_array($db_result) && isset($db_result['error'])) {
			return array(
				'error' => $db_result['error'], 'error_code' => $db_result['error_code']
			);
		} else {
			$this->update_status($task_name, 'db_dump', true);
			//$this->statusLog();
			
			$this->update_status($task_name, 'db_zip');
			//$this->statusLog();
			
			/*zip_backup_db*/
			$fail_safe_db = $this->tasks['args']['fail_safe_db'];
			$disable_comp = $this->tasks['args']['disable_comp'];
			
			if($fail_safe_db){
				$pcl_result = $this->fail_safe_pcl_db($backup_file,$fail_safe_db,$disable_comp);
				if(is_array($pcl_result) && isset($pcl_result['error'])){
					return $pcl_result;
				}
			}
			else{
				chdir(IWP_BACKUP_DIR);
				$zip     = $this->get_zip();
				$command = "$zip -q -r $comp_level $backup_file 'iwp_db'";
				iwp_mmb_print_flush('DB ZIP CMD: Start');
				ob_start();
				$result = $this->iwp_mmb_exec($command);
				ob_get_clean();
				iwp_mmb_print_flush('DB ZIP CMD Result: '.$result);
				iwp_mmb_print_flush('DB ZIP CMD: End');
				/*zip_backup_db */
				if(!$result){
				$zip_archive_db_result = false;
				if (class_exists("ZipArchive")) {
					$this->_log("DB zip, fallback to ZipArchive");
					iwp_mmb_print_flush('DB ZIP Archive: Start');
					$zip_archive_db_result = $this->zip_archive_backup_db($task_name, $db_result, $backup_file);
					iwp_mmb_print_flush('DB ZIP Archive Result: '.$zip_archive_db_result);
					iwp_mmb_print_flush('DB ZIP Archive: End');
				}
				
				if (!$zip_archive_db_result) {
						$pcl_result = $this->fail_safe_pcl_db($backup_file,$fail_safe_db,$disable_comp);
						if(is_array($pcl_result) && isset($pcl_result['error'])){
							return $pcl_result;
						}
					}
				}
			}
			
			@unlink($db_result);
			@unlink(IWP_BACKUP_DIR.'/iwp_db/index.php');
			@rmdir(IWP_DB_DIR);
			
		   $this->update_status($task_name, 'db_zip', true);
		   //$this->statusLog();
		   
		   return true;
		}
        
	}
	
	
	    
    function backup_files_alone($task_name, $backup_file, $exclude = array(), $include = array(), $comp_level = 0, $exclude_file_size = 0, $exclude_extensions = "")
    {
        
		global $zip_errors;
        $sys = substr(PHP_OS, 0, 3);
        
		if(empty($exclude_extensions))
		{
			$exclude_extensions = array();
		}
		else
		{
			$exclude_extensions_array = explode(",",$exclude_extensions);
			$exclude_extensions = array();
			$exclude_extensions = $exclude_extensions_array;
		}
          
        //Always remove backup folders    
        $remove = array(
            trim(basename(WP_CONTENT_DIR)) . "/infinitewp/backups",
            trim(basename(WP_CONTENT_DIR)) . "/" . md5('iwp_mmb-client') . "/iwp_backups",
			trim(basename(WP_CONTENT_DIR)) . "/cache",
			trim(basename(WP_CONTENT_DIR)) . "/w3tc"
        );
		
		//removing files which are larger than the specified size
		//Note: in multicall the exclude process is done on pclzip.
		if((!empty($exclude_file_size))||(!empty($exclude_extensions)))
		{
			//removing files which are larger than the specified size
			$total_files_array = get_all_files_from_dir(ABSPATH, $remove);
			$files_excluded_by_size = array();
			foreach($total_files_array as $key => $value)
			{
				$this_base_name = basename($value);
				$skip_after_ext = false;
				//file extension based exclude
				if(is_array($exclude_extensions) && (!empty($exclude_extensions)))
				{
					foreach($exclude_extensions as $ext)
					{
						$this_pos = strrpos($this_base_name, $ext);
						if($this_pos !== false)
						{
							if(substr($this_base_name, $this_pos) == $ext)
							{
								$files_excluded_by_size[] = substr($value, strlen(ABSPATH));
								$skip_after_ext = true;											//to skip the file exclude by size 
								break;
							}
						}
					}
				}
				if($skip_after_ext)
				{
					continue;
				}
				//file size based exclude
				if(!empty($exclude_file_size))
				{
					if(iwp_mmb_get_file_size($value) >= $exclude_file_size*1024*1024)
					{
						$files_excluded_by_size[] = substr($value, strlen(ABSPATH));
					}
				}
			}
			$remove = array_merge($remove, $files_excluded_by_size);
		}
		
		$exclude = array_merge($exclude, $remove);
		
        //Exclude paths
        $exclude_data = "-x";
        
        $exclude_file_data = '';
        
        if (!empty($exclude) && is_array($exclude)) {
            foreach ($exclude as $data) {
				if(empty($data))
				continue;
                if (is_dir(ABSPATH . $data)) {
                    if ($sys == 'WIN')
                        $exclude_data .= " $data/*.*";
                    else
                        $exclude_data .= " '$data/*'";
                }else {
                    if ($sys == 'WIN'){
                    	if(file_exists(ABSPATH . $data)){
							$exclude_data .= " $data";
                        	$exclude_file_data .= " $data";
                        }
					}else {
						  if(file_exists(ABSPATH . $data)){
							  $exclude_data .= " '$data'";
                        	  $exclude_file_data .= " '$data'";
						  }
					  }
				  }
              }
         }
        
        if($exclude_file_data){
        	$exclude_file_data = "-x".$exclude_file_data;
        }
        
        
        //Include paths by default
        $add = array(
            trim(WPINC),
            trim(basename(WP_CONTENT_DIR)),
            "wp-admin"
        );
        
        $include_data = ". -i";
        foreach ($add as $data) {
            if ($sys == 'WIN')
                $include_data .= " $data/*.*";
            else
                $include_data .= " '$data/*'";
        }
        
        //Additional includes?
        if (!empty($include) && is_array($include)) {
            foreach ($include as $data) {
				if(empty($data))
				continue;
                if ($data) {
                    if ($sys == 'WIN')
                        $include_data .= " $data/*.*";
                    else
                        $include_data .= " '$data/*'";
                }
            }
        }
        
        $this->update_status($task_name, 'files_zip');
        chdir(ABSPATH);
		
		$fail_safe_files = $this->tasks['args']['fail_safe_files'];
		
		if($fail_safe_files){
			$pcl_result = $this->fail_safe_pcl_files($task_name, $backup_file, $exclude, $include, $fail_safe_files, $disable_comp, $add, $remove);
			if(is_array($pcl_result) && isset($pcl_result['error'])){
				return $pcl_result;
			}
		}
		else
		{
			$do_cmd_zip_alternative = false;
			@copy($backup_file, $backup_file.'_2');
			
			iwp_mmb_print_flush('Files ZIP CMD: Start');
			$command  = "$zip -q -j $comp_level $backup_file .* * $exclude_file_data";
			ob_start();
			$result_f = $this->iwp_mmb_exec($command, false, true);
			ob_get_clean();
			iwp_mmb_print_flush('Files ZIP CMD Result: '.$result_f);
			iwp_mmb_print_flush('Files ZIP CMD: 1/2 over');
			if (!$result_f || $result_f == 18) { // disregard permissions error, file can't be accessed			
				$command  = "$zip -q -r $comp_level $backup_file $include_data $exclude_data";
				ob_start();	
				$result_d = $this->iwp_mmb_exec($command, false, true);  
				ob_get_clean();        
				if ($result_d && $result_d != 18) {
					@unlink($backup_file);
					$do_cmd_zip_alternative = true;
					
					
					if($result_d > 0 && $result_d < 18){
					   iwp_mmb_print_flush('Files ZIP CMD: Failed to archive files (' . $zip_errors[$result_d] . ') .');
					}
					else{
						iwp_mmb_print_flush('Files ZIP CMD: Failed to archive files.');
					}
				}
			}
			
			if(!$do_cmd_zip_alternative){//if FILE ZIP CMD successful
				@unlink($backup_file.'_2');
			}
			
			iwp_mmb_print_flush('Files ZIP CMD: End');
			if (($result_f && $result_f != 18) || ($do_cmd_zip_alternative)) {
				
				if($do_cmd_zip_alternative){
					@copy($backup_file.'_2', $backup_file);
					@unlink($backup_file.'_2');
				}
				
				$zip_archive_result = false;
				if (class_exists("ZipArchive")) {
					iwp_mmb_print_flush('Files ZIP Archive: Start');
					$this->_log("Files zip fallback to ZipArchive");
					$zip_archive_result = $this->zip_archive_backup($task_name, $backup_file, $exclude, $include);
					iwp_mmb_print_flush('Files ZIP Archive Result: '.$zip_archive_result);
					iwp_mmb_print_flush('Files ZIP Archive: End');
				}
					
					if (!$zip_archive_result) {
						$pcl_result = $this->fail_safe_pcl_files($task_name, $backup_file, $exclude, $include, $fail_safe_files, $disable_comp, $add, $remove);
						if(is_array($pcl_result) && isset($pcl_result['error'])){
							return $pcl_result;
						}
					}
				}
	    }
	     
        //Reconnect
        $this->wpdb_reconnect();
		
        $this->update_status($task_name, 'files_zip', true);
        return true;
    }

	
	
	function fail_safe_pcl_files($task_name, $backup_file, $exclude, $include, $fail_safe_files, $disable_comp, $add, $remove){ //Try pclZip
		//$this->back_hack($task_name, 'Files ZIP PCL: Start');
				iwp_mmb_print_flush('Files ZIP PCL: Start');
				if (!isset($archive)) {
					//define('IWP_PCLZIP_TEMPORARY_DIR', IWP_BACKUP_DIR . '/');
					//require_once ABSPATH . '/wp-admin/includes/class-pclzip.php';
					require_once $GLOBALS['iwp_mmb_plugin_dir'].'/pclzip.class.php';
					$archive = new IWPPclZip($backup_file);
				}
				
				//Include paths
				$include_data = array();
				if (!empty($include) && is_array($include)) {
					foreach ($include as $data) {
						if ($data && file_exists(ABSPATH . $data))
							$include_data[] = ABSPATH . $data . '/';
					}
				}
				
				foreach ($add as $data) {
					if (file_exists(ABSPATH . $data))
						$include_data[] = ABSPATH . $data . '/';
				}
				
				//Include root files
				if ($handle = opendir(ABSPATH)) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && !is_dir($file) && file_exists(ABSPATH . $file)) {
							$include_data[] = ABSPATH . $file;
						}
					}
					closedir($handle);
				}
				
				//exclude paths
				$exclude_data = array();
				if (!empty($exclude) && is_array($exclude)) {
					foreach ($exclude as $data) {
						if (is_dir(ABSPATH . $data))
							$exclude_data[] = $data . '/';
						else
							$exclude_data[] = $data;
					}
				}
				
				foreach ($remove as $rem) {
					$exclude_data[] = $rem . '/';
				}
				
				if($fail_safe_files && $disable_comp){
					$result = $archive->add($include_data, IWP_PCLZIP_OPT_REMOVE_PATH, ABSPATH, IWP_PCLZIP_OPT_IWP_EXCLUDE, $exclude_data, IWP_PCLZIP_OPT_NO_COMPRESSION, IWP_PCLZIP_OPT_TEMP_FILE_THRESHOLD, 1);
				}
				elseif(!$fail_safe_files && $disable_comp){
					$result = $archive->add($include_data, IWP_PCLZIP_OPT_REMOVE_PATH, ABSPATH, IWP_PCLZIP_OPT_IWP_EXCLUDE, $exclude_data, IWP_PCLZIP_OPT_NO_COMPRESSION);
				}
				elseif($fail_safe_files && !$disable_comp){
					$result = $archive->add($include_data, IWP_PCLZIP_OPT_REMOVE_PATH, ABSPATH, IWP_PCLZIP_OPT_IWP_EXCLUDE, $exclude_data,  IWP_PCLZIP_OPT_TEMP_FILE_THRESHOLD, 1);
				}
				else{
					$result = $archive->add($include_data, IWP_PCLZIP_OPT_REMOVE_PATH, ABSPATH, IWP_PCLZIP_OPT_IWP_EXCLUDE, $exclude_data);
				}
				
				iwp_mmb_print_flush('Files ZIP PCL: End');
				
				if (!$result) {
					@unlink($backup_file);
					return array(
						'error' => 'Failed to zip files. pclZip error (' . $archive->error_code . '): .' . $archive->error_string, 'error_code' => 'failed_zip_files_pclZip_error'
					);
				}            
			//}
        }
        //Reconnect
	function fail_safe_pcl_db($backup_file,$fail_safe_files,$disable_comp){
		//$this->back_hack($task_name, 'DB ZIP PCL: Start');
		iwp_mmb_print_flush('DB ZIP PCL: Start');
		//define('IWP_PCLZIP_TEMPORARY_DIR', IWP_BACKUP_DIR . '/');
		require_once $GLOBALS['iwp_mmb_plugin_dir'].'/pclzip.class.php';
		$archive = new IWPPclZip($backup_file);
        
		if($fail_safe_files && $disable_comp){
			 $result_db = $archive->add(IWP_DB_DIR, IWP_PCLZIP_OPT_REMOVE_PATH, IWP_BACKUP_DIR, IWP_PCLZIP_OPT_NO_COMPRESSION, IWP_PCLZIP_OPT_TEMP_FILE_THRESHOLD, 1);
		}
		elseif(!$fail_safe_files && $disable_comp){
			 $result_db = $archive->add(IWP_DB_DIR, IWP_PCLZIP_OPT_REMOVE_PATH, IWP_BACKUP_DIR, IWP_PCLZIP_OPT_NO_COMPRESSION);
		}
		elseif($fail_safe_files && !$disable_comp){
			 $result_db = $archive->add(IWP_DB_DIR, IWP_PCLZIP_OPT_REMOVE_PATH, IWP_BACKUP_DIR, IWP_PCLZIP_OPT_TEMP_FILE_THRESHOLD, 1);
		}
		else{
			 $result_db = $archive->add(IWP_DB_DIR, IWP_PCLZIP_OPT_REMOVE_PATH, IWP_BACKUP_DIR);
    }
		//$this->back_hack($task_name, 'DB ZIP PCL: End');
		iwp_mmb_print_flush('DB ZIP PCL: End');
		
		@unlink($db_result);
		@unlink(IWP_BACKUP_DIR.'/iwp_db/index.php');
		@rmdir(IWP_DB_DIR);
	
		if (!$result_db) {
			return array(
				'error' => 'Failed to zip database. pclZip error (' . $archive->error_code . '): .' . $archive->error_string, 'error_code' => 'failed_to_zip_database_pclZip_error'
			);
		}
	}
	/**
     * Zipping database dump and index.php in folder iwp_db by ZipArchive class, requires php zip extension.
     *
     * @param 	string 	$task_name		the name of backup task
     * @param	string	$db_result		relative path to database dump file
     * @param 	string 	$backup_file	absolute path to zip file
     * @return 	bool					is compress successful or not
     */
    function zip_archive_backup_db($task_name, $db_result, $backup_file) {
    	$disable_comp = $this->tasks['args']['disable_comp'];
    	if (!$disable_comp) {
    		$this->_log("Compression is not supported by ZipArchive");
    	}
    	$zip = new ZipArchive();

    	$result = $zip->open($backup_file, ZIPARCHIVE::OVERWRITE); // Tries to open $backup_file for acrhiving
    	if ($result === true) {
    		$result = $result && $zip->addFile(IWP_BACKUP_DIR.'/iwp_db/index.php', "iwp_db/index.php"); // Tries to add iwp_db/index.php to $backup_file
    		$result = $result && $zip->addFile($db_result, "iwp_db/" . basename($db_result)); // Tries to add db dump form iwp_db dir to $backup_file
    		$result = $result && $zip->close(); // Tries to close $backup_file
    	} else {
    		$result = false;
    	}
    	
    	return $result; // true if $backup_file iz zipped successfully, false if error is occured in zip process
    }
	
	/**
     * Zipping whole site root folder and append to backup file with database dump
     * by ZipArchive class, requires php zip extension.
     *
     * @param 	string 	$task_name		the name of backup task
     * @param 	string 	$backup_file	absolute path to zip file
     * @param	array	$exclude		array of files of folders to exclude, relative to site's root
     * @param	array	$include		array of folders from site root which are included to backup (wp-admin, wp-content, wp-includes are default)
     * @return 	array|bool				true if successful or an array with error message if not
     */
    function zip_archive_backup($task_name, $backup_file, $exclude, $include, $overwrite = false) {

		$filelist = $this->get_backup_files($exclude, $include);
		
		$disable_comp = $this->tasks['args']['disable_comp'];
		if (!$disable_comp) {
			$this->_log("Compression is not supported by ZipArchive");
		}

		$zip = new ZipArchive();

		if ($overwrite) {
			$result = $zip->open($backup_file, ZipArchive::OVERWRITE); // Tries to open $backup_file for acrhiving			
		} else {
			if(file_exists($backup_file)){
				$result = $zip->open($backup_file); // Tries to open $backup_file for acrhiving
			}else{
				$result = $zip->open($backup_file, ZIPARCHIVE::CREATE);
			}
		}
		if ($result === true) {
			foreach ($filelist as $file) {
				iwp_mmb_auto_print('zip_archive_backup');
				$result = $result && $zip->addFile($file, sprintf("%s", str_replace(ABSPATH, '', $file))); // Tries to add a new file to $backup_file
			}
			$result = $result && $zip->close(); // Tries to close $backup_file
		} else {
			$result = false;
		}
		return $result; // true if $backup_file iz zipped successfully, false if error is occured in zip process
    }
	
	
	  /**
     * Gets an array of relative paths of all files in site root recursively.
     * By default, there are all files from root folder, all files from folders wp-admin, wp-content, wp-includes recursively.
     * Parameter $include adds other folders from site root, and excludes any file or folder by relative path to site's root.
     * 
     * @param 	array 	$exclude	array of files of folders to exclude, relative to site's root
     * @param 	array 	$include	array of folders from site root which are included to backup (wp-admin, wp-content, wp-includes are default)
     * @return 	array				array with all files in site root dir
     */
    function get_backup_files($exclude, $include) {
		
    	$add = array(
    		trim(WPINC),
    		trim(basename(WP_CONTENT_DIR)),
    		"wp-admin"
    	);
    	
    	$include = array_merge($add, $include);
		
	    $filelist = array();
	    if ($handle = opendir(ABSPATH)) {
	    	while (false !== ($file = readdir($handle))) {
				if (is_dir($file) && file_exists(ABSPATH . $file) && !(in_array($file, $include))) {
	    			$exclude[] = $file;
	    		}
	    	}
	    	closedir($handle);
	    }
	   
    	$filelist = get_all_files_from_dir(ABSPATH, $exclude);
    	return $filelist;
    }

   
    function backup_db()
    {
        $db_folder = IWP_DB_DIR . '/';
        if (!file_exists($db_folder)) {
            if (!mkdir($db_folder, 0755, true))
                return array(
                    'error' => 'Error creating database backup folder (' . $db_folder . '). Make sure you have corrrect write permissions.', 'error_code' => 'error_creating_db_folder_check_perm'
                );
			$db_index_file = '<?php
			global $old_url, $old_file_path;
			$old_url = \''.get_option('siteurl').'\';
			$old_file_path = \''.ABSPATH.'\';
			';
			@file_put_contents(IWP_BACKUP_DIR.'/iwp_db/index.php', $db_index_file);
        }
        
        $file   = $db_folder . DB_NAME . '.sql';
         
        if($GLOBALS['fail_safe_db']){
        	$result = $this->backup_db_php($file);
            return $result;
        }

        $result = $this->backup_db_dump($file); // try mysqldump always then fallback to php dump
        return $result;
    }
    
    function backup_db_dump($file)
    {
        global $wpdb;
        $paths   = $this->check_mysql_paths();
        $brace   = (substr(PHP_OS, 0, 3) == 'WIN') ? '"' : '';
		//$command = $brace . $paths['mysqldump'] . $brace . ' --force --host="' . DB_HOST . '" --user="' . DB_USER . '" --password="' . DB_PASSWORD . '" --add-drop-table --skip-lock-tables "' . DB_NAME . '" > ' . $brace . $file . $brace;
        $command0 = $wpdb->get_col('SHOW TABLES LIKE "'.$wpdb->base_prefix.'%"');
        $wp_tables = join("\" \"",$command0);
        $command = $brace . $paths['mysqldump'] . $brace . ' --force --host="' . DB_HOST . '" --user="' . DB_USER . '" --password="' . DB_PASSWORD . '" --add-drop-table --skip-lock-tables --extended-insert=FALSE "' . DB_NAME . '" "'.$wp_tables.'" > ' . $brace . $file . $brace;
		iwp_mmb_print_flush('DB DUMP CMD: Start');
        ob_start();
        $result = $this->iwp_mmb_exec($command);
        ob_get_clean();
		iwp_mmb_print_flush('DB DUMP CMD: End');
        
        if (!$result) { // Fallback to php
            $result = $this->backup_db_php($file);
            return $result;
        }
        
        if (iwp_mmb_get_file_size($file) == 0 || !is_file($file) || !$result) {
            @unlink($file);
            return false;
        } else {
            return $file;
        }
    }
    
    function backup_db_php($file)
    {
        global $wpdb;
		
		if(empty($GLOBALS['fail_safe_db'])){
			iwp_mmb_print_flush('DB DUMP PHP Normal: Start');
			$fp = fopen( $file, 'w' );
			if ( !mysql_ping( $wpdb->dbh ) ) {
				mysql_connect( DB_HOST, DB_USER, DB_PASSWORD );
				mysql_select_db( DB_NAME );
			}
			$_count = 0;
			$insert_sql = '';
			//$result = mysql_query( 'SHOW TABLES' );
			$result = mysql_query( 'SHOW TABLES LIKE "'.$wpdb->base_prefix.'%"' );
			if(!$result)
			{
				 return array(
					'error' => 'MySQL '.mysql_error()." ", 'error_code' => 'MySQL '.str_replace(" ", "_", mysql_error())." "
				);
			}
			while( $row = mysql_fetch_row( $result ) ) {
				$tables[]=$row[0];
				//array_push( $tables, $row[0] );
			}
			
	
			//$tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
			foreach ($tables as $table) {
				iwp_mmb_auto_print('backup_db_php_normal');
				
				$insert_sql .= "DROP TABLE IF EXISTS $table;";
				//create table
				$table_descr_query = mysql_query("SHOW CREATE TABLE `$table`");
				$fetch_table_descr_row = mysql_fetch_array( $table_descr_query );
				$insert_sql .= "\n\n" . $fetch_table_descr_row[1] . ";\n\n";
				
				fwrite( $fp, $insert_sql );
				$insert_sql = '';
				
				$table_query = mysql_query("SELECT * FROM `$table`");
				$num_fields = mysql_num_fields($table_query);
				while ( $fetch_row = mysql_fetch_array( $table_query ) ) {
					$insert_sql .= "INSERT INTO $table VALUES(";
					for ( $n=1; $n<=$num_fields; $n++ ) {
						$m = $n - 1;
										
						if ( $fetch_row[$m] === NULL ) {
							$insert_sql .= "NULL, ";
						} else {
							$insert_sql .= "'" . mysql_real_escape_string( $fetch_row[$m] ) . "', ";
						}
					}
					$insert_sql = substr( $insert_sql, 0, -2 );
					$insert_sql .= ");\n";
					
					fwrite( $fp, $insert_sql );
					$insert_sql = '';
					
					// Help keep HTTP alive.
					$_count++;
					if ($_count >= 400) {
						echo ' ';
						flush();
						$_count = 0;
					}
				} // End foreach $tables.
				
				$insert_sql .= "\n\n\n";
				
				// testing: mysql_close( $wpdb->dbh );
				// Verify database is still connected and working properly. Sometimes mysql runs out of memory and dies in the above foreach.
				// No point in reconnecting as we can NOT trust that our dump was succesful anymore (it most likely was not).
				if ( @mysql_ping( $wpdb->dbh ) ) { // Still connected to database.
					mysql_free_result( $table_query ); // Free memory.
				} /*else { // Database not connected.
			
					return false;
				}*/
				
				// Help keep HTTP alive.
				echo ' ';
				flush();
				
				//unset( $tables[$table_key] );
			}
			fclose( $fp );
			unset ($fp);
			iwp_mmb_print_flush('DB DUMP PHP Normal: End');
		}
		else{
			iwp_mmb_print_flush('DB DUMP PHP Fail-safe: Start');
			file_put_contents($file, '');//safe  to reset any old data
			//$tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
			$tables = $wpdb->get_results('SHOW TABLES LIKE "'.$wpdb->base_prefix.'%"', ARRAY_N);
			foreach ($tables as $table) {
			
				//drop existing table
				$dump_data    = "DROP TABLE IF EXISTS $table[0];";
            file_put_contents($file, $dump_data, FILE_APPEND);
				//create table
				$create_table = $wpdb->get_row("SHOW CREATE TABLE $table[0]", ARRAY_N);
            $dump_data = "\n\n" . $create_table[1] . ";\n\n";
            file_put_contents($file, $dump_data, FILE_APPEND);
				
				$count = $wpdb->get_var("SELECT count(*) FROM $table[0]");
				if ($count > 100)
					$count = ceil($count / 100);
				else if ($count > 0)            
					$count = 1;                
					
				for ($i = 0; $i < $count; $i++) {
					iwp_mmb_auto_print('backup_db_php_fail_safe');
					$low_limit = $i * 100;
					$qry       = "SELECT * FROM $table[0] LIMIT $low_limit, 100";
					$rows      = $wpdb->get_results($qry, ARRAY_A);
					if (is_array($rows)) {
						foreach ($rows as $row) {
							//insert single row
                        $dump_data = "INSERT INTO $table[0] VALUES(";
							$num_values = count($row);
							$j          = 1;
							foreach ($row as $value) {
								$value = addslashes($value);
								$value = preg_replace("/\n/Ui", "\\n", $value);
								$num_values == $j ? $dump_data .= "'" . $value . "'" : $dump_data .= "'" . $value . "', ";
								$j++;
								unset($value);
							}
							$dump_data .= ");\n";
                        file_put_contents($file, $dump_data, FILE_APPEND);
						}
					}
				}
            $dump_data = "\n\n\n";
            file_put_contents($file, $dump_data, FILE_APPEND);
				
				unset($rows);
				unset($dump_data);
			}
			iwp_mmb_print_flush('DB DUMP PHP Fail-safe: End');
        }
        
        if (iwp_mmb_get_file_size($file) == 0 || !is_file($file)) {
            @unlink($file);
            return array(
                'error' => 'Database backup failed. Try to enable MySQL dump on your server.', 'error_code' => 'database_backup_failed_enable_MySQL_dump_server'
            );
        }
        
        return $file;
        
    }
	
	/**
 * Copies a directory from one location to another via the WordPress Filesystem Abstraction.
 * Assumes that WP_Filesystem() has already been called and setup.
 *
 * @since 2.5.0
 *
 * @param string $from source directory
 * @param string $to destination directory
 * @param array $skip_list a list of files/folders to skip copying
 * @return mixed WP_Error on failure, True on success.
 */
function iwp_mmb_direct_to_any_copy_dir($from, $to, $skip_list = array() ) {//$from => direct file system, $to => automatic filesystem
	global $wp_filesystem;
	
	$wp_temp_direct = new WP_Filesystem_Direct('');
	

	$dirlist = $wp_temp_direct->dirlist($from);

	$from = trailingslashit($from);
	$to = trailingslashit($to);

	$skip_regex = '';
	foreach ( (array)$skip_list as $key => $skip_file )
		$skip_regex .= preg_quote($skip_file, '!') . '|';

	if ( !empty($skip_regex) )
		$skip_regex = '!(' . rtrim($skip_regex, '|') . ')$!i';

	foreach ( (array) $dirlist as $filename => $fileinfo ) {
		if ( !empty($skip_regex) )
			if ( preg_match($skip_regex, $from . $filename) )
				continue;

		if ( 'f' == $fileinfo['type'] ) {
			if ( ! $this->iwp_mmb_direct_to_any_copy($from . $filename, $to . $filename, true, FS_CHMOD_FILE) ) {
				// If copy failed, chmod file to 0644 and try again.
				$wp_filesystem->chmod($to . $filename, 0644);
				if ( ! $this->iwp_mmb_direct_to_any_copy($from . $filename, $to . $filename, true, FS_CHMOD_FILE) )
					return new WP_Error('copy_failed', __('Could not copy file.'), $to . $filename);
			}
		} elseif ( 'd' == $fileinfo['type'] ) {
			if ( !$wp_filesystem->is_dir($to . $filename) ) {
				if ( !$wp_filesystem->mkdir($to . $filename, FS_CHMOD_DIR) )
					return new WP_Error('mkdir_failed', __('Could not create directory.'), $to . $filename);
			}
			$result = $this->iwp_mmb_direct_to_any_copy_dir($from . $filename, $to . $filename, $skip_list);
			if ( is_wp_error($result) )
				return $result;
		}
	}
	return true;
}

function iwp_mmb_direct_to_any_copy($source, $destination, $overwrite = false, $mode = false){
	global $wp_filesystem;
	if($wp_filesystem->method == 'direct'){
		return $wp_filesystem->copy($source, $destination, $overwrite, $mode);
	}
	elseif($wp_filesystem->method == 'ftpext' || $wp_filesystem->method == 'ftpsockets'){
		if ( ! $overwrite && $wp_filesystem->exists($destination) )
			return false;
		//$content = $this->get_contents($source);
//		if ( false === $content)
//			return false;
			
		//put content	
		//$tempfile = wp_tempnam($file);
		$source_handle = fopen($source, 'r');
		if ( ! $source_handle )
			return false;

		//fwrite($temp, $contents);
		//fseek($temp, 0); //Skip back to the start of the file being written to
		
		$sample_content = fread($source_handle, (1024 * 1024 * 2));//1024 * 1024 * 2 => 2MB
		fseek($source_handle, 0); //Skip back to the start of the file being written to

		$type = $wp_filesystem->is_binary($sample_content) ? FTP_BINARY : FTP_ASCII;
		unset($sample_content);
		if($wp_filesystem->method == 'ftpext'){
			$ret = @ftp_fput($wp_filesystem->link, $destination, $source_handle, $type);
		}
		elseif($wp_filesystem->method == 'ftpsockets'){
			$wp_filesystem->ftp->SetType($type);
			$ret = $wp_filesystem->ftp->fput($destination, $source_handle);
		}

		fclose($source_handle);
		unlink($source);//to immediately save system space
		//unlink($tempfile);

		$wp_filesystem->chmod($destination, $mode);

		return $ret;
		
		//return $this->put_contents($destination, $content, $mode);
	}
}

    
    function restore($args)
    {
		global $wpdb, $wp_filesystem;
        if (empty($args)) {
            return false;
        }
        
        extract($args);
     	$this->set_resource_limit();
        
        $unlink_file = true; //Delete file after restore
		
		include_once ABSPATH . 'wp-admin/includes/file.php';
        
        //Detect source
        if ($backup_url) {
            //This is for clone (overwrite)
            
            $backup_file = download_url($backup_url);
            if (is_wp_error($backup_file)) {
                return array(
                    'error' => 'Unable to download backup file ('.$backup_file->get_error_message().')', 'error_code' => 'unable_to_download_backup_file'
                );
            }
            $what = 'full';
        } else {
            /*$tasks = $this->tasks;
            $task  = $tasks[$task_name];*/
			$tasks = $this->get_requested_task($result_id);
			$backup = $tasks['taskResults'][$result_id];
			
			$requestParams = unserialize($tasks['requestParams']);
			$args = $requestParams['secure']['account_info'];
			
            if (isset($backup['server'])) {
                $backup_file = $backup['server']['file_path'];
                $unlink_file = false; //Don't delete file if stored on server
				
            }
			elseif (isset($backup['ftp'])) {
                $ftp_file            = $backup['ftp'];
                $args                = $args['iwp_ftp'];
				
                $args['backup_file'] = $ftp_file;
				
				iwp_mmb_print_flush('FTP download: Start');
                $backup_file         = $this->get_ftp_backup($args);
				iwp_mmb_print_flush('FTP download: End');
				
                if ($backup_file == false) {
                    return array(
                        'error' => 'Failed to download file from FTP.', 'error_code' => 'download_failed_ftp'
                    );
                }
            }
			elseif (isset($backup['amazons3'])) {
                $amazons3_file       = $backup['amazons3'];
                $args                = $args['iwp_amazon_s3'];
                $args['backup_file'] = $amazons3_file;
				
				iwp_mmb_print_flush('Amazon S3 download: Start');
                $backup_file         = $this->get_amazons3_backup($args);
				iwp_mmb_print_flush('Amazon S3 download: End');
				
                if ($backup_file == false) {
                    return array(
                        'error' => 'Failed to download file from Amazon S3.', 'error_code' => 'download_failed_s3'
                    );
                }
            }
			elseif(isset($backup['dropbox'])){
            	$dropbox_file       = $backup['dropbox'];
                $args                = $args['iwp_dropbox'];
                $args['backup_file'] = $dropbox_file;
				iwp_mmb_print_flush('Dropbox download: Start');
                $backup_file         = $this->get_dropbox_backup($args);
				iwp_mmb_print_flush('Dropbox download: End');
                
                if ($backup_file == false) {
                    return array(
                        'error' => 'Failed to download file from Dropbox.', 'error_code' => 'download_failed_dropbox'
                    );
                }
            }
			elseif(isset($backup['gDrive'])){
            	$gdrive_file       = $backup['gDrive'];
                $args                = $args['iwp_gdrive'];
                $args['backup_file'] = $gdrive_file;
				iwp_mmb_print_flush('gDrive download: Start');
                $backup_file         = $this->get_google_drive_backup($args);
				iwp_mmb_print_flush('gDrive download: End');

				if(is_array($backup_file) && array_key_exists('error', $backup_file))
				{
					return $backup_file;
				}
            
                if ($backup_file == false) {
                    return array(
                        'error' => 'Failed to download file from gDrive.'
                    );
                }
        }
		
            $what = $requestParams['args']['what'];
        }
        $this->wpdb_reconnect();
		
		/////////////////// dev ////////////////////////
			
			
		if (!$this->is_server_writable()) {
			  return array(
				   'error' => 'Failed, please add FTP details', 'error_code' => 'failes_add_ftp_details'
			 );  
		} 
		
		$url = wp_nonce_url('index.php?page=iwp_no_page','iwp_fs_cred');
		ob_start();
		if (false === ($creds = request_filesystem_credentials($url, '', false, ABSPATH, null) ) ) {
			return array(
				   'error' => 'Unable to get file system credentials', 'error_code' => 'unable_to_get_file_system_credentials'
			 );   // stop processing here
		}
		ob_end_clean();
		
		if ( ! WP_Filesystem($creds, ABSPATH) ) {
			//request_filesystem_credentials($url, '', true, false, null);
			return array(
				   'error' => 'Unable to initiate file system. Please check you have entered valid FTP credentials.', 'error_code' => 'unable_to_initiate_file_system__check_FTP_credentials.'
			 );   // stop processing here
			//return;
		}
		
		require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');//will be used to copy from temp directory
		
		// do process
		$temp_dir = get_temp_dir();
		$new_temp_folder = untrailingslashit($temp_dir);
		$temp_uniq = md5(microtime(1));//should be random
		while (is_dir($new_temp_folder .'/'. $temp_uniq )) {
			$temp_uniq = md5(microtime(1));
		}
		$new_temp_folder = trailingslashit($new_temp_folder .'/'. $temp_uniq);
		$is_dir_created = mkdir($new_temp_folder);// new folder should be empty
		if(!$is_dir_created){
			return array(
				   'error' => 'Unable to create a temporary directory.', 'error_code' => 'unable_to_create_a_temp_directory.'
			);
		}
		
				
		
		$remote_abspath = $wp_filesystem->abspath();
		if(!empty($remote_abspath)){
			$remote_abspath = trailingslashit($remote_abspath);	
		}else{
			return array(
				   'error' => 'Unable to locate WP root directory using file system.', 'error_code' => 'unable_to_locate_wp_root_directory_using_file_system.'
			 );
		}
		
		//global $wp_filesystem;
//		$wp_filesystem->put_contents(
//		  '/tmp/example.txt',
//		  'Example contents of a file',
//		  FS_CHMOD_FILE // predefined mode settings for WP files
//		);
		
		/////////////////// dev ////////////////////////
        
        if ($backup_file && file_exists($backup_file)) {
            if ($overwrite) {//clone only fresh or existing to existing
                //Keep old db credentials before overwrite
                if (!$wp_filesystem->copy($remote_abspath . 'wp-config.php', $remote_abspath . 'iwp-temp-wp-config.php', true)) {
                    if($unlink_file) @unlink($backup_file);
                    return array(
                        'error' => 'Error creating wp-config. Please check your write permissions.',  'error_code' => 'error_creating_wpconfig_check_permissions'
                    );
                }
                
                $db_host     = DB_HOST;
                $db_user     = DB_USER;
                $db_password = DB_PASSWORD;
                $home        = rtrim(get_option('home'), "/");
                $site_url    = get_option('site_url');
                
                $clone_options                       = array();
                if (trim($clone_from_url) || trim($iwp_clone) || trim($maintain_old_key)) {
                    
                    $clone_options['iwp_client_nossl_key']  = get_option('iwp_client_nossl_key');
                    $clone_options['iwp_client_public_key'] = get_option('iwp_client_public_key');
                    $clone_options['iwp_client_action_message_id'] = get_option('iwp_client_action_message_id');
                   
                }
                
                 //$clone_options['iwp_client_backup_tasks'] = serialize(get_option('iwp_client_backup_tasks'));
                 $clone_options['iwp_client_notifications'] = serialize(get_option('iwp_client_notifications'));
                 $clone_options['iwp_client_pageview_alerts'] = serialize(get_option('iwp_client_pageview_alerts'));
                
				 $clone_options['iwp_client_backup_tasks'] = "selet * from iwp_backup_status";
				
                
            } else {
				
            	$restore_options                       = array();
			 	$restore_options['iwp_client_notifications'] = serialize(get_option('iwp_client_notifications'));
			 	$restore_options['iwp_client_pageview_alerts'] = serialize(get_option('iwp_client_pageview_alerts'));
			  	$restore_options['iwp_client_user_hit_count'] = serialize(get_option('iwp_client_user_hit_count'));
			  	//$restore_options['iwp_client_backup_tasks'] = serialize(get_option('iwp_client_backup_tasks'));
				
				$restore_options['iwp_client_backup_tasks'] = "selet * from iwp_backup_status";
            }
			
			
            
			
			//Backup file will be extracted to a temporary path
			
            //chdir(ABSPATH);
            $unzip   = $this->get_unzip();
            $command = "$unzip -o $backup_file -d $new_temp_folder";
			iwp_mmb_print_flush('ZIP Extract CMD: Start');
            ob_start();
            $result = $this->iwp_mmb_exec($command);
            ob_get_clean();
			iwp_mmb_print_flush('ZIP Extract CMD: End');
            
            if (!$result) { //fallback to pclzip
                define('IWP_PCLZIP_TEMPORARY_DIR', IWP_BACKUP_DIR . '/');
                //require_once ABSPATH . '/wp-admin/includes/class-pclzip.php';
				require_once $GLOBALS['iwp_mmb_plugin_dir'].'/pclzip.class.php';
				iwp_mmb_print_flush('ZIP Extract PCL: Start');
                $archive = new IWPPclZip($backup_file);
				$result  = $archive->extract(IWP_PCLZIP_OPT_PATH, $new_temp_folder, IWP_PCLZIP_OPT_TEMP_FILE_THRESHOLD, 1); //actual
                //$result  = $archive->extract(IWP_PCLZIP_OPT_PATH, $new_temp_folder, IWP_PCLZIP_OPT_REMOVE_PATH, $GLOBALS['iwp_mmb_plugin_dir'], IWP_PCLZIP_OPT_TEMP_FILE_THRESHOLD, 1);
				iwp_mmb_print_flush('ZIP Extract PCL: End');
            }
			$this->wpdb_reconnect();
            
            if ($unlink_file) {
                @unlink($backup_file);
            }
            
            if (!$result) {
                return array(
                    'error' => 'Failed to unzip files. pclZip error (' . $archive->error_code . '): .' . $archive->error_string, 'error_code' => 'failed_to_unzip_files_pclZip_error'
                );
            }
            
            $db_result = $this->restore_db($new_temp_folder); 
            
           if (!$db_result) {
                return array(
                    'error' => 'Error restoring database.', 'error_code' => 'error_restoring_db'
                );
            } else if(is_array($db_result) && isset($db_result['error'])){
            		return array(
                    'error' => $db_result['error']
                );
            }
            
        } else {
            return array(
                'error' => 'Backup file not found.', 'error_code' => 'backup_file_not_found'
            );
        }
		
		
		//copy files from temp to ABSPATH
		$copy_result = $this->iwp_mmb_direct_to_any_copy_dir($new_temp_folder, $remote_abspath);
		
		if ( is_wp_error($copy_result) ){
			$wp_temp_direct2 = new WP_Filesystem_Direct('');
			$wp_temp_direct2->delete($new_temp_folder, true);
			return $copy_result;
		}
		
        
        $this->wpdb_reconnect();
		
		
        
        //Replace options and content urls
        if ($overwrite) {//fresh WP package or existing to existing site
            //Get New Table prefix
            $new_table_prefix = trim($this->get_table_prefix());
            //Retrieve old wp_config
            //@unlink(ABSPATH . 'wp-config.php');
			$wp_filesystem->delete($remote_abspath . 'wp-config.php', false, 'f');
            //Replace table prefix
            //$lines = file(ABSPATH . 'iwp-temp-wp-config.php');
			$lines = $wp_filesystem->get_contents_array($remote_abspath . 'iwp-temp-wp-config.php');
            
			$new_lines = '';
            foreach ($lines as $line) {
                if (strstr($line, '$table_prefix')) {
                    $line = '$table_prefix = "' . $new_table_prefix . '";' . PHP_EOL;
                }
				$new_lines .= $line;
               //file_put_contents(ABSPATH . 'wp-config.php', $line, FILE_APPEND);
            }
			
			$wp_filesystem->put_contents($remote_abspath . 'wp-config.php', $new_lines);
            
            //@unlink(ABSPATH . 'iwp-temp-wp-config.php');
			$wp_filesystem->delete($remote_abspath . 'iwp-temp-wp-config.php', false, 'f');
            
            //Replace options
            $query = "SELECT option_value FROM " . $new_table_prefix . "options WHERE option_name = 'home'";
            $old   = $wpdb->get_var($query);
            $old   = rtrim($old, "/");
            $query = "UPDATE " . $new_table_prefix . "options SET option_value = %s WHERE option_name = 'home'";
            $wpdb->query($wpdb->prepare($query, $home));
            $query = "UPDATE " . $new_table_prefix . "options  SET option_value = %s WHERE option_name = 'siteurl'";
            $wpdb->query($wpdb->prepare($query, $home));
            //Replace content urls
            
			$regexp1 = 'src="(.*)'.$old.'(.*)"';
            $regexp2 = 'href="(.*)'.$old.'(.*)"';
            $query = "UPDATE " . $new_table_prefix . "posts SET post_content = REPLACE (post_content, %s,%s) WHERE post_content REGEXP %s OR post_content REGEXP %s";
            $wpdb->query($wpdb->prepare($query, $old, $home, $regexp1, $regexp2));
            
            if (trim($new_password)) {
                $new_password = wp_hash_password($new_password);
            }
            if (!trim($clone_from_url) && !trim($iwp_clone)) {
                if ($new_user && $new_password) {
                    $query = "UPDATE " . $new_table_prefix . "users SET user_login = %s, user_pass = %s WHERE user_login = %s";
                    $wpdb->query($wpdb->prepare($query, $new_user, $new_password, $old_user));
                }
            } else {
				
              // if ($iwp_clone) {
                    if ($admin_email) {
                        //Clean Install
                        $query = "UPDATE " . $new_table_prefix . "options SET option_value = %s WHERE option_name = 'admin_email'";
                        $wpdb->query($wpdb->prepare($query, $admin_email));
                        $query     = "SELECT * FROM " . $new_table_prefix . "users LIMIT 1";
                        $temp_user = $wpdb->get_row($query);
                        if (!empty($temp_user)) {
                            $query = "UPDATE " . $new_table_prefix . "users SET user_email=%s, user_login = %s, user_pass = %s WHERE user_login = %s";
                            $wpdb->query($wpdb->prepare($query, $admin_email, $new_user, $new_password, $temp_user->user_login));
                        }
                        
                    }
               // }
			   
			    //if ($clone_from_url) {
                    if ($new_user && $new_password) {
                        $query = "UPDATE " . $new_table_prefix . "users SET user_pass = %s WHERE user_login = %s";
                        $wpdb->query($wpdb->prepare($query, $new_password, $new_user));
                    }
               // }
                
            }
            
            if (is_array($clone_options) && !empty($clone_options)) {
                foreach ($clone_options as $key => $option) {
                    if (!empty($key)) {
                        $query = "SELECT option_value FROM " . $new_table_prefix . "options WHERE option_name = %s";
                        $res   = $wpdb->get_var($wpdb->prepare($query, $key));
                        if ($res == false) {
                            $query = "INSERT INTO " . $new_table_prefix . "options  (option_value,option_name) VALUES(%s,%s)";
                            $wpdb->query($wpdb->prepare($query, $option, $key));
                        } else {
                            $query = "UPDATE " . $new_table_prefix . "options  SET option_value = %s WHERE option_name = %s";
                            $wpdb->query($wpdb->prepare($query, $option, $key));
                        }
                    }
                }
            }
            
            //Remove hit count
            $query = "DELETE FROM " . $new_table_prefix . "options WHERE option_name = 'iwp_client_user_hit_count'";
           	$wpdb->query($query);
            
            //Check for .htaccess permalinks update
            $this->replace_htaccess($home, $remote_abspath);
        } else {
			//restore client options
             if (is_array($restore_options) && !empty($restore_options)) {
				 foreach ($restore_options as $key => $option) {
					 if (!empty($key)) {
						$query = "SELECT option_value FROM " . $wpdb->base_prefix . "options WHERE option_name = %s";
						$res   = $wpdb->get_var($wpdb->prepare($query, $key));
						if ($res == false) {
							$query = "INSERT INTO " . $wpdb->base_prefix . "options  (option_value,option_name) VALUES(%s,%s)";
							$wpdb->query($wpdb->prepare($query, $option, $key));
						} else {
							$query = "UPDATE " . $wpdb->base_prefix . "options  SET option_value = %s WHERE option_name = %s";
							$wpdb->query($wpdb->prepare($query, $option, $key));
						}
					}
					
					/*$test = update_option($key,$option);*/
				 }
			 }
		}
		
		//clear the temp directory
		$wp_temp_direct2 = new WP_Filesystem_Direct('');
		$wp_temp_direct2->delete($new_temp_folder, true);
                
        return !empty($new_user) ? $new_user : true ;
    }
    
    function restore_db($new_temp_folder)
    {
        global $wpdb;
        $paths     = $this->check_mysql_paths();
        $file_path = $new_temp_folder . '/iwp_db';
        @chmod($file_path,0755);
        $file_name = glob($file_path . '/*.sql');
        $file_name = $file_name[0];
        
        if(!$file_name){
        	return array('error' => 'Cannot access database file.');
        }
        
        $brace     = (substr(PHP_OS, 0, 3) == 'WIN') ? '"' : '';
        $command   = $brace . $paths['mysql'] . $brace . ' --host="' . DB_HOST . '" --user="' . DB_USER . '" --password="' . DB_PASSWORD . '" --default-character-set="utf8" ' . DB_NAME . ' < ' . $brace . $file_name . $brace;
        iwp_mmb_print_flush('DB Restore CMD: Start'); 
        ob_start();
        $result = $this->iwp_mmb_exec($command);
        ob_get_clean();
		iwp_mmb_print_flush('DB Restore CMD: End'); 
        if (!$result) {
            //try php
            $this->restore_db_php($file_name);
        }
        
        
        @unlink($file_name);
		@unlink(dirname($file_name).'/index.php');
		@rmdir(dirname($file_name));//remove its folder
        return true;
    }
    
    function restore_db_php($file_name)
    {
        
		$this->wpdb_reconnect();
		global $wpdb;
		
		$wpdb->query("SET NAMES 'utf8'");
		
        $current_query = '';
        // Read in entire file
        $lines         = file($file_name);
        // Loop through each line
		if(!empty($lines)){
			foreach ($lines as $line) {
				iwp_mmb_auto_print('restore_db_php');
				// Skip it if it's a comment
				if (substr($line, 0, 2) == '--' || $line == '')
					continue;
				
				// Add this line to the current query
				$current_query .= $line;
				// If it has a semicolon at the end, it's the end of the query
				if (substr(trim($line), -1, 1) == ';') {
					// Perform the query
					$result = $wpdb->query($current_query);
					if ($result === false)
						return false;
					// Reset temp variable to empty
					$current_query = '';
				}
			}
		}
        
        return true;
    }
    
    function get_table_prefix()
    {
        $lines = file(ABSPATH . 'wp-config.php');
        foreach ($lines as $line) {
            if (strstr($line, '$table_prefix')) {
                $pattern = "/(\'|\")[^(\'|\")]*/";
                preg_match($pattern, $line, $matches);
                $prefix = substr($matches[0], 1);
                return $prefix;
                break;
            }
        }
        return 'wp_'; //default
    }
    
    function optimize_tables()
    {
        global $wpdb;
        $query  = 'SHOW TABLE STATUS';
        $tables = $wpdb->get_results($query, ARRAY_A);
        foreach ($tables as $table) {
            if (in_array($table['Engine'], array(
                'MyISAM',
                'ISAM',
                'HEAP',
                'MEMORY',
                'ARCHIVE'
            )))
                $table_string .= $table['Name'] . ",";
            elseif ($table['Engine'] == 'InnoDB') {
                $optimize = $wpdb->query("ALTER TABLE {$table['Name']} ENGINE=InnoDB");
            }
        }
        
        if(!empty($table_string)){
			$table_string = rtrim($table_string, ',');
        $optimize     = $wpdb->query("OPTIMIZE TABLE $table_string");
		}
        
        return $optimize ? true : false;
    }
    
    ### Function: Auto Detect MYSQL and MYSQL Dump Paths
    function check_mysql_paths()
    {
        global $wpdb;
        $paths = array(
            'mysql' => '',
            'mysqldump' => ''
        );
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $mysql_install = $wpdb->get_row("SHOW VARIABLES LIKE 'basedir'");
            if ($mysql_install) {
                $install_path       = str_replace('\\', '/', $mysql_install->Value);
                $paths['mysql']     = $install_path . 'bin/mysql.exe';
                $paths['mysqldump'] = $install_path . 'bin/mysqldump.exe';
            } else {
                $paths['mysql']     = 'mysql.exe';
                $paths['mysqldump'] = 'mysqldump.exe';
            }
        } else {
            $paths['mysql'] = $this->iwp_mmb_exec('which mysql', true);
            if (empty($paths['mysql']))
                $paths['mysql'] = 'mysql'; // try anyway
            
            $paths['mysqldump'] = $this->iwp_mmb_exec('which mysqldump', true);
            if (empty($paths['mysqldump']))
                $paths['mysqldump'] = 'mysqldump'; // try anyway         
            
        }
        
        
        return $paths;
    }
    
    //Check if exec, system, passthru functions exist
    function check_sys()
    {
        if ($this->iwp_mmb_function_exists('exec'))
            return 'exec';
        
        if ($this->iwp_mmb_function_exists('system'))
            return 'system';
        
        if ($this->iwp_mmb_function_exists('passhtru'))
            return 'passthru';
        
        return false;
        
    }
    
    function iwp_mmb_exec($command, $string = false, $rawreturn = false)
    {
        if ($command == '')
            return false;
        
        if ($this->iwp_mmb_function_exists('exec')) {
            $log = @exec($command, $output, $return);
            
            if ($string)
                return $log;
            if ($rawreturn)
                return $return;
            
            return $return ? false : true;
        } elseif ($this->iwp_mmb_function_exists('system')) {
            $log = @system($command, $return);
            
            if ($string)
                return $log;
            
            if ($rawreturn)
                return $return;
            
            return $return ? false : true;
        } elseif ($this->iwp_mmb_function_exists('passthru') && !$string) {
            $log = passthru($command, $return);
            
            if ($rawreturn)
                return $return;
            
            return $return ? false : true;
        }
        
        if ($rawreturn)
        	return -1;
        	
        return false;
    }
    
    function get_zip()
    {
        $zip = $this->iwp_mmb_exec('which zip', true);
        if (!$zip)
            $zip = "zip";
        return $zip;
    }
    
    function get_unzip()
    {
        $unzip = $this->iwp_mmb_exec('which unzip', true);
        if (!$unzip)
            $unzip = "unzip";
        return $unzip;
    }
    function getDirectorySize($directory)
    {
        $dirSize=0;
        $fileCount=0;
        $dirInfo = array(
            'dirSize'   =>  0,
            'fileCount' =>  0
        );

        if(!$dh=opendir($directory))
        {
            return false;
        }

        while($file = readdir($dh))
        {
            if($file == "." || $file == "..")
            {
                continue;
            }

            if(is_file($directory."/".$file))
            {
                $dirInfo['dirSize'] += filesize($directory."/".$file);
                $dirInfo['fileCount'] += 1;
            }

            if(is_dir($directory."/".$file))
            {
                $teminfo = $this->getDirectorySize($directory."/".$file);
                if(isset($teminfo['dirSize'])) $dirInfo['dirSize'] += $teminfo['dirSize'];
                if(isset($teminfo['fileCount'])) $dirInfo['fileCount'] += $teminfo['fileCount'];
            }
        }

        closedir($dh);

        return $dirInfo;
    }
    
    function get_database_size() {
        global $wpdb;
        
        $total_size = 0;
            $total_size_with_exclusions = 0;
            $rows = $wpdb->get_results( "SHOW TABLE STATUS", ARRAY_A );
            foreach( $rows as $row ) {
                    $excluded = true; // Default.

                    // TABLE STATUS.
                    $rowsb = $wpdb->get_results( "CHECK TABLE `{$row['Name']}`", ARRAY_A );
                    foreach( $rowsb as $rowb ) {
                            if ( $rowb['Msg_type'] == 'status' ) {
                                    $status = $rowb['Msg_text'];
                            }
                    }
                    unset( $rowsb );

                    // TABLE SIZE.
                    $size = ( $row['Data_length'] + $row['Index_length'] );
                    $total_size += $size;
            }
            return $total_size;
    }
    
     function loopback_test() {
            $loopback_url = admin_url('admin-ajax.php');
            //pb_backupbuddy::status( 'details', 'Testing loopback connections by connecting back to site at the URL: `' . $loopback_url . '`. It should display simply "0" or "-1" in the body.' );

            $response = wp_remote_get(
                    $loopback_url,
                    array(
                            'method' => 'GET',
                            'timeout' => 8, // X second delay. A loopback should be very fast.
                            'redirection' => 5,
                            'httpversion' => '1.0',
                            'blocking' => true,
                            'headers' => array(),
                            'body' => null,
                            'cookies' => array()
                    )
            );

            if( is_wp_error( $response ) ) { // Loopback failed. Some kind of error.
                    $error = $response->get_error_message();
                    //pb_backupbuddy::status( 'error', 'Loopback test error: `' . $error . '`.' );
                    return 'Error: ' . $error;
            } else {
                    if ( ( $response['body'] == '-1' ) || ( $response['body'] == '0' ) ) { // Loopback succeeded.
                            //pb_backupbuddy::status( 'details', 'HTTP Loopback test success. Returned `' . $response['body'] . '`.' );
                            return true;
                    } else { // Loopback failed.
                            $error = 'Connected to server but unexpected output: ' . htmlentities( $response['body'] );
                            //pb_backupbuddy::status( 'error', $error );
                            return $error;
                    }
            }
    }
    
    function is_php( $bits ) {
		
            $result = ( ( PHP_INT_SIZE * 8 ) == $bits ) ? true : false;

            return $result;

    }
    
    function stat( $filename ) {
		
                $result = false;

                // If the file is readable then we should be able to stat it 
                if ( @is_readable( $filename ) ) {

                        $stats = @stat( $filename );

                        if ( false !== $stats ) {

                                // Looks like we got some valid data - for now just process the size
                                if ( $this->is_php( 32 ) ) {

                                        // PHP is 32 bits so we may have a file size problem over 2GB.
                                        // This is one way to test for a file size problem - there are others
                                        if ( 0 > $stats[ 'size' ] ) {

                                                // Unsigned long has been interpreted as a signed int and has sign bit
                                                // set so is appearing as negative - magically convert it to a double
                                                // Note: this only works to give us an extension from 2GB to 4GB but that
                                                // should be enough as the underlying OS probably can't support >4GB or
                                                // zip command cannot anyway
                                                $stats[ 'dsize' ] = ( (double)0x80000000 + ( $stats[ 'size' ] & 0x7FFFFFFF ) );

                                        } else {

                                                // Assume it's valid
                                                $stats[ 'dsize' ] = (double)$stats[ 'size' ];

                                        }

                                } else {

                                        // Looks like 64 bit PHP so file size should be fine
                                        // Force added item to double for consistency
                                        $stats[ 'dsize' ] = (double)$stats[ 'size' ];

                                }

                                // Add an additional item for short octal representation of mode
                                $stats[ 'mode_octal_four' ] = substr( sprintf( '%o', $stats[ 'mode' ] ), -4 );

                                $result = $stats;

                        } else {

                                // Hmm, stat() failed for some reason - could be an LFS problem with the
                                // way PHP has been built :-(
                                // TODO: Consider alternatives - may be able to use exec to run the
                                // command line stat function which _should_ be ok and we can map output
                                // into the same array format. This does depend on having exec() and the
                                // stat command available and it's definitely not a nice option
                                $result = false;

                        }

                }

                return $result;
        }

   
    function getDirectoryInfo() {
        $tests = array();

        $uploads_dirs = wp_upload_dir();
        $directories = array(
                ABSPATH . '',
                ABSPATH . 'wp-includes/',
                ABSPATH . 'wp-admin/',
                ABSPATH . 'wp-content/themes/',
                ABSPATH . 'wp-content/plugins/',
                ABSPATH . 'wp-content/',
                rtrim( $uploads_dirs['basedir'], '\\/' ) . '/',
                ABSPATH . 'wp-includes/',

        );
        
        foreach( $directories as $directory ) {
	
            $mode_octal_four = '<i>Unknown</i>';
            $owner = '<i>Unknown</i>';

            $stats = $this->stat( $directory );
            if ( false !== $stats ) {
                    $mode_octal_four = $stats['mode_octal_four'];
                    $owner = $stats['uid'] . ':' . $stats['gid'];
            }
            $this_test = array(
                                            'title'			=>		'/' . str_replace( ABSPATH, '', $directory ),
                                            'suggestion'	=>		'<= 755',
                                            'value'			=>		$mode_octal_four,
                                            'owner'			=>		$owner,
                                    );
            if ( false === $stats || $mode_octal_four > 755 ) {
                    $this_test['status'] = 'WARNING';
            } else {
                    $this_test['status'] = 'OK';
            }
            array_push( $tests, $this_test );

    } // end foreach.
    return $tests;
        
    }
    
    function check_backup_compat()
    {
        global $wpdb;
        $reqs = array();
        $reqs['serverInfo']['server_os']['name'] = 'Server OS';
        if (strpos($_SERVER['DOCUMENT_ROOT'], '/') === 0) {
            $reqs['serverInfo']['server_os']['status'] = php_uname('s')." ".php_uname('v');
            $reqs['serverInfo']['server_os']['pass']   = true;
        } else {
            $reqs['serverInfo']['server_os']['status'] = php_uname('s')." ".php_uname('v');
            $reqs['serverInfo']['server_os']['pass']   = 'ok';
        }
        $reqs['serverInfo']['server_os']['suggeted'] = 'Linux';
        
        $reqs['serverInfo']['php_version']['name'] = 'PHP Version';
        $reqs['serverInfo']['php_version']['status'] = phpversion();
        $reqs['serverInfo']['php_version']['suggeted'] = '>= 5.2 (5.2.16+ best)';
        if ((float) phpversion() >= 5.1) {
            $reqs['serverInfo']['php_version']['pass'] = true;
        } else {
            $reqs['serverInfo']['php_version']['pass'] = false;
        }
        
        $reqs['mysqlInfo']['mysql_version']['name'] = 'MySql Version';
        $reqs['mysqlInfo']['mysql_version']['status'] = $wpdb->db_version();
        $reqs['mysqlInfo']['mysql_version']['suggeted'] = '>= 5.0';
        
        if ((float) $wpdb->db_version() >= 5.0) {
            $reqs['mysqlInfo']['mysql_version']['pass'] = true;
        } else {
            $reqs['mysqlInfo']['mysql_version']['pass'] = false;
        }
        
        $reqs['serverInfo']['php_max_execution_time']['name'] = 'PHP max_execution_time';
        $reqs['serverInfo']['php_max_execution_time']['status'] = ini_get( 'max_execution_time' );
        $reqs['serverInfo']['php_max_execution_time']['suggeted'] = '>= 30 seconds (30+ best)';
        
        if (str_ireplace( 's', '', ini_get( 'max_execution_time' ) ) < 30) {
            $reqs['serverInfo']['php_max_execution_time']['pass'] = false;
        } else {
            $reqs['serverInfo']['php_max_execution_time']['pass'] = true;
        }
        
        if ( !ini_get( 'memory_limit' ) ) {
		$parent_class_val = 'unknown';
	} else {
		$parent_class_val = ini_get( 'memory_limit' );
	}
        $reqs['serverInfo']['php_memory_limit']['name'] = 'PHP Memory Limit';
        $reqs['serverInfo']['php_memory_limit']['status'] = $parent_class_val;
        $reqs['serverInfo']['php_memory_limit']['suggeted'] = '>= 128M (256M+ best)';
        
        if ( preg_match( '/(\d+)(\w*)/', $parent_class_val, $matches ) ) {
		$parent_class_val = $matches[1];
		$unit = $matches[2];
		// Up memory limit if currently lower than 256M.
		if ( 'g' !== strtolower( $unit ) ) {
			if ( ( $parent_class_val < 128 ) || ( 'm' !== strtolower( $unit ) ) ) {
				$reqs['serverInfo']['php_memory_limit']['pass'] = false;
			} else {
				$reqs['serverInfo']['php_memory_limit']['pass'] = true;
			}
		}
	} else {
		$reqs['serverInfo']['php_memory_limit']['pass'] = false;
	}
        
        //$reqs['serverInfo']['Site Information']['status'] = $this->getDirectorySize(ABSPATH);
        $tempInfo = $this->getDirectorySize(ABSPATH);
        
        
        $reqs['serverInfo']['site_size']['name'] = 'Site size';
        $reqs['serverInfo']['site_size']['status'] = ($tempInfo['dirSize']/1048576). " MB";
        $reqs['serverInfo']['site_size']['pass'] = true;
        $reqs['serverInfo']['site_size']['suggeted'] = 'N/A';
        
        $reqs['serverInfo']['site_number_of_files']['name'] = 'Site number of files';
        $reqs['serverInfo']['site_number_of_files']['status'] = $tempInfo['fileCount'];
        $reqs['serverInfo']['site_number_of_files']['pass'] = true;
        $reqs['serverInfo']['site_number_of_files']['suggeted'] = 'N/A';
        
        $reqs['mysqlInfo']['database_size']['name'] = 'Database Size';
        $reqs['mysqlInfo']['database_size']['status'] = $this->get_database_size();
        $reqs['mysqlInfo']['database_size']['pass'] = true;
        $reqs['mysqlInfo']['database_size']['suggeted'] = 'N/A';
        
        $reqs['serverInfo']['http_loopback']['name'] = 'Http Loopbacks';
        if($this->loopback_test() === true) {
            $reqs['serverInfo']['http_loopback']['status'] = true;
            $reqs['serverInfo']['http_loopback']['pass'] = true;
        } else {
            $reqs['serverInfo']['http_loopback']['status'] = false;
            $reqs['serverInfo']['http_loopback']['pass'] = false;
        }
        $reqs['serverInfo']['http_loopback']['suggeted'] = "enabled";
        
        $reqs['serverInfo']['php_architecture']['name'] = 'PHP Architecture';
        $reqs['serverInfo']['php_architecture']['status'] = ( PHP_INT_SIZE * 8 ) . '-bit';
        $reqs['serverInfo']['php_architecture']['pass'] = true;
        $reqs['serverInfo']['php_architecture']['suggeted'] = '64-bit';
        
        // http Server Software
	if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
		$server_software = $_SERVER['SERVER_SOFTWARE'];
	} else {
		$server_software = 'Unknown';
	}
        $reqs['serverInfo']['http_server_software']['name'] = 'Http Server Software';
        $reqs['serverInfo']['http_server_software']['status'] = $server_software;
        $reqs['serverInfo']['http_server_software']['pass'] = true;
        $reqs['serverInfo']['http_server_software']['suggeted'] = 'N/A';
        
        $reqs['directoryInfo']['status'] = $this->getDirectoryInfo();
        

        
        $reqs['serverInfo']['backup_folder']['name'] = "Backup Folder";
        if (is_writable(WP_CONTENT_DIR)) {
            $reqs['serverInfo']['backup_folder']['status'] = "writable";
            $reqs['serverInfo']['backup_folder']['pass']   = true;
        } else {
            $reqs['serverInfo']['backup_folder']['status'] = "not writable";
            $reqs['serverInfo']['backup_folder']['pass']   = false;
        }
        $reqs['serverInfo']['backup_folder']['suggeted'] = 'Need to writiable';
        
        
        $file_path = IWP_BACKUP_DIR;
        $reqs['serverInfo']['backup_folder']['status'] .= ' (' . $file_path . ')';
        
        $reqs['serverInfo']['execute_function']['name'] = 'Execute Function';
        if ($func = $this->check_sys()) {
            $reqs['serverInfo']['execute_function']['status'] = $func;
            $reqs['serverInfo']['execute_function']['pass']   = true;
        } else {
            $reqs['serverInfo']['execute_function']['status'] = "not found";
            $reqs['serverInfo']['execute_function']['pass']   = false;
        }
        $reqs['serverInfo']['execute_function']['suggeted']   = 'Need any one of support exec, system, passhtru (or will try PHP replacement)';
        $reqs['serverInfo']['zip']['name'] = 'Zip';
        $reqs['serverInfo']['zip']['status'] = $this->get_zip();
        $reqs['serverInfo']['zip']['suggeted']   = 'System Zip need or will try PHP replacement';
        $reqs['serverInfo']['zip']['pass'] = true;
        
        
        
        $reqs['serverInfo']['unzip']['name'] = 'Unzip';
        $reqs['serverInfo']['unzip']['status'] = $this->get_unzip();
        $reqs['serverInfo']['unzip']['suggeted'] = 'System Zip need or will try PHP replacement';
        $reqs['serverInfo']['unzip']['pass'] = true;
        
        $paths = $this->check_mysql_paths();
        $reqs['mysqlInfo']['mysql_dump']['name'] = 'MySQL Dump';
        if (!empty($paths['mysqldump'])) {
            $reqs['mysqlInfo']['mysql_dump']['status'] = $paths['mysqldump'];
            $reqs['mysqlInfo']['mysql_dump']['pass']   = true;
        } else {
            $reqs['mysqlInfo']['mysql_dump']['status'] = "not found";
            $reqs['mysqlInfo']['mysql_dump']['pass']   = false;
        }
        $reqs['mysqlInfo']['mysql_dump']['suggeted']   = "Command line [fastest] > PHP-based [slowest] (or will try PHP replacement)";
        
        $exec_time                        = ini_get('max_execution_time');
        $reqs['serverInfo']['mysql_dump']['name'] = 'Execution time';
        $reqs['serverInfo']['mysql_dump']['status'] = $exec_time ? $exec_time . "s" : 'unknown';
        $reqs['serverInfo']['mysql_dump']['pass']   = true;
        $reqs['serverInfo']['mysql_dump']['suggeted']   = 'N/A';
        
        $mem_limit                      = ini_get('memory_limit');
        $reqs['serverInfo']['memory_limit']['name'] = 'Memory limit';
        $reqs['serverInfo']['memory_limit']['status'] = $mem_limit ? $mem_limit : 'unknown';
        $reqs['serverInfo']['memory_limit']['pass']   = true;
        $reqs['serverInfo']['memory_limit']['suggeted']   = 'N/A';
        
        $reqs['functionList']['file_put_content']['name'] = "File Put Content";
        if(function_exists('file_put_contents')) {
            $reqs['functionList']['file_put_content']['status'] = "Available";
            $reqs['functionList']['file_put_content']['pass'] = true;
        } else {
            $reqs['functionList']['file_put_content']['status'] = "Not Available";
            $reqs['functionList']['file_put_content']['pass'] = false;
            
        }
        $reqs['functionList']['file_put_content']['suggeted']   = 'N/A';
        
        $reqs['functionList']['ftp_functions']['name'] = "FTP Funtions";
        if(function_exists('ftp_connect')) {
            $reqs['functionList']['ftp_functions']['status'] = "Available";
            $reqs['functionList']['ftp_functions']['pass'] = true;
        } else {
            $reqs['functionList']['ftp_functions']['status'] = "Not Available";
            $reqs['functionList']['ftp_functions']['pass'] = false;
            $reqs['functionList']['ftp_functions']['pass'] = false;
        }
        $reqs['functionList']['ftp_functions']['suggeted']   = 'N/A';
        
        return $reqs;
    }
        
function ftp_backup($args)
    {
        extract($args);
        //Args: $ftp_username, $ftp_password, $ftp_hostname, $backup_file, $ftp_remote_folder, $ftp_site_folder
        if(isset($use_sftp) && $use_sftp==1) {
            $port = $ftp_port ? $ftp_port : 22; //default port is 22
            /*
             * SFTP section start here phpseclib library is used for this functionality
             */
            $iwp_mmb_plugin_dir = WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__));
            $path = $iwp_mmb_plugin_dir.'/lib/phpseclib';
            set_include_path(get_include_path() . PATH_SEPARATOR . $path);
            include_once('Net/SFTP.php');
            
            
            $sftp = new Net_SFTP($ftp_hostname, $port);
            if(!$sftp) {
                return array(
                                            'error' => 'Failed to connect to ' . $ftp_hostname,
                                            'partial' => 1
                            );
            }
            if (!$sftp->login($ftp_username, $ftp_password)) {
                return array(
                                            'error' => 'FTP login failed for ' . $ftp_username . ', ' . $ftp_password,
                                            'partial' => 1
                            );
            } else {
                if ($ftp_site_folder) {
                    $ftp_remote_folder .= '/' . $this->site_name;
                }
                $remote_loation = basename($backup_file);
                $local_location = $backup_file;
                $sftp->mkdir($ftp_remote_folder,-1,true);
                $sftp->chdir($ftp_remote_folder);
                //$this->iwp_sftp_mkdir($sftp,'sftpbackup/test123/test1/test2');
                $upload = $sftp->put(basename($backup_file), $backup_file, NET_SFTP_LOCAL_FILE);
                
                if ($upload === false) {
                    return array(
                        'error' => 'Failed to upload file to FTP. Please check your specified path.',
                        'partial' => 1
                    );
                }
                //SFTP library has automatic connection closed. So no need to call seperate connection close function
            }
            
        } else {
        $port = $ftp_port ? $ftp_port : 21; //default port is 21
        if ($ftp_ssl) {
            if (function_exists('ftp_ssl_connect')) {
                $conn_id = ftp_ssl_connect($ftp_hostname,$port);
                if ($conn_id === false) {
                	return array(
                			'error' => 'Failed to connect to ' . $ftp_hostname,
							'error_code' => 'failed_to_connect_ftp_if_ftp_ssl',
                			'partial' => 1
                	);
                }
            } else {
                return array(
                    'error' => 'Your server doesn\'t support FTP SSL',
					'error_code' => 'no_ftp_ssl_support',
                    'partial' => 1
                );
            }
        } else {
            if (function_exists('ftp_connect')) {
                $conn_id = ftp_connect($ftp_hostname,$port);
                if ($conn_id === false) {
                    return array(
                        'error' => 'Failed to connect to ' . $ftp_hostname,
						'error_code' => 'failed_to_connect_ftp',
                        'partial' => 1
                    );
                }
            } else {
                return array(
                    'error' => 'Your server doesn\'t support FTP',
					'error_code' => 'no_ftp_support',
                    'partial' => 1
                );
            }
        }
        $login = @ftp_login($conn_id, $ftp_username, $ftp_password);
        if ($login === false) {
            return array(
                'error' => 'FTP login failed for ' . $ftp_username . ', ' . $ftp_password,
				'error_code' => 'ftp_login_failed',
                'partial' => 1
            );
        }
        
        if($ftp_passive){
					@ftp_pasv($conn_id,true);
				}
				
        @ftp_mkdir($conn_id, $ftp_remote_folder);
        if ($ftp_site_folder) {
            $ftp_remote_folder .= '/' . $this->site_name;
        }
        @ftp_mkdir($conn_id, $ftp_remote_folder);
        
        $upload = @ftp_put($conn_id, $ftp_remote_folder . '/' . basename($backup_file), $backup_file, FTP_BINARY);
        
        if ($upload === false) { //Try ascii
            $upload = @ftp_put($conn_id, $ftp_remote_folder . '/' . basename($backup_file), $backup_file, FTP_ASCII);
        }
        @ftp_close($conn_id);
        
        if ($upload === false) {
            return array(
                'error' => 'Failed to upload file to FTP. Please check your specified path.',
				'error_code' => 'failed_to_upload_file_check_path',
                'partial' => 1
            );
        }
        }
        return true;
    }
    
    function remove_ftp_backup($args)
    {
        extract($args);
        //Args: $ftp_username, $ftp_password, $ftp_hostname, $backup_file, $ftp_remote_folder
        if(isset($use_sftp) && $use_sftp==1) {
            $port = $ftp_port ? $ftp_port : 22; //default port is 22
            /*
             * SFTP section start here phpseclib library is used for this functionality
             */
            $iwp_mmb_plugin_dir = WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__));
            $path = $iwp_mmb_plugin_dir.'/lib/phpseclib';
            set_include_path(get_include_path() . PATH_SEPARATOR . $path);
            include_once('Net/SFTP.php');
            
            
            $sftp = new Net_SFTP($ftp_hostname,$port);
            if(!$sftp) {
                return array(
                                            'error' => 'Failed to connect to ' . $ftp_hostname,
                                            'partial' => 1
                            );
            }
            if (!$sftp->login($ftp_username, $ftp_password)) {
                return array(
                                            'error' => 'FTP login failed for ' . $ftp_username . ', ' . $ftp_password,
                                            'partial' => 1
                            );
            } else {
                if ($ftp_site_folder) {
                    $ftp_remote_folder .= '/' . $this->site_name;
                }
                $remote_loation = basename($backup_file);
                $local_location = $backup_file;
                
                $sftp->chdir($ftp_remote_folder);
                $sftp->delete(basename($backup_file));

            }
            //SFTP library has automatic connection closed. So no need to call seperate connection close function
            
        } else {
        $port = $ftp_port ? $ftp_port : 21; //default port is 21
        if ($ftp_ssl && function_exists('ftp_ssl_connect')) {
            $conn_id = ftp_ssl_connect($ftp_hostname,$port);
        } else if (function_exists('ftp_connect')) {
            $conn_id = ftp_connect($ftp_hostname,$port);
        }
        
        if ($conn_id) {
            $login = @ftp_login($conn_id, $ftp_username, $ftp_password);
            if ($ftp_site_folder)
                $ftp_remote_folder .= '/' . $this->site_name;
            
            if($ftp_passive){
							@ftp_pasv($conn_id,true);
						}
			
			if(!is_array($backup_file))
			{
				$temp_backup_file = $backup_file;
				$backup_file = array();
				$backup_file[] = $temp_backup_file;
			}
			
			foreach($backup_file as $key => $value)
			{
				$delete = ftp_delete($conn_id, $ftp_remote_folder . '/' . $value);
            }
            ftp_close($conn_id);
        }
        }
        
    }
    
    function get_ftp_backup($args)
    {
        extract($args);
        //Args: $ftp_username, $ftp_password, $ftp_hostname, $backup_file, $ftp_remote_folder
        if(isset($use_sftp) && $use_sftp==1) {
            $port = $ftp_port ? $ftp_port : 22; //default port is 22
            /*
             * SFTP section start here phpseclib library is used for this functionality
             */
            $iwp_mmb_plugin_dir = WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__));
            $path = $iwp_mmb_plugin_dir.'/lib/phpseclib';
            set_include_path(get_include_path() . PATH_SEPARATOR . $path);
            include_once('Net/SFTP.php');
            
            
            $sftp = new Net_SFTP($ftp_hostname,$port);
            if(!$sftp) {
                return array(
                                            'error' => 'Failed to connect to ' . $ftp_hostname,
                                            'partial' => 1
                            );
            }
            if (!$sftp->login($ftp_username, $ftp_password)) {
                return array(
                                            'error' => 'FTP login failed for ' . $ftp_username . ', ' . $ftp_password,
                                            'partial' => 1
                            );
            } else {
                if ($ftp_site_folder) {
                    $ftp_remote_folder .= '/' . $this->site_name;
                }
                $remote_loation = basename($backup_file);
                $local_location = $backup_file;
                
                $sftp->chdir($ftp_remote_folder);
                //$sftp->delete(basename($backup_file));
                $temp = wp_tempnam('iwp_temp_backup.zip');
                
                $get  = $sftp->get(basename($backup_file), $temp);
                if ($get === false) {
                    return false;
                } else {
                    return $temp;
                }
                //SFTP library has automatic connection closed. So no need to call seperate connection close function

            }
            
        } else {
        $port = $ftp_port ? $ftp_port : 21; //default port is 21
        if ($ftp_ssl && function_exists('ftp_ssl_connect')) {
            $conn_id = ftp_ssl_connect($ftp_hostname,$port);
            
        } else if (function_exists('ftp_connect')) {
            $conn_id = ftp_connect($ftp_hostname,$port);
            if ($conn_id === false) {
                return false;
            }
        } 
        $login = @ftp_login($conn_id, $ftp_username, $ftp_password);
        if ($login === false) {
            return false;
        }
        
        if ($ftp_site_folder)
            $ftp_remote_folder .= '/' . $this->site_name;
        
        if($ftp_passive){
					@ftp_pasv($conn_id,true);
				}
        
		//$temp = ABSPATH . 'iwp_temp_backup.zip';
        $temp = wp_tempnam('iwp_temp_backup.zip');
		
        $get  = ftp_get($conn_id, $temp, $ftp_remote_folder . '/' . $backup_file, FTP_BINARY);
        if ($get === false) {
            return false;
        } else {
        }
        ftp_close($conn_id);
        
        return $temp;
        }
    }
	
   
 function dropbox_backup($args){
        extract($args);
        
        if(isset($consumer_secret) && !empty($consumer_secret)){

	        require_once $GLOBALS['iwp_mmb_plugin_dir'] . '/lib/dropbox.php';
	        
	        $dropbox = new IWP_Dropbox($consumer_key, $consumer_secret);
	        $dropbox->setOAuthTokens($oauth_token, $oauth_token_secret);
	        
	        if ($dropbox_site_folder == true)
	        	$dropbox_destination .= '/' . $this->site_name . '/' . basename($backup_file);
	        else
	        	$dropbox_destination .= '/' . basename($backup_file);
	        
	        try {
	        	$dropbox->upload($backup_file, $dropbox_destination, true);
	        } catch (Exception $e) {
	        	$this->_log($e->getMessage());
	        	return array(
	        		'error' => $e->getMessage(),
	        		'partial' => 1
	        	);
	        }
	        
	        return true;
        	
        } else {
			return array(
                'error' => 'Please connect your InfiniteWP panel with your Dropbox account.',
				'error_code' => 'please_connect_dropbox_account_with_panel'
            );
		}
        
    }

    
	function remove_dropbox_backup($args) {
    	extract($args);
        
        require_once $GLOBALS['iwp_mmb_plugin_dir'] . '/lib/dropbox.php';
        
        $dropbox = new IWP_Dropbox($consumer_key, $consumer_secret);
        $dropbox->setOAuthTokens($oauth_token, $oauth_token_secret);
        
        if ($dropbox_site_folder == true)
        	$dropbox_destination .= '/' . $this->site_name;
    	
		$temp_backup_file = $backup_file;
		if(!is_array($backup_file))
		{
			$backup_file = array();
			$backup_file[] = $temp_backup_file;
		}
		foreach($backup_file as $key => $value)
		{
			try {
				$dropbox->fileopsDelete($dropbox_destination . '/' . $value);
			} catch (Exception $e) {
				$this->_log($e->getMessage());
				/*return array(
					'error' => $e->getMessage(),
					'partial' => 1
				);*/
			}
    	}
    	//return true;
	}
	

	function get_dropbox_backup($args) {
  		extract($args);
  		
  		require_once $GLOBALS['iwp_mmb_plugin_dir']  . '/lib/dropbox.php';
  		
  		$dropbox = new IWP_Dropbox($consumer_key, $consumer_secret);
        $dropbox->setOAuthTokens($oauth_token, $oauth_token_secret);
        
        if ($dropbox_site_folder == true)
        	$dropbox_destination .= '/' . $this->site_name;
        
  		//$temp = ABSPATH . 'iwp_temp_backup.zip';
        $temp = wp_tempnam('iwp_temp_backup.zip');
  		
  		try {
			
  			$file = $dropbox->download($dropbox_destination.'/'.$backup_file); 
			$handle = @fopen($temp, 'w'); 			
			$result = fwrite($handle, $file);
			fclose($handle);
			
			if($result)
				return $temp;
			else
				return false;
  		} catch (Exception $e) {
  			$this->_log($e->getMessage());
  			return array(
  				'error' => $e->getMessage(),
				'erro_code' => 'download_error_s3',
  				'partial' => 1
  			);
  		}
	}
	

    function amazons3_backup($args)
    {
        if ($this->iwp_mmb_function_exists('curl_init')) {
            require_once($GLOBALS['iwp_mmb_plugin_dir'].'/lib/amazon_s3/sdk.class.php');
			extract($args);
            
            if ($as3_site_folder == true)
			{
				if(!empty($as3_directory))
				{
					$as3_directory .= '/' . $this->site_name;
				}
				else
				{
					$as3_directory = $this->site_name;
				}
            }
			if(empty($as3_directory))
			{
				$as3_file = basename($backup_file);
			}
			else
			{
				$as3_file =  $as3_directory . '/' . basename($backup_file);
			}
            try{
				
			CFCredentials::set(array('development' => array('key' => trim($as3_access_key), 'secret' => trim(str_replace(' ', '+', $as3_secure_key)), 'default_cache_config' => '', 'certificate_authority' => true, 'use_ssl'=>false, 'ssl_verification'=>false), '@default' => 'development'));
			$s3 = new AmazonS3();
            $response = $s3->create_object($as3_bucket, $as3_file, array('fileUpload' => $backup_file));
			$upload = $response->isOk();
			if($upload) {
                return true;
            } else {
                return array(
                    'error' => 'Failed to upload to Amazon S3. Please check your details and set upload/delete permissions on your bucket.',
					'error_code' => 'upload_failed_to_S3_check_your_details_and_set_upload_delete_permissions_on_your_bucket',
                    'partial' => 1
                );
            }

        }catch (Exception $e){
         $err = $e->getMessage();
         if($err){
         	 return array(
                'error' => 'Failed to upload to AmazonS3 ('.$err.').',
				'error_code' => 'failed_upload_s3_with_error'
            );
         } else {
         	return array(
                'error' => 'Failed to upload to Amazon S3.',
				'error_code' => 'failed_upload_s3'
            );
         }
        }
		} else {
            return array(
                'error' => 'You cannot use Amazon S3 on your server. Please enable curl first.',
				'error_code' => 'you_cannot_use_S3_on_your_server_enable_curl',
                'partial' => 1
            );
        }
    }
    
	function google_drive_backup($args = '', $uploadid = null, $offset = 0)
	{
		require_once($GLOBALS['iwp_mmb_plugin_dir'].'/lib/Google/Client.php');
		require_once($GLOBALS['iwp_mmb_plugin_dir'].'/lib/Google/Http/MediaFileUpload.php');
		require_once($GLOBALS['iwp_mmb_plugin_dir'].'/lib/Google/Service/Drive.php');
		
		//$this -> hisID = $historyID;
	
		$upload_file_block_size = 1 *1024 *1024;
		$iwp_folder_id = '';
		$sub_folder_id = '';
		$create_sub_folder = $args['gdrive_site_folder'];
		$sub_folder_name = $this->site_name;
		//$task_result = $this->getRequiredData($historyID, "taskResults");
		
		$fileSizeUploaded = 0;
		$resumeURI = false;
		
		$client = new IWP_google_Client();
		$client->setClientId($args['clientID']);
		$client->setClientSecret($args['clientSecretKey']);
		$client->setRedirectUri($args['redirectURL']);
		$client->setScopes(array(
		  'https://www.googleapis.com/auth/drive',
		  'https://www.googleapis.com/auth/userinfo.email',
		  'https://www.googleapis.com/auth/userinfo.profile'));
		
		
		$accessToken = $args['token'];
		$refreshToken = $accessToken['refresh_token'];
		$backup_file = $args['backup_file'];
		
		try
		{
			$client->refreshToken($refreshToken);
		}
		catch(Exception $e)
		{	
			echo 'google Error ',  $e->getMessage(), "\n";
			return array("error" => $e->getMessage(), "error_code" => "google_error_refresh_token");
		}
		
		$service = new IWP_google_Service_Drive($client);
		
		//create folder if not present
		try 
		{
			$parameters = array();
			$parameters['q'] = "title = 'infinitewp' and trashed = false and mimeType= 'application/vnd.google-apps.folder'";
			$files = $service->files->listFiles($parameters);
			$list_result = array();
			$list_result = array_merge($list_result, $files->getItems());
			$list_result = (array)$list_result;
			
			if(empty($list_result))
			{
				$file = new IWP_google_Service_Drive_DriveFile();
				$file->setTitle('infinitewp');
				$file->setMimeType('application/vnd.google-apps.folder');
				
				$createdFolder = $service->files->insert($file, array(
					'mimeType' => 'application/vnd.google-apps.folder',
				));
				if($createdFolder)
				{
					$createdFolder = (array)$createdFolder;
					$iwp_folder_id = $createdFolder['id'];
				}
			}
			else
			{
				$list_result = (array)$list_result[0];
				$iwp_folder_id = $list_result['id'];
			}
		}catch (Exception $e){
			print "An error occurred: " . $e->getMessage();
			return array('error' => $e->getMessage());
		}
		
		//create sub folder by site name
		if($create_sub_folder)
		{
			$parameters = array();
			$parameters['q'] = "title = '$sub_folder_name' and trashed = false and mimeType = 'application/vnd.google-apps.folder'";
			$files = $service->files->listFiles($parameters);
			$list_result = array();
			$list_result = array_merge($list_result, $files->getItems());
			$list_result = (array)$list_result;
			
			if(empty($list_result))
			{
				$file = new IWP_google_Service_Drive_DriveFile();
				$file->setTitle($sub_folder_name);
				$file->setMimeType('application/vnd.google-apps.folder');
				
				//setting parent as infinitewpFolder
				$parent = new IWP_google_Service_Drive_ParentReference();
				$parent->setId($iwp_folder_id);
				$file->setParents(array($parent));
				
				$createdFolder = $service->files->insert($file, array(
					'mimeType' => 'application/vnd.google-apps.folder',
				));
				if($createdFolder)
				{
					$createdFolder = (array)$createdFolder;
					$sub_folder_id = $createdFolder['id'];
				}
			}
			else
			{
				$list_result = (array)$list_result[0];
				$sub_folder_id = $list_result['id'];
			}
		}
		
		
		//Insert a file
		$file = new IWP_google_Service_Drive_DriveFile();
		$file->setTitle(basename($backup_file));
		$file->setMimeType('binary/octet-stream');
		
		// Set the Parent Folder on Google Drive
		$parent = new IWP_google_Service_Drive_ParentReference();
		if(empty($sub_folder_id))
		{
			$parent->setId($iwp_folder_id);
		}
		else
		{
			$parent->setId($sub_folder_id);
		}
		$file->setParents(array($parent));
		
		$gDriveID = '';
		try
		{
			if(false)
			{
				//single upload
				$data = file_get_contents($backup_file);
				$createdFile = (array)$service->files->insert($file, array(
				  'data' => $data,
				  //'mimeType' => 'text/plain',
				));
				$gDriveID = $createdFile['id'];
			}
			
			//multipart upload
			
			if(true)
			{
				// Call the API with the media upload, defer so it doesn't immediately return.
				$client->setDefer(true);
				$request = $service->files->insert($file);
				
				// Create a media file upload to represent our upload process.
				$media = new IWP_google_Http_MediaFileUpload($client, $request, 'application/zip', null, true, $upload_file_block_size);
				$media->setFileSize(filesize($backup_file));
				

				$status = false;
				$handle = fopen($backup_file, "rb");
				fseek($handle, $fileSizeUploaded);
				
				/* $resArray = array (
				  'status' => 'completed',
				  'backupParentHID' => $historyID,
				); */
						
				while (!$status && !feof($handle))
				{
					iwp_mmb_auto_print('gdrive_chucked_upload');
					$chunk = fread($handle, $upload_file_block_size);
					$statusArray = $media->nextChunk($chunk, $resumeURI, $fileSizeUploaded);
					$status = $statusArray['status'];
					$resumeURI = $statusArray['resumeURI'];
					//$fileSizeUploaded = ftell($handle);
					$fileSizeUploaded = $statusArray['progress'];
				}
				
				$result = false;
				if($status != false) {
				  $result = $status;
				}
				
				fclose($handle);
				$client->setDefer(false);
				
				$completeBackupResult = (array)$status;
				
				//$gDriveID = $createdFile['id'];	
				$gDriveID = $completeBackupResult['id'];	
			}
		} 
		catch (Exception $e) 
		{
			echo "An error occurred: " . $e->getMessage();
			return array("error" => "gDrive Error".$e->getMessage());
		}
		
		/* if($del_host_file)
		{
			unset($task_result['task_results'][$historyID]['server']);
			@unlink($backup_file);
		} */
		$test_this_task = $this->get_this_tasks();
				
		$tasksThere = unserialize($test_this_task['taskResults']);
		
		return $gDriveID;			
	}
    
	
    function remove_amazons3_backup($args)
    {
    	if ($this->iwp_mmb_function_exists('curl_init')) {
        require_once($GLOBALS['iwp_mmb_plugin_dir'].'/lib/amazon_s3/sdk.class.php');
		extract($args);
		
		if(!is_array($backup_file))
		{
			$temp_backup_file = $backup_file;
			$backup_file = array();
			$backup_file[] = $temp_backup_file;
		}
		
        if ($as3_site_folder == true)
		{
			if(!empty($as3_directory))
			{
				$as3_directory .= '/' . $this->site_name;
			}
			else
			{
				$as3_directory =  $this->site_name;
			}
		}
        try{
			CFCredentials::set(array('development' => array('key' => trim($as3_access_key), 'secret' => trim(str_replace(' ', '+', $as3_secure_key)), 'default_cache_config' => '', 'certificate_authority' => true), '@default' => 'development'));
			$s3 = new AmazonS3();
			foreach($backup_file as $single_backup_file)
			{
				if(empty($as3_directory))
				{
					$single_as3_file = $single_backup_file;
				}
				else
				{
					$single_as3_file = $as3_directory . '/' . $single_backup_file;
				}
				$s3->delete_object($as3_bucket, $single_as3_file);
			}
       		
      	} catch (Exception $e){
      		
      	}
      }
    }
    
    function get_amazons3_backup($args)
    {
        require_once($GLOBALS['iwp_mmb_plugin_dir'].'/lib/amazon_s3/sdk.class.php');
        extract($args);
        $temp = '';
        try{
			CFCredentials::set(array('development' => array('key' => trim($as3_access_key), 'secret' => trim(str_replace(' ', '+', $as3_secure_key)), 'default_cache_config' => '', 'certificate_authority' => true), '@default' => 'development'));
			$s3 = new AmazonS3();
			if ($as3_site_folder == true)
			{
				if(!empty($as3_directory))
				{
					$as3_directory .= '/' . $this->site_name;
				}
				else
				{
					$as3_directory =  $this->site_name;
				}
			}
			if(empty($as3_directory))
			{
				$single_as3_file = $backup_file;
			}
			else
			{
				$single_as3_file = $as3_directory . '/' . $backup_file;
			}
			
			//$temp = ABSPATH . 'iwp_temp_backup.zip';
        	$temp = wp_tempnam('iwp_temp_backup.zip');
			$s3->get_object($as3_bucket, $as3_directory . '/' . $backup_file, array("fileDownload" => $temp));
       } catch (Exception $e){
        return $temp;
       }
        return $temp;
    }
	//IWP Remove ends here

	function get_google_drive_backup($args)
	{
		require_once($GLOBALS['iwp_mmb_plugin_dir'].'/lib/Google/Client.php');
		require_once($GLOBALS['iwp_mmb_plugin_dir'].'/lib/Google/Service/Drive.php');
		
		//refresh token 
		$client = new IWP_google_Client();
		$client->setClientId($args['clientID']);
		$client->setClientSecret($args['clientSecretKey']);
		$client->setRedirectUri($args['redirectURL']);
		$client->setScopes(array(
		  'https://www.googleapis.com/auth/drive',
		  'https://www.googleapis.com/auth/userinfo.email',
		  'https://www.googleapis.com/auth/userinfo.profile'));
		  
		//$client->setUseObjects(true);
		
		$accessToken = $args['token'];
		$refreshToken = $accessToken['refresh_token'];
		
		try
		{
			$client->refreshToken($refreshToken);
		}
		catch(Exception $e)
		{	
			echo 'google Error ',  $e->getMessage(), "\n";
			return array("error" => $e->getMessage(), "error_code" => "google_error_get_backup_refresh_token");
		}
		
		//downloading the file
		$service = new IWP_google_Service_Drive($client);
		
		$file = $service->files->get($fileId);
		
		$downloadUrl = $file->getDownloadUrl();
		
		$temp = wp_tempnam('iwp_temp_backup.zip');
		
		try
		{
			if ($downloadUrl) 
			{
				$request = new IWP_google_Http_Request($downloadUrl, 'GET', null, null);
				
				$signHttpRequest = $client->getAuth()->sign($request);
				$httpRequest = $client->getIo()->makeRequest($signHttpRequest);
				
				if ($httpRequest->getResponseHttpCode() == 200) {
					file_put_contents($temp, $httpRequest->getResponseBody());
					return $temp;
				} else {
				  // An error occurred.
				  return array("error" => "There is some error.", "error_code" => "google_error_bad_response_code_single_call");
				}
			}
			else
			{
				// The file doesn't have any content stored on Drive.
				return array("error" => "Google Drive file doesnt have any content.", "error_code" => "google_error_no_content_single_call");
			}
		}catch(Exception $e)
		{	
			echo 'google Error ',  $e->getMessage(), "\n";
			return array("error" => $e->getMessage(), "error_code" => "google_error_get_backup_single_call");
		}
		
		
	}
	
	function remove_google_drive_backup($args)
	{
		require_once($GLOBALS['iwp_mmb_plugin_dir'].'/lib/Google/Client.php');
		require_once($GLOBALS['iwp_mmb_plugin_dir'].'/lib/Google/Service/Drive.php');
		
		$client = new IWP_google_Client();
		$client->setClientId($args['clientID']);
		$client->setClientSecret($args['clientSecretKey']);
		$client->setRedirectUri($args['redirectURL']);
		$client->setScopes(array(
		  'https://www.googleapis.com/auth/drive',
		  'https://www.googleapis.com/auth/userinfo.email',
		  'https://www.googleapis.com/auth/userinfo.profile'));
		  
		//$client->setUseObjects(true);
		
		$accessToken = $args['token'];
		$refreshToken = $accessToken['refresh_token'];
		$backup_file = $args['backup_file'];
		if(!is_array($backup_file))
		{
			$backup_file = array();
			$backup_file[0] = $args['backup_file'];
		}
		
		try
		{
			$client->refreshToken($refreshToken);
		}
		catch(Exception $e)
		{	
			echo 'google Error ',  $e->getMessage(), "\n";
			return array("error" => $e->getMessage(), "error_code" => "google_error_remove_refresh_token");
		}
		
		$service = new IWP_google_Service_Drive($client);
		
		foreach($backup_file as $key => $value)
		{
			try
			{
				$service->files->delete($value);
			}
			catch (Exception $e)
			{
				echo "An error occurred: " . $e->getMessage();
				return array("error" => "gDrive Remove Error".$e->getMessage(), "error_code" => "google_error_delete");
			}
		}
	}
    
    function schedule_next($type, $schedule)
    {
        $schedule = explode("|", $schedule);
        if (empty($schedule))
            return false;
        switch ($type) {
            
            case 'daily':
                
                if (isset($schedule[1]) && $schedule[1]) {
                    $delay_time = $schedule[1] * 60;
                }
                
                $current_hour  = date("H");
                $schedule_hour = $schedule[0];
                if ($current_hour >= $schedule_hour){
                    $time = mktime($schedule_hour, 0, 0, date("m"), date("d") + 1, date("Y"));
					//$time ='0001#'.$current_hour.'|'.$schedule_hour;
					
				}
			
                else{
                    $time = mktime($schedule_hour, 0, 0, date("m"), date("d"), date("Y"));
					//$time ='0000#'.$current_hour.'|'.$schedule_hour;
				}
				$time = time() + 30;
				
			
                break;
            
            
            case 'weekly':
                if (isset($schedule[2]) && $schedule[2]) {
                    $delay_time = $schedule[2] * 60;
                }
                $current_weekday  = date('w');
                $schedule_weekday = $schedule[1];
                $current_hour     = date("H");
                $schedule_hour    = $schedule[0];
                
                if ($current_weekday > $schedule_weekday)
                    $weekday_offset = 7 - ($week_day - $task_schedule[1]);
                else
                    $weekday_offset = $schedule_weekday - $current_weekday;
                
                
                if (!$weekday_offset) { //today is scheduled weekday
                    if ($current_hour >= $schedule_hour)
                        $time = mktime($schedule_hour, 0, 0, date("m"), date("d") + 7, date("Y"));
                    else
                        $time = mktime($schedule_hour, 0, 0, date("m"), date("d"), date("Y"));
                } else {
                    $time = mktime($schedule_hour, 0, 0, date("m"), date("d") + $weekday_offset, date("Y"));
                }
                
                break;
            
            case 'monthly':
                if (isset($schedule[2]) && $schedule[2]) {
                    $delay_time = $schedule[2] * 60;
                }
                $current_monthday  = date('j');
                $schedule_monthday = $schedule[1];
                $current_hour      = date("H");
                $schedule_hour     = $schedule[0];
                
                if ($current_monthday > $schedule_monthday) {
                    $time = mktime($schedule_hour, 0, 0, date("m") + 1, $schedule_monthday, date("Y"));
                } else if ($current_monthday < $schedule_monthday) {
                    $time = mktime($schedule_hour, 0, 0, date("m"), $schedule_monthday, date("Y"));
                } else if ($current_monthday == $schedule_monthday) {
                    if ($current_hour >= $schedule_hour)
                        $time = mktime($schedule_hour, 0, 0, date("m") + 1, $schedule_monthday, date("Y"));
                    else
                        $time = mktime($schedule_hour, 0, 0, date("m"), $schedule_monthday, date("Y"));
                    break;
                }
                
                break;
            default:
                break;
        }
        
        if (isset($delay_time) && $delay_time) {
            $time += $delay_time;
        }
		
        return $time;
    }

    
    //Parse task arguments for info on IWP Admin Panel
	
	function get_all_tasks(){
		/*global $wpdb;
	
		$stats = array();
		$table_name = $wpdb->base_prefix . "iwp_backup_status";
		
		$rows = $wpdb->get_col("SELECT taskResults FROM ".$table_name);
		$task_res = array();
		foreach($rows as $key => $value){
			$task_results = unserialize($value);
			if(is_array($task_results['task_results'])){
				
				foreach($task_results['task_results'] as $key => $data){
					$task_res['task_results'][$key] = $data;
				}
			}
		}*/
		
		global $wpdb;
	
		$stats = array();
		$table_name = $wpdb->base_prefix . "iwp_backup_status";
		
		$rows = $wpdb->get_results("SELECT taskName,taskResults FROM ".$table_name,  ARRAY_A);
		
		$task_res = array();
		foreach($rows as $key => $value){
			$task_results = unserialize($value['taskResults']);
			
			if(!empty($task_results['task_results']))
			foreach($task_results['task_results'] as $key => $data){
				
				$task_res[$value['taskName']]['task_results'][$key] = $data;
			}
		}
			
		return $task_res;
	}
	
	function get_this_tasks($requestParams = ''){
		$this->wpdb_reconnect();
				
		global $wpdb;
		$table_name = $wpdb->base_prefix . "iwp_backup_status";
		
		$rows = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$table_name." WHERE historyID = %d", $GLOBALS['IWP_CLIENT_HISTORY_ID']), ARRAY_A);
		
		if($requestParams == 'requestParams'){
			$rows = unserialize($rows['requestParams']);
		}
								
		return $rows;
		
	}
	
	function get_this_tasks_params(){
		$this->wpdb_reconnect();
		
		global $wpdb;
		$table_name = $wpdb->base_prefix . "iwp_backup_status";
				
		$rows = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$table_name." WHERE historyID = %d", $GLOBALS['IWP_CLIENT_HISTORY_ID']), ARRAY_A);
						
		return unserialize($rows['requestParams']);
	}
	
	function get_requested_task($ID){
		global $wpdb;
		$table_name = $wpdb->base_prefix . "iwp_backup_status";
				
		$rows = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$table_name." WHERE historyID = %d", $ID), ARRAY_A);
		
		$return = unserialize($rows['taskResults']);
				
		return $return;
		
	}
	
    function get_backup_stats()
    {	
		global $wpdb;
		$stats = array();
		$table_name = $wpdb->base_prefix . "iwp_backup_status";
		$rows = $wpdb->get_results("select * from ".$table_name);
		$task_res = array();
		foreach($rows as $key => $value){
			$task_results = unserialize($value->taskResults);
			$task_res[$value->taskName][$value->historyID] = $task_results['task_results'][$value->historyID];
			$task_res[$value->taskName][$value->historyID]['backhack_status'] = $task_results['backhack_status'];
		}		
		$stats = $task_res;
		return $stats;
		/*foreach ($rows as $obj) {
		
			echo $obj->name;
		
		}*/
		
		/*
        $stats = array();
        $tasks = $this->tasks;
        if (is_array($tasks) && !empty($tasks)) {
            foreach ($tasks as $task_name => $info) {
                if (is_array($info['task_results']) && !empty($info['task_results'])) {
                    foreach ($info['task_results'] as $key => $result) {
                        if (isset($result['server']) && !isset($result['error'])) {
                            if (!file_exists($result['server']['file_path'])) {
                                $info['task_results'][$key]['error'] = 'Backup created but manually removed from server.';
                            }
                        }
                    }
                }
                if (is_array($info['task_results']))
                	$stats[$task_name] = $info['task_results'];
            }
        }
        return $stats;
    */
	}
        
        
    function remove_old_backups($task_name)
    {
		global $wpdb;

		$table_name = $wpdb->base_prefix . "iwp_backup_status";
		
		//Check for previous failed backups first
        $this->cleanup();
		
        //Remove by limit
        $backups = $this->get_all_tasks();
		
		$requestParams = $this->get_this_tasks("requestParams");
		
		$limit = $requestParams['args']['limit'];
						
		$select_prev_backup = "SELECT historyID, taskResults FROM ".$table_name." WHERE taskName = '".$task_name."' ORDER BY ID DESC LIMIT ".$limit.",100 ";
										
		$select_prev_backup_res = $wpdb->get_results($select_prev_backup,  ARRAY_A);
		
		
				
		if(!empty($select_prev_backup_res))
		foreach ( $select_prev_backup_res as $backup_data ) 
		{
			$task_result = unserialize($backup_data['taskResults']);
			$thisRequestParams = $this->getRequiredData($backup_data['historyID'], "requestParams");
			
			if (isset($task_result['task_results'][$backup_data['historyID']]['server'])) {
				$backup_file = $task_result['task_results'][$backup_data['historyID']]['server']['file_path'];
				if(!is_array($backup_file))
				{
					$temp_backup_file = $backup_file;
					$backup_file = array();
					$backup_file[0] = $temp_backup_file;
				}
				foreach($backup_file as $value)
				{
					@unlink($value);
				}
			}

			if (isset($task_result['task_results'][$backup_data['historyID']]['ftp'])) {
				$ftp_file            = $task_result['task_results'][$backup_data['historyID']]['ftp'];
				$args                = $thisRequestParams['account_info']['iwp_ftp'];
				$args['backup_file'] = $ftp_file;
				$this->remove_ftp_backup($args);
			}
			
			if (isset($task_result['task_results'][$backup_data['historyID']]['amazons3'])) {
				$amazons3_file       = $task_result['task_results'][$backup_data['historyID']]['amazons3'];
				$args                = $thisRequestParams['account_info']['iwp_amazon_s3'];
				$args['backup_file'] = $amazons3_file;
				$this->remove_amazons3_backup($args);
			}
			
			if (isset($task_result['task_results'][$backup_data['historyID']]['dropbox']) && isset($thisRequestParams['account_info']['iwp_dropbox'])) {
				//To do: dropbox remove
				$dropbox_file       = $task_result['task_results'][$backup_data['historyID']]['dropbox'];
				$args                = $thisRequestParams['account_info']['iwp_dropbox'];
				$args['backup_file'] = $dropbox_file;
			   $this->remove_dropbox_backup($args);
			}
			
			if (isset($task_result['task_results'][$backup_data['historyID']]['gDrive'])) {
				$gdrive_file       = $task_result['task_results'][$backup_data['historyID']]['gDrive'];
				$args                = $thisRequestParams['account_info']['iwp_gdrive'];
				$args['backup_file'] = $gdrive_file;
				$this->remove_google_drive_backup($args);
			}
			
			$delete_query = "DELETE FROM ".$table_name." WHERE historyID = '".$backup_data['historyID']."'";
												
			$deleteRes = $wpdb->query($delete_query);
		}
		
			return true;
     
    }
    
    /**
     * Delete specified backup
     * Args: $task_name, $result_id
     */
    
    function delete_backup($args)
    {
        if (empty($args))
            return false;
			
		global $wpdb;
		$table_name = $wpdb->base_prefix . "iwp_backup_status";
		
        extract($args);
         
		$tasks = $this->get_requested_task($result_id);
		$backup = $tasks['task_results'][$result_id];
		
		//$requestParams = unserialize($tasks['requestParams']);
		$requestParams = $this->getRequiredData($result_id, 'requestParams');
		
		$args = $requestParams['account_info'];
		
        if (isset($backup['server'])) {
			$backup_file = $backup['server']['file_path'];
			if(is_array($backup_file))
			{
				foreach($backup_file as $value)
				{
					@unlink($value);
				}
			}
			else
			{
				@unlink($backup_file);
			}
        }        
        
		
        //Remove from ftp
        if (isset($backup['ftp'])) {
            $ftp_file            = $backup['ftp'];
            $args                = $args['iwp_ftp'];
            $args['backup_file'] = $ftp_file;
            $this->remove_ftp_backup($args);
        }
        
        if (isset($backup['amazons3'])) {
            $amazons3_file       = $backup['amazons3'];
            $args                = $args['iwp_amazon_s3'];
            $args['backup_file'] = $amazons3_file;
            $this->remove_amazons3_backup($args);
        }
        
        if (isset($backup['dropbox'])) {
        	$dropbox_file       = $backup['dropbox'];
            $args                = $args['iwp_dropbox'];
            $args['backup_file'] = $dropbox_file;
            $this->remove_dropbox_backup($args);
        }
		
		if (isset($backup['gDrive'])) {
        	$g_drive_file       = $backup['gDrive'];
            $args                = $args['iwp_gdrive'];
            $args['backup_file'] = $g_drive_file;
            $this->remove_google_drive_backup($args);
        }
		
		$delete_query = "DELETE FROM ".$table_name." WHERE historyID = '".$result_id."'";
												
		$deleteRes = $wpdb->query($delete_query);
		
        return true;
        
    }
    
	function getRequiredData($historyID, $field){
		global $wpdb;
		
		$backupData = $wpdb->get_row("SELECT ".$field." FROM ".$wpdb->base_prefix."iwp_backup_status WHERE historyID = '".$historyID."'");
		if(($field == 'responseParams')||($field == 'requestParams')||($field == 'taskResults'))
		$fieldParams = unserialize($backupData->$field);
		else
		$fieldParams = $backupData->$field;
		return $fieldParams;	
	}
	
    function cleanup()
    {
		$tasks = $this->get_all_tasks(); //all backups task results array.
		
		
        $backup_folder     = WP_CONTENT_DIR . '/' . md5('iwp_mmb-client') . '/iwp_backups/';
        $backup_folder_new = IWP_BACKUP_DIR . '/';
        $files             = glob($backup_folder . "*");
        $new               = glob($backup_folder_new . "*");
        
        //Failed db files first
        $db_folder = IWP_DB_DIR . '/';
        $db_files  = glob($db_folder . "*");
        if (is_array($db_files) && !empty($db_files)) {
            foreach ($db_files as $file) {
                @unlink($file);
            }
			@unlink(IWP_BACKUP_DIR.'/iwp_db/index.php');
            @rmdir(IWP_DB_DIR);
        }
        
        
        //clean_old folder?
        if ((basename($files[0]) == 'index.php' && count($files) == 1) || (!empty($files))) {  //USE  (!empty($files)
            foreach ($files as $file) {
                @unlink($file);
            }
            @rmdir(WP_CONTENT_DIR . '/' . md5('iwp_mmb-client') . '/iwp_backups');
            @rmdir(WP_CONTENT_DIR . '/' . md5('iwp_mmb-client'));
        }
        
        if (!empty($new)) {
	        foreach ($new as $b) {
	            $files[] = $b;
	        }
        }
        $deleted = array();
        
        if (is_array($files) && count($files)) {
            $results = array();
            if (!empty($tasks)) {
                foreach ((array) $tasks as $task) {
                    //if (isset($task) && count($task)) {
                    //    foreach ($task as $backup) {
					if (isset($task['task_results']) && count($task['task_results'])) {
                        foreach ($task['task_results'] as $backup) {
                            if (isset($backup['server'])) {
								$this_backup_file = $backup['server']['file_path'];
								if(is_array($this_backup_file))
								{
									foreach($this_backup_file as $single_backup_file)
									{
										$results[] = $single_backup_file;
									}
								}
								else
								{
									$results[] = $this_backup_file;
								}
                            }
                        }
                    }
                }
            }
            
            $num_deleted = 0;
            foreach ($files as $file) {
                if (!in_array($file, $results) && basename($file) != 'index.php') {
                    @unlink($file);
                    $deleted[] = basename($file);
                    $num_deleted++;
                }
            }
        }

        
        return $deleted;
    }
    

    
    function update_status($task_name, $status, $completed = false, $task_result='')
    {
        /* Statuses:
        0 - Backup started 1 - DB dump 2 - DB ZIP 3 - Files ZIP 4 - Amazon S3 5 - Dropbox 6 - FTP 7 - Email 100 - Finished
        */
      
		//$tasks = $this->tasks;
			
		$test_this_task = $this->get_this_tasks();
				
		$tasks = unserialize($test_this_task['taskResults']);	
				
		$tasks['backhack_status']['adminHistoryID'] = $GLOBALS['IWP_CLIENT_HISTORY_ID']; 
		
		if (!$completed) {
						
			$tasks['backhack_status'][$status]['start'] = microtime(true);
						
			$test = $this->statusLog($GLOBALS['IWP_CLIENT_HISTORY_ID'], array('stage' => $status, 'status' => 'processing', 'statusMsg' => 'processing', 'task_result' => $tasks)); 
			
		}
		else {
						
			$tasks['backhack_status'][$status]['end'] = microtime(true);
						
			if(!empty($task_result)){
				$tasks['task_results'][$GLOBALS['IWP_CLIENT_HISTORY_ID']] = $task_result;
			}
						
			$test2 = $this->statusLog($GLOBALS['IWP_CLIENT_HISTORY_ID'], array('stage' => $status, 'status' => 'completed', 'statusMsg' => 'completed', 'task_result' => $tasks));

		}
		
		$this->update_tasks($tasks);
    }
    
	function statusLog($historyID = '', $statusArray = array(), $params=array())
	{
  		global $wpdb,$insertID;
  		
  		if(empty($historyID))
		{
			
  			$insert  = $wpdb->insert($wpdb->base_prefix.'iwp_backup_status',array( 'stage' => $statusArray['stage'], 'status' => $statusArray['status'],  'action' => $params['args']['action'], 'type' => $params['args']['type'],'category' => $params['args']['what'],'historyID' => $GLOBALS['IWP_CLIENT_HISTORY_ID'],'finalStatus' => 'pending','startTime' => microtime(true),'endTime' => '','statusMsg' => $statusArray['statusMsg'],'requestParams' => serialize($params),'taskName' => $params['task_name']), array( '%s', '%s','%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s', '%s' ) );
			if($insert)
			{
				$insertID = $wpdb->insert_id;
			}
  		}
		else if(isset($statusArray['responseParams']))
		{
			
			$update = $wpdb->update($wpdb->base_prefix.'iwp_backup_status',array( 'responseParams' => serialize($statusArray['responseParams']),'stage' => $statusArray['stage'], 'status' => $statusArray['status'],'statusMsg' => $statusArray['statusMsg'], 'taskResults' =>  serialize($statusArray['task_result'])),array( 'historyID' => $historyID),array('%s','%s', '%s', '%s', '%s'),array('%d'));
			
			
		}
  		else
		{
			$update = $wpdb->update($wpdb->base_prefix.'iwp_backup_status',array( 'stage' => $statusArray['stage'], 'status' => $statusArray['status'],'statusMsg' => $statusArray['statusMsg'], 'taskResults' =>  serialize($statusArray['task_result']) ),array( 'historyID' => $historyID),array('%s', '%s', '%s', '%s'),array('%d'));
						
		}
		
		if( (isset($update)&&(!$update)) || (isset($insert)&&(!$insert)) )
		{
			//return array('error'=> $statusArray['statusMsg']);
			iwp_mmb_response(array('error' => 'MySQL Error: '.$wpdb -> last_error, 'error_code' => 'status_log_my_sql_error'), false);
		}
		
		if((isset($statusArray['sendResponse']) && $statusArray['sendResponse'] == true) || $statusArray['status'] == 'completed')
		{
			$returnParams = array();
			$returnParams['parentHID'] = $historyID;
			$returnParams['backupID'] = $insertID;
			$returnParams['stage'] = $statusArray['stage'] ;
			$returnParams['status'] = $statusArray['status'];
			$returnParams['nextFunc'] = $statusArray['nextFunc'];
			return array('success' => $returnParams);
		}
		else
		{
			if($statusArray['status'] == 'error')
			{
				$returnParams = array();
				$returnParams['parentHID'] = $historyID;
				$returnParams['backupID'] = $insertID;
				$returnParams['stage'] = $statusArray['stage'] ;
				$returnParams['status'] = $statusArray['status'];
				$returnParams['statusMsg'] = $statusArray['statusMsg'];
				
				return array('error'=> $returnParams);
			}
		} 		
  	}

    function update_tasks($tasks)
    {
        //$this->tasks = $tasks;
        update_option('iwp_client_backup_tasks', $tasks);
    }
    
    function wpdb_reconnect(){
    	global $wpdb;
		$old_wpdb = $wpdb;
    	//Reconnect to avoid timeout problem after ZIP files
      	if(class_exists('wpdb') && function_exists('wp_set_wpdb_vars')){
      		@mysql_close($wpdb->dbh);
        	$wpdb = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
        	wp_set_wpdb_vars(); 
			$wpdb->options = $old_wpdb->options;//fix for multi site full backup
      	}
    }
    
  function replace_htaccess($url, $remote_abspath)
	{
		global $wp_filesystem;
		//$file = @file_get_contents(ABSPATH.'.htaccess');
		$file = $wp_filesystem->get_contents($remote_abspath.'.htaccess');
		if ($file && strlen($file)) {
			$args    = parse_url($url);        
			$string  = rtrim($args['path'], "/");
			$regex   = "/BEGIN WordPress(.*?)RewriteBase(.*?)\n(.*?)RewriteRule \.(.*?)index\.php(.*?)END WordPress/sm";
			$replace = "BEGIN WordPress$1RewriteBase " . $string . "/ \n$3RewriteRule . " . $string . "/index.php$5END WordPress";
			$file    = preg_replace($regex, $replace, $file);
			//@file_put_contents(ABSPATH.'.htaccess', $file);
			$wp_filesystem->put_contents($remote_abspath.'.htaccess', $file);
		}
	}
        
	public function readd_tasks( $params = array() ){
		global $iwp_mmb_core;
		
		if( empty($params) || !isset($params['backups']) )
			return $params;
		
		$before = array();
		$tasks = $params['backups'];
		if( !empty($tasks) ){
			$iwp_mmb_backup = new IWP_MMB_Backup();
			
			if( function_exists( 'wp_next_scheduled' ) ){
				if ( !wp_next_scheduled('iwp_client_backup_tasks') ) {
					wp_schedule_event( time(), 'tenminutes', 'iwp_client_backup_tasks' );
				}
			}
			
			foreach( $tasks as $task ){
				$before[$task['task_name']] = array();
				
				if(isset($task['secure'])){
					if($decrypted = $iwp_mmb_core->_secure_data($task['secure'])){
						$decrypted = maybe_unserialize($decrypted);
						if(is_array($decrypted)){
							foreach($decrypted as $key => $val){
								if(!is_numeric($key))
									$task[$key] = $val;							
							}
							unset($task['secure']);
						} else 
							$task['secure'] = $decrypted;
					}
					
				}
				if (isset($task['account_info']) && is_array($task['account_info'])) { //only if sends from panel first time(secure data)
					$task['args']['account_info'] = $task['account_info'];
				}
				
				$before[$task['task_name']]['task_args'] = $task['args'];
				$before[$task['task_name']]['task_args']['next'] = $iwp_mmb_backup->schedule_next($task['args']['type'], $task['args']['schedule']);
			}
		}
		update_option('iwp_client_backup_tasks', $before);
		
		unset($params['backups']);
		return $params;
	}
	
	function is_server_writable(){
		if((!defined('FTP_HOST') || !defined('FTP_USER') || !defined('FTP_PASS')) && (get_filesystem_method(array(), ABSPATH) != 'direct'))
			return false;
		else
			return true;
	}
}

/*if( function_exists('add_filter') ){
	add_filter( 'iwp_website_add', 'IWP_MMB_Backup::readd_tasks' );
}*/

if(!function_exists('get_all_files_from_dir')) {
	/**
	 * Get all files in directory
	 * 
	 * @param 	string 	$path 		Relative or absolute path to folder
	 * @param 	array 	$exclude 	List of excluded files or folders, relative to $path
	 * @return 	array 				List of all files in folder $path, exclude all files in $exclude array
	 */
	function get_all_files_from_dir($path, $exclude = array()) {
			 
		if ($path[strlen($path) - 1] === "/") $path = substr($path, 0, -1);
		global $directory_tree, $ignore_array;
		$directory_tree = array();
		if(!empty($exclude))
		{
			foreach ($exclude as $file) {
				if (!in_array($file, array('.', '..'))) {
					if ($file[0] === "/") $path = substr($file, 1);
					$ignore_array[] = "$path/$file";
				}
			}
		}
		get_all_files_from_dir_recursive($path);
				
		return $directory_tree;
	}
}

if (!function_exists('get_all_files_from_dir_recursive')) {
	/**
	 * Get all files in directory,
	 * wrapped function which writes in global variable
	 * and exclued files or folders are read from global variable
	 *
	 * @param 	string 	$path 	Relative or absolute path to folder
	 * @return 	void
	 */
	function get_all_files_from_dir_recursive($path,$ignore_array=array()) {
		if ($path[strlen($path) - 1] === "/") $path = substr($path, 0, -1);
		global $directory_tree, $ignore_array;
		if(empty($ignore_array))
		{
			$ignore_array = array();
		}
		$directory_tree_temp = array();
		$dh = @opendir($path);
		
		while (false !== ($file = @readdir($dh))) {
				if (!in_array($file, array('.', '..'))) {
					if (!in_array("$path/$file", $ignore_array)) {
						if (!is_dir("$path/$file")) {
							$directory_tree[] = "$path/$file";
						} else {
							get_all_files_from_dir_recursive("$path/$file");
					}
				}
			}
		}
		@closedir($dh);
	}
}

?>