<?php
/** op-core:/ci/Cookie.php
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

/* @var $ci \OP\UNIT\CI\CI_Config */
$ci = OP()->Unit()->CI()->Config();

//	Get
$result = 'Notice: Cookie can not be used in the shell environment.';
$args   =  null;
$ci->Set('Get', $result, $args);

//	Set
$result = 'Notice: Cookie can not be used in the shell environment.';
$args   = ['count','1'];
$ci->Set('Set', $result, $args);

//	...
$method = 'UserID';
$result =  null;
$args   =  null;
$ci->Set($method, $result, $args);

//	...
return $ci->Get();
