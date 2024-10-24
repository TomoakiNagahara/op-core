<?php
/** op-core:/ci/Config.php
 *
 * @created   2022-10-12
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

/*
//	...
$config = [];

//	...
$app_id = 'self-check';

//	...
$config['_Init'][]  = ['result' => 'ci', 'args' => 'CI'];
$config['_Fetch'][] = ['result' => null, 'args' => 'ci'];
$config['Get'][]    = [
	'args'   => 'app_id',
	'result' => [
		'app_id' => $app_id,
	],
];

$config['Set'][]    = [
	'args'   => ['app_id', ['execute'=>false]],
	'result' => [
		'app_id'  => $app_id,
		'execute' => false,
	],
];
*/

/* @var $ci \OP\UNIT\CI\CI_Config */
$ci = OP()->Unit()->CI()->Config();
$ci = OP::Unit('CI')->Config();

//	...
$file   = 'ci';

//	...
$method = '_Init';
$result = strtolower($file);
$args   = strtoupper($file);
$ci->Set($method, $result, $args);

//	...
$method = '_Fetch';
$result =  null;
$args   = 'ci';
$ci->Set($method, $result, $args);

//	...
$method = 'Get';
$result = ['app_id' => 'CI'];
$args   = 'app_id';
$ci->Set($method, $result, $args);

//	Get - Key name is empty.
$method = 'Get';
$core   = OP::MetaPath('core:/');
$core   = realpath($core);
switch( PHP_MAJOR_VERSION.PHP_MINOR_VERSION ){
	case '70':
	case '71':
	case '72':
	case '73':
	case '74':
		$result = 'Exception: Argument 1 passed to OP\Config::_Init() must be of the type string, null given, called in '.$core.'/Config.class.php on line 164';
		break;
	default:
		$result = 'Exception: OP\Config::_Init(): Argument #1 ($name) must be of type string, null given, called in '.$core.'/Config.class.php on line 164';
}
$args   = null;
$ci->Set($method, $result, $args);

//	Get
$file   = 'no_exist';
$result = "Notice: This config file does not exists. ({$file})";
$args   = $file;
$ci->Set('Get', $result, $args);

//	Set - Init
$method = 'Set';
$args   = [$file, ['a' => 'A', 'b' => 'B']];
$result = ['a' => 'A', 'b' => 'B'];
$ci->Set($method, $result, $args);

//	Set - Add
$method = 'Set';
$result = ['a' => 'A', 'b' => 'B', 'c' => 'C'];
$args   = [$file, ['c' => 'C']];
$ci->Set($method, $result, $args);

//	...
return $ci->Get();
