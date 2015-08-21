<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Handler;

use Monolog\Handler\AbstractProcessingHandler;

/**
 * Simply echos all messages.
 */
class EchoHandler extends AbstractProcessingHandler
{
    protected function write(array $record)
    {
        $message = $record['level_name'] . ': ' . $record['message'];

        echo $message . "\n";
    }
}
