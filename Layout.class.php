<?php
/**
 * Layout.class.php
 * 
 * @creation  2015-04-24
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright 2015 (C) Tomoaki Nagahara All right reserved.
 */

/**
 * Layout
 * 
 * @creation  2015-04-24
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright 2015 (C) Tomoaki Nagahara All right reserved.
 */
class Layout extends OnePiece5
{
	/**
	 * Directory where layout is in.
	 * 
	 * @return string
	 */
	private function _GetLayoutDir()
	{
		if(!$layout_dir = $this->GetEnv('layout-dir')){
			if(is_null($layout_dir)){
				//	Generate example.
				if( $this->Dispatcher() instanceof App ){
					$method = "\$app->SetLayoutDir('app:/app/layout');";
				}else{
					$method = "\$this->SetEnv('layout-dir','app:/app/layout');";
				}
				
				//	Stack Error
				$this->AdminNotice("Layout directory was null. Ex: \\$method\.");
			}
		}
		return $layout_dir;
	}
	
	/**
	 * @return string
	 */
	private function _GetLayoutName()
	{
		if(!$layout = $this->GetEnv('layout-name') ){
			if(is_null($layout)){
				//	Generate example.
				if( $this->Dispatcher() instanceof App ){
					$example = "\$app->SetLayoutName('layout-name');";
				}else{
					$example = "\$this->SetEnv('layout-name','flat');";
				}
				$this->AdminNotice("Layout name was null. Ex: \\$example\.");
			}
		}
		return $layout;
	}
	
	/**
	 * Call from layout class inner method.
	 */
	function Content()
	{
		$this->Dispatcher()->Content();
	}
	
	/**
	 * Execute layout.
	 * 
	 * @return boolean
	 */
	function Execute($execute_file=null)
	{
		if(!$execute_file){

			//	Do not want to layout. (False is if not want layout.)
			if( $this->GetEnv('layout') === false ){
				$this->Content();
				return;
			}

			//	Get layout settings.
			$layout_dir  = $this->_GetLayoutDir();
			$layout_name = $this->_GetLayoutName();
			
			//  Get controller name (layout controller)
			$controller = $this->GetEnv('controller-name');
			
			//	Get layout controller path.
			$layout_path = $this->ConvertPath(rtrim($layout_dir,'/')."/$layout_name");
			$execute_file = "$layout_path/$controller";
		}
		
		if(!file_exists($execute_file)){
			$this->AdminNotice("This file does not exist..\n $execute_file");
			return false;
		}
		
		//	Execute layout controller.
		include($execute_file);

		//	For compatibility.
		if(!isset($_layout)){
			$this->AdminNotice('\\$_layout\ variable was empty.');
		}

		//	Check variable.
		if(empty($_layout)){
			return false;
		}
		
		//  Rebuild layout directory.
		$layout_dir = dirname($execute_file) . '/';
		
		//	Execute each part.
		foreach($_layout as $var_name => $file_name){
			//	Build path.
			$path = $layout_dir . $file_name;
			
			if(!file_exists($path)){
				$this->AdminNotice("Layout file does not exist. \($path)\\");
				return false;
			}
			
			//	Assembly of layout.
			ob_start();
			try{
				$this->mark($path,'layout');
				include($path);
				${$file_name} = ob_get_contents();
				${$var_name}  = & ${$file_name}; // TODO: & <- ???
			}catch( Exception $e ){
				$text = $e->getMessage();
				if( method_exists($e,'getLang') ){
					$lang = $e->getLang();
				}else{ $lang = null; }
				$this->AdminNotice($text,$lang);
			}
			ob_end_clean();
		}
		
		//	Execute layout controller.
		if( isset(${$file_name}) ){
			echo ${$file_name};
		}else{
			$this->AdminNotice("Unknown error. ($file_name)");
			return false;
		}
		
		return true;
	}

	/**
	 * Register of dispatcher.
	 *
	 * @see http://onepiece-framework.com/reference/dispatcher
	 * @return NewWorld5 | App | App_i18n
	 */
	function Dispatcher($dispatcher=null)
	{
		static $_dispatcher = null;
		if( $dispatcher ){
			$_dispatcher = $dispatcher;
			return;
		}
		
		if( $_dispatcher ){
			return $_dispatcher;
		}
		
		throw new OpException("Dispatcher has not been registered.",'en');
	}
	
	function __call($name, $args)
	{
		if( $dispatcher = $this->Dispatcher() ){
			if( method_exists($dispatcher, $name) ){
				if( count($args) > 10 ){
					$this->AdminNotice("Limit of argument is exceeded.");
				}
				for($i=0; $i<10; $i++){
					${"arg{$i}"} = isset($args[$i]) ? $args[$i]: null;
				}
				return $dispatcher->$name($arg0, $arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9);
			}
		}
		parent::__call($name, $args);
	}

	/*
	static function __callStatic($name, $args)
	{
		if( $dispatcher = $this->Dispatcher() ){
			if( method_exists($dispatcher, $name) ){if( count($args) > 10 ){
					$this->AdminNotice("Limit of argument is exceeded.");
				}
				for($i=0; $i<10; $i++){
					${"arg{$i}"} = isset($args[$i]) ? $args[$i]: null;
				}
				return $dispatcher::$name($arg0, $arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9);
			}
		}
		parent::__callStatic($name, $args);
	}
	*/
}
