<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\CronArchive;

use Exception;
use Piwik\Config;
use Piwik\Log;
use Piwik\Url;

/**
 * Default logging implementation for CronArchive class.
 *
 * This class will use the Piwik\Log class. It will force logging to the screen and still log
 * to configured log writers.
 *
 * Eventually, it will be possible to supply custom ones to customize how CronArchive will log
 * (or to disable it entirely).
 */
class AlgorithmLogger
{
    // Show only first N characters from Piwik API output in case of errors
    const TRUNCATE_ERROR_MESSAGE_SUMMARY = 6000;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $config = Config::getInstance();

        $log = $config->log;
        $log['log_only_when_debug_parameter'] = 0;
        $log[Log::LOG_WRITERS_CONFIG_OPTION][] = "screen";

        $config->log = $log;

        Log::unsetInstance();

        // Make sure we log at least INFO (if logger is set to DEBUG then keep it)
        $logLevel = Log::getInstance()->getLogLevel();
        if ($logLevel < Log::INFO) {
            Log::getInstance()->setLogLevel(Log::INFO);
        }
    }

    /**
     * Log an error that occurred during CronArchive execution.
     *
     * @param string $errorMessage The string error message.
     */
    public function logError($errorMessage)
    {
        if (!defined('PIWIK_ARCHIVE_NO_TRUNCATE')) {
            $errorMessage = substr($errorMessage, 0, self::TRUNCATE_ERROR_MESSAGE_SUMMARY);
        }

        $errorMessage = str_replace(array("\n", "\t"), " ", $errorMessage);

        Log::error($errorMessage);

        flush();
    }

    /**
     * Marks a new section in the log output.
     *
     * @param string $title The section's title.
     */
    public function logSection($title = "")
    {
        $this->log("---------------------------");
        if (!empty($title)) {
            $this->log($title);
        }
    }

    /**
     * Logs a message to the output.
     *
     * @param string $message
     */
    public function log($message)
    {
        try {
            Log::info($message);

            flush();
        } catch(Exception $e) {
            print($message . "\n");
        }
    }

    /**
     * Logs an error message and throws an exception to stop CronArchive execution completely.
     *
     * @param string $errorMessage The error message.
     * @throws Exception every time.
     */
    public function logFatalError($errorMessage)
    {
        $this->logError($errorMessage);

        throw new Exception($errorMessage);
    }
}