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
 * @return   ?string       $hash of $_content
 */
function Content( string $path=null, array $args=[])
{
	//	...
	static $_content;

	//	$path if empty, echo $_content.
	if( empty($path) ){
		//	...
		echo $_content;

		//	...
		$_content = null;

		//	...
		return null;
	}

	//	Append conent.
	$_content .= GetTemplate($path, $args);

	//	For ETag.
	return $_content ? md5($_content): null;
}

/** If it is not called from anywhere, it will be output here.
 *
 * @created   2021-05-05
 */
register_shutdown_function(function(){
	Content();
});
