<?php
/** op-core:/ci/File.php
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

/* @var $ci \OP\UNIT\CI\CI_Config */
$ci = OP()->Unit()->CI()->Config();

//	Touch
$args   = '/tmp/self-check.txt';
$result = null;
$ci->Set('Touch', $result, $args);

//	Create
$args   = '/tmp/self-check/test.txt';
$result = null;
$ci->Set('Create', $result, $args);

//	Mkdir
$args   = '/tmp/self-check-test';
$result = null;
$ci->Set('Mkdir', $result, $args);

//	...
return $ci->Get();
