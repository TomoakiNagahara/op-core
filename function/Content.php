<?php
/** op-core:/function/Content.php
 *
 * @created   2021-04-21
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 */
namespace OP;

/** Content
 *
 * @created   2021-04-21
 * @param     string       $function
 * @param     array        $args
 * @throws   \Exception
 * @return    string
 */
function Content(?string $path=null, array $args=[])
{
	//	...
	static $_content;

	//	...
	if( $path ){
		$_content .= GetTemplate($path, $args);
		return;
	}

	//	...
	echo $_content;

	//	...
	$_content = null;
}
