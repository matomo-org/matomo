<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Log\Formatter\ExceptionHtmlFormatter;
use Piwik\Log\Formatter\ExceptionTextFormatter;
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
        Piwik::addAction('Log.formatFileMessage', array('Piwik\ExceptionHandler', 'formatFileAndDBLogMessage'));
        Piwik::addAction('Log.formatDatabaseMessage', array('Piwik\ExceptionHandler', 'formatFileAndDBLogMessage'));
        Piwik::addAction('Log.formatScreenMessage', array('Piwik\ExceptionHandler', 'formatScreenMessage'));

        set_exception_handler(array('Piwik\ExceptionHandler', 'logException'));
    }

    public static function formatFileAndDBLogMessage(&$message, $level, $tag, $datetime, Log $log)
    {
        $formatter = new ExceptionTextFormatter();
        $message = $formatter->format($message, $level, $tag, $datetime, $log);
    }

    public static function formatScreenMessage(&$message, $level, $tag, $datetime, $log)
    {
        $formatter = new ExceptionHtmlFormatter();
        $message = $formatter->format($message, $level, $tag, $datetime, $log);
    }

    public static function logException(\Exception $exception)
    {
        Log::error($exception);
    }
}
