<?php
/** op-core:/function/Layout.php
 *
 * @created   2021-01-10
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 */
namespace OP;

/** use
 *
 */

/** Get/Set Layout config value.
 *
 * @created   2021-01-10
 * @param     null|boolean|string $value  is execute flag or layout name.
 * @return    boolean|string      $result is execute flag or layout name.
 */
function Layout($value=null){
	//	...
	if( $value !== null ){
		//	...
		if( is_string($value) ){
			//	...
			Env::Set('layout',['name'=>$value]);
			$value = true;
		}

		//	...
		if( is_bool($value) ){
			//	...
			Env::Set('layout',['execute'=>$value]);
		}
	}

	//	...
	$config = Env::Get('layout');

	//	...
	return $config['execute'] ? $config['name'] : false;
}
