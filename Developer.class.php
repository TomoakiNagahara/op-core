<?php
/**
 * Developer.class.php
 * 
 * Set the only features that developers use.
 * 
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright 2014 (C) Tomoaki Nagahara All right reserved.
 */

//	Developer is not read from not admin.
if(!OnePiece5::Admin()){
	OnePiece5::AdminNotice("!!ATTENSION!!");
}

/**
 * Developer
 * 
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright 2014 (C) Tomoaki Nagahara All right reserved.
 */
class Developer extends OnePiece5
{
	/**
	 * Save mark-label for use footer links.
	 *
	 * @param unknown $mark_label
	 */
	static function SetMarkLabel( $mark_label )
	{
		//  Use footer link
		if( empty($_SERVER[__CLASS__]['MARK_LABEL'][$mark_label]) ){
			$_SERVER[__CLASS__]['MARK_LABEL'][$mark_label] = true;
		}
	}
	
	/**
	 * Get save value from session.
	 * 
	 * @param unknown $mark_label
	 * @return NULL
	 */
	static function GetSaveMarkLabelValue( $mark_label )
	{
		return isset($_SESSION[__CLASS__]['MARK_LABEL'][$mark_label]) ?
		$_SESSION[__CLASS__]['MARK_LABEL'][$mark_label]:
		null;
	}
	
	/**
	 * Save on/off flag to session by get value.
	 * 
	 * @param string $mark_label
	 * @param string $mark_value
	 */
	static function SaveMarkLabelValue( $mark_label=null, $mark_value=null )
	{
		//
		if( $mark_label ){
			$_SESSION[__CLASS__]['MARK_LABEL'][$mark_label] = $mark_value;
		}
	}
	
	static function PrintGetFlagList()
	{
		//	Only admin.
		if(!OnePiece5::admin()){
			$this->AdminNotice("Why is this? Developer is only loading for developer.");
			return;
		}
		
		//	Only html
		if(!Toolbox::isHtml()){
			return;
		}
		
		//  Hide mark label links setting.
		$key = 'hide_these_links';
		$str = 'Hide these links';
		$var = 1;
		if( self::GetSaveMarkLabelValue($key) ){
			return;
		}
		
		//  Mark label links
		$join = array();
		$join[] = sprintf('<a href="?mark_label=%s&mark_label_value=%s">%s</a>', $key, $var, $str);
		
		if( isset($_SERVER[__CLASS__]) ){
			foreach( $_SERVER[__CLASS__]['MARK_LABEL'] as $mark_label => $null ){
				$key = $mark_label;
				$var = self::GetSaveMarkLabelValue($mark_label);
				$str = $var ? 'Hide': 'Show';
				$var = $var ? 0: 1;
				$join[] = sprintf('<a href="?mark_label=%s&mark_label_value=%s">%s %s info</a>', $key, $var, $str, $key);
			}
		}
		
		print '<!-- '.__FILE__.' - '.__LINE__.' -->'.PHP_EOL;
		if( $join ){
			print '<div style="font-size:small;">[ '.join(' | ', $join).' ]</div>';
		}
	}

	/**
	 * Print mark.
	 * 
	 * @param string $str
	 * @param string $mark_labels
	 * @param string $call_line
	 */
	static function PrintMark($str, $mark_labels, $call_line)
	{
		//	check display label.
		if( $mark_labels ){
			foreach( explode(',',$mark_labels) as $mark_label ){
				Developer::SetMarkLabel( $mark_label );
			}
			if(!Developer::GetSaveMarkLabelValue($mark_label) ){
				return;
			}
		}

		//	message
		if( is_int($str) ){
			$str = (string)$str;
		}else
			if( is_null($str) ){
				$str = '![ .red [null]]';
		}else
			if( is_bool($str) ){
				$str = $str ? '![ .blue [true]]': '![ .red [false]]';
		}else
			if( $str and !is_string($str) ){
				$str = var_export($str,true);
				$str = str_replace( array("\r","\n"), array('\r','\n'), $str);
		}

		//	Check Wiki2Engine
		if(!class_exists('Wiki2Engine')){
			include_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'Wiki2Engine.class.php');
		}

		//	build
		$nl = PHP_EOL;
		$str = Wiki2Engine::Wiki2($str);
		$string = "{$nl}<div class=\"OnePiece mark\" style=\"font-size:small;\">{$call_line}- {$str}</div>{$nl}";

		//	Get mime. 
		/**
		 * Broken only test/css.
		$mime = Env::Get('mime')
		*/

		//	Get route table
		$route = Env::Get('route');
		$mime  = $route['mime'];

		if( $mime ){
			list($type, $mime) = explode('/',$mime);
			//	Branch to each mime
			if( $type === 'text' ){
				switch($mime){
					case 'css':
					case 'javascript':
						$string = "/* ".strip_tags(trim($string))." */{$nl}";
						break;
					case 'plain':
						$string = strip_tags(trim($string)).$nl;
						break;
				}
			}else{
				$string = null;
			}
		}

		print $string;
	}
}
