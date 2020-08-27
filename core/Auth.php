<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
 * container with the 'Piwik\Auth' key during the
 * [Request.initAuthenticationObject](https://developer.matomo.org/api-reference/events#requestinitauthenticationobject)
 * event.
 *
 * Authentication implementations must support authentication via username and
 * clear-text password and authentication via username and token auth. They can
 * additionally support authentication via username and an MD5 hash of a password. If
 * they don't support it, then [formless authentication](https://matomo.org/faq/how-to/faq_30/) will fail.
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
 *     $auth = StaticContainer::get('Piwik\Auth');
 *     $auth->setLogin('user');
 *     $auth->setPassword('password');
 *     $result = $auth->authenticate();
 *
 *     // authenticating by token auth
 *     $auth = StaticContainer::get('Piwik\Auth');
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
     * {@link \Piwik\Plugins\Login\SessionInitializer::getHashTokenAuth()} method.
     *
     * @return AuthResult
     * @throws Exception if the Auth implementation has an invalid state (ie, no login
     *                   was specified). Note: implementations are not **required** to throw
     *                   exceptions for invalid state, but they are allowed to.
     */
    public function authenticate();
}
