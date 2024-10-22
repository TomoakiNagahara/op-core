<?php
/** op-core:/trait/OP_UNIT_MAPPER.php
 *
 * @created    2024-06-08
 * @version    1.0
 * @package    op-core
 * @author     Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright  Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 */
namespace OP;

/** OP_UNIT_MAPPER
 *
 * @created    2024-06-08
 * @version    1.0
 * @package    op-core
 * @author     Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright  Tomoaki Nagahara All right reserved.
 */
trait OP_UNIT_MAPPER
{
	/** Map unit name.
	 *
	 * @created  2024-07-08
	 * @param    string     $name
	 * @return   IF_UNIT
	 */
	static private function & _Map(string $name) : IF_UNIT
	{
		//	Get unit config.
		static $_config;
		if(!$_config){
			$_config = Config::Get('unit');
		}

		//	Do mapping.
		if( isset($_config[$name]) ){
			$name = $_config[$name];
		}

		//	Return already instantiated unit object.
		return self::Instantiated($name);
	}
}
