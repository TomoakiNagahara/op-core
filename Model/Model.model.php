<?php
/**
 * Model.model.php
 * 
 * @creation: 2015-02-09
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright 2015 (C) Tomoaki Nagahara All right reserved.
 */

/**
 * Model_Model
 * 
 * @creation: 2015-02-09
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright 2015 (C) Tomoaki Nagahara All right reserved.
 */
abstract class Model_Model extends OnePiece5
{
	/**
	 * Wrapper PDO5 of OnePiece5
	 * 
	 * @return PDO5
	 */
	function pdo()
	{
		$pdo = parent::PDO();	
		if(!$pdo->isConnect()){
			
			//  get database config
			$database = $this->config()->database();
			
			//	databaes name
			if( isset($database->name) ){
				$database->database = $database->name;
			}
			
			//  database connection
			if(!$io = $pdo->Connect($database)){
				throw new OpException("Connection was failed.",'en');
			}
		}
		return $pdo;
	}
}

/**
 * Config_Model
 * 
 * Creation: 2015-02-09
 * 
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright 2015 (C) Tomoaki Nagahara All right reserved.
 */
abstract class Config_Model extends OnePiece5
{
	const COLUMN_CREATED	 = 'created';
	const COLUMN_UPDATED	 = 'updated';
	const COLUMN_DELETED	 = 'deleted';
	const COLUMN_TIMESTAMP	 = 'timestamp';
	
	function __database()
	{
		$database = new Config();
		$database->driver	 = 'mysql';
		$database->host		 = 'localhost';
		$database->port		 = '3306';
		$database->name		 = 'onepiece';
		$database->charset	 = 'utf8';
		$database->user		 = 'op_mdl_model';
		$database->password	 = $this->__password($database);
		return $database;
	}
	
	function __password($database)
	{
		if( empty($database) ){
			$this->StackError('Arguments was empty.','en');
			return md5(__METHOD__);
		}
		
		if( empty($database->name) ){
			$this->StackError('Database name was empty.','en');
			return md5(__METHOD__);
		}
		
		if( empty($database->user) ){
			$this->StackError('User name was empty.','en');
			return md5(__METHOD__);
		}
		
		return md5($_SERVER['SERVER_ADDR'].', '.$database->name.', '.$database->user);
	}
	
	function __select()
	{
		$config = new Config();
		$config->where->deleted = null;
		$config->limit = 1;
		$config->cache = 1;
		return $config;
	}
	
	function __insert()
	{
		$config = new Config();
		$config->set->created = gmdate('Y-m-d H:i:s');
		return $config;
	}
	
	function __update()
	{
		$config = new Config();
		$config->set->updated = gmdate('Y-m-d H:i:s');
		$config->limit = 1;
		return $config;
	}
	
	function __delete()
	{
		$config = new Config();
		$config->set->deleted = gmdate('Y-m-d H:i:s');
		$config->limit = 1;
		return $config;
	}
}
