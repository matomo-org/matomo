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
use Piwik\Log;

/**
 * TODO
 */
class AlgorithmLogger
{
    // Show only first N characters from Piwik API output in case of errors
    const TRUNCATE_ERROR_MESSAGE_SUMMARY = 6000;

    /**
     * TODO
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
     * TODO
     */
    public function logNetworkError($url, $response)
    {
        $message = "Got invalid response from API request: $url. ";
        if (empty($response)) {
            $message .= "The response was empty. This usually means a server error. This solution to this error is generally to increase the value of 'memory_limit' in your php.ini file. Please check your Web server Error Log file for more details.";
        } else {
            $message .= "Response was '$response'";
        }
        $this->logError($message);
        return false;
    }

    /**
     * TODO
     */
    public function logSection($title = "")
    {
        $this->log("---------------------------");
        if (!empty($title)) {
            $this->log($title);
        }
    }

    /**
     * TODO
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
     * TODO
     */
    public function logFatalError($errorMessage)
    {
        $this->logError($errorMessage);

        throw new Exception($errorMessage);
    }
}