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
	 * Old method name.
	 */
	function pdo()
	{
		return $this->DB();
	}
	
	/**
	 * Database
	 * 
	 * @throws OpException
	 * @return PDO5
	 */
	function DB()
	{
		$pdo = parent::PDO();	
		if(!$pdo->isConnect()){
			
			//  get database config
			$database = $this->config()->database();
			if(!$database instanceof Config ){
				throw new OpException("Database connection configuration was not \Config\ object.",'en');
			}
			
			//	databaes name
			if( isset($database->name) ){
				$database->database = $database->name;
			}
			
			//	Do database connection
			if(!$io = $pdo->Connect($database)){
				$this->ReservationToDiagnosis();
				$this->AdminNotice("Connection was failed.");
				$this->Location("app:/_self-test/");
			}
		}
		return $pdo;
	}
	
	/**
	 * Reservation of self-test.
	 */
	function ReservationToDiagnosis()
	{
		if( $this->Admin() ){
			$object = new ReflectionObject($this->Config());
			$class_name = $object->getName();
			$file_path  = $object->getFileName();
			$this->Doctor()->Reservation($class_name, $file_path);
		}
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
			$this->AdminNotice('Arguments was empty.');
			return md5(__METHOD__);
		}
		
		if( empty($database->name) ){
			$this->AdminNotice('Database name was empty.');
			return md5(__METHOD__);
		}
		
		if( empty($database->user) ){
			$this->AdminNotice('User name was empty.');
			return md5(__METHOD__);
		}
		
		return md5($_SERVER['SERVER_ADDR'].', '.$database->name.', '.$database->user);
	}
	
	function __select()
	{
		$config = new Config();
		$config->table = $this->table_name();
		$config->where->deleted = null;
		$config->limit = 1;
		$config->cache = 1;
		return $config;
	}
	
	function __insert()
	{
		$config = new Config();
		$config->table = $this->table_name();
		$config->set->created = gmdate('Y-m-d H:i:s');
		return $config;
	}
	
	function __update()
	{
		$config = new Config();
		$config->table = $this->table_name();
		$config->set->updated = gmdate('Y-m-d H:i:s');
		$config->limit = 1;
		return $config;
	}
	
	function __delete()
	{
		$config = new Config();
		$config->table = $this->table_name();
		$config->set->deleted = gmdate('Y-m-d H:i:s');
		$config->limit = 1;
		return $config;
	}
}
