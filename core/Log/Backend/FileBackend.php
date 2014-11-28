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
use Piwik\Piwik;

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

    public function __construct($logMessageFormat, $logToFilePath)
    {
        $this->logToFilePath = $logToFilePath;

        parent::__construct($logMessageFormat);
    }

    public function __invoke($level, $tag, $datetime, $message, Log $logger)
    {
        $message = $this->getMessageFormattedFile($level, $tag, $datetime, $message, $logger);
        if (empty($message)) {
            return;
        }

        if (!@file_put_contents($this->logToFilePath, $message, FILE_APPEND)
            && !defined('PIWIK_TEST_MODE')
        ) {
            $message = Filechecks::getErrorMessageMissingPermissions($this->logToFilePath);
            throw new \Exception($message);
        }
    }

    private function getMessageFormattedFile($level, $tag, $datetime, $message, Log $logger)
    {
        if (is_string($message)) {
            $message = $this->formatMessage($level, $tag, $datetime, $message);
        } else {
            /**
             * Triggered when trying to log an object to a file. Plugins can use
             * this event to convert objects to strings before they are logged.
             *
             * **Example**
             *
             *     public function formatFileMessage(&$message, $level, $tag, $datetime, $logger) {
             *         if ($message instanceof MyCustomDebugInfo) {
             *             $message = $message->formatForFile();
             *         }
             *     }
             *
             * @param mixed &$message The object that is being logged. Event handlers should
             *                        check if the object is of a certain type and if it is,
             *                        set `$message` to the string that should be logged.
             * @param int $level The log level used with this log entry.
             * @param string $tag The current plugin that started logging (or if no plugin,
             *                    the current class).
             * @param string $datetime Datetime of the logging call.
             * @param Log $logger The Log instance.
             */
            Piwik::postEvent(Log::FORMAT_FILE_MESSAGE_EVENT, array(&$message, $level, $tag, $datetime, $logger));
        }

        $message = trim($message);
        $message = str_replace("\n", "\n  ", $message);

        return $message . "\n";
    }
}
