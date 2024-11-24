<?php
/** op-core:/ci/Debug.php
 *
 * @created   2022-10-31
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
$core = OP::MetaPath('core:/');
$core = str_replace('/', '\\/', $core);

/* @var $ci \OP\UNIT\CI\CI_Config */
$ci = OP()->Unit()->CI()->Config();

//	Get
$result =  null;
$args   =  null;
$ci->Set('Get', $result, $args);

//	Get
$result =  null;
$args   = ['self-check','test'];
$ci->Set('Set', $result, $args);

//	Out not key
$result = null;
$args   = null;
$ci->Set('Out', $result, $args);

//	Out has key
$result = null;
$ci->Set('Out', $result, $args);

//	Debug
$result = null;
$args   = null;
$ci->Set('Debug', $result, $args);

//	...
return $ci->Get();
