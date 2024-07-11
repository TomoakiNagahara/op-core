<?php
/** op-core:/OP_SESSION.php
 *
 * @created   2019-04-10
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @created   2019-04-10
 */
namespace OP;

/** OP_SESSION
 *
 * @created   2019-04-10
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
trait OP_SESSION
{
	/** Get/Set Session
	 *
	 * @created   ????-??-??
	 * @updated   2019-04-10
	 * @return array
	 */
	static function & Session($key=null, $val=null)
	{
		//	...
		$app_id  = Env::AppID();

		//	OP\UNIT\UnitName --> ['OP','UNIT','UnitName']
		$explode = explode('\\', get_called_class());

		//	OP\ClassName --> ['OP','CORE','ClassName']
		if( count($explode) === 2 ){
			$explode[2] = $explode[1];
			$explode[1] = 'CORE';
		}

		//	OP --> NAME_SPACE
		$explode[0] = _OP_NAME_SPACE_;

		//	Reference
		$session = & $_SESSION[$explode[0]][$explode[1]][$explode[2]][$app_id];

		//	If passed assoc key name.
		if( $key ){
			//	If not passed value.
			if( $val !== null ){
				$session[$key] = $val;
			};
		};

		//	...
		if( $key ){
			return $session[$key];
		}else{
			return $session;
		};
	}
}
