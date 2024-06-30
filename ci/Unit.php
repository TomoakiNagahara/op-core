<?php
/** op-core:/ci/Unit.php
 *
 * @created   2022-11-02
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

//	...
$ci = OP::Unit('CI');

//	Instantiate
$args   = 'Dump';
$result = 'OP\UNIT\Dump';
$ci->Set('Instantiate', $result, $args);

//	Instantiated
$args   = 'Dump';
$result = 'OP\UNIT\Dump';
$ci->Set('Instantiated', $result, $args);

//	Load
$args   = 'Dump';
$result =  true;
$ci->Set('Load', $result, $args);

//	Singleton
$args   = 'Dump';
$result = 'OP\UNIT\Dump';
$ci->Set('Singleton', $result, $args);

//	isInstall
$args   = 'Dump';
$result =  true;
$ci->Set('isInstall', $result, $args);

//	isInstall - fail
$args   = 'Failllll';
$result =  false;
$ci->Set('isInstall', $result, $args);

//	...
$method = 'isInstalled';
$args   = 'App';
$result =  true;
$ci->Set($method, $result, $args);

//	...
$method = 'isInstalled';
$args   = 'Core';
$result =  false;
$ci->Set($method, $result, $args);

//	_Map
$method = '_Map';
$args   = 'Dump';
$result = 'OP\UNIT\Dump';
$ci->Set($method, $result, $args);

//	Each unit
$unit = [
	'App',
	'Api',
	'Router',
	'Layout',
	'WebPack',
	'Form',
	'Validate',
	'Database',
	'ORM',
	'QQL',
];
foreach( $unit as $class ){
	$method = $class;
	$args   = $class;
	if( OP()->Unit()->isInstalled($class) ){
		$result = "OP\UNIT\\{$class}";
	}else{
		$result = "Exception: Does not install \"{$class}\" unit. (git:/asset/unit/".strtolower($class).")";
	}

	$ci->Set($method, $result, $args);
}

//	...
return $ci->GenerateConfig();
