<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

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
        set_exception_handler(array('Piwik\ExceptionHandler', 'logException'));
    }

    public static function logException(\Exception $exception)
    {
        Log::error($exception);
    }
}
