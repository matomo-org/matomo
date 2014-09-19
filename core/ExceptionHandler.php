<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\API\ResponseBuilder;
use Piwik\Common;
use Piwik\Plugin;

/**
 * Contains Piwik's uncaught exception handler and log file formatting for exception
 * instances.
 */
class ExceptionHandler
{
    /**
     * The backtrace string to use when testing.
     *
     * @var string
     */
    public static $debugBacktraceForTests = null;

    public static function setUp()
    {
        Piwik::addAction('Log.formatFileMessage', array('\\Piwik\\ExceptionHandler', 'formatFileAndDBLogMessage'));
        Piwik::addAction('Log.formatDatabaseMessage', array('\\Piwik\\ExceptionHandler', 'formatFileAndDBLogMessage'));
        Piwik::addAction('Log.formatScreenMessage', array('\\Piwik\\ExceptionHandler', 'formatScreenMessage'));

        set_exception_handler(array('\\Piwik\\ExceptionHandler', 'logException'));
    }

    public static function formatFileAndDBLogMessage(&$message, $level, $tag, $datetime, $log)
    {
        if ($message instanceof \Exception) {
            $message = sprintf("%s(%d): %s\n%s", $message->getFile(), $message->getLine(), $message->getMessage(),
                self::$debugBacktraceForTests ? : $message->getTraceAsString());

            $message = $log->formatMessage($level, $tag, $datetime, $message);
        }
    }

    public static function formatScreenMessage(&$message, $level, $tag, $datetime, $log)
    {
        if ($message instanceof \Exception) {
            Common::sendHeader('Content-Type: text/html; charset=utf-8');

            $outputFormat = strtolower(Common::getRequestVar('format', 'html', 'string'));
            $response = new ResponseBuilder($outputFormat);
            $message = $response->getResponseException(new \Exception($message->getMessage()));
        }
    }

    public static function logException(\Exception $exception)
    {
        Log::error($exception);
    }
}
