<?php
/** op-core:/function/Template.php
 *
 * @created   2020-04-25
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** Declare strict
 *
 */
declare(strict_types=1);

/** namespace
 *
 */
namespace OP;

/** Template
 *
 * @created   2020-04-25
 * @version   1.0
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 * @param     string       $file
 * @param     array        $args
 * @return    NULL|mixed   $result
 */
function Template(string $file_name, array $args=[])
{
	//	...
	$result = null;

	//	Trim white space.
	$file = $file_name = trim($file_name);

	//	Check if empty.
	if( empty($file) ){
		Notice::Set("Passed empty string. ($file)");
		return;
	}

	//	Check if full path.
	if( $file[0] === '/' ){
		Notice::Set("Template function can not specify the full path from root. ($file)");
		return;
	}

	//	Check if parent path include.
	if( strpos($file, '..') !== false ){
		Notice::Set("Does not support specifying parent directory. ($file)");
		return;
	}

	//	Check if meta path.
	if( strpos($file, ':') ){
		if(!$file = ConvertPath($file, false, false) ){
			Notice::Set("This path is could not convert from meta path. ($file)");
			return;
		}
	}

	//	Check file exists.
	if( file_exists($path = realpath($file)) ){
		//	Relative path.
	}else if( file_exists($path = RootPath('asset') . 'template/' . $file) ){
		//	Template path.
	}else{
		Notice::Set("This file is not located in the template directory. ($file)");
		return;
	}

	//	Check if directory include.
	if( strpos($path, '/') !== false ){
		//	Get current directory.
		$save_directory = getcwd();

		//	Chenge direcotry.
		chdir(dirname($path));
	}

	//	Check args.
	if( isset($args[0]) ){
		Notice::Set('The args is array. Not assoc.');
		return false;
	}

	//	Load file.
	try {
		//	Seal to the SandBox.
		$result = call_user_func(function($template_path, $args){

			//	Swap file name. Because avoid conflicts. --> $args['path']
			$md5 = 'file_' . md5(microtime());
			${$md5} = $template_path;

			//	If variables passed.
			if(!empty($args) ){
				//	Extract passed variables.
				extract($args, EXTR_SKIP);
			};

			//	Flush arguments.
		//	unset($path, $args);

			//	Execute file.
			return include(${$md5});

		},$path, $args);

	}catch(\Throwable $e){
		Notice::Set($e);
	}

	//	Check if directory changed.
	if( $save_directory ?? null ){
		//	Recovery save direcotry.
		chdir($save_directory);
	}

	//	Return result.
	return ($result === 1) ? null : $result;
}
