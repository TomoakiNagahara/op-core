<?php
/**
 * App.class.php
 * 
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/**
 * App
 * 
 * @author tomoaki.nagahara@gmail.com
 */
class App extends NewWorld5
{
	/**
	 * SmatURL's key separate character 
	 * 
	 * @var string
	 */
	private $_args_keys_needle = ':';

	/**
	 * Get URL-Argument. (SmartURL)
	 * 
	 * (non-PHPdoc)
	 * @see NewWorld5::GetArgs()
	 */
	function GetArgs($key=null)
	{
		//	Accelerate
		static $args;
		if(!$args){
			$args = parent::GetArgs();
		}

		//	Return all
		if( is_null($key) ){
			return $args;
		}

		//	Return a specific value.
		if( is_int($key) ){
			//	at integer
			$result = isset($args[$key]) ? $args[$key]: null;
		}else{
			//	at key (ex. /foo/bar/color:red/ )
			$result = null;
			$needle = $this->_args_keys_needle;
			foreach( $args as $var ){
				if( strpos( $var, $needle ) ){
					$temp = explode( $needle, $var );
					if( $temp[0] === $key ){
						$result = $temp[1];
						break;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Get Action name by URL.
	 * 
	 * <pre>
	 * $action = $this->GetAction();
	 * </pre>
	 * 
	 * @return string
	 */
	function GetAction()
	{
		$args = $this->GetArgs();
		$action = empty($args[0]) ? 'index': $args[0];
		return $action;
	}

	function SetAdminIP($var)
	{
		$this->SetEnv('admin-ip', $var);
	}

	function SetAdminEMail($var)
	{
		$this->SetEnv('admin-mail', $var);
	}

	function SetControllerName( $var )
	{
		$this->SetEnv('controller-name', $var);
	}

	function SetModelDir( $var )
	{
		$this->SetEnv('model-dir', $var);
	}

	function SetUnitDir( $var )
	{
		$this->SetEnv('unit-dir', $var);
	}

	function SetLayoutDir( $var )
	{
		$this->SetEnv('layout-dir', $var);
	}

	{
		$this->SetEnv('layout-name', $var);
	}

	function GetLayoutName()
	{
		return $this->GetEnv('layout-name');
	}

	function SetTemplateDir( $var )
	{
		$this->SetEnv('template-dir', $var);
	}

	function GetTemplateDir()
	{
		return $this->GetEnv('template-dir');
	}

	function SetHtmlPassThrough( $var )
	{
		$this->SetEnv('HtmlPassThrough', $var);
	}

	function GetTitle()
	{
		return $this->GetEnv('title');
	}

	function SetTitle( $var )
	{
		$this->SetEnv('title', $var);
	}
	function GetTitle()

	{
		$this->SetTitle($var. $this->GetTitle());
	}

	function Title()
	{
		print '<title>'.$this->GetTitle('title').'</title>';
	}

	function SetDoctype( $var )
	{
		$this->SetEnv('doctype', $var);
	}
	function Doctype( $doctype=null, $version=null )

	{
		if(!$doctype){
			$doctype = $this->GetEnv('doctype');
		}

		switch($doctype){
			case 'xml':
				$doctype = '<?xml version="1.0" encoding="UTF-8"?>';
				break;
				
			case 'xhtml':
				$doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN">';
				break;

			case 'html':
				if( $version == 4 or $version == '4.01' ){
					$doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">';
				}else{
					$doctype = '<!DOCTYPE html>';
				}
				break;
				
			default:
				$doctype = '<!DOCTYPE html>';
		}
		print $doctype.PHP_EOL;
	}

	function SetCharset( $var )
	{
		$this->SetEnv('charset',$var);
	}

	function GetCharset( $args=null )
	{
		return $this->GetEnv('charset');
	}

	function AddKeyword( $var )
	{
		$this->AddKeywords( $var );
	}

	function AddKeywords( $var )
	{
		$keywords = $this->GetEnv('keywords');
		$keywords.= ", $var";
		$this->SetEnv('keywords',$keywords);
	}

	function SetKeyword( $var )
	{
		$this->SetEnv('keywords',$var);
	}

	function SetKeywords( $var )
	{
		$this->SetEnv('keywords',$var);
	}

	function GetKeywords()
	{
		return $this->GetEnv('keywords');
	}

	function Keywords()
	{
		print '<meta name="keywords" content="'.$this->GetKeywords().'">';
	}

	function SetDescription( $var )
	{
		$this->SetEnv('description',$var);
	}

	function GetDescription()
	{
		return $this->GetEnv('description');
	}

	function Description()
	{
		print '<meta name="description" content="'.$this->GetDescription().'">';
	}

	function SetNotFoundPage($filepath)
	{
		$this->SetEnv(NewWorld5::_NOT_FOUND_PAGE_, $filepath);
	}

	function SetTablePrefix( $prefix )
	{
		$this->SetEnv('table_prefix',$prefix);
	}

	function En($english)
	{
		return $this->i18n()->En($english);
	}

	function Ja($japanese)
	{
		return $this->i18n()->Ja($japanese);
	}

	/**
	 * @return Model_Cloud
	 */
	function ModelCloud()
	{
		static $model;
		if(!$model){
			$model = $this->Model('Cloud');
		}
		return $model;
	}

	/**
	 * Import is wrapper of Template method.
	 * 
	 * @param  string $path
	 * @return Ambigous <string, boolean>
	 */
	function Import($path)
	{
		return $this->Template($path);
	}

	/**
	 * Wrapper of NewWorld5's Dispatch method.
	 * Do check of Admin-IP and Admin-Mail.
	 * 
	 * (non-PHPdoc)
	 * @see NewWorld5::Dispatch()
	 */
	function Dispatch($route=null)
	{
		$admin_ip	 = Env::Get('admin-ip');
		$admin_email = Env::Get('admin-mail');

		//	Checking Administrator's settings.
		if(!Toolbox::isLocalhost() and (!$admin_ip or !$admin_email) ){
			$this->SetLayoutName(false);
			$path = $this->ConvertPath('op:/Template/introduction-app.phtml');
			$route['real_path']	 = $path;
			$route['extension']	 = 'phtml';
			$route['mime']		 = Router::CalcMime('phtml');
			$route['debug'][] = 'App have created a route table.';
			$route['debug'][] = __FILE__.', '.__METHOD__.', '.__LINE__;
		}

		//	Execute page controller.(End-poind)
		parent::Dispatch($route);
	}

	/**
	 * Set Favicon URL
	 * 
	 * @param string URL
	 */
	function SetFavicon($var)
	{
		Env::Set('favicon',$var);
	}

	/**
	 * Get Favicon URL
	 * 
	 * @return string URL
	 */
	function GetFavicon()
	{
		return Toolbox::ConvertURL(Env::Get('favicon'));
	}

	/**
	 * Load action file.
	 */
	function Action($file)
	{
		$this->Template($file);
	}
}
