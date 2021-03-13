<?php
/** op-core:/function/GetExtension.php
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

/** Get extension.
 *
 * @param  string $file
 * @return string $extension
 */
function GetExtension(string $file):string
{
	//	...
	if( $pos  = strpos($file, '?') ){
		$file = substr($file, 0, $pos);
	}

	//	...
	if(!$pos = strrpos($file, '.') ){
		return false;
	}

	//	...
	return substr($file, $pos+1);
}
