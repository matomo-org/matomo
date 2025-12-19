<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Exception;

use Piwik\Http\HttpCodeException;

class ThingNotFoundException extends \Piwik\Exception\Exception implements HttpCodeException
{
    public function __construct($message, $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}
