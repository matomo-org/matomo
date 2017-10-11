<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Session;

use Piwik\IP;

/**
 * Manages session information that is used to identify who the session
 * is for.
 *
 * Once a session is authenticated using either a user name & password or
 * token auth, some information about the user is stored in the session.
 * This info includes the user name, the user's IP address and the user agent
 * string of the user's client.
 *
 * In subsequent requests that use this session, we use the above information
 * to verify that the session is allowed to be used by the person sending the
 * request.
 *
 * This is accomplished by checking the request's IP address & user agent
 * against what is stored in the session. If it doesn't then this is a
 * session hijacking attempt.
 */
class SessionFingerprint
{
    const USER_NAME_SESSION_VAR_NAME = 'user.name';
    const SESSION_INFO_SESSION_VAR_NAME = 'session.info';

    public function getUser()
    {
        if (isset($_SESSION[self::USER_NAME_SESSION_VAR_NAME])) {
            return $_SESSION[self::USER_NAME_SESSION_VAR_NAME];
        }

        return null;
    }

    public function getUserInfo()
    {
        if (isset($_SESSION[self::SESSION_INFO_SESSION_VAR_NAME])) {
            return $_SESSION[self::SESSION_INFO_SESSION_VAR_NAME];
        }

        return null;
    }

    public function initialize($userName, $time = null, $ip = null, $userAgent = null)
    {
        $_SESSION[self::USER_NAME_SESSION_VAR_NAME] = $userName;
        $_SESSION[self::SESSION_INFO_SESSION_VAR_NAME] = [
            'ts' => $time ?: time(),
            'ip' => $ip ?: IP::getIpFromHeader(),
            'ua' => $userAgent ?: $this->getUserAgent(),
        ];
    }

    public function isMatchingCurrentRequest()
    {
        $requestIp = IP::getIpFromHeader();
        $requestUa = $this->getUserAgent();

        $userInfo = $this->getUserInfo();
        if (empty($userInfo)) {
            return false;
        }

        return $userInfo['ip'] == $requestIp && $userInfo['ua'] == $requestUa;
    }

    /**
     * @param int $passwordModifiedTime
     * @return bool
     */
    public function hasPasswordChangedSinceSessionStart($passwordModifiedTime)
    {
        $userInfo = $this->getUserInfo();
        if (empty($userInfo)) {
            return true;
        }

        // if the session was created before the password was last modified,
        // it has changed and this session is no longer valid.
        return $userInfo['ts'] < $passwordModifiedTime;
    }

    private function getUserAgent()
    {
        return array_key_exists('HTTP_USER_AGENT', $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }
}
