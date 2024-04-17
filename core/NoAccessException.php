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
    public function __construct($message, $code = 401)
    {
        parent::__construct($message, $code);
    }
}
