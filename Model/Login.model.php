<?php
/**
 * Login.model.php
 * 
 * @creation  2015
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/**
 * Model_Login
 * 
 * Manages the login status.
 * Only do it.
 * 
 * <pre>
 * ex.
 * if( Model_Login::isLoggedin() ){
 *     //  Already logged in.
 *     $id = Model_Login::GetLoginId();
 * }else{
 *     //  Does not login.
 *     if( $id = Model_Account::Auth($account, $password) ){
 *         Model_Login::SetLoginId($id);
 *     }
 * }
 * </pre>
 * 
 * @creation  2015
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Model_Login extends Model_Model
{
	const SESSION_LOGIN_ID         = 'loggedin-id';
	const SESSION_FIRST_LOGIN_TIME = 'first-login-time';
	const COOKIE_LAST_ACCESS_TIME  = 'last-access-time';

	const MESSAGE_EXPIRE_SESSION   = 'Session has expire. (Session has destroyed)';
	const MESSAGE_EXPIRE_LAST      = 'Login has expire. (From the last access)';
	const MESSAGE_EXPIRE_FIRST     = 'Login has expire. (From the first login)';

	private $_is_login = null;
	private $_message = null;
	private $_expire  =  1200; // 60 sec * 20 min
	private $_effect  = 10800; // 60 sec * 60 min * 3 hour

	function Init()
	{
		parent::Init();

		//	check expire
		if( $this->GetCookie(self::COOKIE_LAST_ACCESS_TIME) > (time() - $this->_expire) ){
			//	OK
		}else{
			//	NG
			$this->_message = self::MESSAGE_EXPIRE_LAST;
			$this->Logout();
		}
		$this->SetCookie(self::COOKIE_LAST_ACCESS_TIME, time(), 0);
	}

	/**
	 * This is old method. Wrap of GetLoginID method.
	 * This is method of deprecated.
	 * 
	 * @return string|integer
	 */
	function ID()
	{
		return $this->GetLoginID();
	}
	
	/**
	 * Set login-ID
	 * 
	 * @param  string|number $id
	 * @return boolean
	 */
	function SetLoginId($id)
	{
		//	Check
		if(!$id){
			$this->AdminNotice('Arguments \$id\ is empty. If logout, use \$this->Logout()\.');
			return false;
		}

		//	Set first login time
		if( $_id = $this->GetSession(self::SESSION_LOGIN_ID) ){
			//	
		}else{
			if( $_id != $id ){
				$this->SetSession( self::SESSION_FIRST_LOGIN_TIME, time() );
			}
		}

		//	Set login-ID to session
		$io = $this->SetSession( self::SESSION_LOGIN_ID, $id );

		return $io ? true: false;
	}

	/**
	 * Get login-ID
	 * 
	 * @return string|number
	 */
	function GetLoginId()
	{
		//	Get login-ID from session
		$id = $this->GetSession( self::SESSION_LOGIN_ID );

		//	If empty
		if( $id ){
			//	Check login effective.
			if( $this->GetSession(self::SESSION_FIRST_LOGIN_TIME) < (time() - $this->_effect) ){
				$id = null;
				$this->_message = self::MESSAGE_EXPIRE_FIRST;
			}
		}else{
			//	Will session has destroy?
			if( $this->GetCookie(self::COOKIE_LAST_ACCESS_TIME) ){
				$this->_message = self::MESSAGE_EXPIRE_SESSION;
			}
		}

		return $id;
	}

	/**
	 * To logout
	 */
	function Logout()
	{
		$this->_is_login = false;
		$this->SetSession( self::SESSION_LOGIN_ID, null );
		return true;
	}

	/**
	 * Return to boolean
	 * 
	 * @return boolean
	 */
	function isLoggedin()
	{
		if( $this->_is_login === null ){
			$this->_is_login = $this->GetLoginId() ? true: false;
		}
		return $this->_is_login;
	}

	/**
	 * Get status message
	 * 
	 * @return string
	 */
	function GetMessage()
	{
		return $this->_message;
	}
}
