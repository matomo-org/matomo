<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Session;

use Piwik\Cookie;

class SessionAuthCookieFactory
{
    /**
     * The authenticated session cookie's name. Defaults to the value of the `[General] login_cookie_name`
     * INI config option.
     *
     * @var string
     */
    private $authCookieName;

    /**
     * The time in seconds before the authenticated session cookie expires. Only used if `$rememberMe`
     * is true in the {@link initSession()} call.
     *
     * Defaults to the value of the `[General] login_cookie_expire` INI config option.
     *
     * @var int
     */
    private $authCookieValidTime;

    /**
     * The path for the authenticated session cookie. Defaults to the value of the `[General] login_cookie_path`
     * INI config option.
     *
     * @var string
     */
    private $authCookiePath;

    /**
     * @var int
     */
    private $nowOverride;

    public function __construct($authCookieName, $authCookieValidTime, $authCookiePath, $nowOverride = null)
    {
        $this->authCookieName = $authCookieName;
        $this->authCookieValidTime = $authCookieValidTime;
        $this->authCookiePath = $authCookiePath;
        $this->nowOverride = $nowOverride;
    }

    public function getCookie($rememberMe)
    {
        $now = $this->nowOverride ?: time();
        $authCookieExpiry = $rememberMe ? $now + $this->authCookieValidTime : 0;
        $cookie = new Cookie($this->authCookieName, $authCookieExpiry, $this->authCookiePath);
        return $cookie;
    }

    public function isCookieInRequest()
    {
        return Cookie::isCookieInRequest($this->authCookieName);
    }
}