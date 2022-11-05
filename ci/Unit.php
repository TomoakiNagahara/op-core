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
$ci = new CI();

//	Instantiate
$args   = 'Dump';
$result = 'OP\UNIT\Dump';
$ci->Set('Instantiate', $result, $args);

//	Load
$args   = 'Dump';
$result =  true;
$ci->Set('Load', $result, $args);

//	Singleton
$args   = 'Dump';
$result = 'OP\UNIT\Dump';
$ci->Set('Singleton', $result, $args);

//	...
return $ci->GenerateConfig();