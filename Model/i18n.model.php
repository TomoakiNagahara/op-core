<?php
/**
 * op-core/model/i18n.model.php
 * 
 * i18n model is translate by cloud engine.
 * 
 * @creation  2015-12-26
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/**
 * Model_i18n
 * 
 * @creation  2015-12-26
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Model_i18n extends Model_Model
{
	private $_lang;
	private $_country;
	private $_debug;
	
	/**
	 * @return Config_i18n
	 */
	function Config()
	{
		static $config;
		if(!$config){
			$config = new Config_i18n();
		}
		return $config;
	}
	
	/**
	 * Set locale.
	 * 
	 * @param  string $locale
	 * @return boolean
	 */
	function SetLocale($locale)
	{
		foreach( array('-','_') as $needle ){
			if( strpos($lang, $needle) ){
				list($lang, $country) = explode($needle, $lang);
			}
		}
		
		if( empty($lang) and empty($country) ){
			$this->AdminNotice("Wrong value. ($locale)");
			return false;
		}
		
		$this->SetLang($lang);
		$this->SetCountry($country);
	}
	
	/**
	 * Get locale.
	 * 
	 * @return string
	 */
	function GetLocale()
	{
		return $this->GetLang().'-'.$this->GetCountry();
	}
	
	/**
	 * Set language code.
	 *
	 * @param string $lang
	 */
	function SetLang($lang)
	{
		foreach( array('-','_') as $needle ){
			if( strpos($lang, $needle) ){
				return $this->SetLocale($lang);
			}
		}
		
		$this->_lang = $lang;
		$this->SetCookie('lang',$lang);
	}
	
	/**
	 * Get language code.
	 *
	 * @return string $lang
	 */
	function GetLang()
	{
		if( $lang = $this->_lang ){
			//	OK
		}else 
		if( $lang = $this->GetCookie('lang') ){
			//	OK
		}else{
			$lang = $this->GetEnv('lang');
		}
		
		//	Legacy of the past.
		list($lang) = explode('-', $lang);
		
		return $lang;
	}
	
	/**
	 * Set country code.
	 *
	 * @param string $country
	 */
	function SetCountry($country)
	{
		$this->_country = $country;
		$this->SetCookie('country',$country);
	}
	
	/**
	 * Get country code.
	 *
	 * @return string $country
	 */
	function GetCountry()
	{
		if( $country = $this->_country){
			//	OK
		}else
		if( $country = $this->GetCookie('country') ){
			//	OK
		}else{
			$url = $this->Config()->url('geo');
			$json = $this->Model('Curl')->Json($url);
			$country = $json['geo']['code'];
		}
		return $country;
	}
	
	/**
	 * Translation from English.
	 *
	 * @param  string $source
	 * @param  string $to
	 * @return string
	 */
	function Ja($source, $to=null)
	{
		return $this->Get($source, 'ja-jp', $to);
	}
	
	/**
	 * Translation from English.
	 * 
	 * @param  string $source
	 * @param  string $to
	 * @return string
	 */
	function En($source, $to=null)
	{
		return $this->Get($source, 'en-us', $to);
	}
	
	/**
	 * Translate.
	 * 
	 * @param  string $source
	 * @param  string $to
	 * @param  string $from
	 * @return string
	 */
	function Get($source, $from, $to=null)
	{
		//	From is source language.
		if( empty($from) ){
			$this->AdminNotice("Source language code is empty.");
			$from = 'en-us';
		}
		
		//	To is translation language.
		if( empty($to) ){
			$to = $this->GetLocale();
		}
		
		//	Generate cache key.
		$id = md5("$source, $to, $from");
		$id = substr($id, 0, 8);
		
		//	Fetch from memcache.
		if( $translation = $this->Cache()->Get($id) ){
			if( $this->Admin() ){
				$this->_debug['cache'][$id] = $translation;
			}
			return $translation;
		}
		
		//	Fetch from database.
		if( $translation = $this->Select($id) ){
			return $translation;
		}
		
		//	Fetch from cloud.
		$url = $this->Config()->url_i18n($source, $from, $to);
		$json = $this->Model('Curl')->Json($url);
		
		//	Error check.
		if( empty($json) or !empty($json['error']) ){
			if(!empty($json['error'])){
				$this->AdminNotice($json['error']);
			}
			return preg_replace('|\\\|', '', $source);
		}
		
		//	Get
		$translation = ifset($json['translate'], $source);
		
		//	Save to memcache.
		$expire = $this->Admin() ? 60*60*3*1: 60*60*24*1;
		$this->Cache()->Set($id, $translation, $expire);
		
		//	Save to database.
		$this->Insert($id, $source, $to, $from, $translation);
		
		return $translation;
	}
	
	function GetLanguageList($lang='en')
	{
		$ckey = md5("$lang");
		if( $list = $this->Cache()->Get($ckey) ){
			return $list;
		}
		
		$url  = $this->Config()->url('lang');
		$json = $this->Model('Curl')->Json($url);
		$list = ifset($json['language'], array('en'=>'English'));
		
		//	Error is noting.
		if(!empty($json) and empty($json['error']) ){
			$this->Cache()->Set($ckey, $list);
		}else{
			$error= ifset($json['error'], "Network error.");
			$this->AdminNotice("$error");
		}
		
		return $list;
	}
	
	/**
	 * Select from database by id.
	 * 
	 * @param  string $id
	 * @return array
	 */
	function Select($id)
	{
		$config = $this->Config()->select($id);
		$record = $this->DB()->Select($config);
		if( $this->Admin() ){
			$this->_debug['db']['select'][$id]['config'] = Toolbox::toArray($config);
			$this->_debug['db']['select'][$id]['record'] = $record;
		}
		return ifset($record['translation'], null);
	}
	
	/**
	 * Insert translated result.
	 * 
	 * @param  string $ckey
	 * @param  string $source
	 * @param  string $to
	 * @param  string $from
	 * @param  string $translation
	 * @return boolean
	 */
	function Insert($id, $source, $to, $from, $translation)
	{
		if( $source === $translation ){
			return false;
		}
		
		$config = $this->Config()->insert($id, $source, $to, $from, $translation);
		$new_id = $this->DB()->Insert($config);
		if( $this->Admin() ){
			$this->_debug['db']['insert'][$id]['config'] = Toolbox::toArray($config);
		}
		return $new_id ? true: false;
	}
	
	/**
	 * Update
	 * 
	 * @param  string $id
	 * @return boolean
	 */
	function Update($id)
	{
		$config = $this->Config()->update($id);
		$number = $this->DB()->Update($config);
		if( $this->Admin() ){
			$this->_debug['db']['update'][$id]['config'] = Toolbox::toArray($config);
			$this->_debug['db']['update'][$id]['record'] = $record;
		}
		return $number ? true: false;
	}
	
	/**
	 * For developer.
	 */
	function Debug()
	{
		if(!$this->Admin()){
			return;
		}
		
		$this->P("Debug: Model-i18n");
		$this->D($this->_debug);
		$this->DB()->Debug();
		$this->Model('Curl')->Debug();
	}
}

/**
 * Config_i18n
 * 
 * @creation  2015-12-26
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Config_i18n extends Config_Model
{
	/**
	 * Get fetch url.
	 * 
	 * @param  string $key
	 * @return string
	 */
	function url($key)
	{
		static $domain;
		if(!$domain){
			$domain = $this->Admin() ? 'http://api.uqunie.com': 'http://api.uqunie.com';
		}
		switch($key){
			case 'i18n':
				$url = "$domain/i18n/";
				break;
				
			case 'lang':
				$url = "$domain/i18n/lang/";
				break;
				
			case 'geo':
				$url = "$domain/geo/?ip=".$_SERVER['REMOTE_ADDR'];
				break;
		}
		return $url;
	}
	
	/**
	 * Get translation rul.
	 * 
	 * @param  string $source
	 * @param  string $from
	 * @param  string $to
	 * @return string
	 */
	function url_i18n($source, $from, $to)
	{
		$url  = $this->url('i18n');
		$url .= '?';
		$url .= 'text=' .urlencode($source);
		$url .= '&from='.urlencode($from);
		$url .= '&to='  .urlencode($to);
		return $url;
	}
	
	/**
	 * Get select Config by id.
	 * 
	 * @param  string $id
	 * @return Config
	 */
	function select($id)
	{
		$select = $this->__select();
		$select->where->{self::_COLUMN_ID_}		 = $id;
		return $select;
	}
	
	/**
	 * Get insert Config.
	 * 
	 * @param  string $id
	 * @param  string $source
	 * @param  string $to
	 * @param  string $from
	 * @param  string $translation
	 * @return Config
	 */
	function insert($id, $source, $to, $from, $translation)
	{
		$insert = $this->__insert();
		$insert->set->{self::_COLUMN_ID_}        = $id;
		$insert->set->{self::_COLUMN_LANG_FROM_} = $from;
		$insert->set->{self::_COLUMN_LANG_TO_}   = $to;
		$insert->set->{self::_COLUMN_TEXT_FROM_} = $source;
		$insert->set->{self::_COLUMN_TEXT_TO_}   = $translation;
		return $insert;
	}
	
	/**
	 * Get update Config by id.
	 * 
	 * @param  string $id
	 * @return Config
	 */
	function update($id)
	{
		$update = $this->__update();
		$update->set->{self::_COLUMN_ID_}		 = $id;
		$update->set->{self::_COLUMN_UPDATED_}	 = '';
		return $update;
	}
	
	/**
	 * Get database Config.
	 * 
	 * @return Config
	 */
	function database(/*$database=null*/)
	{
		static $_database;
		
		/*
		//	Custom database setting.
		if( $database ){
			//	Check
			if( $_database ){
				$this->AdminNotice("Is set already.");
				return false;
			}
			
			//	Get default database setting.
			if(!$_database){
				$_database = parent::__database(array('user'=>'op_mdl_i18n'));
			}
			
			//	Overwrite.
			foreach($database as $key => $var){
				$_database->$key = $var;
			}
		}
		*/
		
		//	Default database setting.
		if(!$_database){
			if(!$database = $this->GetEnv('model-i18n')){
				$database['user'] = 'model_mdl_i18n';
			}
			$_database = parent::__database($database);
		}
		
		return $_database;
	}
	
	/**
	 * Get table name.
	 * @return string
	 */
	function table_name()
	{
		return 'i18n';
	}
	
	const _COLUMN_ID_		 = 'id';
	const _COLUMN_LANG_FROM_ = 'source';
	const _COLUMN_LANG_TO_	 = 'target';
	const _COLUMN_TEXT_FROM_ = 'text';
	const _COLUMN_TEXT_TO_	 = 'translation';
	
	/**
	 * Get self-test Config.
	 * 
	 * @return Config
	 */
	function selftest()
	{
		//  Create config
		$config = new Config();
		
		//	Database
		$config->database = $this->database();
		
		//  Tables
		$table_name = $this->table_name();
		$config->table->{$table_name}->table   = $table_name;
		$config->table->{$table_name}->comment = 'Used at i18n model.';
		
		//  Columns
		$column_name = self::_COLUMN_ID_;
		$config->table->{$table_name}->column->{$column_name}->name		 = $column_name;
		$config->table->{$table_name}->column->{$column_name}->type		 = 'char';
		$config->table->{$table_name}->column->{$column_name}->length	 = '8';
		$config->table->{$table_name}->column->{$column_name}->pkey		 = true;
		$config->table->{$table_name}->column->{$column_name}->comment	 = 'Unique id.';
		
		$column_name = self::_COLUMN_LANG_FROM_;
		$config->table->{$table_name}->column->{$column_name}->name		 = $column_name;
		$config->table->{$table_name}->column->{$column_name}->type		 = 'varchar';
		$config->table->{$table_name}->column->{$column_name}->length	 = '5';
		$config->table->{$table_name}->column->{$column_name}->comment	 = 'Original language.';
		
		$column_name = self::_COLUMN_LANG_TO_;
		$config->table->{$table_name}->column->{$column_name}->name		 = $column_name;
		$config->table->{$table_name}->column->{$column_name}->type		 = 'varchar';
		$config->table->{$table_name}->column->{$column_name}->length	 = '5';
		$config->table->{$table_name}->column->{$column_name}->comment	 = 'Translation language.';
		
		$column_name = self::_COLUMN_TEXT_FROM_;
		$config->table->{$table_name}->column->{$column_name}->name		 = $column_name;
		$config->table->{$table_name}->column->{$column_name}->type		 = 'text';
		$config->table->{$table_name}->column->{$column_name}->comment	 = 'Original text.';
		
		$column_name = self::_COLUMN_TEXT_TO_;
		$config->table->{$table_name}->column->{$column_name}->name		 = $column_name;
		$config->table->{$table_name}->column->{$column_name}->type		 = 'text';
		$config->table->{$table_name}->column->{$column_name}->comment	 = 'Translation text.';
		
		$column_name = 'created';
		$config->table->{$table_name}->column->{$column_name}->name		 = $column_name;
		$config->table->{$table_name}->column->{$column_name}->type		 = 'datetime';
		$config->table->{$table_name}->column->{$column_name}->comment	 = '';
		
		$column_name = 'updated';
		$config->table->{$table_name}->column->{$column_name}->name		 = $column_name;
		$config->table->{$table_name}->column->{$column_name}->type		 = 'datetime';
		$config->table->{$table_name}->column->{$column_name}->comment	 = '';
		
		$column_name = 'deleted';
		$config->table->{$table_name}->column->{$column_name}->name		 = $column_name;
		$config->table->{$table_name}->column->{$column_name}->type		 = 'datetime';
		$config->table->{$table_name}->column->{$column_name}->comment	 = '';
		
		$column_name = 'timestamp';
		$config->table->{$table_name}->column->{$column_name}->name		 = $column_name;
		$config->table->{$table_name}->column->{$column_name}->type		 = 'timestamp';
		$config->table->{$table_name}->column->{$column_name}->comment	 = '';
	
		return $config;
	}
}
