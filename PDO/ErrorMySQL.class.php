<?php
/**
 * ErrorMySQL.class.php
 * 
 * @creation  2015-05-08
 * @version   1.0
 * @package   op-core/SQL
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright 2015 (C) Tomoaki Nagahara All right reserved.
 */

/**
 * ErrorMySQL
 * 
 * @creation  2015-05-08
 * @version   1.0
 * @package   op-core/SQL
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright 2015 (C) Tomoaki Nagahara All right reserved.
 */
class ErrorMySQL extends OnePiece5
{
	private $_driver = 'mysql';
	
	function Get($pdo)
	{
		//	Get error.
		$temp = $pdo->errorInfo();
		$error_id = $temp[0];
		$error_no = $temp[1];
	
		//	Get host and user.
		$patt = "|'([\.-_a-z0-9]+)'@'([\.-_a-z0-9]+)'|";
		if( preg_match($patt,$temp[2],$match) ){
			$user = $match[1];
			$host = $match[2];
		}
	
		//	Get database and table.
		$patt_1 = "|'([\.-_a-z0-9]+)'|";
		$patt_2 = "|'([\.-_a-z0-9]+)\.([\.-_a-z0-9]+)'|";
		if( preg_match($patt_2,$temp[2],$match) ){
			//	Escape at backslash.
			$database = $match[1];
			$table    = $match[2];
			$temp[2] = preg_replace($patt, "\\ \\1@\\2 \\", $temp[2]);
		}else if( preg_match($patt_1,$temp[2],$match) ){
			//	Escape at backslash.
			$database = $match[1];
			$temp[2] = preg_replace($patt, "\\ \\1 \\", $temp[2]);
		}else{
			$this->Mark($temp);
		}
	
		//	Branch
		switch($error_no){
			case 1044:
				$message = "This user's access was deny. \\{$user}@{$host}\\";
				break;
	
			case 1062:
				$message = "Duplicate entry.";
				break;
	
			case 1064:
				$message = "You have an error in your SQL syntax. ";
				$message.= "Check the manual that corresponds to your MySQL server version. ";
				break;
	
			case 1091:
				$message = "Cannot DROP \PRIMARY KEY\. Check that column/key exists.";
				break;
	
			case 1142:
				$message = " DELETE command was denied. \(host: $host, user: $user)\ ";
				break;
	
			case 1146:
				//	Table '_database_._table_' doesn't exist
				$database = ConfigSQL::Quote( $database, $this->_driver );
				$table    = ConfigSQL::Quote( $table,    $this->_driver );
				$message  = "Table does not exist. ({$database}.{$table})";
				break;
	
			default:
				$message = $temp[2];
				$message = preg_replace("|'([-_a-z0-9\.]+)'|", ' \\ \\1 \\ ', $message);
				$message = rtrim($message).".";
		}
	
		return "{$message} \SQLSTATE:{$error_id}\ ";
	}	
}
