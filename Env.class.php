<?php
/**
 * Env.class.php
 * 
 * @creation  2015-04-19
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright 2014 (C) Tomoaki Nagahara All right reserved.
 */

/**
 * Env
 * 
 * @creation  2014-01-22
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright 2014 (C) Tomoaki Nagahara All right reserved.
 */
class Env extends OnePiece5
{
	//	Administrator setting
	const _ADMIN_IP_ADDR_		 = 'ADMIN_IP';
	const _ADMIN_EMAIL_ADDR_	 = 'ADMIN_MAIL';
	const _KEY_LOCALE_			 = 'locale';
	
	/**
	 * 
	 * @var array
	 */
	static $_env;

	/**
	 * Display debug information.
	 */
	static public function Debug()
	{
		OnePiece5::D(self::$_env);
	}

	/**
	 * Convert key and value. (Standardization)
	 * 
	 * @param string $key reference
	 * @param string $var reference
	 */
	static private function _Convert( &$key, &$var=null )
	{
		$key = strtoupper($key);
		
		switch($key){
			case 'NL':
				$key = 'NEW_LINE';
				break;
				
			case 'HREF':
				$key = 'HTTP_REFERER';
				break;
				
			case 'LANG':
				$key = 'LANGUAGE';
				break;
				
			case 'FQDN':
			case 'DOMAIN':
				$key = 'HTTP_HOST';
				break;
				
			default:
				if( preg_match('/[-_](ROOT|DIR)$/',$key, $match) ){
					$is_path = true;

					//	OP_DIR --> OP_ROOT
					if( $match[1] === 'DIR' ){
						$key = preg_replace('/(ROOT|DIR)$/', "ROOT", $key);
					}
				}

				//	OP-ROOT --> OP_ROOT
				$key = preg_replace('/-/', '_', $key);
		}
		
		//	$var is a case of path.
		if( $var and !empty($is_path) ){
			
			/*  Convert to unix path separator from Windows path separator.
			 * 
			 * Example
			 * C:¥www¥htdocs¥ -> C:/www/htdocs/
			 */
			$var = str_replace('\\', '/', $var);
			
			/* Add a slash at the end of the path.
			 * 
			 * Example
			 * /var/www/html -> /var/www/html/
			 */
			$var = rtrim( $var, '/').'/';
		}
		
	//	return array( $key, $var );
	}
	
	/**
	 * Bootstrap
	 */
	static function Bootstrap()
	{
		self::Set('mime','text/html');
		self::Set('charset','utf-8');

		self::_init_error();
		self::_init_session();
		self::_init_op_uniq_id();
		self::_init_locale();
		self::_init_mark_label();
	}
	
	/**
	 * Init error.
	 */
	private static function _init_error()
	{
		// Error control
		$save_level = error_reporting();
		error_reporting( E_ALL );
		ini_set('display_errors',1);

		//	Checking admin
		$io = self::isAdmin();

		//	Error check.
		if( $io === null ){
			OnePiece5::AdminNotice("\_init_admin\ has not been called.");
		}

		//	If not an administrator.
		if(!$io){
			//  recovery (display_errors)
			ini_set('display_errors',0);
			//  recovery (error_reporting)
			error_reporting( $save_level );
		}
	}

	/**
	 * Init session.
	 */
	private static function _init_session()
	{
		if( self::isShell() ){
			return;
		}

		//	Checking header sent.
		if( headers_sent($file,$line) ){
			OnePiece5::AdminNotice("Header has already been sent. File: {$file}, Line number #{$line}.");
			return;
		}

		if( session_id() ){
			//	Checking php.ini
			if( ini_get('session.auto_start') ){
				OnePiece5::AdminNotice('Session is already started. Open "php.ini". Write to "session.auto_start = 0;"');
			}
			return;
		}

		/**
		 * @see http://d.hatena.ne.jp/shinyanakao/20080313/1205396128
		 * @see http://qiita.com/mugng/items/ae3c4c07f920a5e6e2ed
		 * @see http://www.glamenv-septzen.net/view/29
		 */
		session_cache_expire(0);
		session_cache_limiter('private_no_expire');
		session_start();

	//	header('Cache-Control: private, max-age=0, pre-check=0');
	}
	
	/**
	 * Init onepiece's unique id.
	 */
	private static function _init_op_uniq_id()
	{
		//	Checking op-uniq-id.
		if( $uniq_id = OnePiece5::GetCookie(OnePiece5::_KEY_COOKIE_UNIQ_ID_) ){
			// Already exists.
			return;
		}
		
		//	Generating op-uniq-id and save.
		$uniq_id = md5(microtime() + $_SERVER['REMOTE_ADDR']);
		$expire  = 60*60*24*365*10;
		OnePiece5::SetCookie(OnePiece5::_KEY_COOKIE_UNIQ_ID_, $uniq_id, $expire);
	}
	
	/**
	 * Init mark label.
	 */
	private static function _init_mark_label()
	{
		//  mark_label
		if( OnePiece5::Admin() and isset($_GET['mark_label']) ){
			$mark_label = $_GET['mark_label'];
			$mark_value = $_GET['mark_label_value'];
			Developer::SaveMarkLabelValue($mark_label,$mark_value);
		}
	}
	
	/**
	 * locale setting.
	 *
	 * @see http://jp.php.net/manual/ja/class.locale.php
	 * @param string $locale ja_JP.UTF-8
	 */
	private static function _init_locale()
	{
		/**
		 * Windows
		 * 	Japanese_Japan.932 = sjis
		 * 	Japanese_Japan.20932 = euc-jp
		 *
		 * PostgreSQL for Windows
		 * 	Japanese_Japan.932 = utf-8 // auto convert
		 *
		 * http://lets.postgresql.jp/documents/technical/text-processing/2/
		 */
		
		//	Get last time locale.
		if(!$locale = OnePiece5::GetCookie(self::_KEY_LOCALE_) ){
			$locale = 'ja_JP.utf-8';
		}
		
		//	Set locale
		Env::SetLocale($locale);
	}
	
	static function GetLocale()
	{
		return Env::Get(self::_KEY_LOCALE_);
	}
	
	/**
	 * Set system environment locale.
	 * 
	 * @param string $locale
	 */
	static function SetLocale( $locale )
	{
		//	Save to cookie. (Will abolished.)
		OnePiece5::SetCookie(self::_KEY_LOCALE_, $locale);

		//	Parse locale code.
		list($lang, $area, $code) = Locale::ParseLocaleCode($locale);

		// Windows is unsupport utf-8
		/*
		if( PHP_OS == 'WINNT' and $lang == 'ja' ){
			// Shift_JIS
			setlocale( LC_ALL, 'Japanese_Japan.932');
		}else if(!setlocale( LC_ALL, $locale )){
			// @see http://jp.php.net/manual/ja/function.setlocale.php
			OnePiece5::AdminNotice("Illigal locale: $locale");
			return false;
		}
		*/

		//	Set each value.
		self::$_env[self::_KEY_LOCALE_] = $locale;
		Env::Set('lang',   $lang);
		Env::Set('area',   $area);
		Env::Set('charset',$code);

		//	Get locale relation value.
		$timezone = Locale::GetTimezone($locale);
		$codes    = Locale::GetEncodingOrderDetect($locale);

		//	Set PHP's environment value
		mb_language($lang);
		mb_internal_encoding($code);
		mb_detect_order($codes);
	//	mb_http_input();
	//	mb_http_output()

		//	set timezone
		ini_set('date.timezone',$timezone);
	}

	/**
	 * Wrapper method of isShell.
	 * 
	 * @return boolean
	 */
	static function isCli()
	{
		return self::isShell();
	}
	
	/**
	 * Checking of shell.
	 * 
	 * @return boolean
	 */
	static function isShell()
	{
		static $_is_shell;

		if( $_is_shell === null ){
			//	Check if shell.
			if( isset($_SERVER['SHELL']) ){
				self::Set('shell',true);
				self::Set('cli',  true);
				self::Set('mime','text/plain');
				$_is_shell = true;
			}

			//	Check if admin.
			if( isset($_SERVER['PS1']) ){
				self::isAdmin(true);
			}
		}

		return $_is_shell;
	}

	/**
	 * Checking of localhost.
	 * 
	 * @return boolean
	 */
	static function isLocalhost()
	{
		static $_is_localhost;

		if( $_is_localhost === null ){
			$remote_addr = $_SERVER['REMOTE_ADDR'];
			if( $remote_addr === '127.0.0.1' or $remote_addr === '::1'){
				$_is_localhost = true;
			}else{
				$_is_localhost = false;
			}
		}

		return $_is_localhost;
	}

	/**
	 * Checking of admin.
	 * 
	 * @return boolean
	 */
	static function isAdmin()
	{
		//	Checking admin conditions.
		if( self::isLocalhost() ){
			$_is_admin = true;
		}else{
			$_is_admin = self::Get('admin-ip') === $_SERVER['REMOTE_ADDR'] ? true: false;
		}

		return $_is_admin;
	}
	
	/**
	 * Get OnePiece5's unique ID.
	 * @return $uniq_id;
	 */
	static function UniqID()
	{
		if(!$uniq_id = OnePiece5::GetCookie(OnePiece5::_KEY_COOKIE_UNIQ_ID_)){
			$uniq_id = self::_init_cookie();
		}
		return $uniq_id;
	}

	/**
	 * Get environment value.
	 * 
	 * <pre>
	 * Env::Get('admin-ip');
	 * </pre>
	 * 
	 * @param  string $key
	 * @return integer|string|null
	 */
	static function Get($key, $default=null)
	{
		self::_Convert($key);

		if( isset(self::$_env[$key]) ){
			$var = self::$_env[$key];
		}else if( isset($_SERVER[$key]) ){
			$var = $_SERVER[$key];
		}else{
			$var = null;
		}

		return $var !== null ? $var: $default;
	}

	/**
	 * Set environment value.
	 * 
	 * <pre>
	 * Env::Set('admin-ip','192.168.1.1');
	 * </pre>
	 * 
	 * @param string $key
	 * @param string $var
	 */
	static function Set( $key, $var )
	{
		self::_Convert( $key, $var );

		if( $key === 'MIME'){
			$charset = Env::Get('charset', 'utf-8');
			header("Content-type: $var; charset=$charset");
		}

		self::$_env[$key] = $var;
	}

	/**
	 * @see http://jp.php.net/manual/ja/function.register-shutdown-function.php
	 */
	static function Shutdown()
	{
		if( $error = error_get_last()){
			OP\Error::LastError($error);
		}
		
		//	Error
		OP\Error::Report();
		
		//	Check
		if(!self::isAdmin() ){
			return;
		}
		
		/**
		 * @see http://jp.php.net/manual/ja/features.connection-handling.php
		 */
		$aborted = connection_aborted();
		$status  = connection_status();
		
		// Session reset
		if( Toolbox::isLocalhost() and Toolbox::isHtml() ){
			$rand = rand( 0, 1000);
			if( 1 == $rand ){
				$_SESSION = array();
				$message = OnePiece5::i18n()->En('\OnePiece5\ did initialize the \SESSION\.');
				print "<script>alert('$message');</script>";
			}
		}
		
		//	Get mime.
		list($main, $sub) = explode('/',Toolbox::GetMIME());
		
		//	In case of not text. (maybe, application or image or sound or movie)
		if( $main !== 'text' ){
			return;
		}
		
		//	Generate shutdown label by each mime.
		switch($sub){
			case 'css':
			case 'javascript':
			case 'csv':
				$label = ' /* OnePiece is shutdown */ ';
				break;
				
			case 'plain':
				if( Env::Get('cli') ){
					$label = ' -- OnePiece is shutdown -- ';
				}else{
					//	json
					$label = null;
				}
				break;
				
				
			case 'html':
				$label = "<OnePiece />";
				
				//	Developer
				if( OnePiece5::Admin() ){
					Developer::PrintGetFlagList();
				}
				break;

			case 'json':
			default:
				$label = null;
		}
		
		//	Output of shutdown label. (if exist)
		if( $label ){
			print PHP_EOL.$label.PHP_EOL;
		}
	}
}
