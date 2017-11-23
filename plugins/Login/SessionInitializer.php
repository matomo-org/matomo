<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Login;

use Exception;
use Piwik\Auth as AuthInterface;
use Piwik\AuthResult;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Cookie;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\ProxyHttp;
use Piwik\Session;
use Piwik\Session\SessionFingerprint;

/**
 * Initializes authenticated sessions using an Auth implementation.
 *
 * If a user is authenticated, a browser cookie is created so the user will be remembered
 * until the cookie is destroyed.
 *
 * Plugins can override SessionInitializer behavior by extending this class and
 * overriding methods. In order for these changes to have effect, however, an instance of
 * the derived class must be used by the Login/Controller.
 *
 * @api
 */
class SessionInitializer
{
    /**
     * The UsersManager API instance.
     *
     * @var UsersManagerAPI
     */
    private $usersManagerAPI;

    /**
     * @param UsersManagerAPI|null $usersManagerAPI
     */
    public function __construct($usersManagerAPI = null)
    {
        if (empty($usersManagerAPI)) {
            $usersManagerAPI = UsersManagerAPI::getInstance();
        }
        $this->usersManagerAPI = $usersManagerAPI;
    }

    /**
     * Authenticates the user and, if successful, initializes an authenticated session.
     *
     * @param \Piwik\Auth $auth The Auth implementation to use.
     * @param bool $rememberMe Whether the authenticated session should be remembered after
     *                         the browser is closed or not.
     * @throws Exception If authentication fails or the user is not allowed to login for some reason.
     */
    public function initSession(AuthInterface $auth, $rememberMe)
    {
        $this->regenerateSessionId();

        $authResult = $this->doAuthenticateSession($auth);

        if (!$authResult->wasAuthenticationSuccessful()) {

            Piwik::postEvent('Login.authenticate.failed', array($auth->getLogin()));

            $this->processFailedSession();
        } else {

            Piwik::postEvent('Login.authenticate.successful', array($auth->getLogin()));

            $this->processSuccessfulSession($authResult, $rememberMe);
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
     * @param bool $rememberMe
     * @return Cookie
     * @deprecated the auth cookie is no longer used
     */
    protected function getAuthCookie($rememberMe)
    {
        $config = Config::getInstance();

        $authCookieName = $config->General['login_cookie_name'];
        $authCookiePath = $config->General['login_cookie_expire'];
        $authCookieValidTime = $config->General['login_cookie_path'];

        $authCookieExpiry = $rememberMe ? time() + $authCookieValidTime : 0;
        $cookie = new Cookie($authCookieName, $authCookieExpiry, $authCookiePath);
        return $cookie;
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
     * @param bool $rememberMe Whether the authenticated session should be remembered after
     *                         the browser is closed or not.
     */
    protected function processSuccessfulSession(AuthResult $authResult, $rememberMe)
    {
        $sessionIdentifier = new SessionFingerprint();
        $sessionIdentifier->initialize($authResult->getIdentity());

        if ($rememberMe) {
            Session::rememberMe(Config::getInstance()->General['login_cookie_expire']);
        }
    }

    protected function regenerateSessionId()
    {
        Session::regenerateId();
    }

    /**
     * Accessor to compute the hashed authentication token.
     *
     * @param string $login user login
     * @param string $token_auth authentication token
     * @return string hashed authentication token
     * @deprecated
     */
    public static function getHashTokenAuth($login, $token_auth)
    {
        return md5($login . $token_auth);
    }
}
