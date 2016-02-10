<?php
/**
 * i18n.app.php
 * 
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/**
 * App_i18n
 * 
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class App_i18n extends App
{
	/***
	 * Html's lang attribute, and translation lang code.
	 * 
	 * {@inheritDoc}
	 * @see NewWorld5::SetLang()
	 * @param $lang translate to language code.
	 */
	function SetLang($lang)
	{
		//	Use for html's lang attribute.
		parent::SetLang($lang);

		//	Use for translation's to lang code.
		$this->i18n()->SetLang($lang);
	}

	/**
	 * Wrap the NewWorld Dispatch method.
	 * 
	 * 1st SmartURL arguments is language code.
	 * Convert to real path from SmartURL.
	 * 
	 * @see NewWorld5::Dispatch()
	 * @param  array   $route
	 * @return boolean
	 */
	function Dispatch($route=null)
	{
		if(!$route){
			//	Entry i18n env.
			$this->SetEnv('app.i18n',true);
			
			//	Parse URL arguments.
			if( strpos($_SERVER['REQUEST_URI'], '?') ){
				list($request_uri, $query) = explode('?',$_SERVER['REQUEST_URI']);
				$query = '?'.$query;
			}else{
				$request_uri = $_SERVER['REQUEST_URI'];
				$query = null;
			}
			$args = explode('/',ltrim($request_uri, '/'));
			
			//	Get language list
			if(!$list = $this->i18n()->GetLanguageList()){
				throw new OpException("Empty language list from api.uqunie.com");
			}
			
			//	Check language code.
			if(!array_key_exists($args[0], $list) ){
				
				//	Get current language code.
				$lang = $this->i18n()->GetLang();
				
				//	In case of localhost.
				if( count($list) <= 1 ){
					if( strlen($args[0]) === 2 ){
						$lang = $args[0];
						$skip = true;
					}
				}
				
				//	Build URL.
				$url = "/{$lang}/".trim(join('/',$args),'/').$query;
				
				//	Set Content method call flag.
				Env::Set(self::_IS_CONTENT_, true);
				
				//	Transfer.
				if( empty($skip) ){
					header("Location: $url"); exit;
				}
			}
			
			//	Get language code.
			$lang = array_shift($args);
			$this->i18n()->SetLang($lang);
			
			//	Set Rewrite base.
			$rewrite_base = Toolbox::GetRewriteBase().$lang.'/';
			Toolbox::SetRewriteBase($rewrite_base);
			
			//	Re:Build Request URI.
			$request_uri = '/'.join('/',$args);

			//	Path info
			$pathinfo = pathinfo($request_uri);
			
			//	Branch by extension.
			if( isset($pathinfo['extension']) ){
				switch(strtolower($pathinfo['extension'])){
					case '':
					case 'js':
					case 'css':
					case 'html':
					case 'php':
						break;
						
					default:
						//	Set Content method call flag.
						Env::Set(self::_IS_CONTENT_, true);
						
						//	Transfer real path. (ex. /img/logo.png)
						header("Location: {$request_uri}{$query}"); exit;
				}
			}
			
			//	Get route infomation.
			$route = Router::GetRoute($request_uri.$query);
		}
		
		return parent::Dispatch($route);
	}
}
