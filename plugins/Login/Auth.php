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
use Piwik\AuthResult;
use Piwik\Db;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Session;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;

/**
 *
 */
class Auth implements \Piwik\Auth
{
    protected $login = null;
    protected $token_auth = null;
    protected $md5Password = null;

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
        if (!empty($this->md5Password)) { // favor authenticating by password
            $this->token_auth = UsersManagerAPI::getInstance()->getTokenAuth($this->login, $this->getTokenAuthSecret());
        }

        if (is_null($this->login)) {
            $model = new Model();
            $user  = $model->getUserByTokenAuth($this->token_auth);

            if (!empty($user['login'])) {
                $code = $user['superuser_access'] ? AuthResult::SUCCESS_SUPERUSER_AUTH_CODE : AuthResult::SUCCESS;

                return new AuthResult($code, $user['login'], $this->token_auth);
            }
        } else if (!empty($this->login)) {
            $model = new Model();
            $user  = $model->getUser($this->login);

            if (!empty($user['token_auth'])
                && ((SessionInitializer::getHashTokenAuth($this->login, $user['token_auth']) === $this->token_auth)
                    || $user['token_auth'] === $this->token_auth)
            ) {
                $this->setTokenAuth($user['token_auth']);
                $code = !empty($user['superuser_access']) ? AuthResult::SUCCESS_SUPERUSER_AUTH_CODE : AuthResult::SUCCESS;

                return new AuthResult($code, $this->login, $user['token_auth']);
            }
        }

        return new AuthResult(AuthResult::FAILURE, $this->login, $this->token_auth);
    }

    /**
     * Returns the login of the user being authenticated.
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
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
     * Returns the secret used to calculate a user's token auth.
     *
     * @return string
     */
    public function getTokenAuthSecret()
    {
        return $this->md5Password;
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
     * Sets the password to authenticate with.
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->md5Password = md5($password);
    }

    /**
     * Sets the password hash to use when authentication.
     *
     * @param string $passwordHash The password hash.
     * @throws Exception if $passwordHash does not have 32 characters in it.
     */
    public function setPasswordHash($passwordHash)
    {
        if (strlen($passwordHash) != 32) {
            throw new Exception("Invalid hash: incorrect length " . strlen($passwordHash));
        }

        $this->md5Password = $passwordHash;
    }
}