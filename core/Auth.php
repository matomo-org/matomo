<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik;

/**
 * Base for authentication modules
 *
 * @package Piwik
 * @subpackage Piwik_Auth
 */
interface Auth
{
    /**
     * Authentication module's name, e.g., "Login"
     *
     * @return string
     */
    public function getName();

    /**
     * Authenticates user
     *
     * @return AuthResult
     */
    public function authenticate();

    /**
     * Authenticates the user and initializes the session.
     */
    public function initSession($login, $md5Password, $rememberMe);
}

/**
 * Authentication result
 *
 * @package Piwik
 * @subpackage Piwik_Auth
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
     * Returns true if this result was successfully authentication.
     *
     * @return bool
     */
    public function wasAuthenticationSuccessful()
    {
        return $this->code > self::FAILURE;
    }
}