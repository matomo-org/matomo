<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

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
     * @param int    $code
     * @param string $login identity
     * @param string $tokenAuth
     */
    public function __construct($code, $login, $tokenAuth)
    {
        $this->code      = (int)$code;
        $this->login     = $login;
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