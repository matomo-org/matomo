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
use Piwik\Piwik;
use Piwik\Log;
use Piwik\FrontController;

/**
 * Exception handler used to display nicely exceptions in Piwik
 *
 * @param Exception $exception
 * @throws Exception
 */
function Piwik_ExceptionHandler(Exception $exception)
{
    try {
        Log::e("%s (%s): %s", array($exception->getFile(), $exception->getLine(), $exception->getMessage())); // TODO add backtrace?
    } catch (Exception $e) {
        if (FrontController::shouldRethrowException()) {
            throw $exception;
        }

        // case when the exception is raised before the logger being ready
        // we handle the exception a la mano, but using the Logger formatting properties
        $event = array();
        $event['errno'] = $exception->getCode();
        $event['message'] = $exception->getMessage();
        $event['errfile'] = $exception->getFile();
        $event['errline'] = $exception->getLine();
        $event['backtrace'] = $exception->getTraceAsString();

        $formatter = new ExceptionScreenFormatter();

        $message = $formatter->format($event);
        $message .= "<br /><br />And this exception raised another exception \"" . $e->getMessage() . "\"";

        Piwik::exitWithErrorMessage($message);
    }
}
