<?php
/**
 * GMail.model.php
 * 
 * @created 2015-09-20
 * @author  Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 */

/**
 * Model_GMail
 *
 * @created 2015-09-20
 * @author  Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 */
class Model_GMail extends Model_Model
{
	private $_GMAIL_HOST;
	private $_GMAIL_PORT;
	private $_GMAIL_ACCOUNT;
	private $_GMAIL_PASSWORD;
	private $_SERVER;
	private $_imap;
	private $_expire	 = 3600;
	private $_charset	 = 'utf-8';
	
	function __destruct()
	{
		$this->Close();
	}
	
	function Test()
	{
		$this->mark("OK");
		return true;
	}
	
	function Init()
	{
		parent::Init();
		
		if(!function_exists('imap_open') ){
			$this->StackError("Does not install PHP IMAP functions.");
			return false;
		}
		
		return true;
	}
	
	function SetAccount($account, $password)
	{
		//	Initialized
		$this->_GMAIL_HOST		 = 'imap.googlemail.com';
		$this->_GMAIL_PORT		 = 993;
		$this->_GMAIL_ACCOUNT	 = $account;
		$this->_GMAIL_PASSWORD	 = $password;
		$this->_SERVER = "{{$this->_GMAIL_HOST}:{$this->_GMAIL_PORT}/novalidate-cert/imap/ssl}";
	}
	
	function Open()
	{
		if(!$this->_imap = imap_open($this->_SERVER."INBOX", $this->_GMAIL_ACCOUNT, $this->_GMAIL_PASSWORD)){
			$this->StackError("Does not open mailbox.");
			$io = false;
		}else{
			$io = true;
		}
		return $io;
	}
	
	function Close()
	{
		if( $this->_imap ){
			if(!imap_close($this->_imap)){
				$this->StackError("Could not close IMAP.");
			}
			$this->_imap = null;
		}
	}
	
	function GetImap()
	{
		if(!$this->_imap){
			$this->Open();
		}
		return $this->_imap;
	}
	
	function GetInfo()
	{
		$key = md5(__METHOD__.', '.$this->_GMAIL_ACCOUNT);
		if(!$value = $this->Cache()->Get($key) ){
			if( $value = imap_mailboxmsginfo($this->GetImap()) ){
				$this->Cache()->Set($key, $value, $this->_expire);
			}
		}
		return $value;
	}
	
	function GetHeader($no)
	{
		$key = md5(__METHOD__.', '.$this->_GMAIL_ACCOUNT.', '.$no);
		if(!$value = $this->Cache()->Get($key) ){
			if( $value = imap_header($this->GetImap(), $no) ){
				$this->Cache()->Set($key, $value, $this->_expire);
			}
		}
		return $value;
	}
	
	function GetHeaders($no)
	{
		$headers = null;
		
		foreach($this->GetHeader($no) as $key => $var){
			$headers[$key] = $var;
		}
		
		return $headers;
	}
	
	function GetAddress($type, $no)
	{
		if(!$header = $this->GetHeader($no)){
			return false;
		}
		if(!isset($header->$type)){
			return null;
		}
		return $this->ParseAddress($header->$type);
	}
	
	function ImapDecodeAndEncode($encode)
	{
		$result = null;
		foreach( imap_mime_header_decode($encode) as $value){
			if( $value->charset != 'default' ) {
				$result .= mb_convert_encoding($value->text, $this->_charset, $value->charset);
			}else{
				$result .= $value->text;
			}
		}
		return $result;
	}
	
	function ParseAddress( $obj )
	{
		$result = null;
		foreach($obj as $i => $v){
			if( isset($v->personal) ){
				$personal = $this->ImapDecodeAndEncode($v->personal);
			}else{
				$personal = null;
			}
			$addr = $v->mailbox;
			$host = $v->host;
			$result['addr'] = $addr;
			$result['host'] = $host;
			$result['mail'] = $addr.'@'.$host;
			$result['personal'] = $personal;
			$results[] = $result;
		}
		return $results;
	}
	
	function GetFrom($no)
	{
		return $this->GetAddress('from', $no);
	}
	
	function GetTo($no)
	{
		return $this->GetAddress('to', $no);
	}
	
	function GetCc($no)
	{
		return $this->GetAddress('cc', $no);
	}
	
	function GetSender($no)
	{
		return $this->GetAddress('sender', $no);
	}
	
	function GetReplyTo($no)
	{
		return $this->GetAddress('reply_to', $no);
	}
	
	function GetSubject($no)
	{
		$header = $this->GetHeader($no);
		if( empty($header->subject) ){
			return null;
		}
		return $this->ImapDecodeAndEncode($header->subject);
	}

	function GetStructure($no)
	{
		$key = md5(__METHOD__.', '.$this->_GMAIL_ACCOUNT.', '.$no);
		if(!$value = $this->Cache()->Get($key) ){
			if( $value = imap_fetchstructure($this->GetImap(), $no) ){
				$this->Cache()->Set($key, $value, $this->_expire);
			}
		}
		return $this->ParseStructure($value);
	}
	
	function ParseStructure($structure)
	{
		foreach($structure as $key => $var){
			if( $key === 'parameters'){
				$result[$key] = $this->ParseStructureParameters($var);
			}else if($key === 'parts'){
				$result[$key] = $this->ParseStructureParts($var);
			}else{
				$result[$key] = $var;
			}
		}
		return $result;
	}
	
	function ParseStructureParameters($parameters)
	{
		foreach($parameters as $i => $parameter){
			$result[strtolower($parameter->attribute)] = $parameter->value;
		}
		return $result;
	}
	
	function ParseStructureParts($parts)
	{
		foreach($parts as $i => $part){
			$result[$i] = $this->ParseStructure($part);
		}
		return $result;
	}
	
	function isHtmlMail($no)
	{
		$structure = $this->GetStructure($no);
		switch($structure['subtype']){
			case 'HTML':
			case 'ALTERNATIVE':
				$io = true;
				break;
			default:
				$io = false;
		}
		return $io;
	}
	
	function isMultipart($no)
	{
		$structure = $this->GetStructure($no);
		return $structure['type'] === TYPEMULTIPART ? true: false;
	}
	
	function isAttachment($no)
	{
		
	}
	
	function GetBodyRaw($no)
	{
		$key = md5(__METHOD__.', '.$this->_GMAIL_ACCOUNT.', '.$no);
		if(!$value = $this->Cache()->Get($key) ){
			if( $value = imap_body($this->GetImap(), $no) ){
				$this->Cache()->Set($key, $value, $this->_expire);
			}
		}
		return $value;
	}
	
	function GetBody($no, $section=1)
	{
		$key = md5(__METHOD__.', '.$this->_GMAIL_ACCOUNT.', '.$no.', '.$section);
		if(!$value = $this->Cache()->Get($key) ){
			if( $value = imap_fetchbody($this->GetImap(), $no, $section, FT_INTERNAL) ){
				$this->Cache()->Set($key, $value, $this->_expire);
			}else{
				return $value;
			}
		}
		
		$structure = $this->GetStructure($no);
		switch($structure['type']){
			case TYPETEXT:
				$encoding = $structure["encoding"];
				$charset  = $structure["parameters"]["charset"];
				$body = $this->ConvertEncoding($value, $encoding, $charset);
				break;
				
			case TYPEMULTIPART:
				$body = $this->GetBodyMultipart($no, $section, $value);
				break;
				
			case TYPEMESSAGE:
			case TYPEAPPLICATION:
			case TYPEAUDIO:
			case TYPEIMAGE:
			case TYPEVIDEO:
			case TYPEMODEL:
			case TYPEOTHER:
			default:
				$this->StackError("Does not support MIME type. ({$structure['type']})");
		}
		
		return $body;
	}

	function GetBodyMultipart($no, $section, $body)
	{
		$structure	 = $this->GetStructure($no);
		$part		 = $structure['parts'][$section-1];
		$encoding	 = $part['encoding'];
		$charset	 = $part['parameters']['charset'];
		return $this->ConvertEncoding($body, $encoding, $charset);
	}
	
	function ConvertEncoding($value, $encoding_type, $charset)
	{
		switch($encoding_type){
			case ENC7BIT:
				$encode = mb_convert_encoding($value, $this->_charset, $charset);
				break;
	
			case ENC8BIT:
				$encode = imap_8bit($value);
				$encode = imap_qprint($encode);
				$encode = mb_convert_encoding($encode, $this->_charset, $charset);
				break;
	
			case ENCBASE64:
				$encode = imap_base64($value);
				$encode = mb_convert_encoding($encode, $this->_charset, $charset);
				break;
	
			case ENCQUOTEDPRINTABLE:
				$encode = imap_qprint($value);
				$encode = mb_convert_encoding($encode, $this->_charset, $charset);
				break;
					
			case ENCBINARY:
			case ENCOTHER:
			default:
				$this->StackError("Does not define encoding type. ($encoding_type)");
				break;
		}
		return $encode;
	}
}
