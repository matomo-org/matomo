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
    public function __invoke(array $record, Log $logger)
    {
        echo $this->formatMessage($record, $logger) . "\n";
    }
}
