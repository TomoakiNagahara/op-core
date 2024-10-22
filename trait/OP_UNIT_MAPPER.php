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

	/** App
	 *
	 * @created    2024-06-08
	 * @return     IF_APP
	 */
	static function & App() : IF_APP
	{
		return self::_Map(__FUNCTION__);
	}

	/** Api
	 *
	 * @created    2024-06-30
	 * @return     IF_API
	 */
	static function & Api() : IF_API
	{
		return self::_Map(__FUNCTION__);
	}

	/** Router
	 *
	 * @created    2024-06-08
	 * @return     IF_ROUTER
	 */
	static function & Router() : IF_ROUTER
	{
		return self::_Map(__FUNCTION__);
	}

	/** Layout
	 *
	 * @created    2024-06-08
	 * @return     IF_LAYOUT
	 */
	static function & Layout() : IF_LAYOUT
	{
		return self::_Map(__FUNCTION__);
	}

	/** WebPack
	 *
	 * @created    2024-06-08
	 * @return     IF_WEBPACK
	 */
	static function & WebPack() : IF_WEBPACK
	{
		return self::_Map(__FUNCTION__);
	}

	/** Form
	 *
	 * @created    2024-06-08
	 * @return     IF_FORM
	 */
	static function & Form() : IF_FORM
	{
		return self::_Map(__FUNCTION__);
	}

	/** Validate
	 *
	 * @created    2024-06-08
	 * @return     IF_VALIDATE
	 */
	static function & Validate() : IF_VALIDATE
	{
		return self::_Map(__FUNCTION__);
	}

	/** Database
	 *
	 * @created    2024-06-08
	 * @return     IF_DATABASE
	 */
	static function & Database() : IF_DATABASE
	{
		return self::_Map(__FUNCTION__);
	}

	/** ORM
	 *
	 * @created    2024-06-08
	 * @return     IF_ORM
	 */
	static function & ORM() : IF_ORM
	{
		return self::_Map(__FUNCTION__);
	}

	/** QQL
	 *
	 * @created    2024-07-13
	 * @return     IF_QQL
	 */
	static function & QQL() : IF_QQL
	{
		return self::_Map(__FUNCTION__);
	}
}
