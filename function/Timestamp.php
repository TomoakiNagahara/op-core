<?php
/** op-core:/function/Timestamp.php
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

/** Get local timezone timestamp.
 *
 * <pre>
 * $localtime = Env::Timestamp();
 * $utc       = Env::Timestamp(true);
 * $offset    = Env::Timestamp(true, '-1 month');
 * </pre>
 *
 * @created  2019-09-24
 * @moved    2023-03-29  OP\Env::Timestamp()
 * @param    boolean     $utc
 * @param    string      $offset
 * @return   string      $timestamp
 */
function Timestamp(      $utc=false, $offset=null):string
{
    //  ...
    require_once(__DIR__.'/Time.php');

    //	...
    $time = Time($utc);

    //	...
    if( $offset ){
        $time = strtotime($offset, $time);
    }

    //	...
    return date(_OP_DATE_TIME_, $time);
}
