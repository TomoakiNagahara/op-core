<?php
/** op-core:/trait/OP_CI.php
 *
 * @created   2022-10-12
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

/** OP_CI
 *
 * @created   2022-10-12
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
trait OP_CI
{
	/** Return all method names that the instance has.
	 *
	 * @created    2023-02-10
	 * @return array
	 */
	function CI_AllMethods():array
	{
		return get_class_methods($this);
	}

	/** Inspection target method.
	 *
	 * @created    2023-02-10
	 * @param      string      $method
	 * @param      array    ...$args
	 * @return     mixed
	 */
	function CI_Inspection(string $method, ...$args)
	{
		return $this->{$method}(...$args);
	}
}
