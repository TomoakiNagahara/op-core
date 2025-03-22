<?php
/** op-core:/ci/OP/Template.php
 *
 * @created   2024-10-03
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

/* @var $ci UNIT\CI\CI_Config */

//	Template - not exists error
$root_core = RootPath('core');
$args   = 'core:/ci/OP/not_exist.txt';
$result = "Notice: This file is not located in the template directory. ({$root_core}ci/OP/not_exist.txt)";
$ci->Set('Template', $result, $args);

//	Template - Display plain test string
$root_core = RootPath('core');
$args   = 'core:/ci/OP/Template.txt';
$result = "This is ci test.";
$ci->Set('Template', $result, $args);

//	DebugBacktraceToString
$args   = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
$result = 'core:/ci/OP.php              26 - require_once()'."\n";
$ci->Set('DebugBacktraceToString', $result, $args);
