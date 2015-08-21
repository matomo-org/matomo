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

class Auth implements \Piwik\Auth
{
    protected $login;
    protected $token_auth;
    protected $md5Password;

    /**
     * @var Model
     */
    private $userModel;

    public function __construct()
    {
        $this->userModel = new Model();
    }

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
            return $this->authenticateWithPassword($this->login, $this->getTokenAuthSecret());
        } elseif (is_null($this->login)) {
            return $this->authenticateWithToken($this->token_auth);
        } elseif (!empty($this->login)) {
            return $this->authenticateWithTokenOrHashToken($this->token_auth, $this->login);
        }

        return new AuthResult(AuthResult::FAILURE, $this->login, $this->token_auth);
    }

    private function authenticateWithPassword($login, $passwordHash)
    {
        $user = $this->userModel->getUser($login);

        if (!empty($user['login']) && $user['password'] === $passwordHash) {
            return $this->authenticationSuccess($user);
        }

        return new AuthResult(AuthResult::FAILURE, $login, null);
    }

    private function authenticateWithToken($token)
    {
        $user = $this->userModel->getUserByTokenAuth($token);

        if (!empty($user['login'])) {
            return $this->authenticationSuccess($user);
        }

        return new AuthResult(AuthResult::FAILURE, null, $token);
    }

    private function authenticateWithTokenOrHashToken($token, $login)
    {
        $user = $this->userModel->getUser($login);

        if (!empty($user['token_auth'])
            // authenticate either with the token or the "hash token"
            && ((SessionInitializer::getHashTokenAuth($login, $user['token_auth']) === $token)
                || $user['token_auth'] === $token)
        ) {
            return $this->authenticationSuccess($user);
        }

        return new AuthResult(AuthResult::FAILURE, $login, $token);
    }

    private function authenticationSuccess(array $user)
    {
        $this->setTokenAuth($user['token_auth']);

        $isSuperUser = (int) $user['superuser_access'];
        $code = $isSuperUser ? AuthResult::SUCCESS_SUPERUSER_AUTH_CODE : AuthResult::SUCCESS;

        return new AuthResult($code, $user['login'], $user['token_auth']);
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
        if (empty($password)) {
            $this->md5Password = null;
        } else {
            $this->md5Password = md5($password);
        }
    }

    /**
     * Sets the password hash to use when authentication.
     *
     * @param string $passwordHash The password hash.
     * @throws Exception if $passwordHash does not have 32 characters in it.
     */
    public function setPasswordHash($passwordHash)
    {
        if ($passwordHash === null) {
            $this->md5Password = null;
            return;
        }

        if (strlen($passwordHash) != 32) {
            throw new Exception("Invalid hash: incorrect length " . strlen($passwordHash));
        }

        $this->md5Password = $passwordHash;
    }
}
