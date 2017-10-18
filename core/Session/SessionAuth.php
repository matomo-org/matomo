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
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\Session;

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

    /**
     * For tests, since there's no actual session there.
     *
     * @var bool
     */
    private $shouldDestroySession;

    public function __construct(SessionAuthCookieFactory $sessionAuthCookieFactory, $shouldDestroySession = true)
    {
        $this->sessionAuthCookieFactory = $sessionAuthCookieFactory;
        $this->shouldDestroySession = $shouldDestroySession;
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
        $cookieHash = $cookie->get('id');

        $userForSession = $sessionId->getUser();
        if (empty($userForSession)) {
            return $this->makeAuthFailure();
        }

        $user = $userModel->getUser($userForSession);
        if (empty($user)) {
            return $this->makeAuthFailure();
        }

        if (!$sessionId->isMatchingCurrentRequest()) {
            return $this->makeAuthFailure();
        }

        if (!$this->isCookieHashMatchingSession($sessionId, $cookieHash, $user['ts_password_modified'])) {
            // Note: can't use Session::destroy() since Zend prohibits starting a new session
            // after session_destroy() is called.
            $sessionId->clear();

            // if the cookie hash doesn't match, then the password's been changed, so
            // we can get rid of this session
            if ($this->shouldDestroySession) {
                Session::expireSessionCookie();
            }

            $cookie->delete();

            return $this->makeAuthFailure();
        }

        return $this->makeAuthSuccess($user);
    }


    public function isCookieHashMatchingSession(SessionFingerprint $sessionId, $cookieHash, $passwordModifiedTime)
    {
        return $sessionId->getHash($passwordModifiedTime) == $cookieHash;
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
}
