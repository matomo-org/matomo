<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Exception;

use Piwik\Http\HttpCodeException;

class NoPrivilegesException extends Exception implements HttpCodeException
{
    public function __construct($message, $code = 401)
    {
        parent::__construct($message, $code);
    }
}
