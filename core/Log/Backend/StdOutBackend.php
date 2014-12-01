<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Backend;

use Piwik\Log;

/**
 * Writes log to stdout.
 */
class StdOutBackend extends Backend
{
    public function __invoke($level, $tag, $datetime, $message, Log $logger)
    {
        $message = $this->formatMessage($level, $tag, $datetime, $message, $logger);

        echo $message . "\n";
    }
}
