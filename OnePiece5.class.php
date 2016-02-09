<?php
/**
 * OnePiece5.class.php
 * 
 * By using this program, you agree to our Privacy Policy, Terms of Use and End User License Agreement.
 * 
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright 2009 (C) Tomoaki Nagahara All right reserved.
 */

//	Bootstrap
include(__DIR__.'/Bootstrap.php');

/**
 * OnePiece5
 * 
 * This is OnePiece-Framework's core.
 * 
 * @version   1.0
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright 2009 (C) Tomoaki Nagahara All right reserved.
 * @package   op-core
 */
class OnePiece5
{
	const  KEY_COOKIE_UNIQ_ID	 = 'op-uniq-id';
	const _KEY_COOKIE_UNIQ_ID_	 = self::KEY_COOKIE_UNIQ_ID;

	private $session = array();
	private $_is_init  = null;

	function __construct()
	{
		//	Do Initialized in the init-method.(for extends class)
		if( method_exists($this, 'Init') ){
			$this->Init();
		}

		switch( $type = ifset($_GET['onepiece']['admin-notice']) ){
			case 'js':
				$mime = 'text/javascript';
				$path = $_SERVER['OP_ROOT'].'Template/js/AdminNotice.js';
				break;

			case 'css':
				$mime = 'text/css';
				$path = $_SERVER['OP_ROOT'].'Template/css/AdminNotice.css';
				break;
		}

		if( isset($path) ){
			Toolbox::SetMime($mime);
			echo file_get_contents( $path );
			if( $this instanceof NewWorld5 ){
				$this->Content();
			}
			exit;
		}
	}

	function __destruct()
	{
		//  Called Init?
		if(!$this->_is_init){
			$class = get_class($this);
			$this->AdminNotice("\\$class\ has not call \parent::init()\.");
		}
	}
	
	function __call( $name, $args )
	{
		$class = get_class($this);
		OP\Error::MagicMethodCall( $class, $name, $args );
	}
	
	static function __callStatic( $name , $args )
	{
		$class = isset($this) ? get_class($this): 'unknow';
		OP\Error::MagicMethodCallStatic( $class, $name, $args);
	}
	
	function __set( $name, $args )
	{
		$class	 = get_class($this);
		$call	 = OnePiece5::GetCallerLine();
		OP\Error::MagicMethodSet( $class, $name, $args, $call );
	}
	
	function __get( $name )
	{
		$class = get_class($this);
		$call  = $this->GetCallerLine();
		OP\Error::MagicMethodGet( $class, $name, $call );
	}
	
	function __isset( $name )
	{
		$class = get_class($this);
		$call = $this->GetCallerLine();
		OP\Error::MagicMethodGet( $class, $name, $call );
	}
	
	function __unset( $name )
	{
		$class = get_class($this);
		$call = $this->GetCallerLine();
		OP\Error::MagicMethodGet( $class, $name, $call );
	}
	
	/*
	function __sleep()
	{
		
	}
	
	function __wakeup()
	{
		
	}
	*/
	
	function __toString()
	{
		OnePiece5::Mark('![.red .bold[ CATCH MAGIC METHOD ]]');
	}
	
	function __invoke()
	{
		OnePiece5::Mark('![.red .bold[ CATCH MAGIC METHOD ]]');
	}
	
	function __set_state()
	{
		OnePiece5::Mark('![.red .bold[ CATCH MAGIC METHOD ]]');
	}
	
	function Init()
	{
		$this->_is_init = true;
		return true;
	}
	
	/**
	 * You check whether you an administrator by IP-Address.
	 */
	static function Admin()
	{
		return Env::isAdmin();
	}

	/**
	 * This method will inform to administrator by mail.
	 * Display of error at html in case of administrator.
	 *
	 * Language code example:
	 * 	ja, ja-JP, en, en-US, en-UK, zh-CN, zh-TW, zh-HK
	 *
	 * @param string $message  Message of notice to admin.
	 * @param string $language Language code of message.
	 */
	static function AdminNotice($message, $lang='en')
	{
		return OP\Error::Set($message, $lang);
	}

	/**
	 * Stack of error.
	 * 
	 * This method will abolished.
	 * Please use SystemError method.
	 * 
	 * Language code example:
	 * 	ja, ja-JP, en, en-US, en-UK, zh-CN, zh-TW, zh-HK
	 * 
	 * @param string $message  Message of notice to admin.
	 * @param string $language Language code of message. 
	 */
	static function StackError($message, $lang=null)
	{
		$call = self::GetCallerLine();
		self::Mark("StackError method will abolished. Please use AdminNotice method. --> $call");
		self::AdminNotice($message, $lang);
	}

	/**
	 * Set env-value.
	 * 
	 * @param string $key
	 * @param string|array $var
	 */
	static function SetEnv( $key, $var )
	{
		return Env::Set($key, $var);
	}

	/**
	 * Get env-value.
	 * 
	 * @param string $key
	 */
	static function GetEnv($key, $default=null)
	{
		return Env::Get($key, $default);
	}
	
	/**
	 * Set session value.
	 * 
	 * <pre>
	 * $this->SetSession('count', $count+1);
	 * </pre>
	 * 
	 * @param string $key
	 * @param string|array $var
	 */
	static function SetSession($key, $var)
	{
		if( $key === null ){
			$_SESSION['_ONEPIECE_'][__CLASS__] = $var;
		}

		if( $var === null ){
			unset($_SESSION['_ONEPIECE_'][__CLASS__][$key]);
			return;
		}

		$_SESSION['_ONEPIECE_'][__CLASS__][$key] = $var;
	}

	/**
	 * Get session value.
	 * 
	 * <pre>
	 * $this->GetSession('count');
	 * </pre>
	 * 
	 * @param string $key
	 */
	function GetSession($key)
	{
		if( $key === null ){
			return $_SESSION['_ONEPIECE_'][__CLASS__];
		}

		if( isset( $_SESSION['_ONEPIECE_'][__CLASS__][$key]) ){
			return $_SESSION['_ONEPIECE_'][__CLASS__][$key];
		}

		return null;
	}

	/**
	 * SetCookie is auto set to $_COOKIE, and value is valid all value! (string, number, array and object!!)
	 *
	 * Expire value's default is 0.
	 * 0 is 365 days. -1 is out of valid expire. 
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @param number $expire
	 * @param string $path
	 * @param string $domain
	 * @param number $secure
	 * @param string $httponly
	 * @return boolean
	 */
	static function SetCookie( $key, $value, $expire=0, $path='/', $domain='', $secure=0, $httponly=true )
	{
		$key   = OnePiece5::Escape($key);
		$value = OnePiece5::Escape($value);
		
		if( is_null($expire) ){
			OnePiece5::AdminNotice("\expire\ value does not set. (ex. 0 is 365days, -1 is out of valid expire.)");
		}
		
		if( headers_sent($file,$line) ){
			OnePiece5::AdminNotice("Header has already been sent. \File: {$file}\, Line number \#$line\.");
			return false;
		}
		
		if( is_null($value) ){
			$expire = time() -10;
		}else if( $expire === 0 ){
			$expire = time() + 60*60*24*365;
		}else if( $expire < time() ){
			$expire += time();
		}
		
		$_key   = $key;
		$_key   = md5($key);
		$_value = serialize($value);
		
		if (version_compare(PHP_VERSION, '5.2.0') >= 0) {
			//	httponly=true is Only http accsess. Can not access from JavaScript.
			//	However, it is dependent on a browser.
			$io = setcookie( $_key, $_value, $expire, $path, $domain, $secure, $httponly );
		}else{
			$io = setcookie( $_key, $_value, $expire, $path, $domain, $secure );
		}
		
		if( is_null($value) ){
			unset($_COOKIE[$_key]);
		}else if( $io ){
			$_COOKIE[$_key] = $_value;
		}else{
			OnePiece5::mark('SetCookie is failed.');
			OnePiece5::AdminNotice("SetCookie is fail: \{$key}={$value}\\");
		}
		
		return $io;
	}
	
	/**
	 * Get Cookie by key.
	 * 
	 * @param string $key
	 * @param string $default
	 * @return Ambigous <string, string|array|object, Config>
	 */
	static function GetCookie( $key, $default=null )
	{
		$key = md5($key);
		if( isset($_COOKIE[$key]) ){
			$value = $_COOKIE[$key];
			$value = unserialize($value);
			$value = OnePiece5::Escape($value);
		}else{
			$value = $default;
		}
		return $value;
	}
	
	/**
	 * Set the value to memcache.
	 * 
	 * @param string $key
	 * @param string|array|Config $value
	 * @param number $expire 2592000 = 60*60*24*30
	 */
	function SetCache($key, $value, $expire=2592000)
	{
		OnePiece5::Cache()->Set($key, $value, $expire);
	}
	
	/**
	 * Get the value from memcache.
	 */
	function GetCache($key)
	{
		return OnePiece5::Cache()->Get($key);
	}
	
	/**
	 * Get call line.
	 * 
	 * @param integer $depth
	 * @param integer $num
	 * @param string  $format
	 */
	static function GetCallerLine( $depth=0, $num=1, $format=null )
	{
		//	file system encoding
		$encode_file_system = PHP_OS === 'WINNT' ? 'sjis-win': 'utf-8';
		
		//	init
		$call_line = '';
		$depth++;
		$nl = self::GetEnv('nl');
		
		//	debug_backtrace
		if( version_compare(PHP_VERSION, '5.2.5') >= 0 ){
			$back = debug_backtrace(false);
		}else{
			$back = debug_backtrace();
		}
		
		//	num
		if( $num >= count($back) or $num <= 0 ){
			$num = count($back) -1;
		}
		
		//	Not exists
		if( count($back) <= $depth ){
			return 'null';
		}
		
		// loop
		for($i=1; $i <= $num; $depth++, $i++){
			
			$func   = isset($back[$depth]['function']) ? $back[$depth]['function']: null;
			$args   = isset($back[$depth]['args'])     ? $back[$depth]['args']:     null;
			$file   = isset($back[$depth]['file'])     ? $back[$depth]['file']:     null;
			$line   = isset($back[$depth]['line'])     ? $back[$depth]['line']:     null;
			$type   = isset($back[$depth]['type'])     ? $back[$depth]['type']:     null;
			$class  = isset($back[$depth]['class'])    ? $back[$depth]['class']:    null;
			$line1m = isset($back[$depth-1]['line'])   ? $back[$depth-1]['line']:   null;
			
			$filefull = $file;
			$function = $func;

			// args controll
			if(count($args) == 0){
				$args = '';
			}else if($func == 'include'){
				// does not process.
				$path = realpath($args[0]);
				$path = self::CompressPath($path);
				$args = self::Escape( $path, $encode_file_system ); // file system's encode is not html encode
				$func = $func.'("$args")';
			}else if(count($args) == 1){
				if(!$args[0]){
					$args = '![.red[null]]';
				}else{
					if( is_object($args[0]) ){
						$var_export = get_class($args[0]);
					}else{
						$var_export = var_export($args[0],true);
					}
					$str = $var_export;
					$str = str_replace(array("\n","\r","\t"), '', $str);
					$str = str_replace("\\'", "'", $str);
					$str = str_replace(",)", ") ", $str);
					$str = str_replace(",  )", ") ", $str);
					$str = self::Escape($str);
					$args = $str;
				}
			}else{
				$vars = array();
				foreach($args as $var){
					switch(gettype($var)){
						case 'string':
							$vars[] = self::Escape($var);
							break;
							
						case 'array':
							$str = '';//serialize($var);//var_export($var,true);
							$str = str_replace(array("\n","\r","\t"), '', $str);
							$str = str_replace("\\'", "'", $str);
							$str = str_replace(",)", ") ", $str);
							$str = str_replace(",  )", ") ", $str);
							$vars[] = self::Escape($str);
							break;
							
						case 'object':
							$vars[] = get_class($var);
							break;
							
						default:
							$vars[] = gettype($var);
					}
				}
				$args = implode(', ',$vars);
				$args = str_replace(array("\n","\r"), '', $args);
			}
			
			// __call is empty case
			if(empty($file)){
				$file = __FILE__;
			}
			
			// Path is shorten
			if( self::GetEnv('Pacifista') ){
				//	Does not shorten.
				$file = trim($file,"\t");
			}else{
				$file = self::CompressPath($file);
			}
			
			// method
			if( $type ){
				$method = "$class$type$func($args)";
			}else{
				$method = null;
			}
			
			switch(strtolower($format)){
				case 'mark':
					$format = '$file [$line] ';
					break;
					
				case 'incident':
					$format = '![.bold[$class$type$func]] [$line1m]' ;
					break;
				
				case 'trace':
					$format = '![div .line [$file: $line]] ![div .method [ $class$type$function($args)]] '.$nl;
					break;
					
				case '':
				case 'null':
					if( $func ){
						if( $method ){
							$format = '$file [$line] ';
						}else if( $class ){
							$format = '$file [$line] ';
						}else{
							$format = '$file [$line] ';
						}
					}else{
						$format = '$file [$line] ';
					}
					break;
				default:
				//	$format = 'This format is undefined. ($format)';
			}
			
			$patt = array('/\$filefull/','/\$file/','/\$line1m/','/\$line/','/\$class/','/\$type/','/\$function/','/\$func/','/\$args/','/\$method/');
			$repl = array($filefull, $file, $line1m, $line, $class, $type, $function, $func, $args, $method);
			$call_line .= preg_replace( $patt, $repl, $format );
		}
		
		return $call_line;
	}
	
	/**
	 * Compress file path by metaphar.
	 * 
	 * @param  string $file_path
	 * @return string $file_path
	 */
	static function CompressPath( $path )
	{
		$path = OnePiece5::Escape($path);
		
		// TODO: file system encoding. (Does not support multi language, yet)
		$encode_file_system = PHP_OS === 'WINNT' ? 'sjis-win': 'utf-8';
		if( PHP_OS == 'WINNT' ){
			$path = str_replace( '\\', '/', $path );
		}
		
		//	get each root directory.
		$op_root  = self::GetEnv('op_root');
		$app_root = self::GetEnv('app_root');
		
		//  remove slash. (easy-to-read)
		$op_root  = $op_root  ? rtrim($op_root,  '/') : ' ';
		$app_root = $app_root ? rtrim($app_root, '/') : ' ';
		
		//	replace.
		$patt = array();
		$patt[] = "|^".preg_quote($app_root)."|";
		$patt[] = "|^".preg_quote($op_root)."|";
		$repl = array('App:','OP:');
		$path = preg_replace( $patt, $repl, $path );
		
		//  easy-to-read. (op:OnePiece.class.php & app:/template/form.phtml)
		$path = preg_replace( '|(]])/([^/]+)$|', '\\1\\2', $path );
		
		return $path;
	}
	
	/**
	 * The File name, Function or Method name, and the number of line which are performed are outputed.
	 * 
	 * @param string  $str
	 * @param boolean $mark_labels
	 */
	static function Mark( $str='', $mark_labels=false )
	{
		//	display is Admin-ip only.
		if(!self::Admin()){ return; }
		
		/*
		//	disable mark
		if(!self::GetEnv('mark') === false){
			return;
		}
		*/
		
		//	Print mark.
		Developer::PrintMark( $str, $mark_labels, self::GetCallerLIne(0,1,'mark') );
	}

	/**
	 * Display at html format.
	 * 
	 * @param  string $str
	 * @param  string $tag
	 * @param  array  $attr
	 * @return string
	 */
	static function Html($str, $tag='span', $attr=null)
	{
		$str   = self::Escape($str);
		$tag   = self::Escape($tag);
		$attr  = self::Escape($attr);
		$str   = self::Wiki2($str, array('tag'=>true) );
		$style = '';
		$class = '';
		
		if(isset($attr['class'])){
			if(is_array($attr['class'])){
				$temp = implode(' ',$attr['class']);
			}else{
				$temp = $attr['class'];
			}
			$class = sprintf(' class="%s"', $temp);
		}
		
		if(isset($attr['style'])){
			foreach($attr['style'] as $key => $var){
				$styles[] = "$key:$var;";
			}
			$style = sprintf(' style="%s"', implode(' ', $styles));
		}
		
		return PHP_EOL."<{$tag}{$class}{$style}>{$str}</{$tag}>".PHP_EOL;
		return sprintf($nl.'<%s %s %s>%s</%s>'.$nl, $tag, $class, $style, $str, $tag );
	}
	
	/**
	 * Display at html format by p tag.
	 * 
	 * <pre>
	 * Example:
	 * $this->P('Message','div',array('class'=>'bold blue'));
	 * </pre>
	 * 
	 * @param string $str
	 * @param string $tag
	 * @param array  $attr
	 */
	static function P( $str, $tag='p', $attr=null)
	{
		//	In case of plain text.
		if( Toolbox::isHtml() ){
			print self::Html( $str, $tag, $attr );
		}else{
			print trim( html_entity_decode(strip_tags(self::Html( $str, $tag, $attr ))) );
		}
		print PHP_EOL;
	}
	
	/**
	 * Dump
	 * 
	 * <pre>
	 * Example:
	 * $this->D($_SESSION);
	 * </pre>
	 * 
	 * @param string|array $args
	 * @param string $use_get_flag
	 */
	static function D( $args, $mark_label=null )
	{
		// displayed is only admin-ip.
		if(!self::admin()){ return; }
		
		// displayed is Admin-ip and flag.
		if( $mark_label ){
			if(!Developer::GetSaveMarkLabelValue($mark_label)){
				return;
			}
		}
		
		//	Call line.
		$line = self::GetCallerLine();
		
		//	type
		$type = gettype($args);
		
		//	join
		$line .= " - ".$type;
		
		//	CLI
		if( self::GetEnv('cli') ){
			$mime = Toolbox::GetMIME(1);
			$flag = ($mime == 'js' or $mime == 'css') ? true: false;
			if( $flag ){ print '/*'.PHP_EOL; }
			self::p($line);
			print_r($args);
			if( $flag ){ print '*/'.PHP_EOL; }
			return;
		}
		
		if( class_exists('Dump',true) ){
			self::p($line, 'div', array('class' => array('OnePiece','small','_bold','mark'), 
			                            'style' => array('color'=>'black',
			                            				 'font-size' => '9pt',
														 'background-color'=>'white'
														)));
			if( $args instanceof Config ){
				$args = Toolbox::toArray($args);
			}
			
			// Dump.class.php include by __autoloader
			Dump::d($args);
		}else{
			$line = self::Wiki2($line,array('tag'=>true));
			print strip_tags($line);
			print serialize($args);
		}
	}
	
	/**
	 * Decode value.
	 * 
	 * @param string|array|object $args
	 * @param string $charset
	 * @return string|array|object
	 */
	static function Decode( $args, $charset=null)
	{
		//  Accelerate
		static $charset = null;
		if(!$charset){
			$charset = self::GetEnv('charset');
		}
		
		switch($type = gettype($args)){
			case 'string':
				$args = html_entity_decode($args, ENT_QUOTES, $charset);
				$args = str_replace(array('&ldquo;','&rdquo;','&lsquo;','&rsquo;'), array('“','”','‘','’'), $args);
				break;
				
			case 'array':
				foreach( $args as $key => $var ){
					$key  = self::Decode( $key, $charset );
					$var  = self::Decode( $var, $charset );
					$temp[$key] = $var;
				}
				$args = $temp;
				break;
				
			case 'object':
				foreach( $args as $key => $var ){
					$key  = self::Decode( $key, $charset );
					$var  = self::Decode( $var, $charset );
					if( $type === 'array' ){
						$temp[$key] = $var;
					}else if( $type === 'object' ){
						$temp->$key = $var;
					}
				}
				$args = $temp;
				break;
				
			default:
				break;
		}
		
		return $args;
	}
	
	/**
	 * Escape value.
	 * 
	 * @param string|array|object $args
	 * @param string $charset
	 */
	static function Escape( $args, $charset=null )
	{
		if(!$charset){
			$charset = Env::Get('charset');
		}
		
		switch($type = gettype($args)){
			case 'null':
			case 'NULL':
			case 'integer':
			case 'boolean':
			case 'double':
				break;
			case 'string':
				$args = self::_EscapeString($args,$charset);
				break;
			case 'array':
				$args = self::_EscapeArray($args,$charset);
				break;
			case 'object':
				$args = self::_EscapeObject($args,$charset);
				break;
			default:
				self::p("[".__METHOD__."] undefined type($type)");
		}
		
		return $args;
	}
	
	/**
	 * Escape string.
	 * 
	 * @param string $args
	 * @param string $charset
	 */
	static private function _EscapeString( $args, $charset )
	{
		//  Anti null byte attack
		$args = str_replace("\0", '\0', $args);
		
		//  Anti ASCII Control code.
	//	$args = str_replace("\x00..\x1F",'',$args);
		
		/**
		 * htmlentities's double_encoding off funciton is PHP Version 5.2.3 latter.
		 */
		if( version_compare(PHP_VERSION, '5.2.3') >= 0 ){
			$args = htmlentities( $args, ENT_QUOTES, $charset, false );
		}else{
			$args = html_entity_decode( $args, ENT_QUOTES, $charset );
			$args = htmlentities( $args, ENT_QUOTES, $charset );
		}
		
		return $args;
	}
	
	/**
	 * Escape array.
	 * 
	 * @param array  $args
	 * @param string $charset
	 */
	static private function _EscapeArray( $args, $charset )
	{
		$temp = array();
		foreach ( $args as $key => $var ){
			$key = self::_EscapeString( $key, $charset );
			$var = self::Escape( $var, $charset );
			$temp[$key] = $var;
		}
		return $temp;
	}
	
	/**
	 * Escape object.
	 * 
	 * @param array  $args
	 * @param string $charset
	 */
	static private function _EscapeObject( $args, $charset )
	{
		$temp = new Config();
		foreach ( $args as $key => $var ){
			$key = self::_EscapeString( $key, $charset );
			$var = self::Escape( $var, $charset );
			$temp->$key = $var;
		}
		return $temp;
	}
	
	function SetHeader( $str, $replace=null, $code=null )
	{
		//	2015-04-06
		self::AdminNotice("This method will abolished.");
		
		$cgi = $this->GetEnv('cgi'); // FastCGI
		
		switch($code){
			case '500':
				if($cgi){
					$str = 'Status: 500 Internal Server Error';
				}else{
					$str = 'HTTP/1.1 500 Internal Server Error';
				}
				break;
			case (preg_match('/^[0-9]+$/',$code) ? true: false):
				
				break;
		}
		
		if(headers_sent()){
			$io = false;
			$this->AdminNotice("Already header sent.");
		}else{
			$io = true;
			$str = str_replace( array("\n","\r"), '', $str );
			header( $str, $replace, $code );
		}
		
		return $io;
	}

	/**
	 * Set http header.
	 * 
	 * @param string $str
	 * @param string $replace
	 * @param string $code
	 * @return boolean
	 */
	static function Header( $str, $replace=null, $code=null )
	{
		if( headers_sent($file, $line) ){
			$io = false;
			self::AdminNotice("Header is already been sent.\($file, $line)\\");
		}else{
			$io = true;
			$str = str_replace( array("\n","\r"), '', $str );
			header( $str, $replace, $code );
		}
		return $io;
	}

	/**
	 * Forward local location.(not external URL)
	 *
	 * @param string  $url  transfer url.
	 * @param boolean $exit default is true.
	 * @return void|boolean
	 */
	function Location( $url, $exit=true )
	{
		//	Document root path
		$url = $this->ConvertUrl($url,false);
		
		//	Check infinity loop.
		if( $_SERVER['REQUEST_METHOD'] === 'POST' ){
			//	Does not for infinity.
		}else{
			//	Get current URI. (remove URL query)
			list($request_uri) = explode('?',$_SERVER['REQUEST_URI']);
			//	Compare URL.
			if( $io = rtrim($url,'/') === rtrim($request_uri,'/') ){
				$this->mark("![.red[Location is Infinite loop. ($url)]]");
				return false;
			}
		}
	
		$io = $this->Header("Location: " . $url);
		if( $io ){
			$location['message'] = 'Do Location!!' . date('Y-m-d H:i:s');
			$location['post']	 = $_POST;
			$location['get']	 = $_GET;
			$location['referer'] = $_SERVER['REQUEST_URI'];
			$this->SetSession('Location', $location);
			if( $exit ){
				exit(0);
			}
		}
		return $io;
	}
	
	/**
	 * Get template file content.
	 * 
	 * Does not support static type.
	 * Because, it would unusable $this. 
	 * 
	 * @param  string $file file name or path.(current-dir or template-dir)
	 * @param  array|Config $args  
	 * @return string
	 */
	function GetTemplate( $file, $args=null )
	{
		// ob_start is stackable
		if( ob_start() ){
			
			try{
				self::Template( $file, $args );
			}catch(Exception $e){
				//	Catch
			}
			
			if( isset($e) ){
				if( method_exists($e,"isSelftest") ){
					if( $e->isSelftest() ){
						self::AdminNotice($e);
					}
				}else{
					self::AdminNotice($e);
				}
			}
			
			$temp = ob_get_contents();
			$io   = ob_end_clean();
		}else{
			self::AdminNotice("\ob_start\ is failed.");
		}
		
		return $temp;
	}
	
	/**
	 * Pirnt of tempalte file.
	 * 
	 * Does not support static type.
	 * Because, it would unusable $this.
	 * 
	 * @param  string $file file name or path.(current-dir or template-dir)
	 * @param  array|Config $args
	 * @return string|boolean Success is empty string return.
	 */
	function Template( $file_path, $data=null )
	{
		if(!is_string($file_path)){
			self::AdminNotice("Passed arguments is not string. \(".gettype($file_path).")\\");
			return false;
		}
		
		//	Convert meta modifier.
		$file = self::ConvertPath($file_path);
		
		//  for developper's debug
		self::Mark($file,'template');
		
		//  access is deny, above current directory
		if( Env::Get('allowDoubleDot') ){
			//  OK
		}else if( preg_match('|\.\./|',$file) ){ 
			self::AdminNotice("Does not allow parent directory. \($file)\\");
			return false;
		}
		
		//	necessary to convert the path?
		if( file_exists($file) ){
			//  absolute
			$path = $file;
		}else if( $dir = Env::Get('template-dir') ){
			// the path is converted.
			$dir  = self::ConvertPath($dir);
			$path = rtrim($dir,'/').'/'.$file_path;
			
			// 2nd check
			if(!file_exists($path)){
				self::AdminNotice("$file does not exist.\nDir=$dir");
				return false;
			}
		}
		
		// extract array
		if( is_array($data) and count($data) ){
			if(isset($data[0])){
				self::AdminNotice('Passed arguments is not an assoc array.'."\n".'Ex. $this->Template("index.phtml", array("key"=>"value")');
			}else{
				extract($data);
			}
		}else if(is_object($data)){
			// object
		}
		
		// read file
		$io = include($path);
		
		return $io ? '': false;
	}
	
	/**
	 * Wrap of ConvertURL method.
	 * 
	 * @param  string  $path
	 * @param  boolean $domain
	 * @return string
	 */
	static function toURL($meta, $domain=false)
	{
		return self::ConvertURL($meta, $domain);
	}
	
	/**
	 * Convert to browser url from meta-path. (base is document root.)
	 * 
	 * @param  string  $path
	 * @param  boolean $domain
	 * @return string
	 */
	static function ConvertURL( $meta, $domain=null )
	{
		if( $domain ){
			$domain = Toolbox::GetDomain(array('scheme'=>true));
		}
		return $domain . Toolbox::ConvertURL($meta);
	}
	
	/**
	 * Wrap of ConvertPath method.
	 * 
	 * @param  string $path
	 * @return string
	 */
	static function toPath($meta)
	{
		return self::ConvertPath($meta);
	}
	
	/**
	 * Convert to server-side full-path from meta-path.
	 * 
	 * @param  string $path
	 * @return string
	 */
	static function ConvertPath( $meta )
	{
		return Toolbox::ConvertPath($meta);
	}
	
	/**
	 * Returns a singleton of the model.
	 * 
	 * <pre>
	 * Ex 1:
	 * $model = $this->Model('Login');
	 * 
	 * Ex 2:
	 * $this->Model('Login')->GetLoginId();
	 * </pre>
	 * 
	 * @param  string $name
	 * @throws OpException
	 * @return Model_Model
	 */
	static function Model($name)
	{
		try{
			if(!$name){
				throw new OpException("Model name is empty.",'en');
			}
			
			//	hogeHoge -> HogeHoge
			//	$name = ucfirst($name);
			
			//	hogeHoge -> Hogehoge
			//	$name = ucfirst(strtolower($name));
			
			if( empty($_SERVER[__CLASS__]['model'][$name]) ){
				
				$model_name = $name;
				$class_name = "Model_{$name}";
				$file_name  = "{$name}.model.php";
				
				self::_ModelNameCheck($name);
				
				if( class_exists($class_name,false) ){
					//  OK
				}else if( file_exists(getcwd()."/{$file_name}") ){
					//  OK
					include(getcwd()."/{$file_name}");
				}else if( self::_ModelLoadFromApp($file_name) ){
					//  OK
				}else if( self::_ModelLoadFromCore($file_name) ){
					//  OK
				}else{
					throw new OpException("This file not found. \($file_name)\\",'en');
				}
				
				if( class_exists($class_name,false) ){
					$_SERVER[__CLASS__]['model'][$name] = new $class_name();
				}else{
					throw new OpException("This file could not be loaded. \($class_name)\\",'en');
				}
			}
		}catch( Exception $e ){
			$file = $e->getFile();
			$line = $e->getLine();
			$text = $e->getMessage();
			self::AdminNotice("$text\n ![.gray[$file, $line]]");
			return new OnePiece5();
		}
		
		return $_SERVER[__CLASS__]['model'][$name];
	}
	
	static private function _ModelNameCheck($name)
	{
		//  Notice
		if( strpos( $name, '_') !== false ){
			OnePiece5::AdminNotice("Underscore(_) is reserved. For the feature functions. (maybe, namespace)");
		}
	}
	
	/**
	 * File include from app's model directory.
	 */
	static private function _ModelLoadFromApp($file_name)
	{
		//	App's model directory.
		if(!$model_dir = self::GetEnv('model-dir')){
			return false;
		}
		
		//	Convert path.
		$path = self::ConvertPath($model_dir);
		
		//	Check model directory.
		if(!file_exists($path)){
			throw new OpException("This directory has not exists. \($path)\\",'en');
		}
		
		//	Added file name.
		$path .= $file_name;
		
		//	include
		if( $io = file_exists($path) ){
			$io = include_once($path);
		}
		
		return $io;
	}
	
	/**
	 * File include from op-core's model directory.
	 */
	static private function _ModelLoadFromCore($file_name)
	{
		$path = self::ConvertPath("op:/Model/$file_name");
		
		if( $io = file_exists($path) ){
			$io = include_once($path);
		}
		
		return $io;
	}
	
	/**
	 * PDO5 Object
	 * 
	 * @var PDO5
	 */
	private $_pdo = null;
	
	/**
	 * Get PDO5 object
	 * 
	 * @return PDO5
	 */
	function PDO()
	{
		if(!$this->_pdo){
			$op_root = $this->GetEnv('op-root');
			$op_root = rtrim($op_root,'/').'/';
			$path = $op_root.'PDO/PDO5.class.php';

			if(!file_exists($path)){
				$this->AdminNotice("Does not exists file. \($path)\\");
				return false;
			}

			if(!include_once($path) ){
				$this->AdminNotice("Does not include file. \($path)\\");
				return false;
			}

			//	Instance
			$this->_pdo = new PDO5();
		}

		return $this->_pdo;
	}
	
	/**
	 * @var MySQL
	 */
	private $mysql;
	
	/**
	 * Return MySQL Object
	 * 
	 * @param  array|string configuration-array or configuration-file-path
	 * @return MySQL $mysql
	 */
	function MySQL($args=null)
	{
		if( empty($this->mysql) ){
			include_once('SQL/MySQL.class.php');
			$this->mysql = new MySQL( $args, $this );
		}
		return $this->mysql;
	}
	
	/**
	 * Generate single instance. Does not create new instance.
	 * 
	 * @param  string $name
	 * @return OnePiece5
	 */
	static function Singleton($name)
	{
		if( isset( $_SERVER[__CLASS__]['singleton'][$name] ) ){
			return $_SERVER[__CLASS__]['singleton'][$name];
		}
		
		if( $_SERVER[__CLASS__]['singleton'][$name] = new $name() ){
			return $_SERVER[__CLASS__]['singleton'][$name];
		}
		
		return new OnePiece5();
	}
	
	/**
	 * Abstract Form object.
	 * 
	 * @return Form5
	 */
	static function Form()
	{
		return self::Singleton('Form5');
	}
	
	/**
	 * i18n is translate object.
	 * 
	 * @return Model_i18n
	 */
	static function i18n()
	{
		return self::Model('i18n');
	}
	
	/**
	 * Cache is presents the memcached interface.
	 *
	 * @return Cache
	 */
	static function Cache()
	{
		return self::Singleton('Cache');
	}
	
	/**
	 * Wizard
	 * 
	 * @return Wizard
	 */
	function Wizard()
	{
		return $this->Singleton('Wizard');
	}
	
	/**
	 * Doctor
	 * 
	 * @return Doctor
	 */
	static function Doctor()
	{
		return self::Singleton('Doctor');
	}
	
	/**
	 * Wiki2
	 * 
	 * @param  string $string
	 * @param  array  $options
	 * @return string
	 */
	static function Wiki2( $string, $options=null )
	{
		if( class_exists('Wiki2Engine',true) ){
			return nl2br(trim(Wiki2Engine::Wiki2( $string, $options ))) . PHP_EOL;
		}else{
			return nl2br( self::Escape(trim($string)) ) . PHP_EOL;
		}
	}

	/**
	 * EMail
	 * 
	 * @see http://onepiece-framework.com/reference/email
	 * @return EMail
	 */
	function EMail()
	{
		return $this->Singleton('EMail');
	}
	
	/**
	 * Get Model by unit name.
	 *
	 * @param string $name
	 */
	static function Unit($name)
	{
		/**
		 * Order to search for unit-root.
		 * 1. unit-root
		 * 2. app-root
		 * 3. document-root
		 */
		if( $root = Env::Get('unit-root') ){
			$root = self::ConvertPath($root);
			$separate = '/';
		}else{
			if( $root = Env::Get('app-root') ){
				//	OK
			}else{
				$root = $_SERVER['DOCUMENT_ROOT'];
			}
			$separate = '/_';
		}

		//	Generate unit directory.
		$dir = rtrim($root,'/').$separate.lcfirst($name);

		//	Instanciate
		if( file_exists($dir) ){
			if( class_exists("Model_{$name}", false) ){
				//	OK
			}else{
				include("$dir/{$name}.model.php");
			}
			$model = self::Model($name);
		}else{
			self::AdminNotice("This directory does not exists. \($dir)\ ");
			$model = new OnePiece5();
		}

		return $model;
	}
	
	/**
	 * Get submitted value. from form and url query.
	 * 
	 * @param  string $key
	 * @param  string $method
	 * @param  string $default
	 * @return string
	 */
	static function GetRequest($key, $method=null, $default=null)
	{
		return Toolbox::GetRequest($key, $method, $default);
	}
}
