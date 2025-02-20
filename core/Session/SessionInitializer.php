<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Session;

use Exception;
use Piwik\Access;
use Piwik\Auth as AuthInterface;
use Piwik\AuthResult;
use Piwik\Piwik;
use Piwik\Session;

/**
 * Initializes authenticated sessions using an Auth implementation.
 */
class SessionInitializer
{
    /**
     * Authenticates the user and, if successful, initializes an authenticated session.
     *
     * @param \Piwik\Auth $auth The Auth implementation to use.
     * @throws Exception If authentication fails or the user is not allowed to login for some reason.
     */
    public function initSession(AuthInterface $auth)
    {
        $this->regenerateSessionId();

        $authResult = $this->doAuthenticateSession($auth);

        if (!$authResult->wasAuthenticationSuccessful()) {
            Piwik::postEvent('Login.authenticate.failed', array($auth->getLogin()));

            $this->processFailedSession();
        } else {
            Piwik::postEvent('Login.authenticate.successful', array($auth->getLogin()));

            $this->processSuccessfulSession($authResult);
        }
    }

    /**
     * Authenticates the user.
     *
     * Derived classes can override this method to customize authentication logic or impose
     * extra requirements on the user trying to login.
     *
     * @param AuthInterface $auth The Auth implementation to use when authenticating.
     * @return AuthResult
     */
    protected function doAuthenticateSession(AuthInterface $auth)
    {
        Piwik::postEvent(
            'Login.authenticate',
            array(
                $auth->getLogin(),
            )
        );

        return $auth->authenticate();
    }

    /**
     * Executed when the session could not authenticate.
     *
     * @throws Exception always.
     */
    protected function processFailedSession()
    {
        throw new Exception(Piwik::translate('Login_LoginPasswordNotCorrect'));
    }

    /**
     * Executed when the session was successfully authenticated.
     *
     * @param AuthResult $authResult The successful authentication result.
     */
    protected function processSuccessfulSession(AuthResult $authResult)
    {
        $sessionIdentifier = new SessionFingerprint();
        $sessionIdentifier->initialize($authResult->getIdentity(), $authResult->getTokenAuth(), $this->isRemembered());

        // reload access
        Access::getInstance()->reloadAccess();

        /**
         * @ignore
         */
        Piwik::postEvent('Login.authenticate.processSuccessfulSession.end', array($authResult->getIdentity()));
    }

    protected function regenerateSessionId()
    {
        Session::regenerateId();
    }

    private function isRemembered()
    {
        $cookieParams = session_get_cookie_params();
        return $cookieParams['lifetime'] > 0;
    }
}
