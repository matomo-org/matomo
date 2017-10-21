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
use Piwik\Session;

/**
 * Validates already authenticated sessions.
 *
 * See {@link \Piwik\Session\SessionFingerprint} for more info.
 */
class SessionAuth implements Auth
{
    /**
     * For tests, since there's no actual session there.
     *
     * @var bool
     */
    private $shouldDestroySession;

    public function __construct($shouldDestroySession = true)
    {
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

        $userForSession = $sessionId->getUser();
        if (empty($userForSession)) {
            return $this->makeAuthFailure();
        }

        $user = $userModel->getUser($userForSession);
        if (empty($user)) {
            return $this->makeAuthFailure();
        }

        if (!$sessionId->isMatchingCurrentRequest()) {
            // this user should be using a different session, so generate a new ID
            // NOTE: Zend_Session cannot be used since it will destroy the old
            // session.
            if ($this->shouldDestroySession) {
                session_regenerate_id();
            }
            $sessionId->clear();

            return $this->makeAuthFailure();
        }

        $tsPasswordModified = $user['ts_password_modified'];
        if ($this->isSessionStartedBeforePasswordChange($sessionId, $tsPasswordModified)) {
            // Note: can't use Session::destroy() since Zend prohibits starting a new session
            // after session_destroy() is called.
            $sessionId->clear();
            if ($this->shouldDestroySession) {
                Session::regenerateId();
            }

            return $this->makeAuthFailure();
        }

        return $this->makeAuthSuccess($user);
    }

    private function isSessionStartedBeforePasswordChange(SessionFingerprint $sessionId, $tsPasswordModified)
    {
        // if the session start time doesn't exist for some reason, log the user out
        $sessionStartTime = $sessionId->getSessionStartTime();
        if (empty($sessionStartTime)) {
            return true;
        }

        return $sessionStartTime < Date::factory($tsPasswordModified)->getTimestampUTC();
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
