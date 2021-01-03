<?php
/** op-core:/Bootstrap.php
 *
 * @created   2015-12-10   op-core(5)
 * @updated   2016-06-09   op-core(7)
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** Checking PHP version.
 *
 */
if( version_compare(PHP_VERSION, '7.0.0') < 0 ){
	exit('<p>onepiece-framework is not support to this php version.('.PHP_VERSION.')</p>');
}

/** Session management.
 *
 *  1. Auto start session.
 *  2. Separate session ID for each PHP version.
 *  3. SameSite support.
 *
 */
if(!session_id() and empty($_SERVER['SHELL']) ){

	/** For SameSite
	 *
	 */
	//	Get current values.
	$params   = session_get_cookie_params();
	//	Check if https.
	$secure   = $_SERVER['REQUEST_SCHEME']==='https' ? true: false;
	//	Always true.
	$httponly = true;
	//	None is required for https.
	$samesite = $secure ? 'None':'Lax';

	//	Branch by PHP version.
	if( version_compare(PHP_VERSION, '7.3.0') <= 0 ){
		//	Under 7.3
		$params['path'] .= "; SameSite={$samesite}";
		session_set_cookie_params($params['lifetime'], $params['path'], $params['domain'], $secure, $httponly);
	}else{
		//	Upper 7.3
		$params['secure']   = $secure;
		$params['httponly'] = $httponly;
		session_set_cookie_params($params);
	}

	//	Get default session name.
	$name = session_name();

	//	Added PHP version for run different versions of PHP-FPM at the same time.
	session_name($name .'_'. PHP_VERSION_ID);

	//	Start session.
	if(!session_start() ){
		exit("<p>Session start was failed.</p>");
	}
}

/** Register autoloader.
 *
 */
include_once(__DIR__.'/Autoloader.php');

/** Include Error hendler.
 *
 */
include_once(__DIR__.'/Error.php');

/** Include defines.
 *
 */
include_once(__DIR__.'/Defines.php');

/** Include custome functions.
 *
 */
include_once(__DIR__.'/Functions.php');

/** Include OP CORE.
 *
 */
include_once(__DIR__.'/trait/OP_CORE.php');

/** Include JSON submitted process.
 *
 */
include_once(__DIR__.'/include/json.php');
