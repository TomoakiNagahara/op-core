<?php
/** op-core:/function/Charset.php
 *
 * @created   2021-05-12
 * @package   op-core
 * @version   1.0
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 */
namespace OP;

/** Charset
 *
 */
function Charset( string $charset=null)
{
	return Env::Charset($charset);
}
