<?php
/** op-core:/ci/Unit_Mapper.php
 *
 * @created    2024-06-30
 * @version    1.0
 * @package    op-core
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

/* @var $ci UNIT\CI\CI_Config */

//	...
$method = '_Map';
$args   = 'App';
$result = 'OP\UNIT\App';
$ci->Set($method, $result, $args);

//	...
$_unit_config = Config::Get('unit');

//	...
$units = [
	'App'      => null,
	'Api'      => null,
	'Router'   => null,
	'Layout'   => null,
	'WebPack'  => null,
	'Form'     => null,
	'Validate' => null,
	'Database' => null,
	'ORM'      => null,
	'QQL'      => null,
];

//	...
foreach( $units as $unit => $args ){
	//	...
	$method = $unit;
	$args   = $args;

	//	Re:map
	if( isset($_unit_config[$unit]) ){
		$unit = $_unit_config[$unit];
	}

	//	...
	if( Unit::isInstalled($unit) ){
		$result = "OP\UNIT\\{$unit}";
	}else{
		$name   = strtolower($unit);
		$result = "Exception: Does not install \"{$unit}\" unit. (git:/asset/unit/{$name})";
	}

	//	...
	$ci->Set($method, $result, $args);
}
