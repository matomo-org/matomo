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
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\ProxyHttp;
use Piwik\Session;
use Piwik\Session\SessionAuthCookieFactory;
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
     * @var SessionAuthCookieFactory
     */
    private $sessionCookieFactory;

    /**
     * The UsersManager API instance.
     *
     * @var UsersManagerAPI
     */
    private $usersManagerAPI;

    /**
     * @param UsersManagerAPI|null $usersManagerAPI
     * @param string|null $authCookieName
     * @param int|null $authCookieValidTime
     * @param string|null $authCookiePath
     */
    public function __construct(SessionAuthCookieFactory $sessionCookieFactory = null, $usersManagerAPI = null)
    {
        $this->sessionCookieFactory = $sessionCookieFactory ?: StaticContainer::get(SessionAuthCookieFactory::class);

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

            $this->processFailedSession($rememberMe);
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
     * Executed when the session could not authenticate.
     *
     * @param bool $rememberMe Whether the authenticated session should be remembered after
     *                         the browser is closed or not.
     * @throws Exception always.
     */
    protected function processFailedSession($rememberMe)
    {
        $cookie = $this->sessionCookieFactory->getCookie($rememberMe);
        $cookie->delete();

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

        $cookie = $this->sessionCookieFactory->getCookie($rememberMe);
        $cookie->clear();
        $cookie->setSecure(ProxyHttp::isHttps());
        $cookie->setHttpOnly(true);
        $cookie->save();

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
