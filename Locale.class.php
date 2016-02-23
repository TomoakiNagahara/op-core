<?php
/**
 * Locale.class.php
 * 
 * @creation  2016-02-23
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/**
 * Locale
 * 
 * @creation  2016-02-23
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Locale extends OnePiece5
{
	/**
	 * Parse locale code
	 * 
	 * @param  string $locale
	 * @return array
	 */
	static function ParseLocaleCode($locale)
	{
		//	Parse locale code.
		if( preg_match('|([a-z]+)[-_]([a-z]+)\.([-_a-z0-9]+)|i', $locale, $match) or true){
			$lang = strtolower($match[1]);
			$area = strtoupper($match[2]);
			$code = strtoupper($match[3]);
		}else{
			OnePiece5::AdminNotice("Did not match locale format. ($locale, Ex. ja_JP.utf-8) ");
		}

		//	Result.
		return array($lang, $area, $code);
	}

	static function GetTimezone($locale)
	{
		list($lang, $area, $code) = self::ParseLocaleCode($locale);

		/**
		 * timezone list
		 * @see http://jp2.php.net/manual/ja/timezones.php
		 */
		switch( strtolower($area) ){
			case 'us':
				$timezone = 'America/Chicago';
				break;

			case 'jp':
				$timezone = 'Asia/Tokyo';
				break;
			default:
				OnePiece5::AdminNotice("Does not define this country code. ($lang)");
		}

		//	Result
		return $timezone;
	}

	static function GetEncodingOrderDetect($locale)
	{
		list($lang, $area, $code) = self::ParseLocaleCode($locale);

		//	detect order value
		switch( strtolower($lang) ){
			case 'en':
				$codes[] = 'UTF-8';
				$codes[] = 'ASCII';
				break;

			case 'ja':
				$codes[] = 'eucjp-win';
				$codes[] = 'sjis-win';
				$codes[] = 'UTF-8';
				$codes[] = 'ASCII';
				$codes[] = 'JIS';
				break;

			default:
				OnePiece5::AdminNotice("Does not define this language code. ($lang)");
		}

		//	Result.
		return $codes;
	}
}
