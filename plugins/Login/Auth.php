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

use Piwik\AuthResult;
use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\Plugins\UsersManager\API;

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