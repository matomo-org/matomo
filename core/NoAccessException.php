<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Http\HttpCodeException;

/**
 * Exception thrown when a user doesn't have sufficient access to a resource.
 *
 * @api
 */
class NoAccessException extends InvalidRequestParameterException implements HttpCodeException
{
    /**
     * @var bool
     */
    private $sessionExpired = false;

    public function __construct($message, $code = 401, $sessionExpired = false)
    {
        parent::__construct($message, $code);
        $this->sessionExpired = (bool) $sessionExpired;
    }

    /**
     * @return bool
     */
    public function hasSessionExpired()
    {
        return $this->sessionExpired;
    }

    /**
     * @param bool $sessionExpired
     * @return void
     */
    public function setSessionExpired($sessionExpired)
    {
        $this->sessionExpired = (bool) $sessionExpired;
    }
}
