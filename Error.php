<?php
/** op-core:/Error.php
 *
 * @created   2014-02-18
 * @updated   2016-12-07
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** Error report settings.
 *
 * To display error log to until startuped of the onepiece-framework.
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

/** Catch standard error.
 *
 * @see   http://php.net/manual/ja/function.restore-error-handler.php
 * @param integer $errno
 * @param string  $error
 * @param string  $file
 * @param integer $line
 * @param array   $context
 */
set_error_handler( function($errno, $error, $file, $line, $context=null)
{
	//	...
	if( class_exists('OP\Notice', true) ){
		OP\Notice::Set($error, debug_backtrace());
	}else{
	//	register_shutdown_function(function($error){
			if( OP\Env::Mime() === 'text/html' ){
				var_dump($error, debug_backtrace());
			}else{
				print_r($error);
				print_r(debug_backtrace());
			};
	//	}, $error);
	};

	//	...
	return true;
}, E_ALL);

/** Catch of uncaught error.
 *
 * @param \Throwable $e
 */
set_exception_handler(function( \Throwable $e)
{
	//	...
	$backtrace = [];
	$backtrace['file']		 = $e->getFile();
	$backtrace['line']		 = $e->getLine();
	$backtrace['function']	 = null;

	//	...
	$backtraces = $e->getTrace();

	//	...
	switch( $backtraces[0]['function'] ){
		case 'include':
		case 'require':
		case 'include_once':
		case 'require_once':
			if( empty($backtraces[0]['args']) ){
				$backtraces[0]['args'][] = $backtrace['file'];
			}
			break;
	}

	//	...
	array_unshift($backtraces, $backtrace);

	//	...
	if( class_exists('OP\Notice', true) ){
		OP\Notice::Set($e->getMessage(), $backtraces);
	}else{
		var_dump($e->getMessage() .', '. $e->getFile() .', '. $e->getLine(), $backtraces);
	};
});

/** Called back on shutdown.
 *
 * @see http://www.php.net/manual/ja/function.pcntl-signal.php
 */
register_shutdown_function(function()
{
	//	...
	if( $error = error_get_last() ){

		//	...
		if( class_exists('OP\Notice', true) ){
			OP\Notice::Set($error, debug_backtrace());
		}else{
			var_dump($error, debug_backtrace());
		};
	}

	//	...
	return true;
});
