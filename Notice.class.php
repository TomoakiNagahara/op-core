<?php
/**
 * Notice.class.php
 * 
 * @creation  2016-01-17
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/**
 * Notice
 *
 * @creation  2016-01-17
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

class Notice extends OnePiece5
{
	static function Report()
	{
		if( OnePiece5::Admin() ){
			$io = self::_toDisplay();
		}else{
			$io = self::_toMail();
		}
		return $io;
	}

	static function _toDisplay()
	{
		$json['status'] = true;
		$json['errors'] = OP\Error::GetAll();

		if( Env::Get('jQuery-load') ){
			//	jQuery was loaded.
		}else{
			Env::Set('jQuery-load', true);
			print '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>';
		}

		print '<script>__notice__='.json_encode($json).'</script>';
		print '<script src="'.$_SERVER['SCRIPT_NAME'].'?onepiece[admin-notice]=js"></script>';
		print '<link rel="stylesheet" type="text/css" href="'.$_SERVER['SCRIPT_NAME'].'?onepiece[admin-notice]=css">';

		return true;
	}

	static function _toMail()
	{
		return false;
	}
}
