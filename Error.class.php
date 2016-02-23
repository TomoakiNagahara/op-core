<?php
/**
 * Error.class.php
 * 
 * @creation  2014-11-29
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/**
 * Added namespace at 2016-01-16
 */
namespace OP;

/**
 * Error
 * 
 * @creation  2014-02-18
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Error
{
	/**
	 * Stack for notice message.
	 * 
	 * @var array
	 */
	static private $_error;

	/**
	 * For developer.
	 */
	static public function Debug()
	{
	//	var_dump(self::$_error);
		foreach( self::$_error as $error ){
			foreach( $error as $key => $var ){
				switch($key){
					case 'message':
						print "$key => $var<br/>\n";
					default:
				}
			}
		}
	}

	/**
	 * Stacking error information.
	 * 
	 * @param string $message
	 * @param array  $backtrace
	 * @param string $lang langugae code
	 */
	static private function _Set($message, $backtrace=null, $lang=false)
	{
		$key = sha1($message);
		$key = substr($key, 0, 8);

		if( self::$_error === null ){
			self::$_error = &$_SESSION[__CLASS__];
		}

		if(!$backtrace){
			$backtrace = debug_backtrace();
		}

		$error['message']	 = htmlentities($message, ENT_QUOTES, 'utf-8');
		$error['backtrace']	 = $backtrace;
		$error['timestamp']	 = date('Y-m-d H:i:s');
		$error['lang']		 = $lang;

		self::$_error[$key] = $error;
	}

	/**
	 * Set admin notice message. (For external class interface)
	 * 
	 * @param \Exception $e
	 * @param string $lang
	 */
	static public function Set($e, $lang=null)
	{
		if( $e instanceof Exception ){
			$message   = $e->getMessage();
			$backtrace = $e->getTrace();
			$file      = $e->getFile();
			$line      = $e->getLine();
			$prev      = $e->getPrevious();
			$code      = $e->getCode();
			if( method_exists($e,'getLang') ){
				$lang = $e->getLang();
			}
		}else{
			$message = $e;
			$backtrace = debug_backtrace();
		}
		self::_Set($message, $backtrace, $lang);
	}

	/**
	 * Get an admin notice message. (only one message)
	 * 
	 * @return array
	 */
	static public function Get()
	{
		if( empty(self::$_error) ){
			return null;
		}
		return array_shift(self::$_error);
	}

	/**
	 * Get all admin notice message
	 * 
	 * @return array
	 */
	static public function GetAll()
	{
		$errors = null;
		while( $error = self::Get() ){
			$errors[] = $error;
		}
		return $errors;
	}

	/**
	 * Do reporting.
	 * 
	 * @return boolean
	 */
	static function Report()
	{
		if( empty(self::$_error) ){
			return;
		}
		\Notice::Report();
	}

	static function ConvertStringFromErrorNumber( $number )
	{
		/* @see http://www.php.net/manual/ja/errorfunc.constants.php */
		switch( $number ){
			case E_ERROR:	// 1
				$type = 'E_ERROR';
				break;
					
			case E_WARNING:	// 2
				$type = 'E_WARNING';
				break;
					
			case E_PARSE:	// 4
				$type = 'E_PARSE';
				break;
					
			case E_NOTICE:	// 8
				$type = 'E_NOTICE';
				break;
				
			case E_COMPILE_ERROR:		// 64
				$type = 'E_COMPILE_ERROR';
				break;
				
			case E_COMPILE_WARNING:		// 128
				$type = 'E_COMPILE_WARNING';
				break;
				
			case E_USER_ERROR:			// 256
				$type = 'E_USER_ERROR';
				break;
				
			case E_USER_WARNING:		// 512
				$type = 'E_USER_WARNING';
				break;
				
			case E_USER_NOTICE:			// 1024
				$type = 'E_USER_NOTICE';
				break;
				
			case E_STRICT:				// 2048
				$type = 'E_STRICT';
				break;
				
			case E_RECOVERABLE_ERROR:	// 4096
				$type = 'E_RECOVERABLE_ERROR';
				break;
				
			case E_DEPRECATED:			// 8192
				$type = 'E_DEPRECATED';
				break;
				
			case E_USER_DEPRECATED:		// 16384
				$type = 'E_USER_DEPRECATED';
				break;
				
			default:
				$type = $number;
		}
		
		return $type;
	}

	/**
	 * Method was not exist.
	 * 
	 * @param string $class class name
	 * @param string $name  method name
	 * @param array  $args  argument
	 * @param string $call  Caller line
	 */
	static function MagicMethodCall($class, $name, $args, $call)
	{
		//  If Toolbox method.
		if( method_exists('\Toolbox', $name) and false ){
			\OnePiece5::Mark("Please use Toolbox::$name, $call");
			return \Toolbox::$name(
				isset($args[0]) ? $args[0]: null,
				isset($args[1]) ? $args[1]: null,
				isset($args[2]) ? $args[2]: null,
				isset($args[3]) ? $args[3]: null,
				isset($args[4]) ? $args[4]: null,
				isset($args[5]) ? $args[5]: null
			);
		}

		$supplement = $class ? "$class->$name ": null;
		$supplement.= "method=$name, call=$call";

		$message = "This method was not exist. \($supplement)\\";
		$backtrace = debug_backtrace();
		self::_Set($message, $backtrace, 'en');
	}

	/**
	 * Method was not exist.
	 * 
	 * @param string $class class name
	 * @param string $name  method name
	 * @param array  $args  argument
	 * @param string $call  Caller line
	 */
	static function MagicMethodCallStatic($class, $name, $args, $call)
	{
		//	Call static is PHP 5.3.0 later
		self::MagicMethodCall($class, $name, $args, $call);
	}

	/**
	 * Can not be accessible.
	 * 
	 * @param string $class class name
	 * @param string $name  method name
	 * @param array  $args  argument
	 * @param string $call  caller line
	 */
	static function MagicMethodSet( $class, $name, $args, $call )
	{
		$message = "\\{$class}::{$name}\ can not be accessible. \({$call})\\";
		$backtrace = debug_backtrace();
		self::_Set($message, $backtrace, 'en');
	}

	/**
	 * Can not be accessible.
	 * 
	 * @param string $class class name
	 * @param string $name  method name
	 * @param string $call  caller line
	 */
	static function MagicMethodGet( $class, $name, $call )
	{
		$message = "\\{$class}::{$name}\ can not be accessible. \({$call})\\";
		$backtrace = debug_backtrace();
		self::_Set($message, $backtrace, 'en');
	}

	/**
	 * The last error that occurred.
	 * 
	 * @param mixed $e
	 */
	static function LastError($e)
	{
		self::Set($e);
	}

	/**
	 * This method has been transferred from OnePiece5::ErrorHandler.
	 * 
	 * @param  integer $type
	 * @param  string  $str
	 * @param  string  $file
	 * @param  integer $line
	 * @param  unknown $context
	 */
	static function Handler($type, $str, $file, $line, $context)
	{
		$type = self::ConvertStringFromErrorNumber($type);
	//	$backtrace = debug_backtrace();

		//	Get route table.
		if(!$route = \Env::Get('route')){
			$route['mime'] = 'text/plain';
		}

		//	Get mime
		if( empty($route['mime']) ){
			$route['mime'] = 'text/plain';
		}

		//	Generate error format.
		$base = '%s [%s] %s: %s';
		switch( $mime = strtolower($route['mime']) ){
			case 'text/html':
				$format = "<div>$base</div>";
				break;
			case 'text/css':
			case 'text/javascript':
				$format = "/* $base */";
				break;
			default:
				$format = $base;
		}

		//  check ini setting
		if( ini_get( 'display_errors') ){
			printf( $format."\n", $file, $line, $type, $str );
		}
	}
	
	/**
	 * This method has been transferred from OnePiece5::ErrorExceptionHandler.
	 * 
	 * @param OpException $e
	 */
	static function ExceptionHandler($e)
	{
		self::Set($e);
	}
}
