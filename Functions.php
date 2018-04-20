<?php
/**
 * Functions.php
 *
 * @creation  2016-11-16
 * @version   1.0
 * @package   core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** Return meta root path array.
 *
 * @return array
 */
function _GetRootsPath()
{
	//	...
	global $_OP;

	//	...
	if( class_exists('Unit', false) ){
		$root['unit:/']= realpath(Env::Get(Unit::_DIRECTORY_));
	}

	//	...
	if( isset($_OP['LAYOUT']) ){
		$root['layout:/'] = $_OP['LAYOUT'];
	}

	//	...
	$root['app:/'] = $_OP['APP_ROOT'];
	$root['doc:/'] = $_OP['DOC_ROOT'];
	$root['op:/']  = $_OP['OP_ROOT'];
	return $root;
}

/** Compress to meta path from local file path.
 *
 * <pre>
 * print CompressPath(__FILE__); // -> App:/index.php
 * </pre>
 *
 * @param  string $file_path
 * @return string
 */
function CompressPath($path)
{
	foreach( _GetRootsPath() as $key => $var ){
		if( strpos($path, $var) === 0 ){
			$path = substr($path, strlen($var));
			return $key . ltrim($path,'/');
		}
	}
	return $path;
}

/** Convert to local file path from meta path.
 *
 * <pre>
 * print ConvertPath('app:/index.php'); // -> /www/localhost/index.php
 * </pre>
 *
 * @param  string $meta_path
 * @return string
 */
function ConvertPath($path)
{
	foreach( _GetRootsPath() as $key => $var ){
		if( strpos($path, $key) === 0 ){
			$path = substr($path, strlen($key));
			return $var.$path;
		}
	}
	return $path;
}

/** Convert url from meta path to document root path.
 *
 * <pre>
 * print ConvertURL('app:/index.php'); // -> /index.php
 * </pre>
 *
 * @param  string $meta_url
 * @return string
 */
function ConvertURL($url)
{
	//	...
	if( strpos($url, 'app:/') === 0 ){
		//	...
		$rewrite_base = dirname($_SERVER['SCRIPT_NAME']).'/';

		//	...
		return rtrim($rewrite_base, '/').substr($url,4);
	}else if( strpos($url, $_SERVER['DOCUMENT_ROOT']) === 0 ){
		//	...
		$rewrite_base = $_SERVER['DOCUMENT_ROOT'];

		//	...
		return substr($url, strlen($_SERVER['DOCUMENT_ROOT']));
	}else{
		d('url ->', $url);
		d('document root ->', $_SERVER['DOCUMENT_ROOT']);
		d(_GetRootsPath());
	}
}

/** Dump value for developers only.
 *
 * @param boolean|integer|string|array|object $value
 */
function D()
{
	//	If not admin will skip.
	if(!Env::isAdmin()){
		return;
	}

	//	...
	if(!Unit::Load('dump') ){
		return;
	}

	//	Dump.
	OP\UNIT\Dump::Mark();
}

/** Escape mixid value.
 *
 * @param  mixed  $var
 * @param  string $charset
 * @return mixed  $var
 */
function Escape($var, $charset=null)
{
	//	...
	if(!$charset ){
		$charset = Env::Charset();
	}

	//	...
	switch( $type = gettype($var) ){
		case 'string':
			return _EscapeString($var, $charset);

		case 'array':
			$var = _EscapeArray($var, $charset);
			break;

		case 'object':
			Notice::Set("Objects are not yet supported.");
			break;

		default:
	}

	//	...
	return $var;
}

/** Escape array.
 *
 * @param  array $arr
 * @return array
 */
function _EscapeArray($arr, $charset)
{
	//	...
	$new = [];

	//	...
	foreach( $arr as $key => $var ){
		//	Escape index key in case of string.
		if( is_string($key) ){
			$key = _EscapeString($key, $charset);
		}

		//	Escape value.
		$new[$key] = Escape($var);
	}

	//	...
	return $new;
}

/** Escape string.
 *
 * @param  string $var
 * @return string
 */
function _EscapeString($var, $charset)
{
	$var = str_replace("\0", "", $var);
	return htmlentities($var, ENT_QUOTES, $charset, false);
}

/** To hash
 *
 * This function is convert to fixed length unique string from long or short strings.
 *
 *
 * @param string $var
 * @param number $length
 */
function Hasha1($var, $length=8){
	return substr(sha1($var . _OP_SALT_), 0, $length);
}

/** ifset
 *
 * @see    http://qiita.com/devneko/items/ee83854eb422c352abc8
 * @param  mixed $check
 * @param  mixed $alternate
 * @return mixed
 */
function ifset(&$check, $alternate=null)
{
	return isset($check) ? $check : $alternate;
}
