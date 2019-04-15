<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Session;

use Piwik\Date;

/**
 * Manages session information that is used to identify who the session
 * is for.
 *
 * Once a session is authenticated using either a user name & password or
 * token auth, some information about the user is stored in the session.
 * This info includes the user name and the user agent
 * string of the user's client, and a random session secret.
 *
 * In subsequent requests that use this session, we use the above information
 * to verify that the session is allowed to be used by the person sending the
 * request.
 *
 * This is accomplished by checking the request's user agent
 * against what is stored in the session. If it doesn't then this is a
 * session hijacking attempt.
 *
 * We also check that a hash in the piwik_auth cookie matches the hash
 * of the time the user last changed their password + the session secret.
 * If they don't match, the password has been changed since this session
 * started, and is no longer valid.
 */
class SessionFingerprint
{
    const USER_NAME_SESSION_VAR_NAME = 'user.name';
    const SESSION_INFO_SESSION_VAR_NAME = 'session.info';
    const SESSION_INFO_TWO_FACTOR_AUTH_VERIFIED = 'twofactorauth.verified';

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

    public function hasVerifiedTwoFactor()
    {
        if (isset($_SESSION[self::SESSION_INFO_TWO_FACTOR_AUTH_VERIFIED])) {
            return !empty($_SESSION[self::SESSION_INFO_TWO_FACTOR_AUTH_VERIFIED]);
        }

        return null;
    }

    public function setTwoFactorAuthenticationVerified()
    {
        $_SESSION[self::SESSION_INFO_TWO_FACTOR_AUTH_VERIFIED] = 1;
    }

    public function initialize($userName, $isRemembered = false, $time = null)
    {
        $_SESSION[self::USER_NAME_SESSION_VAR_NAME] = $userName;
        $_SESSION[self::SESSION_INFO_TWO_FACTOR_AUTH_VERIFIED] = 0;
        $_SESSION[self::SESSION_INFO_SESSION_VAR_NAME] = [
            'ts' => $time ?: Date::now()->getTimestampUTC(),
            'remembered' => $isRemembered,
        ];
    }

    public function clear()
    {
        unset($_SESSION[self::USER_NAME_SESSION_VAR_NAME]);
        unset($_SESSION[self::SESSION_INFO_SESSION_VAR_NAME]);
        unset($_SESSION[self::SESSION_INFO_TWO_FACTOR_AUTH_VERIFIED]);
    }

    public function getSessionStartTime()
    {
        $userInfo = $this->getUserInfo();
        if (empty($userInfo)
            || empty($userInfo['ts'])
        ) {
            return null;
        }

        return $userInfo['ts'];
    }

    public function isRemembered()
    {
        $userInfo = $this->getUserInfo();
        return !empty($userInfo['remembered']);
    }
}
