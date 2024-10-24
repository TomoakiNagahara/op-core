<?php
/** op-core:/function/Mime.php
 *
 * @created    2024-10-24
 * @package    op-core
 * @version    1.0
 * @author     Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright  Tomoaki Nagahara All right reserved.
 */

/** Declare strict
 *
 */
declare(strict_types=1);

/** namespace
 *
 */
namespace OP;

/** Gets the MIME from the file contents.
 *
 * @param  string $file_path
 * @return string $mime
 */
function MIME(string $file_path) : string
{
	//	...
	if( function_exists("finfo_file") ){
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime  = finfo_file($finfo, $file_path);
		finfo_close($finfo);
	}else if(!$mime = exec('file -ib '.$file_path)){
		$mime = mime_content_type($file_path);
	}

	//	...
	return $mime;
}
