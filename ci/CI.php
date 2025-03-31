<?php
/** op-core:/ci/CI.php
 *
 * @created   2022-10-18
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
$ci = OP::Unit('CI')->Config();

//	Set
$result =  null;
$args   = ['Test', [], []];
$ci->Set('Set', $result, $args);

//	GenerateConfig
$result = ['Test' => [['result' => [], 'args' => []]]];
$args   = null;
$ci->Set('GenerateConfig', $result, $args);

//	...
return $ci->Get();
