<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Piwik\Config;
use Piwik\Cookie;
use Piwik\ProxyHttp;

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
    private static function getTrackingCookie()
    {
        $cookie_name = @Config::getInstance()->Tracker['cookie_name'];
        $cookie_path = @Config::getInstance()->Tracker['cookie_path'];

        $cookie = new Cookie($cookie_name, null, $cookie_path);

        $domain = @Config::getInstance()->Tracker['cookie_domain'];
        if (!empty($domain)) {
            $cookie->setDomain($domain);
        }

        return $cookie;
    }

    public static function deleteThirdPartyCookieUIDIfExists()
    {
        $trackingCookie = self::getTrackingCookie();
        if ($trackingCookie->isCookieFound()) {
            $trackingCookie->delete();
        }
    }

    /**
     * Get ignore (visit) cookie
     *
     * @return Cookie
     * @throws \Exception
     */
    public static function getIgnoreCookie()
    {
        $cookie_name = @Config::getInstance()->Tracker['ignore_visits_cookie_name'];
        $cookie_path = @Config::getInstance()->Tracker['cookie_path'];


        $cookie = new Cookie($cookie_name, "+ 30 years", $cookie_path, false);

        $domain = @Config::getInstance()->Tracker['cookie_domain'];
        if (!empty($domain)) {
            $cookie->setDomain($domain);
        }

        return $cookie;
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
            if (ProxyHttp::isHttps()) {
                $ignoreCookie->setSecure(true);
                $ignoreCookie->save('None');
            } else {
                $ignoreCookie->save('Lax');
            }
        }

        self::deleteThirdPartyCookieUIDIfExists();
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
