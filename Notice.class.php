<?php
/**
 * Notice.class.php
 * 
 * @creation  2016-01-17
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/**
 * Notice
 * 
 * @creation  2016-01-17
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Notice extends OnePiece5
{
	/**
	 * Reporting.
	 */
	static function Report()
	{
		if(!OnePiece5::Admin() ){
			self::toMail();
			return;
		}

		if(!Toolbox::isHtml() ){
			return;
		}

		self::toDisplay();
	}

	/**
	 * Display of error.
	 */
	static public function toDisplay()
	{
		$json['status'] = true;
		$json['errors'] = OP\Error::GetAll();

		if( Env::Get('jQuery-load') ){
			//	jQuery was loaded.
		}else{
			Env::Set('jQuery-load', true);
			print '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>'.PHP_EOL;
		}

	//	print '<script>__notice__='. preg_replace('|null|', '"00"', json_encode($json)) .'</script>'.PHP_EOL;
		print '<script>__notice__='.json_encode($json).'</script>'.PHP_EOL;
		print '<script src="'.$_SERVER['SCRIPT_NAME'].'?onepiece[admin-notice]=js"></script>'.PHP_EOL;
		print '<link rel="stylesheet" type="text/css" href="'.$_SERVER['SCRIPT_NAME'].'?onepiece[admin-notice]=css">'.PHP_EOL;
	}

	/**
	 * Send error mail to admin.
	 */
	static public function toMail()
	{
		foreach( OP\Error::GetAll() as $error ){
			//	Cache's key.
			$ckey = md5($error['message']);

			//	Would not send same mail.
			if( isset($_SESSION['_ONEPIECE_'][__CLASS__][$ckey]) ){
				//	Already send.
				continue;
			}else if( OnePiece5::Cache()->Get($ckey) ){
				//	Already send.
				continue;
			}else{
				//	Does not duplicate send.
				$_SESSION['_ONEPIECE_'][__CLASS__][$ckey] = $error['message'];
				OnePiece5::Cache()->Set($ckey, $error['message'], 60*60*24*1);
			}

			//	Execute
			self::_SendMail($error);
		}
	}

	/**
	 * Execute send of mail of each errors.
	 * 
	 * @param  array $error
	 */
	static private function _SendMail($error)
	{
		$message   = $error['message'];
		$backtrace = $error['backtrace'];
		$timestamp = $error['timestamp'];
		$lang      = $error['lang'];

		//	Translation
		if( $lang ){
			/**
			 * Does not work i18n.
			 * 
			list($a, $b) = explode("\n", $error['message']);
			$message = $this->i18n()->Get($a, $lang, Env::Get('lang')).$b;
			 */
		}else{
			$message = $this->i18n()->RemoveBackSlash($error['message']);
		}

		//	Get mail subject.
		$subject = '[Error] '.$message;

		//	From
		$from_addr = EMail::GetLocalAddress();
		$from_name = 'op-core/Error';

		//	To
		$to_addr = Env::Get('admin-mail');
		$to_name = $_SERVER['HTTP_HOST'];

		//	HTML
		$html = self::_GenerateBody($timestamp, $backtrace);

		//	Execute EMail.
		$mail = new EMail();
		$mail->From($from_addr, $from_name);
		$mail->To($to_addr, $to_name);
		$mail->Subject($subject);
		$mail->Content($html,'text/html');
		$mail->Send();
	//	$mail->Debug();
	}

	/**
	 * Genrate html body.
	 * 
	 * @param  string $timestamp
	 * @param  array  $backtrace
	 * @return string
	 */
	static private function _GenerateBody($timestamp, $backtrace)
	{
		$key = 'Timestamp';
		$var = $timestamp;
		$tr[] = "![tr[ ![th[$key]] ![td[$var]] ]]".PHP_EOL;

		$key = 'User';
		$var = $_SERVER['REMOTE_ADDR'].' --> '.gethostbyaddr($_SERVER['REMOTE_ADDR']);
		$tr[] = "![tr[ ![th[$key]] ![td[$var]] ]]".PHP_EOL;

		$key = 'UserAgent';
		$var = $_SERVER['HTTP_USER_AGENT'];
		$tr[] = "![tr[ ![th[$key]] ![td[$var]] ]]".PHP_EOL;

		$key = 'Host';
		$var = $_SERVER['HTTP_HOST'];
		$tr[] = "![tr[ ![th[$key]] ![td[$var]] ]]".PHP_EOL;

		$key = 'URL';
		$var = Toolbox::GetURL(array('port'=>1,'query'=>1));
		$tr[] = "![tr[ ![th[$key]] ![td[$var]] ]]".PHP_EOL;

		$key = 'Referer';
		$var = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER']: null;
		$tr[] = "![tr[ ![th[$key]] ![td[$var]] ]]".PHP_EOL;

		$table = '![table['.join('',$tr).']]'.PHP_EOL;

		//	INIT
		$html = '';

		//	HTML
		$html .= '<html>'.PHP_EOL;

		//	HEAD
		$html .= '<head>'.PHP_EOL;

		//	STYLE
		$html .= '<style type="text/css">'.PHP_EOL;
		$html .= file_get_contents(OnePiece5::ConvertPath('op:/Template/css/Dump.css'));
		$html .= '</style>'.PHP_EOL;

		//	HEAD CLOSE
		$html .= '</head>'.PHP_EOL;

		//	BODY
		$html .= '<body>'.PHP_EOL;

		//	HTML
		$html .= Wiki2Engine::Wiki2($table);
		$html .= '<hr/>'.PHP_EOL;
		$html .= self::_GenerateTable( array_reverse($backtrace) );

		//	BODY CLOSE
		$html .= '<body>'.PHP_EOL;

		//	HTML
		$html .= '</html>';

		return $html;
	}

	/**
	 * Generate backtrace table.
	 * 
	 * @param  array $backtrace
	 * @return string
	 */
	static private function _GenerateTable($backtrace)
	{
		$table = '<table style="width:100%; font-size:small;">';
		$table.= '<tbody>';

		foreach($backtrace as $data){
			$table .= self::_GenerateTableLine($data);
		}

		$table.= '</tbody>';
		$table.= '</table>';

		return $table;
	}

	/**
	 * Generate backtrace table's each line.
	 * 
	 * @param  array $data
	 * @return string
	 */
	static private function _GenerateTableLine($data)
	{
		$file  = isset($data['file'])     ? $data['file']     : null;
		$line  = isset($data['line'])     ? $data['line']     : null;
		$func  = isset($data['function']) ? $data['function'] : null;
		$class = isset($data['class'])    ? $data['class']    : null;
		$type  = isset($data['type'])     ? $data['type']     : null;
		$args  = isset($data['args'])     ? $data['args']     : null;

		$file = OnePiece5::CompressPath($file);
		$args = self::_GenerateArgs($args);

		$tr = '<tr>';
		$tr.= '<td>'.$file.'</td>';
		$tr.= '<td style="text-align:right;">'.$line.'</td>';
		$tr.= '<td>'.$class.$type.$func."($args)</td>";
		$tr.= '</tr>';

		return $tr;
	}

	/**
	 * Generate backtrace argument.
	 * 
	 * @param  array $args
	 * @return string
	 */
	static private function _GenerateArgs($args)
	{
		$span = array();
		foreach($args as $var){
			$span[] = self::_GenerateArgsSpan($var);
		}
		return join(', ', $span);
	}

	/**
	 * Generate backtrace argument's each value's span.
	 * 
	 * @param  array $var
	 * @return string
	 */
	static private function _GenerateArgsSpan($var)
	{
		$style = null;
		$type  = strtolower(gettype($var));
		switch($type){
			case 'array':
				$style = 'font-style: italic;';
				$count = count($var);
				$value = "array($count)";
				break;

			case 'object':
				$style = 'font-style: italic;';
				$value = get_class($var);
				break;

			case 'string':
				$value = "'$var'";
				break;

			default:
				$style = 'font-style: italic;';
				$value = $var;
		}
		return "<span style=\"$style\">$value</span>";
	}
}
