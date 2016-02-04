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
		//	\OnePiece5::D(self::$_error);
		foreach( self::$_error as $error ){
			foreach( $error as $key => $var ){
				switch($key){
					case 'message':
						print "$key => $var\n";
					default:
				}
			}
		}
	}

	/**
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

		$error['message']	 = $message;
		$error['backtrace']	 = $backtrace;
		$error['timestamp']	 = date('Y-m-d H:i:s');
		$error['lang']		 = $lang;

		self::$_error[$key] = $error;
	}

	/**
	 * Set admin notice message.
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

		if( $lang !== false ){
			$message = \OnePiece5::i18n()->Get($message, $lang);
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
		return array_shift(self::$_error);
	}

	/**
	 * Get all admin notice message
	 * 
	 * @return array
	 */
	static public function GetAll()
	{
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

	static function MagicMethodCall( $class, $name, $args )
	{
		//  If Toolbox method.
		if( method_exists('\Toolbox', $name) and false ){
			\OnePiece5::Mark("Please use Toolbox::$name");
			return \Toolbox::$name(
				isset($args[0]) ? $args[0]: null,
				isset($args[1]) ? $args[1]: null,
				isset($args[2]) ? $args[2]: null,
				isset($args[3]) ? $args[3]: null,
				isset($args[4]) ? $args[4]: null,
				isset($args[5]) ? $args[5]: null
			);
		}
		
		$message = "This method does not exists in class.".PHP_EOL."\ - {$class}::{$name}\\";
		self::_Set( $name, $message, null, 'en' );
	}
	
	static function MagicMethodCallStatic( $class, $name, $args )
	{
		//	Call static is PHP 5.3.0 later
		self::MagicMethodCall( $class, $name, $args );
	}
	
	static function MagicMethodSet( $class, $name, $args, $call )
	{
		$message = \OnePiece5::i18n()->En("\\{$class}::{$name}\ can not be accessible.");
		$message.= " ({$call}, value={$args})";
		\OnePiece5::AdminNotice($message);
	}
	
	static function MagicMethodGet( $class, $name, $call )
	{
		$message = \OnePiece5::i18n()->En("\\{$class}::{$name}\ can not be accessible.");
		$message.= " ({$call})";
		\OnePiece5::AdminNotice($message);
	}
	
	static function LastError( $e )
	{
		$file	 = $e['file'];
		$line	 = $e['line'];
		$type	 = $e['type'];
		$message = $e['message'];
		
		$type = self::ConvertStringFromErrorNumber($type);
		
		\OnePiece5::AdminNotice("$file [$line] $type: $message");
	}
	
	/**
	 * This method has been transferred from OnePiece5::ErrorHandler.
	 * 
	 * @param  integer $type
	 * @param  string  $str
	 * @param  string  $file
	 * @param  integer $line
	 * @param  unknown $context
	 * @return boolean
	 */
	static function Handler( $type, $str, $file, $line, $context )
	{
		$type = self::ConvertStringFromErrorNumber($type);
		
		//	Get route table.
		if(!$route = \Env::Get('route')){
			$route['mime'] = 'text/plain';
			\OnePiece5::AdminNotice("Route table has not been set.");
		}
		
		//	Get mime
		if( empty($route['mime']) ){
			$route['mime'] = 'text/plain';
			\OnePiece5::AdminNotice("MIME has not been set in the Route table.");
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
		
		return true;
	}
	
	/**
	 * This method has been transferred from OnePiece5::ErrorExceptionHandler.
	 * 
	 * @param OpException $e
	 */
	static function ExceptionHandler( $e )
	{
		$class	 = get_class($e);
		$file	 = $e->GetFile();
		$line	 = $e->GetLine();
		$message = $e->GetMessage();
		$lang	 = null;
		
		//	get language of message's original language.
		if( method_exists($e,'getLang') ){
			$lang = $e->getLang();
		}
		
		//	to translate
		if( $lang ){
			$message = \OnePiece5::i18n()->En($message);
		}
		
		//	join
		$error = "$class: $file [$line] $message";
		\OnePiece5::AdminNotice($error,$lang);
	}
}
