<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\Api\Service;

/**
 */
class Exception extends \Exception
{
    public const HTTP_ERROR = 100;
    public const API_ERROR  = 101;
}
