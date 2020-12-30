<?php
/** op-core:/function/Args.php
 *
 * @created   2020-12-30
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

/** Return \OP\Unit('Router')->Args();
 *
 * @created   2020-12-30
 * @return    array        $args
 */
function Args(){
	return \OP\Unit('Router')->Args();
}
