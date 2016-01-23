<?php
/**
 * Error.class.php
 * 
 * @creation  2014-11-29
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/**
 * Added namespace at 2016-01-16
 */
namespace OP;

/**
 * Error
 * 
 * @creation  2014-02-18
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Error
{
	/**
	 * Stack error.
	 * @var array
	 */
	static private $_error;

	/**
	 * Stack notice message.
	 * 
	 * @param string $message
	 * @param array  $backtrace
	 * @param string $lang langugae code
	 */
	static private function _Set($message, $backtrace=null, $lang=false)
	{
		$key = sha1($message);
		$key = substr($key, 0, 8);

		if( self::$_error === null ){
			self::$_error = &$_SESSION[__CLASS__];
		}

		if(!$backtrace){
			$backtrace = debug_backtrace();
		}

		$error['message']	 = $message;
		$error['backtrace']	 = $backtrace;
		$error['timestamp']	 = date('Y-m-d H:i:s');
		$error['lang']		 = $lang;

		self::$_error[$key] = $error;
	}

	static function Set($e, $lang=null)
	{
		if( $e instanceof Exception ){
			$message   = $e->getMessage();
			$backtrace = $e->getTrace();
			$file      = $e->getFile();
			$line      = $e->getLine();
			$prev      = $e->getPrevious();
			$code      = $e->getCode();
			if( method_exists($e,'getLang') ){
				$lang = $e->getLang();
			}
		}else{
			$message = $e;
			$backtrace = debug_backtrace();
		}

		self::_Set($message, $backtrace, $lang);
	}
	
	static function Get()
	{
		return array_shift(self::$_error);
	}

	static function GetAll()
	{
		while( $error = self::Get() ){
			$errors[] = $error;
		}
		return $errors;
	}

	static function Report()
	{
		if( empty(self::$_error) ){
			return;
		}

		if( \Notice::Report() ){
			return;
		}

		//	Check display is html.
		if(!$is_html = \Toolbox::isHTML() and !$is_cli = \Env::Get('cli') ){
			return;
		}
		
		//	Check admin.
		if( \OnePiece5::Admin() ){
			$io = self::_toDisplay();
		}else{
			$io = self::_toMail();
		}
	}
	
	static private function _getMailSubject()
	{
		foreach($_SESSION[self::_NAME_SPACE_] as $key => $backtraces){
			//	Generate error message.
			$message = $backtraces['message'];
			$message = \OnePiece5::Wiki2($message);
			$message = strip_tags($message);
			$message = \OnePiece5::Decode($message);
			
			//	Translation.
			if( isset($backtraces['translation']) ){
				$temp = explode("\n",$message."\n");
				$from = $backtraces['translation'];
				$message = \OnePiece5::i18n()->En($temp[0], $from);
				$message.= $temp[1];
			}
			
			return $message;
		}
	}
	
	static private function _formatBacktrace( $index, $backtrace, $name )
	{
		static $find;
		$file	 = isset($backtrace['file'])	 ? $backtrace['file']:	 null;
		$line	 = isset($backtrace['line'])	 ? $backtrace['line']:	 null;
		$func	 = isset($backtrace['function']) ? $backtrace['function']: null;
		$class	 = isset($backtrace['class'])	 ? $backtrace['class']:	 null;
		$type	 = isset($backtrace['type'])	 ? $backtrace['type']:	 null;
		$args	 = isset($backtrace['args'])	 ? $backtrace['args']:	 null;
		
		$file	 = \OnePiece5::CompressPath($file);
		
		if( $name === $func or $func === '__get' or $func === 'AdminNotice' ){
			$find = true;
			$style = 'bg-yellow bold';
		}else if( $find ){
			$style = 'gray';
		}else{
			$style = 'gray';
		}
		
		if( $index === 0 ){
			$index   = '';
			$tail	 = $args[0]; // error message
		}else{
			$method	 = $type ? $class.$type.$func: $func;
			$args	 = $args ? self::ConvertStringFromArguments($args, $is_dump): null;
			$tail	 = "$method($args)";
			if(!empty($is_dump) ){
				$did = md5(microtime());
				$tail .= "<span class='dkey more' did='{$did}'>more...</span>";
				$tail .= "<div id='{$did}' style='display:none;'>".\Dump::GetDump($backtrace['args']).'</div>';
			}
		}
		
		$info = "<tr style='font-size:small;' class='{$style}'><th>{$index}</th><td>{$file}</td><th style='text-align:right;'>{$line}</th><td>{$tail}</td></tr>".PHP_EOL;
		
		return $info.PHP_EOL;
	}
	
	static private function _getBacktrace()
	{
		$i = 0;
		$return = '<table>';
		foreach( self::$_error as $error ){
			$i++;
			$name		 = $error['name'];
			$message	 = $error['message'];
			$backtraces	 = $error['backtrace'];
			$from		 = $error['translation'];
			
			//	i18n
			if( $from ){
				//	Separate by line break.
				list($main, $sub) = explode(PHP_EOL, $message.PHP_EOL);
				
				//	Translation.
				$message = \OnePiece5::i18n()->Get($main, $from);
				
				//	Add sub message.
				if( $sub ){
					$message .= ' ![.gray['.trim($sub,'\\').']]';
				}
			}
			
			//	Wiki2 convert. 
			$message = \OnePiece5::Wiki2($message);
			
			//	Sequence no.
			$return .= "<tr><td colspan=4 class='' style='padding:0.5em 1em; color:red; font-weight: bold; font-size:small;'>Error #{$i} $message</td></tr>".PHP_EOL;
			
			if( $count = count($backtraces) ){
				foreach( $backtraces as $index => $backtrace ){
					$return .= self::_formatBacktrace( $count-$index, $backtrace, $name );
				}
			}
		}
		$return .= '</table>'.PHP_EOL;
		return $return;
	}
	
	static private function _toDisplay()
	{
		if( \Toolbox::isHtml() ){
			print self::_getBacktrace();
			self::_AdminNoticeJs();
			\Dump::PrintAttach();
		}
		return true;
	}
	
	/**
	 * Display of "StckError.js" to HTML.
	 */
	static private function _AdminNoticeJs()
	{
		print "<script>".PHP_EOL;
		print file_get_contents(\Toolbox::ConvertPath('op:/Template/js/AdminNotice.js')).PHP_EOL;
		print "</script>".PHP_EOL;
	}
	
	/**
	 * Send error mail to admin.
	 */
	static private function _toMail()
	{
		//	Get mail subject.
		$subject = '[Error] '.self::_getMailSubject();
		
		//	Cache's key.
		$ckey = md5($subject);
		
		//	Would not send same mail.
		if( \OnePiece5::GetSession($ckey) ){
			return true;
		}else if( \OnePiece5::Cache()->Get($ckey) ){
			return true;
		}else{
			\OnePiece5::SetSession($ckey, $subject);
			\OnePiece5::Cache()->Set($ckey, $subject);
		}
		
		//	From
		$from_addr = EMail::GetLocalAddress();
		$from_name = 'op-core/Error';
		
		//	To
		$to_addr = \Env::GetAdminMailAddress();
		$to_name = $_SERVER['HTTP_HOST'];
		
		//	HTML
		$html = self::_getMailMessage();
		
		//	Execute EMail.
		$mail = new EMail();
		$mail->From($from_addr, $from_name);
		$mail->To($to_addr, $to_name);
		$mail->Subject($subject);
		$mail->Content($html,'text/html');
		$mail->Send();
		
		return true;
	}
	
	static private function _getMailMessage()
	{
		$key = 'Timestamp';
		$var = date('Y-m-d H:i:s');
		$tr[] = "![tr[ ![th[$key]] ![td[$var]] ]]".PHP_EOL;
		
		$key = 'UserAgent';
		$var = $_SERVER['HTTP_USER_AGENT'];
		$tr[] = "![tr[ ![th[$key]] ![td[$var]] ]]".PHP_EOL;
		
		$key = 'Host';
		$var = $_SERVER['HTTP_HOST'];
		$tr[] = "![tr[ ![th[$key]] ![td[$var]] ]]".PHP_EOL;
		
		$key = 'URL';
		$var = \Toolbox::GetURL(array('port'=>1,'query'=>1)); // urldecode( $_SERVER['REQUEST_URI'] );
		$tr[] = "![tr[ ![th[$key]] ![td[$var]] ]]".PHP_EOL;
		
		$key = 'Referer';
		$var = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER']: null;
		$tr[] = "![tr[ ![th[$key]] ![td[$var]] ]]".PHP_EOL;
		
		$table = '![table['.join('',$tr).']]'.PHP_EOL;
		
		//	INIT
		$message = '';
		
		//	HTML
		$message .= '<html>'.PHP_EOL;
		
		//	HEAD
		$message .= '<head>'.PHP_EOL;
		
		//	STYLE
		$message .= '<style type="text/css">'.PHP_EOL;
		$message .= file_get_contents(\OnePiece5::ConvertPath('op:/Template/css/Dump.css'));
		$message .= '</style>'.PHP_EOL;
		
		//	HEAD CLOSE
		$message .= '</head>'.PHP_EOL;
		
		//	BODY
		$message .= '<body>'.PHP_EOL;
		
		//	HTML
		$message .= Wiki2Engine::Wiki2($table);
		$message .= '<hr/>'.PHP_EOL;
		$message .= self::_getBacktrace();
		
		//	BODY CLOSE
		$message .= '<body>'.PHP_EOL;
		
		//	HTML
		$message .= '</html>';
		
		return $message;
	}
	
	static function ConvertStringFromErrorNumber( $number )
	{
		/* @see http://www.php.net/manual/ja/errorfunc.constants.php */
		switch( $number ){
			case E_ERROR:	// 1
				$type = 'E_ERROR';
				break;
					
			case E_WARNING:	// 2
				$type = 'E_WARNING';
				break;
					
			case E_PARSE:	// 4
				$type = 'E_PARSE';
				break;
					
			case E_NOTICE:	// 8
				$type = 'E_NOTICE';
				break;
				
			case E_COMPILE_ERROR:		// 64
				$type = 'E_COMPILE_ERROR';
				break;
				
			case E_COMPILE_WARNING:		// 128
				$type = 'E_COMPILE_WARNING';
				break;
				
			case E_USER_ERROR:			// 256
				$type = 'E_USER_ERROR';
				break;
				
			case E_USER_WARNING:		// 512
				$type = 'E_USER_WARNING';
				break;
				
			case E_USER_NOTICE:			// 1024
				$type = 'E_USER_NOTICE';
				break;
				
			case E_STRICT:				// 2048
				$type = 'E_STRICT';
				break;
				
			case E_RECOVERABLE_ERROR:	// 4096
				$type = 'E_RECOVERABLE_ERROR';
				break;
				
			case E_DEPRECATED:			// 8192
				$type = 'E_DEPRECATED';
				break;
				
			case E_USER_DEPRECATED:		// 16384
				$type = 'E_USER_DEPRECATED';
				break;
				
			default:
				$type = $number;
		}
		
		return $type;
	}
	
	static function ConvertStringFromArguments( $args, &$is_dump )
	{
		$is_dump = false;
		$join = array();
		foreach( $args as $temp ){
			switch( $type = strtolower(gettype($temp)) ){
				case 'null':
					$join[] = '<span style="color:red;">null</span>';
					break;
					
				case 'boolean':
					$join[] = $temp ? '<span style="color:blue;">true</span>':'<span style="color:red;">false</span>';
					break;
					
				case 'integer':
				case 'double':
					$join[] = $temp;
					break;
					
				case 'string':
					$join[] = "<span style='color:gray;'>'".\OnePiece5::Escape($temp)."'</span>";
					break;
					
				case 'object':
					$is_dump = true;
					$class_name = get_class($temp);
					$join[] = '<span style="color:green;">'.$class_name.'</span>';
					break;
					
				case 'array':
					$is_dump = true;
					$join[] = '<span style="color:green;">array</span>';
					break;
					
				default:
					$join[] = $type;
					break;
			}
		}
		return join(', ',$join);
	}
	
	static function MagicMethodCall( $class, $name, $args )
	{
		//  If Toolbox method.
		if( method_exists('\Toolbox', $name) and false ){
			\OnePiece5::Mark("Please use Toolbox::$name");
			return \Toolbox::$name(
				isset($args[0]) ? $args[0]: null,
				isset($args[1]) ? $args[1]: null,
				isset($args[2]) ? $args[2]: null,
				isset($args[3]) ? $args[3]: null,
				isset($args[4]) ? $args[4]: null,
				isset($args[5]) ? $args[5]: null
			);
		}
		
		$message = "This method does not exists in class.".PHP_EOL."\ - {$class}::{$name}\\";
		self::_Set( $name, $message, null, 'en' );
	}
	
	static function MagicMethodCallStatic( $class, $name, $args )
	{
		//	Call static is PHP 5.3.0 later
		self::MagicMethodCall( $class, $name, $args );
	}
	
	static function MagicMethodSet( $class, $name, $args, $call )
	{
		$message = \OnePiece5::i18n()->En("\\{$class}::{$name}\ can not be accessible.");
		$message.= " ({$call}, value={$args})";
		\OnePiece5::AdminNotice($message);
	}
	
	static function MagicMethodGet( $class, $name, $call )
	{
		$message = \OnePiece5::i18n()->En("\\{$class}::{$name}\ can not be accessible.");
		$message.= " ({$call})";
		\OnePiece5::AdminNotice($message);
	}
	
	static function LastError( $e )
	{
		$file	 = $e['file'];
		$line	 = $e['line'];
		$type	 = $e['type'];
		$message = $e['message'];
		
		$type = self::ConvertStringFromErrorNumber($type);
		
		\OnePiece5::AdminNotice("$file [$line] $type: $message");
	}
	
	/**
	 * This method has been transferred from OnePiece5::ErrorHandler.
	 * 
	 * @param  integer $type
	 * @param  string  $str
	 * @param  string  $file
	 * @param  integer $line
	 * @param  unknown $context
	 * @return boolean
	 */
	static function Handler( $type, $str, $file, $line, $context )
	{
		$type = self::ConvertStringFromErrorNumber($type);
		
		//	Get route table.
		if(!$route = \Env::Get('route')){
			$route['mime'] = 'text/plain';
			\OnePiece5::AdminNotice("Route table has not been set.");
		}
		
		//	Get mime
		if( empty($route['mime']) ){
			$route['mime'] = 'text/plain';
			\OnePiece5::AdminNotice("MIME has not been set in the Route table.");
		}
		
		//	Generate error format.
		$base = '%s [%s] %s: %s';
		switch( $mime = strtolower($route['mime']) ){
			case 'text/html':
				$format = "<div>$base</div>";
				break;
			case 'text/css':
			case 'text/javascript':
				$format = "/* $base */";
				break;
			default:
				$format = $base;
		}
		
		//  check ini setting
		if( ini_get( 'display_errors') ){
			printf( $format."\n", $file, $line, $type, $str );
		}
		
		return true;
	}
	
	/**
	 * This method has been transferred from OnePiece5::ErrorExceptionHandler.
	 * 
	 * @param OpException $e
	 */
	static function ExceptionHandler( $e )
	{
		$class	 = get_class($e);
		$file	 = $e->GetFile();
		$line	 = $e->GetLine();
		$message = $e->GetMessage();
		$lang	 = null;
		
		//	get language of message's original language.
		if( method_exists($e,'getLang') ){
			$lang = $e->getLang();
		}
		
		//	to translate
		if( $lang ){
			$message = \OnePiece5::i18n()->En($message);
		}
		
		//	join
		$error = "$class: $file [$line] $message";
		\OnePiece5::AdminNotice($error,$lang);
	}
}
