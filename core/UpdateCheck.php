<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Class to check if a newer version of Piwik is available
 *
 * @package Piwik
 */
class Piwik_UpdateCheck 
{
	const CHECK_INTERVAL = 86400;
	const LAST_TIME_CHECKED = 'UpdateCheck_LastTimeChecked';
	const LATEST_VERSION = 'UpdateCheck_LatestVersion';
	const PIWIK_HOST = 'http://api.piwik.org/1.0/getLatestVersion/';
	const SOCKET_TIMEOUT = 2;

	/**
	 * Check for a newer version
	 */
	public static function check()
	{
		$lastTimeChecked = Piwik_GetOption(self::LAST_TIME_CHECKED);
		if($lastTimeChecked === false
			|| time() - self::CHECK_INTERVAL > $lastTimeChecked )
		{
			$parameters = array(
				'piwik_version' => Piwik_Version::VERSION,
				'php_version' => phpversion(),
				'url' => Piwik_Url::getCurrentUrlWithoutQueryString(),
				'trigger' => Piwik_Common::getRequestVar('module','','string'),
			);

			$url = self::PIWIK_HOST . "?" . http_build_query($parameters, '', '&');
			$timeout = self::SOCKET_TIMEOUT;
			try {
				$latestVersion = Piwik::sendHttpRequest($url, $timeout);
				Piwik_SetOption(self::LATEST_VERSION, $latestVersion);
			} catch(Exception $e) {
				// e.g., disable_functions = fsockopen; allow_url_open = Off
				Piwik_SetOption(self::LATEST_VERSION, '');
			}
			Piwik_SetOption(self::LAST_TIME_CHECKED, time(), $autoload = 1);
		}
	}
	
	/**
	 * Returns version number of a newer Piwik release.
	 *
	 * @return string|false false if current version is the latest available, 
	 * 	 or the latest version number if a newest release is available
	 */
	public static function isNewestVersionAvailable()
	{
		$latestVersion = Piwik_GetOption(self::LATEST_VERSION);
		if(!empty($latestVersion)
			&& version_compare(Piwik_Version::VERSION, $latestVersion) == -1)
		{
			return $latestVersion;
		}
		return false;
	}
}
