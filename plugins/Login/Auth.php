<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Login
 */
namespace Piwik\Plugins\Login;

use Exception;
use Piwik\AuthResult;
use Piwik\Common;
use Piwik\Config;
use Piwik\Cookie;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\API;
use Piwik\ProxyHttp;
use Piwik\Session;

/**
 *
 * @package Login
 */
class Auth implements \Piwik\Auth
{
    protected $login = null;
    protected $token_auth = null;

    /**
     * Authentication module's name, e.g., "Login"
     *
     * @return string
     */
    public function getName()
    {
        return 'Login';
    }

    /**
     * Authenticates user
     *
     * @return AuthResult
     */
    public function authenticate()
    {
        $rootLogin = Config::getInstance()->superuser['login'];
        $rootPassword = Config::getInstance()->superuser['password'];
        $rootToken = API::getInstance()->getTokenAuth($rootLogin, $rootPassword);

        if (is_null($this->login)) {
            if ($this->token_auth === $rootToken) {
                return new AuthResult(AuthResult::SUCCESS_SUPERUSER_AUTH_CODE, $rootLogin, $this->token_auth);
            }

            $login = Db::fetchOne(
                'SELECT login
                FROM ' . Common::prefixTable('user') . '
					WHERE token_auth = ?',
                array($this->token_auth)
            );
            if (!empty($login)) {
                return new AuthResult(AuthResult::SUCCESS, $login, $this->token_auth);
            }
        } else if (!empty($this->login)) {
            if ($this->login === $rootLogin
                && ($this->getHashTokenAuth($rootLogin, $rootToken) === $this->token_auth)
                || $rootToken === $this->token_auth
            ) {
                $this->setTokenAuth($rootToken);
                return new AuthResult(AuthResult::SUCCESS_SUPERUSER_AUTH_CODE, $rootLogin, $this->token_auth);
            }

            $login = $this->login;
            $userToken = Db::fetchOne(
                'SELECT token_auth
                FROM ' . Common::prefixTable('user') . '
					WHERE login = ?',
                array($login)
            );
            if (!empty($userToken)
                && (($this->getHashTokenAuth($login, $userToken) === $this->token_auth)
                    || $userToken === $this->token_auth)
            ) {
                $this->setTokenAuth($userToken);
                return new AuthResult(AuthResult::SUCCESS, $login, $userToken);
            }
        }

        return new AuthResult(AuthResult::FAILURE, $this->login, $this->token_auth);
    }

    /**
     * Authenticates the user and initializes the session.
     */
    public function initSession($login, $md5Password, $rememberMe)
    {
        $tokenAuth = API::getInstance()->getTokenAuth($login, $md5Password);

        $this->setLogin($login);
        $this->setTokenAuth($tokenAuth);
        $authResult = $this->authenticate();

        $authCookieName = Config::getInstance()->General['login_cookie_name'];
        $authCookieExpiry = $rememberMe ? time() + Config::getInstance()->General['login_cookie_expire'] : 0;
        $authCookiePath = Config::getInstance()->General['login_cookie_path'];
        $cookie = new Cookie($authCookieName, $authCookieExpiry, $authCookiePath);
        if (!$authResult->wasAuthenticationSuccessful()) {
            $cookie->delete();
            throw new Exception(Piwik::translate('Login_LoginPasswordNotCorrect'));
        }

        $cookie->set('login', $login);
        $cookie->set('token_auth', $this->getHashTokenAuth($login, $authResult->getTokenAuth()));
        $cookie->setSecure(ProxyHttp::isHttps());
        $cookie->setHttpOnly(true);
        $cookie->save();

        @Session::regenerateId();

        // remove password reset entry if it exists
        Login::removePasswordResetInfo($login);
    }

    /**
     * Accessor to set login name
     *
     * @param string $login user login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * Accessor to set authentication token
     *
     * @param string $token_auth authentication token
     */
    public function setTokenAuth($token_auth)
    {
        $this->token_auth = $token_auth;
    }

    /**
     * Accessor to compute the hashed authentication token
     *
     * @param string $login user login
     * @param string $token_auth authentication token
     * @return string hashed authentication token
     */
    public function getHashTokenAuth($login, $token_auth)
    {
        return md5($login . $token_auth);
    }
}