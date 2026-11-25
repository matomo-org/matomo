<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Http;

/**
 * Exception for an invalid request. Its default code is 400.
 *
 * @api
 */
class BadRequestException extends \Exception implements HttpCodeException
{
    /**
     * @param string $message The error message.
     * @param int $code The HTTP status code to respond with.
     */
    public function __construct($message, $code = 400)
    {
        parent::__construct($message, $code);
    }
}
