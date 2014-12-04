<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Backend;

use Monolog\Handler\AbstractProcessingHandler;

/**
 * Writes log to stdout.
 */
class StdOutBackend extends AbstractProcessingHandler
{
    protected function write(array $record)
    {
        echo $record['formatted']['message'] . "\n";
    }
}
