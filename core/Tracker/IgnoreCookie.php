<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Tracking cookies.
 * 
 * @package Piwik
 * @subpackage Piwik_Tracker
 */
class Piwik_Tracker_IgnoreCookie
{
	/**
	 * Get tracking cookie
	 *
	 * @return Piwik_Cookie
	 */
	static public function getTrackingCookie()
	{
		if(!empty($GLOBALS['PIWIK_TRACKER_MODE']))
		{
			$cookie_name = @Piwik_Tracker_Config::getInstance()->Tracker['cookie_name'];
			$cookie_path = @Piwik_Tracker_Config::getInstance()->Tracker['cookie_path'];
		}
		else
		{
			$config = Zend_Registry::get('config');
			if($config !== false)
			{
				$cookie_name = @$config->Tracker->cookie_name;
				$cookie_path = @$config->Tracker->cookie_path;
			}
		}

		return new Piwik_Cookie($cookie_name, null, $cookie_path);
	}

	/**
	 * Get ignore (visit) cookie
	 *
	 * @return Piwik_Cookie
	 */
	static public function getIgnoreCookie()
	{
		if(!empty($GLOBALS['PIWIK_TRACKER_MODE']))
		{
			$cookie_name = @Piwik_Tracker_Config::getInstance()->Tracker['ignore_visits_cookie_name'];
			$cookie_path = @Piwik_Tracker_Config::getInstance()->Tracker['cookie_path'];
		}
		else
		{
			$config = Zend_Registry::get('config');
			if($config !== false)
			{
				$cookie_name = @$config->Tracker->ignore_visits_cookie_name;
				$cookie_path = @$config->Tracker->cookie_path;
			}
		}

		return new Piwik_Cookie($cookie_name, null, $cookie_path);
	}

	/**
	 * Set ignore (visit) cookie or deletes it if already present
	 */
	static public function setIgnoreCookie()
	{
		$ignoreCookie = self::getIgnoreCookie();
		if($ignoreCookie->isCookieFound())
		{
			$ignoreCookie->delete();
		}
		else
		{
			$ignoreCookie->set('ignore', '*');
			$ignoreCookie->save();

			$trackingCookie = self::getTrackingCookie();
			$trackingCookie->delete();
		}
	}

	/**
	 * Returns true if ignore (visit) cookie is present
	 *
	 * @return bool True if ignore cookie found; false otherwise
	 */
	static public function isIgnoreCookieFound()
	{
		$cookie = self::getIgnoreCookie();
		return $cookie->isCookieFound() && $cookie->get('ignore') === '*';
	}
}
