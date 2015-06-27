<?php

//require_once 'Mandrill/Templates.php';
//require_once 'Mandrill/Exports.php';
require_once 'Mandrill/Users.php';
//require_once 'Mandrill/Rejects.php';
//require_once 'Mandrill/Inbound.php';
//require_once 'Mandrill/Tags.php';
require_once 'Mandrill/Messages.php';
//require_once 'Mandrill/Whitelists.php';
//require_once 'Mandrill/Ips.php';
//require_once 'Mandrill/Internal.php';
//require_once 'Mandrill/Subaccounts.php';
//require_once 'Mandrill/Urls.php';
//require_once 'Mandrill/Webhooks.php';
//require_once 'Mandrill/Senders.php';
//require_once 'Mandrill/Metadata.php';
require_once 'Mandrill/Exceptions.php';

class Mandrill {
    
    public $apikey;
    public $ch;
    public $root = 'https://mandrillapp.com/api/1.0';
    public $debug = false;

    public static $error_map = array(
        "ValidationError" => "Mandrill_ValidationError",
        "Invalid_Key" => "Mandrill_Invalid_Key",
        "PaymentRequired" => "Mandrill_PaymentRequired",
        "Unknown_Subaccount" => "Mandrill_Unknown_Subaccount",
        "Unknown_Template" => "Mandrill_Unknown_Template",
        "ServiceUnavailable" => "Mandrill_ServiceUnavailable",
        "Unknown_Message" => "Mandrill_Unknown_Message",
        "Invalid_Tag_Name" => "Mandrill_Invalid_Tag_Name",
        "Invalid_Reject" => "Mandrill_Invalid_Reject",
        "Unknown_Sender" => "Mandrill_Unknown_Sender",
        "Unknown_Url" => "Mandrill_Unknown_Url",
        "Unknown_TrackingDomain" => "Mandrill_Unknown_TrackingDomain",
        "Invalid_Template" => "Mandrill_Invalid_Template",
        "Unknown_Webhook" => "Mandrill_Unknown_Webhook",
        "Unknown_InboundDomain" => "Mandrill_Unknown_InboundDomain",
        "Unknown_InboundRoute" => "Mandrill_Unknown_InboundRoute",
        "Unknown_Export" => "Mandrill_Unknown_Export",
        "IP_ProvisionLimit" => "Mandrill_IP_ProvisionLimit",
        "Unknown_Pool" => "Mandrill_Unknown_Pool",
        "NoSendingHistory" => "Mandrill_NoSendingHistory",
        "PoorReputation" => "Mandrill_PoorReputation",
        "Unknown_IP" => "Mandrill_Unknown_IP",
        "Invalid_EmptyDefaultPool" => "Mandrill_Invalid_EmptyDefaultPool",
        "Invalid_DeleteDefaultPool" => "Mandrill_Invalid_DeleteDefaultPool",
        "Invalid_DeleteNonEmptyPool" => "Mandrill_Invalid_DeleteNonEmptyPool",
        "Invalid_CustomDNS" => "Mandrill_Invalid_CustomDNS",
        "Invalid_CustomDNSPending" => "Mandrill_Invalid_CustomDNSPending",
        "Metadata_FieldLimit" => "Mandrill_Metadata_FieldLimit",
        "Unknown_MetadataField" => "Mandrill_Unknown_MetadataField"
    );

    public function __construct($apikey=null) {
        if(!$apikey) $apikey = getenv('MANDRILL_APIKEY');
        if(!$apikey) $apikey = $this->readConfigs();
        if(!$apikey) throw new Mandrill_Error('You must provide a Mandrill API key');
        $this->apikey = $apikey;

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mandrill-PHP/1.0.52');
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 600);

        $this->root = rtrim($this->root, '/') . '/';

        //$this->templates = new Mandrill_Templates($this);
        //$this->exports = new Mandrill_Exports($this);
        $this->users = new Mandrill_Users($this);
        //$this->rejects = new Mandrill_Rejects($this);
        //$this->inbound = new Mandrill_Inbound($this);
        //$this->tags = new Mandrill_Tags($this);
        $this->messages = new Mandrill_Messages($this);
        //$this->whitelists = new Mandrill_Whitelists($this);
        //$this->ips = new Mandrill_Ips($this);
        //$this->internal = new Mandrill_Internal($this);
        //$this->subaccounts = new Mandrill_Subaccounts($this);
        //$this->urls = new Mandrill_Urls($this);
        //$this->webhooks = new Mandrill_Webhooks($this);
        //$this->senders = new Mandrill_Senders($this);
        //$this->metadata = new Mandrill_Metadata($this);
    }

    public function __destruct() {
        curl_close($this->ch);
    }

    public function call($url, $params) {
        $params['key'] = $this->apikey;
        $params = json_encode($params);
		$response_body = false;
		
		if ( function_exists('wp_remote_request') ) { 
			$options = array(
				'method' 	=> 'POST', 
				'timeout' 	=> 45, 
				'body' 		=> $params,
				);
	
			$raw_response = wp_remote_request( $this->root . $url . '.json', $options);
			if ( is_wp_error( $raw_response ) || ( is_array( $raw_response ) && 200 != $raw_response['response']['code'] ) ){
				$error_message = '';
				if ( is_array( $raw_response ) ) {
					$response_body = json_decode($raw_response['body'], true);
					$error_message = $response_body['message'];
				}
				throw new Mandrill_HttpError("WP Request: API call to $url failed: " . $error_message );
			}else{
				$response_body = $raw_response['body'];
			}
			$info = array('http_code' => $raw_response['response']['code']);
			
		} elseif( function_exists('curl_init') && function_exists('curl_exec') ) {
			if( !ini_get('safe_mode') ){
                set_time_limit(2 * 60);
            }
			
        	$ch = $this->ch;

			curl_setopt($ch, CURLOPT_URL, $this->root . $url . '.json');
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2 * 60 * 1000);
	
			$start = microtime(true);
			$this->log('Call to ' . $this->root . $url . '.json: ' . $params);
			if($this->debug) {
				$curl_buffer = fopen('php://memory', 'w+');
				curl_setopt($ch, CURLOPT_STDERR, $curl_buffer);
			}
	
			$response_body = curl_exec($ch);
			$info = curl_getinfo($ch);
			$time = microtime(true) - $start;
			if($this->debug) {
				rewind($curl_buffer);
				$this->log(stream_get_contents($curl_buffer));
				fclose($curl_buffer);
			}
			$this->log('Completed in ' . number_format($time * 1000, 2) . 'ms');
			$this->log('Got response: ' . $response_body);
	
			if(curl_error($ch)) {
				throw new Mandrill_HttpError("API call to $url failed: " . curl_error($ch));
			}
			
		} elseif( function_exists( 'fsockopen' ) ) {
			$parsed_url = parse_url( $this->root . $url . '.json' );

	        $host = $parsed_url['host'];
	        if ( isset($parsed_url['path']) ) {
		        $path = $parsed_url['path'];
	        } else {
		        $path = '/';
	        }

            $params = '';
            if (isset($parsed_url['query'])) {
                $params = $parsed_url['query'] . '&' . $fields;
            } elseif ( trim($fields) != '' ) {
                $params = $fields;
            }

	        if (isset($parsed_url['port'])) {
		        $port = $parsed_url['port'];
	        } else {
		        $port = ($parsed_url['scheme'] == 'https') ? 443 : 80;
	        }

	        $response_body = false;

	        $errno    = '';
	        $errstr   = '';
            ob_start();
            $fp = fsockopen( 'ssl://'.$host, $port, $errno, $errstr, 5 );

            if( $fp !== false ) {
                stream_set_timeout($fp, 30);
                
                $payload = "$method $path HTTP/1.0\r\n" .
		            "Host: $host\r\n" . 
		            "Connection: close\r\n"  .
                	"User-Agent: $useragent\r\n" .
                    "Content-type: application/x-www-form-urlencoded\r\n" .
                    "Content-length: " . strlen($params) . "\r\n" .
                    "Connection: close\r\n\r\n" .
                    $params;
                fwrite($fp, $payload);
                stream_set_timeout($fp, 30);
                
                $info = stream_get_meta_data($fp);
                while ((!feof($fp)) && (!$info["timed_out"])) {
                    $response_body .= fread($fp, 4096);
                    $info = stream_get_meta_data($fp);
                }
                
                fclose( $fp );
                ob_end_clean();
                
                list($headers, $response_body) = explode("\r\n\r\n", $response_body, 2);

                if(ini_get("magic_quotes_runtime")) $response_body = stripslashes($response_body);
    	        $info = array('http_code' => 200);
            } else {
                ob_end_clean();
    	        $info = array('http_code' => 500);
    	        throw new Mandrill_Error($errstr,$errno);
            }
            $error = '';
		} else {
            throw new Mandrill_Error("No valid HTTP transport found", -99);
        }
		
        $result = json_decode($response_body, true);
        if($result === null) throw new Mandrill_Error('We were unable to decode the JSON response from the Mandrill API: ' . $response_body);
        
        if(floor($info['http_code'] / 100) >= 4) {
            throw $this->castError($result);
        }

        return $result;
    }

    public function readConfigs() {
        $paths = array('~/.mandrill.key', '/etc/mandrill.key');
        foreach($paths as $path) {
            if(file_exists($path)) {
                $apikey = trim(file_get_contents($path));
                if($apikey) return $apikey;
            }
        }
        return false;
    }

    public function castError($result) {
        if($result['status'] !== 'error' || !$result['name']) throw new Mandrill_Error('We received an unexpected error: ' . json_encode($result));

        $class = (isset(self::$error_map[$result['name']])) ? self::$error_map[$result['name']] : 'Mandrill_Error';
        return new $class($result['message'], $result['code']);
    }

    public function log($msg) {
        if($this->debug) error_log($msg);
    }
	
	public function processAttachments($attachments = array()) {
        if ( !is_array($attachments) && $attachments )
	        $attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
	        
        foreach ( $attachments as $index => $attachment ) {
            try {
                $attachments[$index] = $this->getAttachmentStruct($attachment);
            } catch ( Exception $e ) {
                error_log( "\nwpMandrill::processAttachments: $attachment => ".$e->getMessage()."\n" );
                return new WP_Error( $e->getMessage() );
            }
        }
        
        return $attachments;
    }
	
	public function getAttachmentStruct($path) {
        
        $struct = array();
        
        try {
            
            if ( !@is_file($path) ) throw new Exception($path.' is not a valid file.');

            $filename = basename($path);
            
            if ( !function_exists('get_magic_quotes') ) {
                function get_magic_quotes() { return false; }
            }
            if ( !function_exists('set_magic_quotes') ) {
                function set_magic_quotes($value) { return true;}
            }
            
            if (strnatcmp(phpversion(),'6') >= 0) {
                $magic_quotes = get_magic_quotes_runtime();
                set_magic_quotes_runtime(0);
            }
            
            $file_buffer  = file_get_contents($path);
            $file_buffer  = chunk_split(base64_encode($file_buffer), 76, "\n");
            
            if (strnatcmp(phpversion(),'6') >= 0) set_magic_quotes_runtime($magic_quotes);
            
            $mime_type = '';
			if ( function_exists('finfo_open') && function_exists('finfo_file') ) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $path);
            } elseif ( function_exists('mime_content_type') ) {
                $mime_type = mime_content_type($path);
            }

            if ( !empty($mime_type) ) $struct['type']     = $mime_type;
            $struct['name']     = $filename;
            $struct['content']  = $file_buffer;

        } catch (Exception $e) {
            throw new WP_Error('Error creating the attachment structure: '.$e->getMessage());
        }
        
        return $struct;
    }
	
	public function findTags($tags) {

        // Getting general tags
        $gtags   = array( 'wp_email_template' );
		
		// Finding tags based on WP Backtrace 
		$trace  = debug_backtrace();
		$level  = 4;        
		$function = $trace[$level]['function'];

        $wtags = array();
		if( 'include' == $function || 'require' == $function ) {

			$file = basename($trace[$level]['args'][0]);
			$wtags[] = "{$file}";
		}
		else {
			if( isset( $trace[$level]['class'] ) )
				$function = $trace[$level]['class'].$trace[$level]['type'].$function;
			$wtags[] = "{$function}";
		}
		
		return array('user' => $tags, 'general' => $gtags, 'automatic' => $wtags);
	}
}


