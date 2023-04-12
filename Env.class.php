<?php
/** op-core:/Env.class.php
 *
 * @created   2016-06-09
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** Declare strict
 *
 */
declare(strict_types=1);

/** namespace
 *
 * @created   2019-02-20
 */
namespace OP;

/** use
 *
 */
use function OP\Encode;

/**
 * Env
 *
 * @created   2016-06-09
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Env
{
	/** trait.
	 *
	 */
	use OP_CORE, OP_CI;

	/** Constant.
	 *
	 * @var string
	 */
	const _ADMIN_IP_	 = _OP_DEVELOPER_IP_;
	const _ADMIN_MAIL_	 = _OP_DEVELOPER_MAIL_;
	const _ADMIN_FROM_	 = 'mail-from';

	/** Private static values.
	 *
	 * @var array
	 */
	static private $_env;
	static private $_is_admin;
	static private $_is_localhost;

	/** Is Admin
	 *
	 * @return boolean
	 */
	static function isAdmin()
	{
		//	Keep calced value.
		static $_is_admin;

		//	Check if not init.
		if( $_is_admin === null ){
			$_is_admin = include(__DIR__.'/include/isAdmin.php');
		}

		//	Return already calced static value.
		return $_is_admin;
	}

	/** Is localhost
	 *
	 * @return boolean
	 */
	static function isLocalhost()
	{
		//	Keep calced value.
		static $_is_localhost;

		//	Check if not init.
		if( $_is_localhost === null ){
			$_is_localhost = include(__DIR__.'/include/isLocalhost.php');
		}

		//	Return already calced static value.
		return $_is_localhost;
	}

	/** Is Http(s) protocol.
	 *
	 * @return boolean
	 */
	static function isHttp()
	{
		/*
		return isset($_SERVER['REDIRECT_STATUS']);
		*/

		//	...
		return isset($_SERVER['SERVER_NAME']);
	}

	/** Is HTTPs protocal.
	 *
	 * @return boolean
	 */
	static function isHTTPs():bool
	{
		return isset($_SERVER['HTTPS']);
	}

	/** Is Shell
	 *
	 * @return boolean
	 */
	static function isShell():bool
	{
		/*
		return isset($_SERVER['SHELL']);
		*/

		/*
		//	For GitHub action. --> This is empty PHP built-in server.
		return empty($_SERVER['REQUEST_SCHEME']);
		*/

		/*
		//	For PHP built-in server.
		return !isset($_SERVER['SERVER_NAME']);
		*/

		//	...
		return php_sapi_name() === 'cli' ? true: false;
	}

	/** Is CI
	 *
	 * @created    2022-11-11
	 * @return     boolean
	 */
	static function isCI() : bool
	{
		return ( basename($_SERVER['SCRIPT_NAME']) === 'ci.php' ) ? true: false;
	}

	/** Which WebServer doing on?
	 *
	 * @deprecated 2023-04-12
	 * @created    2023-02-18
	 * @return     string
	 */
	static function WebServer():string
	{
		//	...
		static $_software;
		if( $_software ){
			return $_software;
		}

		//	...
		$_software = strtolower($_SERVER['SERVER_SOFTWARE'] ?? '');

		//	...
		foreach( ['php','apache','nginx'] as $key ){
			if( strpos($_software, $key) === 0 ){
				$_software = $key;
			}
		}

		//	...
		return $_software;
	}

	/** Get environment value.
	 *
	 * @deprecated 2023-04-12  Config::Get()
	 * @param  string $key
	 * @return mixed  $var
	 */
	static function Get($key)
	{
		//	...
		switch( $key ){
			case _OP_APP_ID_:
				return self::AppID();

			case self::_ADMIN_IP_:
			case self::_ADMIN_MAIL_:
				return self::$_env[$key];
		}

		//	...
		return Config::Get($key);
	}

	/** Set environment value.
	 *
	 * @deprecated 2023-04-12  Config::Get()
	 * @param string $key
	 * @param mixed  $var
	 */
	static function Set($key, $var)
	{
		//	...
		switch( $key ){
			case self::_ADMIN_IP_:
			case self::_ADMIN_MAIL_:

				/* Not work
				self::$_env[$key] = $var;
				*/

				//	...
				Config::Set('admin',[$key => $var]);

				//	...
				if( $key === self::_ADMIN_IP_ ){
					$label = '_ADMIN_IP_';
				}
				if( $key === self::_ADMIN_MAIL_ ){
					$label = '_ADMIN_MAIL_';
				}

				//	...
				D('Set(self::'.$label.') feature will deprecate. Set by asset:/config/admin.php file.');

			return true;
		}

		//	...
		return Config::Set($key, $var);
	}

	/** Get/Set language code.
	 *
	 * @deprecated 2020-10-31  self::Language()
	 * @param      string       $lang
	 * @return     string       $lang
	 */
	static function Lang($lang=null)
	{
		return self::Language($lang);
	}

	/** Get/Set Language.
	 *
	 * @created 2019-04-27
	 * @param   string     $lang
	 * @return  string     $lang
	 */
	static function Language($lang=null)
	{
		/*
		//	...
		if( $lang ){
			$cont = self::Country();
			self::$_env['locale'] = $lang.':'.$cont;
		}else{
			return explode(':', self::Locale())[0];
		};
		*/

		if( $lang ){
			Config::Set('locale', ['language'=>$lang]);
		}

		//	...
		return Config::Get('locale')['language'] ?? null;
	}

	/** Get/Set Country.
	 *
	 * @created 2019-04-29
	 * @param   string     $country
	 * @return  string     $country
	 */
	static function Country($country=null)
	{
		/*
		//	...
		if( $country ){
			$lang = self::Language();
			self::$_env['locale'] = $lang.':'.$country;
		}else{
			return strtoupper(explode(':',self::Locale())[1] ?? null);
		};
		*/

		if( $country ){
			Config::Set('locale', ['country'=>$country]);
		}

		//	...
		return Config::Get('locale')['country'] ?? null;
	}

	/** Get/Set Locale.
	 *
	 * @created 2019-04-27
	 * @param   string     $locale
	 * @return  string     $locale
	 */
	static function Locale($locale=null)
	{
		/*
		//	...
		if( $locale ){
			self::$_env['locale'] = $locale;
		};
		*/

		if( $locale ){
			/* @var $match array */
			if( preg_match('/([\w]+)([^\w])?([\w]*)/', $locale, $match) ){
				$config = [
					'language' => $match[0],
					'separate' => $match[1],
					'country'  => $match[2],
				];
				Config::Set('locale', $config);
			}
		}else{
			$counfig  = Config::Get('locale');
			$separate = $counfig['separate'] ?? ':';
			$language = $counfig['language'] ?? null;
			$country  = $counfig['country']  ?? null;
		}

		//	...
		return "{$language}{$separate}{$country}";

		/*
		//	...
		if( empty(self::$_env['locale']) ){
			$locale = Cookie::Get('locale') ?? self::$_env['g11n']['default'] ?? 'en:US';
		};

		//	...
		return $locale;
		*/
	}

	/** Get/Set charset.
	 *
	 * This charset is just html only.
	 * For developers charset is not yet consider.
	 * Source code is always UTF-8.
	 *
	 * @param  string $charset
	 * @return string $charset
	 */
	static function Charset($charset=null)
	{
		//	...
		if( $charset ){
			/*
			if( self::$_env['charset'] ){
				throw new \Exception("Charset was already set.");
			}else{
				self::$_env['charset'] = $charset;
			};
			*/
			self::$_env['charset'] = $charset;
		};

		//	...
		if( empty(self::$_env['charset']) ){
			self::$_env['charset'] = 'utf-8';
		};

		//	...
		return self::$_env['charset'];
	}

	/** Get mime from extension.
	 *
	 * @created   2019-12-16
	 * @param     string       $ext
	 * @return    string       $mime
	 */
	static function Ext(string $ext)
	{
		//	...
		switch($ext = strtolower($ext)){
			case 'php':
			case 'html':
			case 'phtml':
				$mime = 'text/html';
				break;

			case 'js':
				$mime = 'text/javascript';
				break;

			case 'css':
				$mime = 'text/css';
				break;

			default:
				$mime = 'text/plain';
		}

		//	...
		return $mime;
	}

	/** Get/Set MIME
	 *
	 * @param  string $mime
	 * @return string $mime
	 */
	static function Mime($mime=null)
	{
		//	...
		if( $mime ){
			/* @var $file null */
			/* @var $line null */
			if( headers_sent($file, $line) ){
				$meta    =  OP::MetaFromPath($file);
				$message = "Header has already sent. ($meta, $line)";
				Notice::Set($message);
			}

			//	...
			if( self::isHttp() ){
				//	...
				$header = "Content-type: $mime";

				//	...
				if( $charset = self::Charset()){
					$header .= "; charset={$charset}";
				}

				//	...
				header($header);
			}

			//	...
			self::$_env['mime'] = strtolower($mime);

			//	...
			if( self::$_env['mime'] !== 'text/html' ){
				self::$_env['layout']['execute'] = false;
			}
		}

		//	...
		return self::$_env['mime'] ?? null;
	}

	/** Get frozen unix time.
	 *
	 * @deprecated 2023-03-29  OP::Time()
	 * @param  boolean $utc
	 * @param  string  $time
	 * @return integer $time
	 */
	static function Time(      $utc=false,  string $time=''):int
	{
        return OP::Time($utc, $time);
	}

	/** Get local timezone timestamp.
	 *
	 * <pre>
	 * $localtime = Env::Timestamp();
	 * $utc       = Env::Timestamp(true);
	 * $offset    = Env::Timestamp(true, '-1 month');
	 * </pre>
	 *
	 * @deprecated 2023-03-29  OP::Timestamp()
	 * @created  2019-09-24
	 * @param    boolean     $utc
	 * @param    string      $offset
	 * @return   string      $timestamp
	 */
	static function Timestamp(      $utc=false, $offset=null):string
	{
        return OP::Timestamp($utc, $offset);
	}

	/** Get/Set App ID.
	 *
	 * @created  2019-09-13
	 * @param    string      $app_id
	 * @return   string      $app_id
	 */
	static function AppID($app_id=null)
	{
	//	There can not initialize AppID.
	//	return OP::Sandbox('asset:/config/app_id.php')['app_id'];
	//	return Config::Get( strtolower(_OP_APP_ID_) )['app_id'];

		//	Can initialize AppID only 1st call.
		if( $app_id ){
			//	...
			/* Need. CI is in use.
			Notice("Overwrite AppID is will deprecated.");
			*/

			//	...
			if( isset(self::$_env[_OP_APP_ID_]) ){
				//	...
				if( self::$_env[_OP_APP_ID_] !== $app_id ){
					throw new \Exception('AppID is already set. ('.self::$_env[_OP_APP_ID_].')');
				}
			}

			//	...
			self::$_env[_OP_APP_ID_] = $app_id;

			//	...
			Config::Set('app_id', ['app_id'=>$app_id]);
		}

		//	If not set app_id.
		if( empty(self::$_env[_OP_APP_ID_]) ){
			// Set by config file.
			self::$_env[_OP_APP_ID_] = Config::Get('app_id')['app_id'] ?? null;
		}

		//	...
		return self::$_env[_OP_APP_ID_] ?? null;
	}

	/** Get request value.
	 *
	 * <pre>
	 * Use to OP::Request().
	 * </pre>
	 *
	 * @deprecated 2022-10-28 <- Why? <- Move to OP::Request()
	 * @created   2020-05-04
	 * @param     string       $_key
	 * @param     mixed        $_default
	 * @return    mixed        $request
	 */
	static function Request($_key=null, $_default=null)
	{
		//	...
		static $_request = null;

		//	...
		if( $_request === null ){
			//	In case of shell.
			if( isset($_SERVER['argv']) ){
				//	CLI
				$_request = require_once(__DIR__.'/include/request_cli.php');
			}else{
				//	GET, POST, JSON, HTTP request headers.
				$_request = require_once(__DIR__.'/include/request_web.php');
			}

			/* Does not need.
			//	Why? --> Suppresses duplicate processing.
			if( $_request === null ){
				Notice('Returned $_request value is null. (Useless processing is repeated.)', debug_backtrace());
			}
			*/

			//	...
			$_request = Encode($_request);
		}

		//	...
		return empty($_key) ? $_request: ($_request[$_key] ?? $_default);
	}

	/** Get Admin IP address.
	 *
	 * @created   2021-03-09
	 * @return    NULL|string
	 */
	static function AdminIP()
	{
		return Config::Get('admin')[Env::_ADMIN_IP_] ?? null;
	}

	/** Get Admin EMail address.
	 *
	 * @created   2021-03-09
	 * @return    NULL|string
	 */
	static function AdminMail()
	{
		return Config::Get('admin')[Env::_ADMIN_MAIL_] ?? null;
	}
}
