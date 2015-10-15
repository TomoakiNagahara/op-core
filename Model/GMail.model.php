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
	private $_expire	 = 3600;
	private $_charset	 = 'utf-8';
	
	/**
	 * IMAP stream resource.
	 * @var resource
	 */
	private $_imap;
	
	/**
	 * Flag of delete of message.
	 * @var boolean
	 */
	private $_deleted;
	
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
		
		//	Checking of Installed of IMAP module.
		if(!function_exists('imap_open') ){
			$this->StackError("Does not install PHP IMAP functions.");
			return false;
		}
		
		//	Initialized
		$this->_GMAIL_HOST		 = 'imap.googlemail.com';
		$this->_GMAIL_PORT		 = 993;
		$this->_SERVER = "{{$this->_GMAIL_HOST}:{$this->_GMAIL_PORT}/novalidate-cert/imap/ssl}";
		
		return true;
	}
	
	function SetAccount($account, $password=null)
	{
		$this->_GMAIL_ACCOUNT	 = $account;
		$this->_GMAIL_PASSWORD	 = $password;
	}
	
	function SetPassword($password)
	{
		$this->_GMAIL_PASSWORD	 = $password;
	}
	
	function GetAccount()
	{
		if( $account = $this->_GMAIL_ACCOUNT ){
			// OK
		}else if( $account = $this->GetEnv('GMAIL_ACCOUNT')){
			// OK
		}else{
			// NG
			$account = null;
		}
		return $account;
	}
	
	function GetPassword()
	{
		if( $password = $this->_GMAIL_PASSWORD ){
			// OK
		}else if( $password = $this->GetEnv('GMAIL_PASSWORD')){
			// OK
		}else{
			// NG
			$password = null;
		}
		return $password;
	}
	
	function Open($account=null, $password=null)
	{
		if(!$account){
			$account = $this->GetAccount();
		}
		
		if(!$password){
			$password = $this->GetPassword();
		}
		
		if(!$this->_imap = imap_open($this->_SERVER."INBOX", $account, $password)){
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
			//	Delete of message.
			if( $this->_deleted ){
				imap_expunge($this->_imap);
			}
			
			//	Close of IMAP stream.
			if(!imap_close($this->_imap)){
				$this->StackError("Could not close IMAP.");
			}
			
			//	Init.
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
			}else{
				$this->StackError("Bad message number. ($no)");
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
			
			$mail = $addr.'@'.$host;
			$mail = trim($mail);
			$mail = trim($mail,'<>');
			
			list($first, $family) = $this->ParseName($personal, $addr);
			
			$result['addr'] = $addr;
			$result['host'] = $host;
			$result['mail'] = $mail;
			$result['personal'] = $personal;
			$result['name']['first']  = $first;
			$result['name']['family'] = $family;
			$results[] = $result;
		}
		return $results;
	}
	
	/**
	 * Get First and Family name.
	 * 
	 * @param  string $personal
	 * @param  string $address
	 * @return array(First, Family)
	 */
	function ParseName($personal, $address)
	{	
		if( empty($personal) ){
			return $this->ParseNameByAddress($address);
		}
		
		$personal = str_replace('　', ' ', $personal);
		$personal = trim($personal);
		$personal = trim($personal,'"\'<>');
		
		if( strpos($personal,' ') ){
			$full_name = explode(' ',$personal);
			$first  = array_shift($full_name);
			$family = array_pop($full_name);
		}else if( mb_strlen($personal,'utf-8') === 4 ){
			if( preg_match('/^[a-z\s]+$/i', $personal) ){
				// Alphabet only
				$first = $family = null;
			}else{
				$name = preg_split('//u', $personal);
				$family = $name[1].$name[2];
				$first  = $name[3].$name[4];
			}
		}else{
			return $this->ParseNameByAddress($address);
		}
		
		return array($first, $family);
	}
	
	/**
	 * Get First and Family name by address part.
	 * 
	 * @param  string $address
	 * @return array(First, Family)
	 */
	function ParseNameByAddress($address)
	{
		if( mb_substr_count($address, '.') !== 1 ){
			return array(null, null);
		}
		
		list($address) = explode('+', $address.'+');
		list($first, $family) = explode('.', $address);
		
		return array(ucfirst($first), ucfirst($family));
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
			switch($key){
				case 'parameters':
				case 'dparameters':
					$result[$key] = $this->ParseStructureParameters($var);
					break;
					
				case 'parts':
					$result[$key] = $this->ParseStructureParts($var);
					break;
					
				default:
					$result[$key] = $var;
			}
		}
		return $result;
	}
	
	function ParseStructureParameters($parameters)
	{
		foreach($parameters as $i => $parameter){
			$key = strtolower($parameter->attribute);
			if( isset($result[$key]) ){
				$this->StackError("$key is already exists.");
				$result[$i][$key] = $parameter->value;
			}
			$result[$key] = $parameter->value;
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
	
	function isAttachedFile($no)
	{
		$structure = $this->GetStructure($no);
		return $structure['subtype'] === 'MIXED' ? true: false;
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
			case TYPETEXT:	// 0
				$encoding = $structure["encoding"];
				$charset  = $structure["parameters"]["charset"];
				$body = $this->ConvertEncoding($value, $encoding, $charset);
				break;
				
			case TYPEMULTIPART:	// 1
				$body = $this->GetBodyMultipart($no, $section, $value);
				break;
				
			case TYPEMESSAGE:	// 2
			case TYPEAPPLICATION://3
			case TYPEAUDIO:		// 4
			case TYPEIMAGE:		// 5
			case TYPEVIDEO:		// 6
			case TYPEMODEL:		// 7
			case TYPEOTHER:		// 8
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
		$charset	 = isset($part['parameters']['charset']) ? $part['parameters']['charset']: null;
		return $this->ConvertEncoding($body, $encoding, $charset);
	}
	
	function GetBodyText($no)
	{
		$structure = $this->GetStructure($no);
		if( $structure['type'] === TYPETEXT ){
			$body = $this->GetBody($no);
		}else{
			foreach($structure['parts'] as $i => $part){
				if( $part['type'] === TYPETEXT ){
					$body = $this->GetBody($no, $i+1);
				}
			}
		}
		return $body;
	}
	
	function GetBodyHtml($no)
	{
		$structure = $this->GetStructure($no);
		if( $structure['subtype'] === 'HTML' ){
			$body = $this->GetBody($no);
		}else{
			foreach($structure['parts'] as $i => $part){
				if( $part['subtype'] === 'HTML' ){
					$body = $this->GetBody($no, $i+1);
				}
			}
		}
		return $body;
	}
	
	function ConvertEncoding($value, $encoding_type, $charset=null)
	{
		switch($encoding_type){
			case ENC7BIT:	// 0
				break;
	
			case ENC8BIT:	// 1
				$encode = imap_8bit($value);
				$encode = imap_qprint($encode);
				break;
	
			case ENCBASE64:	// 3
				$encode = imap_base64($value);
				break;
	
			case ENCQUOTEDPRINTABLE: // 4
				$encode = imap_qprint($value);
				break;
					
			case ENCBINARY:	// 2
			case ENCOTHER:	// 5
			default:
				$this->StackError("Does not define encoding type. ($encoding_type)");
				break;
		}

		if( $charset ){
			$encode = mb_convert_encoding($encode, $this->_charset, $charset);
		}
		
		return $encode;
	}
	
	function GetAttachedFileNum($no)
	{
		$num = 0;
		$structure = $this->GetStructure($no);
		foreach($structure['parts'] as $i => $part ){
			if( $part['type'] !== TYPETEXT ){
				$num++;
			}
		}
		return $num;
	}
	
	function GetAttachedFileList($no)
	{
		$list = null;
		$structure = $this->GetStructure($no);
		foreach($structure['parts'] as $i => $part ){
			if( isset($part['parameters']['name']) ){
				$temp = array();
				$temp['section'] = $i+1;
				$temp['name'] = $part['parameters']['name'];
				$list[] = $temp;
			}
		}
		return $list;
	}

	function GetAttachedFileName($no, $section)
	{
		$name = null;
		$structure = $this->GetStructure($no);
		foreach($structure['parts'] as $i => $part ){
			if( isset($part['parameters']['name']) ){
				$name = $part['parameters']['name'];
			}
		}
		return $name;
	}
	
	function Delete($no)
	{
		$this->_deleted = true;
		if(!imap_delete($this->GetImap(), $no)){
			$this->StackError("Delete of message was failed.");
		}
	}
}
