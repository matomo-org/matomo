<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TestPlugin;

use Piwik\Log;

class TestLoggingUtility
{
    public static function doLog($message)
    {
        Log::warning($message);
    }
}