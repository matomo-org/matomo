<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Http;

use Piwik\NoAccessException;

class BadRequestException extends NoAccessException
{
    public function __construct($message)
    {
        parent::__construct($message, $code = 400);
    }
}
