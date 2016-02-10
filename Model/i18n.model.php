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
	 * {@inheritDoc}
	 * @see OnePiece5::Init()
	 */
	function Init()
	{
		parent::Init();

		//	Set default language.
		$this->SetLang( $this->GetLang() );

		//	For admin.
		if( $this->Admin() ){
			$this->_debug['count']['fetch']  = 0;
			$this->_debug['count']['cache']  = 0;
			$this->_debug['count']['select'] = 0;
			$this->_debug['count']['error']  = 0;
			$this->_debug['count']['language']['fetch'] = 0;
			$this->_debug['count']['language']['cache'] = 0;
		}
	}

	/**
	 * Set locale.
	 * 
	 * @param  string $locale
	 * @return boolean
	 */
	function SetLocale($locale)
	{
		$this->_debug[__FUNCTION__][] = $locale;

		$locale = str_replace('_', '-', $locale);
		list($lang, $country) = explode('-', $locale);

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
		return $this->GetLang(); // .'-'.$this->GetCountry();
	}

	/**
	 * Set language code.
	 *
	 * @param string $lang
	 */
	function SetLang($lang)
	{
		$this->_debug[__FUNCTION__][] = $lang;

		if( preg_match('/[-_]/', $lang) ){
			return $this->SetLocale($lang);
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
			//	This is system language code.
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
		$this->_debug[__FUNCTION__][] = $country;
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
			if( isset($json['geo']['code']) ){
				$country = $json['geo']['code'];
				$this->SetCookie('country', $country, 60*60*6);
			}else{
				$country = null;
			}
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
	 * Do translation.
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
		$id = md5("$source, $from, $to, 0");
		$id = substr($id, 0, 8);

		//	Execute by each method.
		if( $translation = $this->_get($id, $source, $from, $to) ){
			//	Save to memcache.
			$this->Cache()->Set($id, $translation);
		}else{
			//	Remove escape character.
			$translation = $this->RemoveBackslash($source);
		}

		return $translation;
	}

	/**
	 * Remove back slash.
	 * 
	 * <pre>
	 * This is a \test\. --> This is a test.
	 * </pre>
	 * 
	 * @param  string $source
	 * @return string
	 */
	function RemoveBackslash($source)
	{
		return preg_replace('|\\\([^\\\]+)\\\|i', '$1', $source);
	}

	/**
	 * Execute each method.
	 * 
	 * @param  string $id
	 * @param  string $source
	 * @param  string $from
	 * @param  string $to
	 * @return string
	 */
	private function _get($id, $source, $from, $to)
	{
		if( $admin = $this->Admin() ){
			$this->_debug[__FUNCTION__][] = "$id, $source, $from, $to";
		}

		if( $translation = $this->Cache()->Get($id) ){
			$type = 'cache';
		}else if( $translation = $this->Select($id) ){
			$type = 'select';
		}else if( $translation = $this->_Translation($id, $source, $from, $to) ){
			$type = 'fetch';
		}else{
			$type = 'error';
			$translation = false;
		}

		if( $admin ){
			$this->_debug['count'][$type]++;
		}

		return $translation;
	}

	/**
	 * Translation by cloud.
	 * 
	 * @param  string $id
	 * @param  string $source
	 * @param  string $from
	 * @param  string $to
	 * @return string|boolean
	 */
	private function _Translation($id, $source, $from, $to)
	{
		$url  = $this->Config()->url_i18n($source, $from, $to);
		$json = $this->Model('Curl')->Json($url);

		//	Error check.
		if( empty($json) ){
			//	Check flag.
			if( $this->Cache()->Get(__METHOD__) ){
				//	Already to notice.
				return false;
			}else{
				//	Set flag.
				$this->Cache()->Set(__METHOD__, true);
			}
			$this->AdminNotice("\\$json\ was empty. (Not connected to the Internet?)");
		}else if(!empty($json['error'])){
			$this->AdminNotice($json['error']);
			return false;
		}else if( empty($json['translate']) ){
			$this->AdminNotice("Translate string is empty. \($source)\\");
			return false;
		}

		//	Save to database.
		$this->Insert($id, $source, $from, $to, $json['translate']);

		return $json['translate'];
	}

	/**
	 * Get the language code that corresponds.
	 * 
	 * @param  string $lang
	 * @return array
	 */
	function GetLanguageList($lang='en')
	{
		$ckey = md5(__METHOD__.', '.$lang);
		if(!$list = $this->Cache()->Get($ckey) ){
			$this->_debug['count']['language']['fetch']++;
		}else{
			$this->_debug['count']['language']['cache']++;
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
	function Insert($id, $source, $from, $to, $translation)
	{
		if( $source === $translation ){
			return false;
		}
		
		$config = $this->Config()->insert($id, $source, $from, $to, $translation);
		$result = $this->DB()->Insert($config);
		
		if( $this->Admin() ){
			$this->_debug['db']['insert'][$id]['config'] = Toolbox::toArray($config);
			$this->_debug['db']['insert'][$id]['result'] = $result;
		}
		
		return $result ? true: false;
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
			$this->_debug['db']['update'][$id]['result'] = $number;
		}
		
		return $number ? true: false;
	}
	
	/**
	 * For developer.
	 */
	function Debug()
	{
		if(!$this->Admin() and !Toolbox::isHtml() ){
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
	 * @param  string $from
	 * @param  string $to
	 * @param  string $translation
	 * @return Config
	 */
	function insert($id, $source, $from, $to, $translation)
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
		$update->where->{self::_COLUMN_ID_}		 = $id;
		return $update;
	}
	
	/**
	 * Get database Config.
	 * 
	 * @return Config
	 */
	function database()
	{
		static $_database;
		
		if(!$_database){
			$database = $this->GetEnv('model-i18n');
			if( empty($database['user']) ){
				$database['user'] = 'op_mdl_i18n';
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
