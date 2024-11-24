<?php
/** op-core:/ci/Encrypt.php
 *
 * @created   2022-10-20
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

//	_iv
$result = '3ba0f40775d6d6ac';
$args   = null;
$ci->Set('_iv', $result, $args);

//	_password
$result = '3ba0f40775d6d6ace27ef929f5be3cdf';
$args   = null;
$ci->Set('_password', $result, $args);

//	Enc
$result = 'PINL3C+1XaiCb/1QZNdGZA==';
$args   = 'self-check';
$ci->Set('Enc', $result, $args);

//	Dec
$result = 'self-check';
$args   = 'PINL3C+1XaiCb/1QZNdGZA==';
$ci->Set('Dec', $result, $args);

//	...
return $ci->Get();
