<?php
/** op-core:/function/GetTemplate.php
 *
 * @created   2020-05-10
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 */
namespace OP;

/** Template
 *
 * @created   2020-05-10
 * @param     string       $function
 * @param     array        $args
 * @throws   \Exception
 * @return    string
 */
function GetTemplate(string $path, array $args=[]):string
{
	//	...
	if(!ob_start()){
		throw new \Exception('ob_start was failed.');
	}

	//	...
	Template($path, $args);

	//	...
	return ob_get_clean();
}
