<?php
/** op-core:/function/encode.php
 *
 * @created   2019-12-20
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @created   2019-12-20
 */
namespace OP;

/** Encode mixed value.
 *
 * @created
 * @moved     2019-12-20   From functions.php
 * @param     mixed        $var
 * @param     string       $charset
 * @return    mixed        $var
 */
function Encode($value, $charset=null)
{
	//	...
	if(!$charset ){
		$charset = Env::Charset();
	}

	//	...
	switch( gettype($value) ){
		case 'string':
			$value = str_replace("\0", '\0', $value);
			return htmlentities($value, ENT_QUOTES, $charset, false);

		case 'array':
			$result = [];
			foreach( $value as $key => $val ){
				$key = is_string($key) ? Encode($key, $charset): $key;
				$val = Encode($val, $charset);
				$result[$key] = $val;
			}
			$value = $result;
			break;

		case 'object':
			/*
			D("Objects are not yet supported.");
			*/
			$value = get_class($value);
			break;

		default:
	}

	//	...
	return $value;
}
