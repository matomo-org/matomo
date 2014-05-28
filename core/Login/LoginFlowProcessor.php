<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Login;

use Piwik\Piwik;
use Piwik\Auth;
use Piwik\AuthResult;

/**
 * Class LoginFlowProcessor
 * @package Piwik\Login
 */
class LoginFlowProcessor
{
    /**
     * @var \Piwik\Auth
     */
    protected $auth;

    /**
     * @param Auth $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function setAuth(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Add hooks to login process:
     *  - 'Login.preventInitSession' is using to prevent user session initialize if given user doesn't meet the requirements.
     *  - 'Login.initSession' is using to notify about user successful login, but before any cookie has been created.
     *
     * @param string $login
     * @param string $md5Password
     * @return AuthResult
     */
    public function processAuthenticate($login, $md5Password)
    {
        Piwik::postEvent(
            'Login.preventInitSession',
            array(
                array(
                    'login' => $login,
                    'md5Password' => $md5Password
                )
            )
        );

        $authResult = $this->auth->authenticate();

        if ($authResult->wasAuthenticationSuccessful()) {
            Piwik::postEvent(
                'Login.initSession',
                array(
                    array(
                        'login' => $login,
                        'md5Password' => $md5Password
                    )
                )
            );
        }

        return $authResult;
    }

    /**
     * Add hook after successful login process:
     *  - 'Login.successful' is using to notify about successful login, but after cookies creation process.
     */
    public function processSuccessfulAuthenticate()
    {
        Piwik::postEvent('Login.successful');
    }
} 
