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
	php_mbstring();
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
set_error_handler('Error::Handler',$level);

//	Set exception handler
set_exception_handler('Error::ExceptionHandler');

//	Checking error reporting administrator's E-Mail address.
if(!$admin_mail = Env::Get('admin-mail') ){
	OnePiece5::StackError("SERVER_ADMIN is empty.");
}else{
	Validator::isEmail($admin_mail);
}

return true;

function php_mbstring(){
	
	print '<h1>Does not install php-mbstring module.</h1>';
	print '<p>Please install php-mbstring module.</p>';
	print '<pre style="margin:0 1em; padding:0.5em; background-color:#ccc;"><code>';
	
	switch (true) {
		case stristr(PHP_OS, 'DAR'):
			php_mbstring_osx();
			break;
		case stristr(PHP_OS, 'WIN'):
			php_mbstring_win();
			break;
		case stristr(PHP_OS, 'LINUX'):
			php_mbstring_linux();
			break;
		default:
			php_mbstring_other();
		break;
	}

	print '</code></pre>';
	print '<hr/>';
	print '<p style="color:#ccc;">'.__FILE__.' ('.__LINE__.')</p>';
}

function php_mbstring_osx(){
	print <<< "EOL"
sudo port install php-mbstring
sudo /opt/local/apache2/bin/apachectl restart
EOL;
}

function php_mbstring_win(){

}

function php_mbstring_linux(){
	print <<< "EOL"
sudo yum install php-mbstring
sudo service httpd restart
EOL;
}

function php_mbstring_other(){
	
}
