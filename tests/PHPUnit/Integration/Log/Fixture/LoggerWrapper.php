<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Log\Fixture;

use Piwik\Log;

class LoggerWrapper
{
    public static function doLog($message)
    {
        Log::warning($message);
    }
}
