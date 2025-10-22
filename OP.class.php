<?php
/** op-core:/OP.class.php
 *
 * @created   2022-09-30
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
 */
namespace OP;

/** OP
 *
 * @created   2018-04-04
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class OP
{
	/** trait.
	 *
	 */
	use OP_CORE, OP_OBJECT, OP_FUNCTION, OP_ENV, OP_CI {
        OP_FUNCTION::__call       insteadof OP_CORE;
		OP_FUNCTION::__callstatic insteadof OP_CORE;
	}
}

/** OP_OBJECT
 *
 * @created   2022-10-05
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
trait OP_OBJECT
{
	/** Router unit.
	 *
	 * @created   2022-09-30
	 * @return   &UNIT\Router
	 */
	static function & Router()
	{
		return Unit::Singleton('Router');
	}

	/** Template is Sandbox.
	 *
	 * <pre>
	 * //  Passed path is execute core Template function.
	 * OP::Template($path);
	 *
	 * //  No argument is return the Unit of Template.
	 * OP::Template()->Get($path);
	 * </pre>
	 *
	 * @created   2022-10-04
	 * @return    UNIT\Template
	 */
	static function Template(string $path=null, array $args=[])
	{
		//	...
		if( $path ){
			require_once('function/Template.php');
			return Template($path, $args);
		}

		//	...
		return Unit::Singleton('Template');
	}

	/** Layout unit.
	 *
	 * @created   2022-10-04
	 * @return    UNIT\Layout
	 */
	static function /* & */ Layout($value=null)
	{
		//	...
		if( $value !== null ){
			require_once('function/Layout.php');
			return Layout($value);
		}

		//	...
		return Unit::Singleton('Layout');
	}

	/** Notice
	 *
	 * @created    2022-10-06
	 * @param      string     $message
	 * @return     \OP\Notice
	 */
	static function Notice($message=null)
	{
		//	...
		static $_notice;

		//	...
		if( $message ){
			return Notice::Set($message);
		}

		//	...
		if(!$_notice ){
			$_notice = new Notice();
		}

		//	...
		return $_notice;
	}

	/** Meta path
	 *
	 * <pre>
	 * // Set meta path.
	 * OP()->MetaPath()->Set('hoge', '/var/www/htdocs/hoge/');
	 *
	 * // Get meta path to full path.
	 * $full_path = OP()->MetaPath('hoge:/foo/bar'); -> /var/www/htdocs/hoge/foo/bar/
	 *
	 * // Get full path to meta path.
	 * $meta_path = OP()->MetaPath('/var/www/htdocs/hoge/foo/bar'); -> hoge:/foo/bar/
	 *
	 * // Get document root path from meta path.
	 * $url_path  = OP()->MetaPath('hoge:/foo/bar?key=value', true); -> /hoge/foo/bar/?key=value
	 * </pre>
	 *
	 * @created    2022-10-16
	 * @param      string     $meta
	 * @param      bool       $url
	 * @throws    \Exception
	 * @return    \OP\MetaPath
	 */
	static function MetaPath(?string $path=null, ?bool $url=null)
	{
		//	...
		static $_meta_path;

		//	...
		if( $path ){
			$path = trim($path);

			//	Full path to meta path
			if( $path[0] === '/' ){
				//	Full path to URL is not support.
				if( $url ){
					throw new \Exception("Full path to URL is not support. ($path)");
				}
				return MetaPath::Encode($path);
			}

			/*
			//	Meta path to full path
			if( strpos($path, ':/') ){
				//	If URL
				if( $url ){
					return MetaPath::URL($path);
				}
				//	to Full path.
				return MetaPath::Decode($path);
			}
			*/

			//	Decode meta path.
			if( $url ){
				// to URL
				return MetaPath::URL($path);
			}else{
				// to File path.
				return MetaPath::Decode($path);
			}
		}

		//	...
		if(!$_meta_path ){
			$_meta_path = new MetaPath();
		}

		//	...
		return $_meta_path;
	}

	/** Config
	 *
	 * <pre>
	 * // Get config by name. file is "asset:/config/name.php".
	 * $config = OP::Config('name');
	 * // Set config by name.
	 * OP::Config('name', ['key'=>'value']);
	 * </pre>
	 *
	 * @created    2022-11-01
	 * @param      string     $name
	 * @param      array      $config
	 * @return
	 */
	static function Config(?string $name=null, ?array $config=null)
	{
		//	...
		if( $name ){
			if( $config ){
				return Config::Set($name, $config);
			}else{
				return Config::Get($name);
			}
		}else{
			static $_config;
			if(!$_config ){
				$_config = new Config();
			}
			return $_config;
		}
	}

    /** Env
     *
     * @created     2023-04-26
     * @return      Env
     */
    static function & Env()
    {
        static $_env;
        if(!$_env ){
            $_env = new Env();
        }
        return $_env;
    }

	/** App
	 *
	 * @return \OP\UNIT\App
	 */
	static function & App() : IF_APP
	{
		return Unit::Singleton('App');
	}

	/** WebPack
	 *
	 * @return IF_WEBPACK
	 */
	static function & WebPack() : IF_WEBPACK
	{
		return Unit::Singleton('WebPack');
	}
} // OP_OBJECT

/** OP_FUNCTION
 *
 *  Wrapper functions. auto load function/*.php.
 *
 * @created   2022-10-05
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
trait OP_FUNCTION
{
    /** An undefined method was called.
     *
     * @created     2022-11-12
     * @param       string      $method_name
     * @param       array       $args
     * @return      mixed
     */
    function __call($method_name, $args)
    {
        return self::_Function($method_name, ...$args);
    }

	/** An undefined method was called.
	 *
	 * @created   2022-10-11
	 * @param     string $method_name
	 * @param     array  $args
	 */
	static function __callstatic($method_name, $args)
	{
		return self::_Function($method_name, ...$args);
	}

	/** _Function
	 *
	 * @created   2022-10-05
	 * @param     string     $function
	 * @param     variable variable
	 * @return    null|boolean|string|array|object
	 */
	static private function _Function(string $function, ...$args)
	{
		//	...
		if(!function_exists('OP\\'.$function) ){
			$path = __DIR__."/function/{$function}.php";
			if( file_exists( $path) ){
				require_once($path);
			}else{
				Notice("This function is not implemented. ($function)");
				return;
			}
		}

		//	...
		$function = 'OP\\'.$function;

		//	...
		return $function( ...$args );
	}

	/** Encode to html entities.
	 *
	 * @created    2022-11-12
	 * @param      mixed      $value
	 * @return     mixed
	 */
	static function Encode($value)
	{
		return self::_Function(__FUNCTION__, $value);
	}

	/** Decode from html entities.
	 *
	 * @created    2022-11-12
	 * @param      mixed      $value
	 * @return     mixed
	 */
	static function Decode($value)
	{
		return self::_Function(__FUNCTION__, $value);
	}

	/** Return unit instance (Singleton)
	 *
	 * @created   2022-10-07
	 * @param     string     $unit_name
	 * @return   \OP\Unit
	 */
	static function & Unit(string $unit_name)
	{
		//	...
		if( $unit_name ){
			return Unit::Singleton($unit_name);
		}

		//	...
		static $_unit;
		if(!$_unit ){
			$_unit = new Unit();
		}
		return $_unit;
	}

	/** Set / Get meta root path.
	 *
	 * <pre>
	 * //  Set meta root path.
	 * OP::RootPath('foo', __DIR__.'/foo/');
	 *
	 * //  Get meta root path.
	 * $path = OP::RootPath('foo');
	 * </pre>
	 *
	 * @deprecated 2024-01-08
	 * @created    2022-10-10
	 * @param      string     $meta_label
	 * @return     string     $full_path
	 */
	static function MetaRoot(string $meta_label='', string $full_path='')
	{
		return self::_Function('RootPath', $meta_label, $full_path);
	}

	/** Convert to document root path from meta path.
	 *
	 * <pre>
	 * $document_root_url = OP::ConvertURL('app:/foo/bar');
	 * </pre>
	 *
	 * @deprecated 2022-10-16
	 * @created    2022-10-08
	 * @param      string     $meta_path
	 * @return     string     $full_path
	 */
	static function MetaToURL(string $path)
	{
		return self::_Function('ConvertURL', $path);
	}

	/** Convert to full path from meta path.
	 *
	 * <pre>
	 * $full_path = OP::ConvertPath('app:/foo/bar');
	 * </pre>
	 *
	 * @deprecated 2022-10-16
	 * @created    2022-10-05
	 * @param      string     $meta_path
	 * @return     string     $full_path
	 */
	static function MetaToPath(string $path, $throw_exception=true)
	{
		return self::_Function('ConvertPath', $path, $throw_exception);
	}

	/** Convert to meta path from full path.
	 *
	 * <pre>
	 * $full_path = OP::ConvertPath('app:/foo/bar');
	 * </pre>
	 *
	 * @deprecated 2022-10-16
	 * @created    2022-10-08
	 * @param      string     $meta_path
	 * @return     string     $full_path
	 */
	static function MetaFromPath(string $path)
	{
		return self::_Function('CompressPath', $path);
	}

	/** Parse URL
	 *
	 * @created    2022-10-23
	 * @param      string     $string
	 * @return     array
	 */
	static function ParseURL(string $url)
	{
		return include(__DIR__.'/include/ParseURL.php');
	}

	/** DebugBacktrace array is convert to string.
	 *
	 * @created    2022-10-31
	 * @param      array
	 * @return     string
	 */
	static function DebugBacktraceToString(array $trace) : string
	{
        /*
		return include(__DIR__.'/include/DebugBacktraceToString.php');
        */
        return DebugBacktrace::Numerator($trace);
	}

    /** Get template.
     *
     * @created     2023-04-26
     * @param       string      $path
     * @return      string      $content
     */
    static function GetTemplate(string $path) : string
    {
        require_once(__DIR__.'/function/GetTemplate.php');
        return GetTemplate($path);
    }
} // OP_FUNCTION

/** OP_ENV
 *
 *  Move from Env.
 *
 * @created   2022-10-05
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
trait OP_ENV
{
	/** Return GET/POST method or
	 *
	 * <pre>
	 * OP::Request(){
	 *   return Env::Request();
	 * }
	 * </pre>
	 *
	 * @created    2022-10-28
	 * @param      string     $key
	 * @param      mixed      $default
	 * @return
	 */
	static function Request(?string $key=null, $default=null)
	{
		return Env::Request($key, $default);
	}

	/** Get AppID and Can set AppID only 1st time.
	 *
	 * <pre>
	 * OP::AppID(){
	 *   return Env::AppID();
	 * }
	 * </pre>
	 *
	 * @created    2022-11-14
	 * @param      string     $app_id
	 * @return     string
	 */
	static function AppID(?string $app_id=null)
	{
		return Env::AppID($app_id);
	}

    /** Get frozen unix time.
     *
     * @created  ????-??-??
     * @moved    2023-03-29  \OP\Env::Time()
     * @param    boolean     $utc
     * @param    string      $time
     * @return   integer     $time
     */
    static function Time(?bool $utc=false, ?string $time=''):int
    {
        require_once(__DIR__.'/function/Time.php');
        return Time($utc, $time);
    }

    /** Get local timezone timestamp.
     *
     * <pre>
     * $localtime = Env::Timestamp();
     * $utc       = Env::Timestamp(true);
     * $offset    = Env::Timestamp(true, '-1 month');
     * </pre>
     *
     * @created  2019-09-24
     * @moved    2023-03-29  \OP\Env::Timestamp()
     * @param    boolean     $utc
     * @param    string      $offset
     * @return   string      $timestamp
     */
    static function Timestamp(?bool $utc=false, $offset=null):string
    {
        require_once(__DIR__.'/function/Timestamp.php');
        return Timestamp($utc, $offset);
    }

    /** Get / Set MIME
     *
     * @deprecated  2024-04-08  OP()->Env()->MIME();
     * @created     2023-04-15
     * @param      ?string      $mime
     * @return      string
     */
    static function MIME(?string $mime=null) : string
    {
        return Env::MIME($mime);
    }
} // OP_ENV
