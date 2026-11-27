<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Http;

/**
 * Interface for exceptions whose code is sent as the HTTP status code of a UI or API response. Codes from 400 to 499
 * are treated as client errors and only logged at debug level.
 *
 * @api
 */
interface HttpCodeException extends \Throwable
{
}
