<?php
/** op-core:/function/Time.php
 *
 * @created   2023-03-29
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

/** use
 *
 */

/** Get frozen unix time.
 *
 * @created  ????-??-??
 * @moved    2023-03-29  OP\Env::Time()
 * @param    boolean     $utc
 * @param    string      $time
 * @return   integer     $time
 */
function Time(      $utc=false,  string $time=''):int
{
    //  ...
    static $_time;

	//	...
	if( $time ){
		//	...
		if( $_time ?? null ){
			Notice::Set("Frozen time has already set.");
		};

		//	...
		$_time = strtotime($time);

		//	...
		if(!$utc ){
			//	Add timezone offset at php.ini timezone.
			$_time -= date('Z');
		}
	};

	//	...
	if( empty($_time) ){
		//	Always UTC.
		$_time = \time() - date('Z');
	};

	//	...
	return $utc ? $_time : $_time + date('Z');
}
