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
				$url = $this->GetEnv('URL_SELF_TEST', "app:/_self-test/");
				$this->Location($url);
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
	const _COLUMN_CREATED_   = 'created';
	const _COLUMN_UPDATED_   = 'updated';
	const _COLUMN_DELETED_   = 'deleted';
	const _COLUMN_TIMESTAMP_ = 'timestamp';
	
	/**
	 * Get default database configuration.
	 * 
	 * @param  array $args
	 * @return Config
	 */
	function __database($args=null)
	{
		$database = new Config();
		$database->driver    = 'mysql';
		$database->host      = 'localhost';
		$database->port      = '3306';
		$database->name      = 'onepiece';
		$database->charset   = 'utf8';
		$database->user      = 'op_mdl_model';
		
		//	Overwrite.
		if( $args ){
			foreach($args as $key => $var){
				$database->$key = $var;
			}
		}
		
		//	Password.
		if( empty($database->password) ){
			$database->password	 = $this->__password($database);
		}
		
		return $database;
	}
	
	/**
	 * Generate password.
	 * 
	 * @param unknown $database
	 */
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

		switch($server_addr = $_SERVER['SERVER_ADDR']){
			case '::1':
			case '127.0.0.1':
				$server_addr = 'localhost';
				break;
		}

		return md5($server_addr.', '.$database->name.', '.$database->user);
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
