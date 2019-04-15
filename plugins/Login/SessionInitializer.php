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
use Piwik\Cookie;
use Piwik\Db;
use Piwik\Log;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\ProxyHttp;
use Piwik\Session;

/**
 * This SessionInitializer is no longer used, but is kept for backwards compatibility.
 * Session management no longer uses the piwik_auth cookie.
 *
 * @deprecated
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
     * @var string
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
     * Constructor.
     *
     * @param UsersManagerAPI|null $usersManagerAPI
     * @param string|null $authCookieName
     * @param int|null $authCookieValidTime
     * @param string|null $authCookiePath
     */
    public function __construct($usersManagerAPI = null, $authCookieName = null, $authCookieValidTime = null,
                                $authCookiePath = null)
    {
        if (empty($usersManagerAPI)) {
            $usersManagerAPI = UsersManagerAPI::getInstance();
        }
        $this->usersManagerAPI = $usersManagerAPI;

        if (empty($authCookieName)) {
            $authCookieName = Config::getInstance()->General['login_cookie_name'];
        }
        $this->authCookieName = $authCookieName;

        if (empty($authCookieValidTime)) {
            $authCookieValidTime = Config::getInstance()->General['login_cookie_expire'];
        }
        $this->authCookieValidTime = $authCookieValidTime;

        if (empty($authCookiePath)) {
            $authCookiePath = Config::getInstance()->General['login_cookie_path'];
        }
        $this->authCookiePath = $authCookiePath;
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
     * Returns a Cookie instance that manages the browser cookie used to store session
     * information.
     *
     * @param bool $rememberMe Whether the authenticated session should be remembered after
     *                         the browser is closed or not.
     * @return Cookie
     */
    protected function getAuthCookie($rememberMe)
    {
        $authCookieExpiry = $rememberMe ? time() + $this->authCookieValidTime : 0;
        $cookie = new Cookie($this->authCookieName, $authCookieExpiry, $this->authCookiePath);
        return $cookie;
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
        $cookie = $this->getAuthCookie($rememberMe);
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
        $cookie = $this->getAuthCookie($rememberMe);
        $cookie->set('login', $authResult->getIdentity());
        $cookie->set('token_auth', $this->getHashTokenAuth($authResult->getIdentity(), $authResult->getTokenAuth()));
        $cookie->setSecure(ProxyHttp::isHttps());
        $cookie->setHttpOnly(true);
        $cookie->save();

        return $cookie;
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
     */
    public static function getHashTokenAuth($login, $token_auth)
    {
        return md5($login . $token_auth);
    }
}
