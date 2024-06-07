<?php
/** op-core:/ci/EMail.php
 *
 * @created   2022-10-31
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** Declare strict
 *
 */
declare(strict_types=1);

/** namespace
 *
 */
namespace OP;

//	...
$subject = 'This is self-check test';
$content = 'This is self-check test mail.';
$attach_file_name = 'email.txt';

/* @var $ci \OP\UNIT\CI\CI_Config */
$ci = OP()->Unit()->CI()->Config();

//	init
$user_name = get_current_user();
$file_name = OP::MetaPath("core:/testcase/{$attach_file_name}");

//	From
$result =  null;
$args   = ["{$user_name}@localhost", $user_name];
$ci->Set('From', $result, $args);

//	To
$result =  null;
$args   = ["{$user_name}@localhost", $user_name];
$ci->Set('To', $result, $args);

//	Cc
$result =  null;
$args   = ["{$user_name}@localhost", $user_name];
$ci->Set('Cc', $result, $args);

//	Bcc
$result =  null;
$args   = ["{$user_name}@localhost"];
$ci->Set('Bcc', $result, $args);

//	ReplyTo
$result =  null;
$args   = ["{$user_name}@localhost", $user_name];
$ci->Set('ReplyTo', $result, $args);

//	ErrorsTo
$result =  null;
$args   = ["{$user_name}@localhost", $user_name];
$ci->Set('ErrorsTo', $result, $args);

//	ReturnPath
$result =  null;
$args   = ["{$user_name}@localhost", $user_name];
$ci->Set('ReturnPath', $result, $args);

//	...
$method = 'Subject';
$result =  null;
$args   = $subject;
$ci->Set($method, $result, $args);

//	...
$method = 'Content';
$result =  null;
$args   = $content;
$ci->Set($method, $result, $args);

//	Attachment
$result =  null;
$args   = [$file_name,'text/plain','EMail.php'];
$ci->Set('Attachment', $result, $args);

//	Send
$result = true;
$args   = null;
$ci->Set('Send', $result, $args);

//	Debug
$result = null;
$args   = null;
$ci->Set('Debug', $result, $args);

//	_mail
$result = true;
$args   = null;
$ci->Set('_mail', $result, $args);

//	_socket
$result = false;
$args   = null;
$ci->Set('_socket', $result, $args);

//	_mta
$result = false;
$args   = null;
$ci->Set('_mta', $result, $args);

//	_get_headers
$args   = null;
$result = "MIME-Version: 1.0
Content-Type: Multipart/Mixed; boundary=\"--boundary--ci\"
Content-Transfer-Encoding: Base64
X-SENDER: onepiece-framework:EMail
From: {$user_name} <{$user_name}@localhost>
Cc: {$user_name} <{$user_name}@localhost>
Bcc: {$user_name}@localhost
Reply-to: {$user_name} <{$user_name}@localhost>
Return-path: {$user_name} <{$user_name}@localhost>
Errors-to: {$user_name} <{$user_name}@localhost>
";
$ci->Set('_get_headers', $result, $args);

//	_get_mail_address
$args   = null;
$result = "X-SENDER: onepiece-framework:EMail
From: {$user_name} <{$user_name}@localhost>
Cc: {$user_name} <{$user_name}@localhost>
Bcc: {$user_name}@localhost
Reply-to: {$user_name} <{$user_name}@localhost>
Return-path: {$user_name} <{$user_name}@localhost>
Errors-to: {$user_name} <{$user_name}@localhost>";
$ci->Set('_get_mail_address', $result, $args);

//	_get_to
$args   = null;
$result = "{$user_name} <{$user_name}@localhost>";
$ci->Set('_get_to', $result, $args);

//	_get_full_name
$result = "{$user_name} <{$user_name}@localhost>";
$args   = ["{$user_name}@localhost", $user_name];
$ci->Set('_get_full_name', $result, $args);

//	GetLocalAddress
$args   = null;
$result = "{$user_name}@localhost";
$ci->Set('GetLocalAddress', $result, $args);

//	_get_parameters
$args   = null;
$result = "-f {$user_name}@localhost";
$ci->Set('_get_parameters', $result, $args);

//	_get_boundary
$args   = null;
$result = '--boundary--ci';
$ci->Set('_get_boundary', $result, $args);

//	_get_content_type
$args   = null;
$result = "MIME-Version: 1.0
Content-Type: Multipart/Mixed; boundary=\"--boundary--ci\"
Content-Transfer-Encoding: Base64
";
$ci->Set('_get_content_type', $result, $args);

//	...
$method = '_get_content';
$args   = null;
$result = "----boundary--ci
Content-Type: text/plain; charset=\"utf-8\"
Content-Transfer-Encoding: 7bit

$content
----boundary--ci
Content-Type: text/plain; charset=\"utf-8\"
Content-Transfer-Encoding: 7bit

$attach_file_name
----boundary--ci--
";
$ci->Set($method, $result, $args);

//	_get_content_multipart
$args   = null;
$result = "MIME-Version: 1.0
Content-Type: Multipart/Mixed; boundary=\"--boundary--ci\"
Content-Transfer-Encoding: Base64
";
$ci->Set('_get_content_type', $result, $args);

//	_get_content
$args   = null;
$result = "----boundary--ci
Content-Type: text/plain; charset=\"utf-8\"
Content-Transfer-Encoding: 7bit

$content
----boundary--ci
Content-Type: text/plain; charset=\"utf-8\"
Content-Transfer-Encoding: 7bit

$attach_file_name
----boundary--ci--
";
$ci->Set('_get_content_multipart', $result, $args);

//	_get_subject
$args   = null;
$result = $subject;
$ci->Set('_get_subject', $result, $args);

//	_set_addr
$args   = ["{$user_name}@localhost",$user_name,'to'];
$result = null;
$ci->Set('_set_addr', $result, $args);

//	...
if( version_compare(PHP_VERSION, '8.0.0') >= 0 ){
	// PHP version is 8.0 over.
	$error = 'OP\EMail::_set_error(): Argument #1 ($message) must be of';
}else{
	// PHP version is 8.0 under.
	$error = 'Argument 1 passed to OP\EMail::_set_error() must be of the';
}
$core_path = \OP\RootPath('git').'asset/core/trait/OP_CI.php';
$core_path = realpath($core_path);
$method = '_set_error';
$result = "Exception: {$error} type string, null given, called in {$core_path} on line 50";
$args   = null;
$ci->Set($method, $result, $args);

//	...
return $ci->Get();
