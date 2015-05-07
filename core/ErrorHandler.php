<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Piwik\Exception\ErrorException;

/**
 * Piwik's error handler function.
 */
class ErrorHandler
{
    /**
     * Returns a string description of a PHP error number.
     *
     * @param int $errno `E_ERROR`, `E_WARNING`, `E_PARSE`, etc.
     * @return string
     */
    public static function getErrNoString($errno)
    {
        switch ($errno) {
            case E_ERROR:
                return "Error";
            case E_WARNING:
                return "Warning";
            case E_PARSE:
                return "Parse Error";
            case E_NOTICE:
                return "Notice";
            case E_CORE_ERROR:
                return "Core Error";
            case E_CORE_WARNING:
                return "Core Warning";
            case E_COMPILE_ERROR:
                return "Compile Error";
            case E_COMPILE_WARNING:
                return "Compile Warning";
            case E_USER_ERROR:
                return "User Error";
            case E_USER_WARNING:
                return "User Warning";
            case E_USER_NOTICE:
                return "User Notice";
            case E_STRICT:
                return "Strict Notice";
            case E_RECOVERABLE_ERROR:
                return "Recoverable Error";
            case E_DEPRECATED:
                return "Deprecated";
            case E_USER_DEPRECATED:
                return "User Deprecated";
            default:
                return "Unknown error ($errno)";
        }
    }

    public static function registerErrorHandler()
    {
        set_error_handler(array('Piwik\ErrorHandler', 'errorHandler'));
    }

    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        // if the error has been suppressed by the @ we don't handle the error
        if (error_reporting() == 0) {
            return;
        }

        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_CORE_WARNING:
            case E_COMPILE_ERROR:
            case E_COMPILE_WARNING:
            case E_USER_ERROR:
                Common::sendResponseCode(500);
                // Convert the error to an exception with an HTML message
                $e = new \Exception();
                $message = self::getHtmlMessage($errno, $errstr, $errfile, $errline, $e->getTraceAsString());
                throw new ErrorException($message, 0, $errno, $errfile, $errline);
                break;

            case E_WARNING:
            case E_NOTICE:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            case E_STRICT:
            case E_RECOVERABLE_ERROR:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            default:
                try {
                    Log::warning(self::createLogMessage($errno, $errstr, $errfile, $errline));
                } catch (\Exception $ex) {
                    // ignore (it's possible for this to happen if the StaticContainer hasn't been created yet)
                }

                break;
        }
    }

    private static function createLogMessage($errno, $errstr, $errfile, $errline)
    {
        return sprintf(
            "%s(%d): %s - %s - Piwik " . (class_exists('Piwik\Version') ? Version::VERSION : '') . " - Please report this message in the Piwik forums: http://forum.piwik.org (please do a search first as it might have been reported already)",
            $errfile,
            $errline,
            ErrorHandler::getErrNoString($errno),
            $errstr
        );
    }

    private static function getHtmlMessage($errno, $errstr, $errfile, $errline, $trace)
    {
        $trace = Log::$debugBacktraceForTests ?: $trace;

        $message = ErrorHandler::getErrNoString($errno) . ' - ' . $errstr;

        $html = "<p>There is an error. Please report the message (Piwik " . (class_exists('Piwik\Version') ? Version::VERSION : '') . ")
        and full backtrace in the <a href='?module=Proxy&action=redirect&url=http://forum.piwik.org' target='_blank'>Piwik forums</a> (please do a Search firit might have been reported already!).</p>";
        $html .= "<p><strong>{$message}</strong> in <em>{$errfile}</em>";
        $html .= " on line {$errline}</p>";
        $html .= "Backtrace:<pre>";
        $html .= str_replace("\n", "\n", $trace);
        $html .= "</pre>";

        return $html;
    }
}
