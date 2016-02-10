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

	/**
	 * Set admin IP-Address. Use for admin notice.
	 * 
	 * <pre>
	 * $this->SetAdminIP('192.168.1.1');
	 * </pre>
	 * 
	 * @param string $var IP-Address
	 */
	function SetAdminIP($var)
	{
		$this->SetEnv('admin-ip', $var);
	}

	/**
	 * Set admin EMail Address. Use for admin notice.
	 * 
	 * <pre>
	 * $this->SetAdminEMail('user@example.com');
	 * </pre>
	 * 
	 * @param string $var EMail address
	 */
	function SetAdminEMail($var)
	{
		$this->SetEnv('admin-mail', $var);
	}

	/**
	 * Set controller name. So-called end-point.
	 * 
	 * <pre>
	 * $this->SetControllerName('index.php');
	 * </pre>
	 * 
	 * @param string $var file name
	 */
	function SetControllerName( $var )
	{
		$this->SetEnv('controller-name', $var);
	}

	/**
	 * Set model directory. Meata-qualifier can be used.
	 *
	 * <pre>
	 * $this->SetModelDir('app:/app/model');
	 * </pre>
	 *
	 * @param string $var file path.
	 */
	function SetModelDir( $var )
	{
		$this->SetEnv('model-dir', $var);
	}

	/**
	 * Set unit directory. Meata-qualifier can be used.
	 *
	 * <pre>
	 * $this->SetUnitDir('app:/app/unit');
	 * </pre>
	 *
	 * @param string $var file path.
	 */
	function SetUnitDir( $var )
	{
		$this->SetEnv('unit-dir', $var);
	}

	/**
	 * Set layout directory. Meata-qualifier can be used.
	 *
	 * <pre>
	 * $this->SetLayoutDir('app:/app/layout');
	 * </pre>
	 *
	 * @param string $var file path.
	 */
	function SetLayoutDir( $var )
	{
		$this->SetEnv('layout-dir', $var);
	}

	/**
	 * Set layout name.
	 *
	 * <pre>
	 * $this->SetLayoutName('flat');
	 * </pre>
	 *
	 * @param string $var layout name.
	 */
	function SetLayoutName( $var )
	{
		$this->SetEnv('layout-name', $var);
	}

	/**
	 * Get layout name.
	 *
	 * <pre>
	 * $layout = $this->GetLayoutName();
	 * </pre>
	 *
	 * @return string
	 */
	function GetLayoutName()
	{
		return $this->GetEnv('layout-name');
	}

	/**
	 * Set template directory. Meta-qualifier can be used.
	 * 
	 * <pre>
	 * $this->SetTemplateDir('app:/app/template');
	 * </pre>
	 * 
	 * @param string $var
	 */
	function SetTemplateDir( $var )
	{
		$this->SetEnv('template-dir', $var);
	}

	/**
	 * Get template directory.
	 *
	 * <pre>
	 * $this->GetTemplateDir();
	 * </pre>
	 *
	 * @return string
	 */
	function GetTemplateDir()
	{
		return $this->GetEnv('template-dir');
	}

	/**
	 * Html-pass-through-feature is not need controller.
	 * Html files's content is direct output.
	 * Of course, PHP is run! in content!!
	 * 
	 * <pre>
	 * $this->SetHtmlPassThrough(true);
	 * </pre>
	 *
	 * @param boolean $var
	 */
	function SetHtmlPassThrough( $var )
	{
		$this->SetEnv('HtmlPassThrough', $var);
	}

	/**
	 * Get current value.
	 * 
	 * <pre>
	 * $this->GetTitle();
	 * </pre>
	 * 
	 * @return string
	 */
	function GetTitle()
	{
		return $this->GetEnv('title');
	}

	/**
	 * Set value will use at title tag. Existing title will be overwritten.
	 * 
	 * <pre>
	 * $this->SetTitle('My site name');
	 * </pre>
	 * 
	 * @param string $var
	 */
	function SetTitle( $var )
	{
		$this->SetEnv('title', $var);
	}

	/**
	 * Prepend to existing value.
	 * 
	 * <pre>
	 * $this->PrependTitle('Page Name:');
	 * </pre>
	 * 
	 * @param string $var
	 */
	function PrependTitle($var)
	{
		$this->SetTitle($var. $this->GetTitle());
	}

	/**
	 * Append to existing value.
	 * 
	 * <pre>
	 * $this->AppendTitle(' - Supplemental');
	 * </pre>
	 * 
	 * @param string $var
	 */
	function AppendTitle($var)
	{
		$this->SetTitle($this->GetTitle().$var);
	}

	/**
	 * Output title tag.
	 * 
	 * <pre>
	 * <head>
	 * <?php $this->Title(); ?>
	 * </head>
	 * </pre>
	 */
	function Title()
	{
		print '<title>'.$this->GetTitle('title').'</title>';
	}

	/**
	 * Set doctype.
	 * 
	 * <pre>
	 * $this->SetDoctype('html');
	 * $this->SetDoctype('xml');
	 * $this->SetDoctype('xhtml');
	 * </pre>
	 * 
	 * @param string $var
	 */
	function SetDoctype( $var )
	{
		$this->SetEnv('doctype', $var);
	}

	/**
	 * Output doctype tag.
	 * 
	 * <pre>
	 * <?php $this->Doctype('html'); ?>
	 * </pre>
	 * 
	 * @param string $doctype html, xml, xhtml
	 * @param number $version 5, 4, 4.01
	 */
	function Doctype( $doctype='html', $version=null )
	{
		if(!$doctype){
			$doctype = $this->GetEnv('doctype');
		}

		switch($doctype){
			case 'html':
				if( $version === null or $version === 5 ){
					$doctype = '<!DOCTYPE html>';
				}else
				if( $version == 4 or $version == '4.01' ){
					$doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">';
				}
				break;
			case 'xml':
				$doctype = '<?xml version="1.0" encoding="UTF-8"?>';
				break;
			case 'xhtml':
				$doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN">';
				break;
			default:
				$doctype = null;
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
	 * 
	 * @param string file path.
	 */
	function Action($file)
	{
		$this->Template($file);
	}
}
