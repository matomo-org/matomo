<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Log;
use Piwik\FrontController;
use Piwik\API\ResponseBuilder;

/**
 * TODO
 */
class ExceptionHandler
{
    public static function setUp()
    {
        Piwik_AddAction('Log.formatFileMessage', array('\\Piwik\\ExceptionHandler', 'formatFileAndDBLogMessage'));
        Piwik_AddAction('Log.formatDatabaseMessage', array('\\Piwik\\ExceptionHandler', 'formatFileAndDBLogMessage'));
        Piwik_AddAction('Log.formatScreenMessage', array('\\Piwik\\ExceptionHandler', 'formatScreenMessage'));

        set_exception_handler(array('\\Piwik\\ExceptionHandler', 'exceptionHandler'));
    }

    public static function formatFileAndDBLogMessage(&$message, $level, $pluginName, $datetime, $log)
    {
        if ($message instanceof \Exception) {
            $message = sprintf("%s(%d): %s\n%s", $message->getFile(), $message->getLine(), $message->getMessage(),
                $message->getTraceAsString());

            $message = $log->formatMessage($level, $pluginName, $datetime, $message);
        }
    }

    public static function formatScreenMessage(&$message, $level, $pluginName, $datetime, $log)
    {
        if ($message instanceof \Exception) {
            if (!Common::isPhpCliMode()) {
                @header('Content-Type: text/html; charset=utf-8');
            }

            $outputFormat = strtolower(Common::getRequestVar('format', 'html', 'string'));
            $response = new ResponseBuilder($outputFormat);
            $message = $response->getResponseException(new \Exception($message->getMessage()));
        }
    }

    public static function exceptionHandler(Exception $exception)
    {
        Log::error($exception);

        // TODO: what about this code?
        /*if (FrontController::shouldRethrowException()) {
            throw $exception;
        }*/
    }
}