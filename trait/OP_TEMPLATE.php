<?php
/** op-core:/OP_TEMPLATE.php
 *
 * @created    2024-06-28
 * @version    1.0
 * @package    op-core
 * @author     Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright  Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 */
namespace OP;

/** OP_TEMPLATE
 *
 * @created    2024-06-28
 * @version    1.0
 * @package    op-core
 * @author     Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright  Tomoaki Nagahara All right reserved.
 */
trait OP_TEMPLATE
{
	/** Template
	 *
	 * <pre>
	 * Features:
	 *  1. It is executed in a closure and the variables are isolated.
	 *  2. You can pass arguments.
	 *
	 * Rules:
	 *  1. You can not specify full path.
	 *  2. You can specify the meta path.
	 *  3. You can specify relative path.
	 *  4. You can not access the upper path.
	 *
	 * Search order:
	 *  1. Current directory
	 *  2. Templates directory in the Unit.
	 *  3. Templates directory in the Layout.
	 *  4. Templates directory in the Skeleton.
	 *
	 * ```index.php
	 * OP()->Template('index.phtml', ['foo'=>'bar']);
	 * ```
	 *
	 * ```index.phtml
	 * &lt;?php
	 * D($args);
	 * D($args['foo']); // --> 'bar'
	 * ```
	 * </pre>
	 *
	 * @created    A long time ago...
	 * @updated    2017-05-09 to op-core-7
	 * @updated    2019-02-22 to op-unit-template
	 * @updated    2020-04-25 to OP\Template()
	 * @moved      2024-06-28 to OP_TEMPLATE
	 * @param      string       $file
	 * @param      array        $args
	 * @return     NULL|mixed   $result
	 */
	static function Template(string $file, array $args=[])
	{
		//	...
		$result = null;

		//	Trim white space.
		$file = trim($file);

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

		/*
		//	Check if meta path.
		if( strpos($file, ':') ){
			if(!$file = ConvertPath($file, false, false) ){
				Notice::Set("This path is could not convert from meta path. ($file)");
				return;
			}
		}
		*/

		/*
		//	Check file exists.
		if( file_exists($path = realpath($file)) ){
			//	Relative path.
		}else if( file_exists($path = RootPath('asset') . 'template/' . $file) ){
			//	Template path.
		}else{
			Notice::Set("This file is not located in the template directory. ($file)");
			return;
		}
		*/

		//	Check if meta path.
		if( strpos($file, ':') ){
			//	Convert to real path.
			$path = ConvertPath($file, false, false);
		}else{
			//	Init variables.
			$asset  = RootPath('asset');
			$layout = Config::Get('layout')['name'] ?? null;
			if( __NAMESPACE__ === 'OP\UNIT' ){
				$unit = explode('\\', self::class)[2];
			}

			//	Search order.
			$dirs   = [];
			$dirs[] = getcwd().'/';
			if(!empty($unit  )){ $dirs[] = "{$asset}/unit/{$unit}/template/";     }
			if(!empty($layout)){ $dirs[] = "{$asset}/layout/{$layout}/template/"; }
			$dirs[] = "{$asset}/template/";

			//	Search file.
			foreach( $dirs as $dir ){
				//	Full path.
				$path = $dir . $file;

				//	Check file exists.
				if( file_exists($path) ){
					//	Break loop.
					break;
				}

				//	Reset path.
				$path = null;
			}
		}

		//	Check file exists.
		if( empty($path) or !file_exists($path) ){
			Notice::Set("This file is not located in the template directory. ($path)");
			return;
		}

		//	Check if directory include.
		if( strpos($path, '/') !== false ){
			//	Get current directory.
			$save_directory = getcwd();

			//	Change directory.
			chdir(dirname($path));
		}

		//	Check args.
		if( isset($args[0]) ){
			Notice::Set('The args is array. Not assoc.');
			return false;
		}

		//	Load file.
		try {
			//	Sealed inside the closure.
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
			//	Recovery save directory.
			chdir($save_directory);
		}

		//	Return result.
		return ($result === 1) ? null : $result;
	}
}
