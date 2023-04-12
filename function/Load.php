<?php
/** op-core:/function/Load.php
 *
 * @created   2020-05-08
 * @package   op-core
 * @version   1.0
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 */
namespace OP;

/** Load function, require once.
 *
 * @param string $function
 */
function Load(string $function)
{
	//	Deny relative path
	if( strpos($function, '/') ){
		throw new \Exception("Deny relative path. ($function)");
	}

	//	...
	$root = RootPath('asset');

	//	...
	if( file_exists($path = "{$root}function/{$function}.php") ){
		require_once($path);
	}else

	//	...
	if( file_exists($path = "{$root}core/function/{$function}.php") ){
		require_once($path);
	}else

	//	...
	if( true ){
		throw new \Exception("This function file cannot be found. ($function)");
	}
}
