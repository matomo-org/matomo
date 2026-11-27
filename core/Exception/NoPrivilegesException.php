<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Exception;

use Piwik\Http\HttpCodeException;

/**
 * Exception for a user who lacks the privileges for an action. Its default code is 401.
 *
 * @api
 */
class NoPrivilegesException extends Exception implements HttpCodeException
{
    /**
     * @param string $message The error message.
     * @param int $code The HTTP status code to respond with.
     */
    public function __construct($message, $code = 401)
    {
        parent::__construct($message, $code);
    }
}
