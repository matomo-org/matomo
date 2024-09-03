<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Http;

class BadRequestException extends \Exception implements HttpCodeException
{
    public function __construct($message, $code = 400)
    {
        parent::__construct($message, $code);
    }
}
