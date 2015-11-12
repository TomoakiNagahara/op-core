<?php
/**
 * Validator.class.php
 * 
 * @creation  2015-05-10
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright 2015 (C) Tomoaki Nagahara All right reserved.
 */

/**
 * Validator
 * 
 * @creation  2015-05-10
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright 2015 (C) Tomoaki Nagahara All right reserved.
 */
class Validator extends OnePiece5
{
	static function isPositiveNumber($var)
	{
		if(!is_numeric($var)){
			return false;
		}
		
		if( $var === 0 ){
			return false;
		}
		
		return $var > 0 ? true: false;
	}
	
	static function isNegativeNumber($var)
	{
		if(!is_numeric($var)){
			return false;
		}
		
		if( $var === 0 ){
			return false;
		}
		
		return $var < 0 ? true: false;
	}
	
	/**
	 * <pre>
	 * OK:
	 *  0
	 *  1
	 *  1.0
	 *  1.1
	 * -1
	 * -1.0
	 * -1.1
	 * 
	 * NG:
	 * </pre>
	 * 
	 * @return boolean
	 */
	static function isNumber($var)
	{
		return is_numeric($var);
	}

	/**
	 * <pre>
	 * OK:
	 *  1
	 *  1.0
	 *  1.1
	 * +1
	 * +1.0
	 * +1.1
	 *
	 * NG:
	 *  0
	 * -1
	 * -1.0
	 * -1.1
	 * </pre>
	 * 
	 * @return boolean
	 */
	static function isNumberPositive($var)
	{
		if(!self::isNumber($var)){
			return false;
		}
		return $var>0 ? true: false;
	}

	/**
	 * <pre>
	 * OK:
	 * -1
	 * -1.0
	 * -1.1
	 *
	 * NG:
	 *  0
	 *  1
	 *  1.0
	 *  1.1
	 * </pre>
	 * 
	 * @return boolean
	 */
	static function isNumberNegative()
	{
		if(!self::isNumber($var)){
			return false;
		}
		return $var<0 ? true: false;
	}
	
	/**
	 * @see Validator::isInteger
	 * @return boolean
	 */
	static function isInt()
	{
		return self::isInteger();
	}

	/**
	 * <pre>
	 * OK:
	 *  0
	 *  1
	 * -1
	 *
	 * NG:
	 *  1.0
	 * -1.0
	 *  1.1
	 * -1.1
	 * </pre>
	 * 
	 * @return boolean
	 */
	static function isInteger($var)
	{
		if(!self::isNumber($var)){
			return false;
		}
		
		return is_float($var) ? false: true;
	}
	
	/**
	 * <pre>
	 * OK:
	 *  0
	 *  1
	 * -1
	 *  1.0
	 * -1.0
	 *  1.1
	 * -1.1
	 *
	 * NG:
	 * </pre>
	 * 
	 * @return boolean
	 */
	static function isFloat($var)
	{
		return is_float($var);
	}
	
	static function isDecimal($var)
	{
		OnePiece5::Mark();
	}
	
	static function isHost($host)
	{
		//	Does not check by localhost
		switch($_SERVER['REMOTE_ADDR']){
			case '::1':
			case '127.0.0.1':
				OnePiece5::mark("![.gray[Skipped remote IP address check. ({$_SERVER['REMOTE_ADDR']})]]",'debug');
				return true;
			default:
		}
		
		// Checking host name.
		return checkdnsrr($host,'MX');
	}
	
	static function isEmail($mail)
	{
		//	check part of address
		$patt = '/^[a-z0-9][-_a-z0-9\.\+]+@[\.-_a-z0-9]+\.[a-z][a-z]+/i';
		if(!preg_match( $patt, $mail, $match ) ){
			return false;
		}
		
		//	Host name checking.
		list($addr, $host) = explode('@',$mail);
		return Validator::isHost($host);
	}
}
