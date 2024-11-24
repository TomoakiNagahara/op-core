<?php
/** op-core:/ci/Notice.php
 *
 * @created   2022-11-01
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

//	_Session
$args   = null;
$result = [];
$ci->Set('_Session', $result, $args);

//	Get
$args   = null;
$result = [];
$ci->Set('Get', $result, $args);

//	Pop
$args   = null;
$result = [];
$ci->Set('Pop', $result, $args);

//	Set
$args   = ['This is notice message.',[]];
$result =  'Notice: This is notice message.';
$ci->Set('Set', $result, $args);

//	Has
$args   = null;
$result = false;
$ci->Set('Has', $result, $args);

//	...
return $ci->Get();
