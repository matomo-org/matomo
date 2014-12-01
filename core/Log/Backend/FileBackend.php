<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Backend;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Piwik\Filechecks;
use Piwik\Log;

/**
 * Writes log to file.
 */
class FileBackend extends AbstractProcessingHandler
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
        $message = str_replace("\n", "\n  ", $record['formatted']['message']) . "\n";

        if (!@file_put_contents($this->logToFilePath, $message, FILE_APPEND)
            && !defined('PIWIK_TEST_MODE')
        ) {
            $message = Filechecks::getErrorMessageMissingPermissions($this->logToFilePath);
            throw new \Exception($message);
        }
    }
}
