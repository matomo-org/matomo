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
 * Exception for a website (idSite) that does not exist or was not expected. Its default code is 400.
 *
 * @api
 */
class UnexpectedWebsiteFoundException extends Exception implements HttpCodeException
{
    /**
     * @param string $message The error message.
     * @param int $code The HTTP status code to respond with.
     * @param \Throwable|null $previous The previous exception, if any.
     */
    public function __construct($message = '', $code = 400, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
