<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Backend;

use Piwik\Filechecks;
use Piwik\Log;
use Piwik\Log\Formatter\Formatter;

/**
 * Writes log to file.
 */
class FileBackend extends Backend
{
    /**
     * Path to the file to log to.
     *
     * @var string
     */
    private $logToFilePath;

    public function __construct(Formatter $formatter, $logToFilePath)
    {
        $this->logToFilePath = $logToFilePath;

        parent::__construct($formatter);
    }

    public function __invoke(array $record, Log $logger)
    {
        $message = $this->formatMessage($record, $logger);
        $message = str_replace("\n", "\n  ", $message) . "\n";

        if (!@file_put_contents($this->logToFilePath, $message, FILE_APPEND)
            && !defined('PIWIK_TEST_MODE')
        ) {
            $message = Filechecks::getErrorMessageMissingPermissions($this->logToFilePath);
            throw new \Exception($message);
        }
    }
}
