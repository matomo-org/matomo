<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Piwik\Filechecks;

/**
 * Writes log to file.
 */
class FileHandler extends AbstractProcessingHandler
{
    /**
     * Path to the file to log to.
     *
     * @var string
     */
    private $logToFilePath;

    public function __construct($logToFilePath, $level = Logger::DEBUG)
    {
        $this->logToFilePath = $logToFilePath;

        parent::__construct($level);
    }

    protected function write(array $record)
    {
        if (!@file_put_contents($this->logToFilePath, $record['formatted'], FILE_APPEND)
            && !defined('PIWIK_TEST_MODE')
        ) {
            throw new \Exception(
                Filechecks::getErrorMessageMissingPermissions($this->logToFilePath)
            );
        }
    }
}
