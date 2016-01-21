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
	/**
	 * If true is caching.
	 * 
	 * @var boolean
	 */
	private $_is_cache;
	
	/**
	 * Debug information for developer.
	 * 
	 * @var array
	 */
	private $_debug;
	
	/**
	 * Get json array by url.
	 * 
	 * @param  string $url
	 * @param  array  $post
	 * @return array
	 */
	function Json($url, $post=null, $expire=null)
	{
		return json_decode(
				//	Json string.
				$this->Get($url, $post=null),
				//	To Assoc flag.
				true);
	}
	
	/**
	 * Get content by url.
	 * 
	 * @param  string $url
	 * @param  string $post
	 * @return string
	 */
	function Get($url, $post=null, $expire=null)
	{
		//	Cache
		if( $this->_is_cache or $expire){
			$key = md5($url);
			$key = substr($key, 0, 8);
			if( $content = $this->Cache()->Get($key) ){
				$this->_debug['url']['cache'] = $url;
				return $content;
			}
		}

		//	Debub
		$this->_debug['url']['fetch'] = $url;

		//	Fetch
		if( function_exists('curl_init') ){
			$content = $this->Curl($url, $post);
		}else{
			$content = $this->Fetch($url, $post);
		}

		//	Cache
		if( isset($key) ){
			$this->Cache()->Set($key, $content, $expire);
		}

		return $content;
	}

	/**
	 * http headers
	 * 
	 * @var array
	 */
	private $_headers;

	/**
	 * Add http header. (single line)
	 * 
	 * @param string $key
	 * @param string $value
	 */
	function AddHeader($key, $value)
	{
		$this->_headers[$key] = $value;
	}

	/**
	 * Set http headers by array.
	 * 
	 * @param array $headers
	 */
	function SetHeaders($headers)
	{
		$this->_headers = $headers;
	}

	/**
	 * Get http headers.
	 * 
	 * @return array
	 */
	function GetHeaders()
	{
		return $this->_headers;
	}
	
	/**
	 * Certificate.
	 * 
	 * @var string
	 */
	private $_certificate;

	/**
	 * Set certificate.
	 * 
	 * @param string $certificate
	 */
	function SetCertificate($certificate)
	{
		$this->_certificate = $certificate;
	}

	/**
	 * Get certificate.
	 * 
	 * @return string
	 */
	function GetCertificate()
	{
		return $this->_certificate;
	}

	/**
	 * Timeout seconds.
	 * 
	 * @var integer
	 */
	private $_timeout;

	/**
	 * Set timeout at seconds.
	 * 
	 * @param integer $seconds
	 */
	function SetTimeout($seconds)
	{
		$this->_timeout = $seconds;
	}

	/**
	 * Get timeout seconds.
	 * 
	 * @return integer
	 */
	function GetTimeout()
	{
		static $io;

		if( $timeout = $this->_timeout ){
			//	OK
		}else{
			if( $io ){
				//	Not first time.
				$timeout = 1;
			}else{
				//	First time is 3 sec. But, localhost is 1 sec.
				$timeout = Toolbox::isLocalhost() ? 1: 3;
				$io = true;
			}
		}

		return $timeout;
	}

	/**
	 * Save and to load from cookie file path.
	 * 
	 * @var string
	 */
	private $_cookie_file_path;
	
	/**
	 * Set cookie file path;
	 * 
	 * @param string $path
	 */
	function SetCookieFilePath($path)
	{
		$this->_cookie_file_path = $path;
	}

	/**
	 * Get cookie file path.
	 */
	function GetCookieFilePath()
	{
		return $this->_cookie_file_path;
	}

	/**
	 * Cookie string 
	 * 
	 * @var string
	 */
	private $_cookie;
	
	/**
	 * Set cookie data string.
	 * 
	 * <pre>
	 * Example:
	 * $this->SetCookieString("fruit=apple; colour=red");
	 * </pre>
	 * 
	 * @param string $cookie
	 */
	function SetCookieString($cookie)
	{
		$this->_cookie;
	}

	/**
	 * Get cookie data string.
	 * 
	 * @return string
	 */
	function GetCookieString()
	{
		return $this->_cookie;
	}

	/**
	 * User agent.
	 * 
	 * @var string
	 */
	private $_user_agent;

	/**
	 * Set user agent.
	 * 
	 * @param string $user_agent
	 */
	function SetUserAgent($user_agent)
	{
		$this->_user_agent = $user_agent;
	}

	/**
	 * Get user agent.
	 * 
	 * @return string
	 */
	function GetUserAgent()
	{
		return $this->_user_agent; 
	}

	/**
	 * Resource of cURL.
	 * 
	 * @var resource
	 */
	private $_curl;

	/**
	 * Get cURL object.
	 * 
	 * @return resource
	 */
	function GetCurl()
	{
		if(!$this->_curl ){
			$this->_curl = curl_init();
		}
		return $this->_curl;
	}

	/**
	 * cURL connection information.
	 * 
	 * @var array
	 */
	private $_info;

	/**
	 * Get cURL connection information.
	 * 
	 * @return arary
	 */
	function GetCurlInfo()
	{
		return $this->_info;
	}
	
	/**
	 * Do curl by url.
	 *
	 * @param  string $url
	 * @param  array $post
	 * @return string
	 */
	function Curl($url, $post=null)
	{
		if( isset($this->_debug['curl']) ){
			$this->_debug['curl']++;
		}else{
			$this->_debug['curl'] = 1;
		}

	//	$this->AdminNotice("Does not implements, yet.");
	//	return self::Fetch($url, $post);

		//	Get cURL resource.
		$curl = $this->GetCurl();

		//	Fetch URL
		curl_setopt( $curl, CURLOPT_URL, $url );

		// Get http header with body
		curl_setopt( $curl, CURLOPT_HEADER, false );

		//	Get string of body. (false is to output)
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

		// Set timeout seconds.
		curl_setopt( $curl, CURLOPT_TIMEOUT, $this->GetTimeout());

		// Track the location header. (To redirect)
		curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );

		// Max redirect.
		curl_setopt( $curl, CURLOPT_MAXREDIRS, 10 );

		//	User agent.
		curl_setopt( $curl, CURLOPT_USERAGENT, $this->GetUserAgent() );

		//	Referer
	//	curl_setopt( $curl, CURLOPT_REFERER, "" );

		/* ==== HEADER ==== */

		if( $header = $this->GetHeaders() ){
			curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );
		}

		/* ==== POST ==== */

		if( $post ){
			curl_setopt( $curl, CURLOPT_POST, true );
			curl_setopt( $curl, CURLOPT_POSTFIELDS, http_build_query($post) );
		}

		/* ==== COOKIE ==== */

		// Set header's cookie data. (Direct write to http header.)
		if( $cookie = $this->GetCookieString() ){
			curl_setopt( $curl, CURLOPT_COOKIE, $cookie );
		}

		// Load cookie data by file path.
		if( $path = $this->GetCookieFilePath() ){
			//	Read only?
			curl_setopt( $curl, CURLOPT_COOKIEFILE, $path );

			// Save cookie data after curl_close. (File name that holds cookie data.)
			curl_setopt( $curl, CURLOPT_COOKIEJAR, $this->_cookie );
		}

		/* ==== PROXY ==== */

		//	Set proxy.
		if( null ){
			curl_setopt($ch, CURLOPT_PROXY,     $ip_address);
			curl_setopt($ch, CURLOPT_PROXYPORT, $port_number);
		}

		/* ==== SSL ==== */

		//	SSL Connection.
		if( $this->GetCertificate() ){
			//	Use the certificate.
			curl_setopt( $curl, CURLOPT_CAINFO, $certificate );
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, true );
			//	curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 2 );
		}else{
			//	Does not validation of the certificate.
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
		}

		//	Execute
		$body = curl_exec( $curl );

		//	Information
		$this->_info = curl_getinfo( $curl );

		return $body;
	}

	/**
	 * Do file_get_contents by url.
	 *
	 * @param  string $url
	 * @param  array $post
	 * @return string
	 */
	function Fetch($url, $post=null)
	{
		if( isset($this->_debug['fetch']) ){
			$this->_debug['fetch']++;
		}else{
			$this->_debug['fetch'] = 1;
		}

		//	Used at timeout.
		$st = microtime(true);

		//	Set timeout time.
		$conf = array();
		$conf['http']['timeout'] = $this->GetTimeout();
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
