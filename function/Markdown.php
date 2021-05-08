<?php
/** op-core:/function/Markdown.php
 *
 * @created   2020-09-05
 * @moved     2020-11-18   from uqunie.com
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 */
namespace OP;

/** Markdown
 *
 * @created   2020-09-05   Born at uqunie.com
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 * @param     string       $meta_file_path
 * @param     boolean      $throw_exception
 */
function Markdown($file, $throw_exception=true)
{
	//	Checking if the file actually exists.
	if(!ConvertPath($file, $throw_exception) ){
		return;
	}

	//	...
	echo '<div class="markdown"><pre><code>';
	Template($file);
	echo '</code></pre></div>';
}
