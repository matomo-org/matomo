<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Exception;

/**
 * Base interface for authentication implementations.
 *
 * Plugins that provide Auth implementations must provide a class that implements
 * this interface. Additionally, an instance of that class must be set in the
 * {@link \Piwik\Registry} class with the 'auth' key during the
 * [Request.initAuthenticationObject](http://developer.piwik.org/api-reference/events#requestinitauthenticationobject)
 * event.
 *
 * Authentication implementations must support authentication via username and
 * clear-text password and authentication via username and token auth. They can
 * additionally support authentication via username and an MD5 hash of a password. If
 * they don't support it, then [formless authentication](http://piwik.org/faq/how-to/faq_30/) will fail.
 *
 * Derived implementations should favor authenticating by password over authenticating
 * by token auth. That is to say, if a token auth and a password are set, password
 * authentication should be used.
 *
 * ### Examples
 *
 * **How an Auth implementation will be used**
 *
 *     // authenticating by password
 *     $auth = \Piwik\Registry::get('auth');
 *     $auth->setLogin('user');
 *     $auth->setPassword('password');
 *     $result = $auth->authenticate();
 *
 *     // authenticating by token auth
 *     $auth = \Piwik\Registry::get('auth');
 *     $auth->setLogin('user');
 *     $auth->setTokenAuth('...');
 *     $result = $auth->authenticate();
 *
 * @api
 */
interface Auth
{
    /**
     * Must return the Authentication module's name, e.g., `"Login"`.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the authentication token to authenticate with.
     *
     * @param string $token_auth authentication token
     */
    public function setTokenAuth($token_auth);

    /**
     * Returns the login of the user being authenticated.
     *
     * @return string
     */
    public function getLogin();

    /**
     * Returns the secret used to calculate a user's token auth.
     *
     * A users token auth is generated using the user's login and this secret. The secret
     * should be specific to the user and not easily guessed. Piwik's default Auth implementation
     * uses an MD5 hash of a user's password.
     *
     * @return string
     * @throws Exception if the token auth secret does not exist or cannot be obtained.
     */
    public function getTokenAuthSecret();

    /**
     * Sets the login name to authenticate with.
     *
     * @param string $login The username.
     */
    public function setLogin($login);

    /**
     * Sets the password to authenticate with.
     *
     * @param string $password Password (not hashed).
     */
    public function setPassword($password);

    /**
     * Sets the hash of the password to authenticate with. The hash will be an MD5 hash.
     *
     * @param string $passwordHash The hashed password.
     * @throws Exception if authentication by hashed password is not supported.
     */
    public function setPasswordHash($passwordHash);

    /**
     * Authenticates a user using the login and password set using the setters. Can also authenticate
     * via token auth if one is set and no password is set.
     *
     * Note: this method must successfully authenticate if the token auth supplied is a special hash
     * of the user's real token auth. This is because the SessionInitializer class stores a
     * hash of the token auth in the session cookie. You can calculate the token auth hash using the
     * {@link Piwik\Plugins\Login\SessionInitializer::getHashTokenAuth()} method.
     *
     * @return AuthResult
     * @throws Exception if the Auth implementation has an invalid state (ie, no login
     *                   was specified). Note: implementations are not **required** to throw
     *                   exceptions for invalid state, but they are allowed to.
     */
    public function authenticate();
}

/**
 * Authentication result. This is what is returned by authentication attempts using {@link Auth}
 * implementations.
 *
 * @api
 */
class AuthResult
{
    const FAILURE = 0;
    const SUCCESS = 1;
    const SUCCESS_SUPERUSER_AUTH_CODE = 42;

    /**
     * token_auth parameter used to authenticate in the API
     *
     * @var string
     */
    protected $tokenAuth = null;

    /**
     * The login used to authenticate.
     *
     * @var string
     */
    protected $login = null;

    /**
     * The authentication result code. Can be self::FAILURE, self::SUCCESS, or
     * self::SUCCESS_SUPERUSER_AUTH_CODE.
     *
     * @var int
     */
    protected $code = null;

    /**
     * Constructor for AuthResult
     *
     * @param int $code
     * @param string $login identity
     * @param string $tokenAuth
     */
    public function __construct($code, $login, $tokenAuth)
    {
        $this->code = (int)$code;
        $this->login = $login;
        $this->tokenAuth = $tokenAuth;
    }

    /**
     * Returns the login used to authenticate.
     *
     * @return string
     */
    public function getIdentity()
    {
        return $this->login;
    }

    /**
     * Returns the token_auth to authenticate the current user in the API
     *
     * @return string
     */
    public function getTokenAuth()
    {
        return $this->tokenAuth;
    }

    /**
     * Returns the authentication result code.
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns true if the user has Super User access, false otherwise.
     *
     * @return bool
     */
    public function hasSuperUserAccess()
    {
        return $this->getCode() == self::SUCCESS_SUPERUSER_AUTH_CODE;
    }

    /**
     * Returns true if this result was successfully authentication.
     *
     * @return bool
     */
    public function wasAuthenticationSuccessful()
    {
        return $this->code > self::FAILURE;
    }
}