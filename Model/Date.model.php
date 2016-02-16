<?php
/**
 * op-core/model/Date.model.php
 * 
 * Control of world date.
 * 
 * @creation  2016-02-07
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/**
 * Model_Date
 * 
 * @creation  2016-02-07
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Model_Date extends Model_Model
{
	/**
	 * Current world time.
	 * 
	 * @var integer
	 */
	private $_time;

	function init()
	{
		parent::init();

		//	World time was freeze.
		$this->_time = time();
	}

	/**
	 * Generate of world date (have time).
	 * 
	 * @return string
	 */
	function GetDate()
	{
		return date('Y-m-d H:i:s', $this->_time);
	}

	/**
	 * Get frozen time.
	 * 
	 * @return number
	 */
	function GetTime()
	{
		return $this->_time;
	}
}
