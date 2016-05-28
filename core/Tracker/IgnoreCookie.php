<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Piwik\Config;
use Piwik\Cookie;

/**
 * Tracking cookies.
 *
 */
class IgnoreCookie
{
    /**
     * Get tracking cookie
     *
     * @return Cookie
     */
    public static function getTrackingCookie()
    {
        $cookie_name = @Config::getInstance()->Tracker['cookie_name'];
        $cookie_path = @Config::getInstance()->Tracker['cookie_path'];

        return new Cookie($cookie_name, null, $cookie_path);
    }

    /**
     * Get ignore (visit) cookie
     *
     * @return Cookie
     */
    public static function getIgnoreCookie()
    {
        $cookie_name = @Config::getInstance()->Tracker['ignore_visits_cookie_name'];
        $cookie_path = @Config::getInstance()->Tracker['cookie_path'];

        return new Cookie($cookie_name, null, $cookie_path);
    }

    /**
     * Set ignore (visit) cookie or deletes it if already present
     */
    public static function setIgnoreCookie()
    {
        $ignoreCookie = self::getIgnoreCookie();
        if ($ignoreCookie->isCookieFound()) {
            $ignoreCookie->delete();
        } else {
            $ignoreCookie->set('ignore', '*');
            $ignoreCookie->save();

            $trackingCookie = self::getTrackingCookie();
            $trackingCookie->delete();
        }
    }

    /**
     * Returns true if ignore (visit) cookie is present
     *
     * @return bool  True if ignore cookie found; false otherwise
     */
    public static function isIgnoreCookieFound()
    {
        $cookie = self::getIgnoreCookie();
        return $cookie->isCookieFound() && $cookie->get('ignore') === '*';
    }
}
