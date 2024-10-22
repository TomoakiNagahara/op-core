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
