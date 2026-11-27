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
 * Exception for a requested entity that does not exist. Its code is 404.
 *
 * @api
 */
class ThingNotFoundException extends \Piwik\Exception\Exception implements HttpCodeException
{
    /**
     * @param string $message The error message.
     * @param \Throwable|null $previous The previous exception, if any.
     */
    public function __construct($message, $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}
