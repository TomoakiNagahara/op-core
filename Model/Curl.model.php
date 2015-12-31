<?php
/**
 * op-core/model/Curl.model.php
 * 
 * @creation  2015-12-26
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/**
 * Model_Curl
 * 
 * @creation  2015-12-26
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Model_Curl extends Model_Model
{
	private $_debug;
	
	function Json($url, $post=null)
	{
		return json_decode(
				$this->Get($url, $post=null), 
				true);
	}
	
	function Get($url, $post=null)
	{
		$this->_debug['url'][] = $url;
		
		if( function_exists('curl_init') ){
			$content = $this->Curl($url, $post);
		}else{
			$content = $this->Fetch($url, $post);
		}
		return $content;
	}
	
	function Curl($url, $post=null)
	{
		$this->AdminNotice("Does not implements, yet.");
	}
	
	function Fetch($url, $post=null)
	{
		//	Used at timeout.
		$st = microtime(true);
		
		//	Set timeout time.
		$conf = array();
		$conf['http']['timeout'] = Toolbox::isLocalhost() ? 1: 3;
		$context = stream_context_create($conf);
		
		//	E_WARNING
		ini_set('display_errors',false);
		
		if(!$body = file_get_contents($url, false, $context)){
			if( isset($http_response_header) and count($http_response_header) > 0){
				$stat = explode(' ', $http_response_header[0]);
				switch($stat[1]){
					case 404:
						// 404 Not found
						$this->AdminNotice("404 Not found.");
						break;
					case 500:
						// 500 Internal Server Error
						$this->AdminNotice("500 Internal Server Error.");
						break;
					default:
						$this->AdminNotice("Does not define. ($stat)");
						break;
				}
			}else{
				//	timeout
				$en = microtime(true);
				$time = $en - $st;
				$this->Mark("timeout. ".$time, __CLASS__);
			}
		}
		
		//	E_WARNING
		ini_set('display_errors',true);
		
		return $body;
	}
	
	/**
	 * For developer
	 */
	function Debug()
	{
		$this->P("Debug: Model-Curl");
		$this->d($this->_debug);
	}
}

/**
 * Config_Curl
 * 
 * @creation  2015-12-26
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Config_Curl extends Config_Model
{
	
}
