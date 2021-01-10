<?php
/** op-core:/function/E.php
 *
 * @created   2021-01-09
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

/** E is error dump.
 *
 *  If access is admin, show dump.
 *  If access is not admin, Throw Notice.
 *
 */
function E(){
	//	Switch
	if( Env::isAdmin() ){
		D(func_get_args());
	}else{
		Notice(func_get_args()[0]);
	};
}
