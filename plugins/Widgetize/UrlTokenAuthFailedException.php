<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Widgetize;

use Piwik\Exception\Exception;
use Piwik\Http\HttpCodeException;

/**
 * Thrown when a widget URL includes a token_auth that failed to authenticate the request.
 *
 * Implements HttpCodeException with a 4xx code so the response is a client error instead
 * of an HTTP 500, and is only logged at debug level.
 */
class UrlTokenAuthFailedException extends Exception implements HttpCodeException
{
    public function __construct(string $message, int $code = 401)
    {
        parent::__construct($message, $code);
    }
}
