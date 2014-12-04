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
 * Contains Piwik's uncaught exception handler.
 */
class ExceptionHandler
{
    public static function setUp()
    {
        set_exception_handler(array('Piwik\ExceptionHandler', 'logException'));
    }

    public static function logException(\Exception $exception)
    {
        Log::error($exception);
    }
}
