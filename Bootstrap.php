<?php
/**
 * Bootstrap.php
 * 
 * @creation  2015-12-10
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

//	Added float format request time.
if( empty($_SERVER['REQUEST_TIME_FLOAT']) ){
	$_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
}

//	Deactivates the circular reference collector.
if( $_SERVER['REMOTE_ADDR'] === '127.0.0.1' ){
	gc_disable();
}

//	Check mbstring installed.
if(!function_exists('mb_language') ){
	include(__DIR__.'/Template/introduction-php-mbstring.phtml');
	exit;
}

//	If time zone is not set, then set to UTC.
if(!ini_get('date.timezone')){
	date_default_timezone_set('UTC');
}

//	Security: PHP_SELF has XSS risk.
$_SERVER['PHP_SELF_XSS'] = htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES);
$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];

//	OP_ROOT
$op_root = $_SERVER['OP_ROOT'] = __DIR__.'/';

//	APP_ROOT
$app_root = $_SERVER['APP_ROOT'] = dirname($_SERVER['SCRIPT_FILENAME']).'/';

//	Register autoloader.
include('Autoloader.class.php');
if(!spl_autoload_register('Autoloader::Autoload',true,true)){
	function __autoload($class){
		Autoloader::Autoload($class);
	}
}

//	Init Env
Env::Bootstrap();

//	Register shutdown function
register_shutdown_function('Env::Shutdown');

//	Set error heandler
$level = Toolbox::isLocalhost() ? E_ALL | E_STRICT: error_reporting();
set_error_handler('OP\Error::Handler',$level);

//	Set exception handler
set_exception_handler('OP\Error::ExceptionHandler');

//	Checking error reporting administrator's E-Mail address.
$admin_mail = isset($_SERVER['SERVER_ADMIN']) ? $_SERVER['SERVER_ADMIN']: null;
if( $admin_mail ){
	Env::Set('admin-mail', $admin_mail);
}else{
	OnePiece5::AdminNotice('$_SERVER["SERVER_ADMIN"] is empty.');
}
