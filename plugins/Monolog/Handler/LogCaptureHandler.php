<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Handler;

use Monolog\Handler\AbstractHandler;

class LogCaptureHandler extends AbstractHandler
{
    /**
     * @var array
     */
    private $allLogs;

    public function handle(array $record)
    {
        $this->allLogs[] = $record;
    }

    /**
     * Returns all records. The records should be processed, so one could just use $record['message'].
     *
     * @return array[]
     */
    public function getAllRecords()
    {
        return $this->allLogs;
    }
}