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
	const _NAME_SPACE_		 = 'ONEPIECE_5_ENV';
	
	const _ADMIN_IP_ADDR_	 = 'ADMIN_IP';
	const _ADMIN_EMAIL_ADDR_ = 'ADMIN_MAIL';
	
	const _KEY_LOCALE_ = 'locale';
	
	//	Just ready.
	static $_is_localhost;
	static $_is_admin;
	static $_is_shell;
	
	static private function _Convert( $key, $var=null )
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
		
		return array( $key, $var );
	}
	
	static function Bootstrap()
	{
		self::Set('mime','text/html');
		self::Set('charset','utf-8');

		self::_init_admin();
		self::_init_error();
		self::_init_session();
		self::_init_op_uniq_id();
		self::_init_locale();
		self::_init_mark_label();
	}
	
	private static function _init_error()
	{
		// Error control
		$save_level = error_reporting();
		error_reporting( E_ALL );
		ini_set('display_errors',1);
		
		//	Checking admin
		$io = self::isAdmin();
		
		//	Error check.
		if( is_null($io) ){
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
	
	private static function _init_admin()
	{
		//	Check if admin
		if( self::isLocalhost() ){
			self::$_is_admin = true;
		}else if( isset($_SERVER[self::_ADMIN_IP_ADDR_]) ){
			self::$_is_admin = $_SERVER[self::_ADMIN_IP_ADDR_] === $remote_addr ? true: false;
		}else{
			self::$_is_admin = false;
		}
	}
	
	private static function _init_session()
	{
		if( self::isCLI() ){
			return;
		}
	
		if( session_id() ){
			//	Checking php.ini
			if( ini_get('session.auto_start') ){
				/**
				 * php.ini
				 * session.auto_start = 1;
				 */
				OnePiece5::AdminNotice("php.ini: session.auto_start = 1;");
			}
				
			//	Checking header sent.
			if( headers_sent($file,$line) ){
				OnePiece5::AdminNotice("Header has already been sent. File: {$file}, Line number #{$line}.");
			}else{
				header('Cache-Control: private, max-age=0, pre-check=0');
			}
		}else{
			if( headers_sent($file,$line) ){
				OnePiece5::AdminNotice("Header has already been sent. File: {$file}, Line number #{$line}.");
			}else{
				/**
				 * @see http://d.hatena.ne.jp/shinyanakao/20080313/1205396128
				 * @see http://qiita.com/mugng/items/ae3c4c07f920a5e6e2ed
				 * @see http://www.glamenv-septzen.net/view/29
				 */
				session_cache_expire(0);
				session_cache_limiter('private_no_expire');
				session_start();
			}
		}
	}
	
	private static function _init_localhost()
	{
		$remote_addr = $_SERVER['REMOTE_ADDR'];
		if( $remote_addr === '127.0.0.1' or $remote_addr === '::1'){
			self::$_is_localhost = true;
		}else{
			self::$_is_localhost = false;
		}
	}
	
	private static function _init_shell()
	{
		//	Check if shell.
		if( isset($_SERVER['SHELL']) ){
			self::Set('shell',true);
			self::Set('cli',  true);
			self::Set('mime','text/plain');
			self::$_is_shell = true;
		}
	
		//	Check if admin.
		if( isset($_SERVER['PS1']) ){
			self::$_is_admin = true;
		}
	}
	
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
	
	private static function _init_mark_label()
	{
		//  mark_label
		if( OnePiece5::Admin() and isset($_GET['mark_label']) ){
			$mark_label = $_GET['mark_label'];
			$mark_value = $_GET['mark_label_value'];
			Developer::SaveMarkLabelValue($mark_label,$mark_value);
		//	list($uri) = explode('?',$_SERVER['REQUEST_URI'].'?');
		//	header("Location: $uri");
		//	exit;
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
	
	static function SetLocale( $locale )
	{
		//	Save to cookie.
		OnePiece5::SetCookie(self::_KEY_LOCALE_, $locale);
		
		//	parse
		if( preg_match('|([a-z]+)[-_]([a-z]+)\.([-_a-z0-9]+)|i', $locale, $match) or true){
			$lang = strtolower($match[1]);
			$area = strtoupper($match[2]);
			$code = strtoupper($match[3]);
		}else{
			OnePiece5::AdminNotice("Did not match locale format. ($locale, Ex. ja_JP.utf-8) ");
		}
		
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
		
		//	Set each value
		$_SERVER[self::_NAME_SPACE_][self::_KEY_LOCALE_] = $locale;
		Env::Set('lang',   $lang);
		Env::Set('area',   $area);
		Env::Set('charset',$code);
		
		//	Get locale relation value.
		list( $codes, $timezone ) = Env::GetLocaleValue();
		
		//	Set PHP's environment value
		mb_language($lang);
		mb_internal_encoding($code);
		mb_detect_order($codes);
		//mb_http_input();
		//mb_http_output()
		
		//	set timezone
		ini_set('date.timezone',$timezone);
	}
	
	static function GetLocaleValue()
	{
		$lang = Env::Get('lang');
		$area = Env::Get('area');
		
		//	detect order value
		switch($lang){
			case 'en':
				$codes[] = 'UTF-8';
				$codes[] = 'ASCII';
				break;
				
			case 'ja':
				$codes[] = 'eucjp-win';
				$codes[] = 'sjis-win';
				$codes[] = 'UTF-8';
				$codes[] = 'ASCII';
				$codes[] = 'JIS';
				break;
			default:
			$this->AdminNotice("Does not define this language code. ($lang)");
		}
		
		/**
		 * timezone list
		 * @see http://jp2.php.net/manual/ja/timezones.php
		 */
		switch($area){
			case 'US':
				$timezone = 'America/Chicago';
				break;
				
			case 'JP':
				$timezone = 'Asia/Tokyo';
				break;
			default:
			$this->AdminNotice("Does not define this country code. ($lang)");
		}
		
		return array( $codes, $timezone );
	}
	
	static function isCli()
	{
		return self::isShell();
	}
	
	static function isShell()
	{
		if( self::$_is_shell === null ){
			self::_init_shell();
		}
		return self::$_is_shell;
	}
	
	static function isLocalhost()
	{
		if( self::$_is_localhost === null ){
			self::_init_localhost();
		}
		return self::$_is_localhost;
	}
	
	static function isAdmin()
	{
		if( self::$_is_admin === null ){
			self::_init_admin();
		}
		return self::$_is_admin;
	}
	
	static function SetAdminIpAddress($var)
	{
		$_SERVER[self::_NAME_SPACE_][self::_ADMIN_IP_ADDR_] = $var;
		if( self::isLocalhost() ){
			self::$_is_admin = true;
		}else{
			self::$_is_admin = $_SERVER['REMOTE_ADDR'] === $var ? true: false;
		}
		self::_init_error();
		self::_init_mark_label();
	}
	
	static function GetAdminMailAddress()
	{
		if(!empty($_SERVER[self::_NAME_SPACE_][self::_ADMIN_EMAIL_ADDR_]) ){
			$mail_addr = $_SERVER[self::_NAME_SPACE_][self::_ADMIN_EMAIL_ADDR_];
		}else if(!empty($_SERVER['SERVER_ADMIN']) ){
			$mail_addr = $_SERVER['SERVER_ADMIN'];
		}else{
			$mail_addr = null;
		}
		return $mail_addr;
	}
	
	static function SetAdminMailAddress($mail_addr)
	{
		$_SERVER[self::_NAME_SPACE_][self::_ADMIN_EMAIL_ADDR_] = $mail_addr;
	}
	
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
		//	Convert
		list( $key, $var ) = self::_Convert( $key );
		
		//	Admin's E-Mail
		if( $key === self::_ADMIN_EMAIL_ADDR_ ){
			return self::GetAdminMailAddress();
		}
		
		//	
		if( isset($_SERVER[self::_NAME_SPACE_][$key]) ){
			$var = $_SERVER[self::_NAME_SPACE_][$key];
		}else if( isset($_SERVER[$key]) ){
			$var = $_SERVER[$key];
		}else{
			$var = null;
		}
		
		//	
		return $var ? $var: $default;
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
		//	Convert
		list( $key, $var ) = self::_Convert( $key, $var );
		
		//	Reset admin flag.
		if( $key === self::_ADMIN_IP_ADDR_ ){
			return self::SetAdminIpAddress($var);
		}
		
		//	Admin's E-Mail
		if( $key === self::_ADMIN_EMAIL_ADDR_ ){
			return self::SetAdminMailAddress($var);
		}
		
		//	Set locale.
		if( $key === strtoupper(self::_KEY_LOCALE_) ){
			return self::SetLocale($var);
		}
		
		//	Set
		$_SERVER[self::_NAME_SPACE_][$key] = $var;
	}
	
	/**
	 * @see http://jp.php.net/manual/ja/function.register-shutdown-function.php
	 */
	static function Shutdown()
	{
		if( $error = error_get_last()){
			Error::LastError($error);
		}
		
		//	Error
		Error::Report();
		
		//	Check
		if(!OnePiece5::Admin()){
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
