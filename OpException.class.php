<?php
/**
 * OpException.class.php
 *
 * @creation  2015-03-02
 * @version   1.0
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright 2015 (C) Tomoaki Nagahara All right reserved.
 * @package   op-core
 */

/**
 * OpException
 * 
 * @creation  2013
 * @version   1.0
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright 2015 (C) Tomoaki Nagahara All right reserved.
 * @package   op-core
 */
class OpException extends Exception
{
	/**
	 * Message's original language.
	 * 
	 * @var string
	 */
	private $_lang = null;

	/**
	 * @param message[optional]
	 * @param lang[optional]
	 * @param code[optional]
	 * @param previous[optional]
	 */
	public function __construct( $message, $lang=null, $code=null, $previous=null )
	{
		if(!is_string($message)){
			$message = "Passed error message is not a string. \Caller: ".OnePiece5::GetCallerLine()."\ ";
			$lang = 'en';
		}
		if( $lang ){
			$this->_lang = $lang;
		}
		parent::__construct( $message, $code, $previous );
	}
	
	/**
	 * Language of message. 
	 * 
	 * @return string
	 */
	function getLang()
	{
		return $this->_lang;
	}
	
	/**
	 * Self-test flag.
	 * 
	 * @param  boolean $var
	 * @return boolean
	 */
	function isSelftest($var=null)
	{
		static $io;
		if( $var ){
			$io = $var;
		}
		return $io;
	}
}
