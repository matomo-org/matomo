<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Session;

use Piwik\Auth;
use Piwik\AuthResult;
use Piwik\Date;
use Piwik\Plugins\UsersManager\Model as UsersModel;

/**
 * Validates already authenticated sessions.
 *
 * See {@link \Piwik\Session\SessionFingerprint} for more info.
 */
class SessionAuth implements Auth
{
    /**
     * @var SessionAuthCookieFactory
     */
    private $sessionAuthCookieFactory;

    public function __construct(SessionAuthCookieFactory $sessionAuthCookieFactory)
    {
        $this->sessionAuthCookieFactory = $sessionAuthCookieFactory;
    }

    public function getName()
    {
        // empty
    }

    public function setTokenAuth($token_auth)
    {
        // empty
    }

    public function getLogin()
    {
        // empty
    }

    public function getTokenAuthSecret()
    {
        // empty
    }

    public function setLogin($login)
    {
        // empty
    }

    public function setPassword($password)
    {
        // empty
    }

    public function setPasswordHash($passwordHash)
    {
        // empty
    }

    public function authenticate()
    {
        $sessionId = new SessionFingerprint();
        $userModel = new UsersModel();

        $cookie = $this->sessionAuthCookieFactory->getCookie($rememberMe = false);
        $userNameInCookie = $cookie->get('user');
        $cookieHash = $cookie->get('id');

        $user = $userModel->getUser($userNameInCookie);
        if (empty($user)) {
            return $this->makeAuthFailure();
        }

        $userForSession = $sessionId->getUser();
        if (empty($userForSession)) {
            return $this->reAuthenticateSession($user, $cookieHash, $sessionId);
        }

        if (!$sessionId->isMatchingCurrentRequest()
            || $userNameInCookie != $userForSession
            || $this->hasPasswordChangedSinceSessionStart($user, $sessionId)
        ) {
            return $this->makeAuthFailure();
        }

        return $this->makeAuthSuccess($user);
    }

    private function makeAuthFailure()
    {
        return new AuthResult(AuthResult::FAILURE, null, null);
    }

    private function makeAuthSuccess($user)
    {
        $this->setTokenAuth($user['token_auth']);

        $isSuperUser = (int) $user['superuser_access'];
        $code = $isSuperUser ? AuthResult::SUCCESS_SUPERUSER_AUTH_CODE : AuthResult::SUCCESS;

        return new AuthResult($code, $user['login'], $user['token_auth']);
    }

    /**
     * Piwik uses the session cookie expiration time as the session expiration
     * time. When a cookie expires, the session is no longer authenticated.
     *
     * Unfortunately, PHP's session.gc-probability INI config can delete a
     * session server side, before the cookie expires. In this case, we have
     * to securely re-authenticate, without revealing sensitive information
     * in the cookie.
     */
    private function reAuthenticateSession($user, $cookieHash, SessionFingerprint $sessionId)
    {
        $passwordHelper = new Auth\Password();

        $isValid = $passwordHelper->verify($user['password'], $cookieHash);

        if ($isValid) {
            $sessionId->initialize($user['login']);
            return $this->makeAuthSuccess($user);
        } else {
            return $this->makeAuthFailure();
        }
    }

    private function hasPasswordChangedSinceSessionStart($user, SessionFingerprint $sessionId)
    {
        if (empty($user['ts_password_modified'])) { // sanity check
            return true;
        }

        $tsPasswordModified = Date::factory($user['ts_password_modified'])->getTimestampUTC();
        return $sessionId->hasPasswordChangedSinceSessionStart($tsPasswordModified);
    }
}
