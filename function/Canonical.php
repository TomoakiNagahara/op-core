<?php
/** op-core:/function/Canonical.php
 *
 * @created   2020-05-23   Move from Functions.php
 * @package   op-core
 * @version   1.0
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 */
namespace OP;

/** Canonical
 *
 * @deprecated 2025-05-19
 * @param    string     $url
 * @return   string     $fqdn
 */
function Canonical($url=null)
{
	//	...
	return Config::Get('app')['canonical'] ?? null;
}
