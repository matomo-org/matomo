<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Piwik\Container\StaticContainer;
use Piwik\Exception\ErrorException;
use Piwik\Log\LoggerInterface;

/**
 * Piwik's error handler function.
 */
class ErrorHandler
{
    private static $fatalErrorStackTrace = [];

    private static $lastError = '';

    /**
     * Fatal errors in PHP do not leave behind backtraces, which can make it impossible to determine
     * the exact cause of one. We can, however, save a partial stack trace by remembering certain execution
     * points. This method and popFatalErrorBreadcrumb() are used for that purpose.
     *
     * To use this method, surround a function call w/ pushFatalErrorBreadcrumb() & popFatalErrorBreadcrumb()
     * like so:
     *
     *     public function theMethodIWantToAppearInFatalErrorStackTraces()
     *     {
     *         try {
     *             ErrorHandler::pushFatalErrorBreadcrumb(static::class);
     *
     *             // ...
     *         } finally {
     *             ErrorHandler::popFatalErrorBreadcrumb();
     *         }
     *     }
     *
     * If a fatal error occurs, theMethodIWantToAppearInFatalErrorStackTraces will appear in the stack trace,
     * if PIWIK_PRINT_ERROR_BACKTRACE is true.
     */
    public static function pushFatalErrorBreadcrumb($className = null, $importantArgs = null)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $limit = 2);
        $backtrace[1]['class'] = $className; // knowing the derived class name is far more useful
        $backtrace[1]['args'] = empty($importantArgs) ? [] : array_map('json_encode', $importantArgs);
        array_unshift(self::$fatalErrorStackTrace, $backtrace[1]);
    }

    public static function popFatalErrorBreadcrumb()
    {
        array_shift(self::$fatalErrorStackTrace);
    }

    public static function getFatalErrorPartialBacktrace()
    {
        $result = '';
        foreach (self::$fatalErrorStackTrace as $index => $entry) {
            $function = $entry['function'];
            if (!empty($entry['class'])) {
                $function = $entry['class'] . $entry['type'] . $function;
            }

            $args = '';
            if (!empty($entry['args'])) {
                $isFirst = true;
                foreach ($entry['args'] as $name => $value) {
                    if ($isFirst) {
                        $isFirst = false;
                    } else {
                        $args .= ', ';
                    }
                    $args .= $name . '=' . $value;
                }
            }

            $result .= sprintf("#%s %s(%s): %s(%s)\n", $index, $entry['file'], $entry['line'], $function, $args);
        }
        return $result;
    }

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
            // E_STRICT is deprecated as of PHP 8.4
            // @todo can be removed once only PHP 8 is supported
            case @E_STRICT:
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
        self::$lastError = self::createLogMessage($errno, $errstr, $errfile, $errline);

        // if the error has been suppressed by the @ we don't handle the error
        if (!(error_reporting() & $errno)) {
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
                $backtrace = \Piwik_ShouldPrintBackTraceWithMessage() ? $e->getTraceAsString() : '';
                $message = self::getHtmlMessage($errno, $errstr, $errfile, $errline, $backtrace);
                throw new ErrorException($message, 0, $errno, $errfile, $errline);
            case E_WARNING:
            case E_NOTICE:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            // E_STRICT is deprecated as of PHP 8.4
            // @todo can be removed once only PHP 8 is supported
            case @E_STRICT:
            case E_RECOVERABLE_ERROR:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            default:
                $context = array('trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15));
                try {
                    StaticContainer::get(LoggerInterface::class)->warning(
                        self::createLogMessage($errno, $errstr, $errfile, $errline),
                        $context
                    );
                } catch (\Exception $ex) {
                    // ignore (it's possible for this to happen if the StaticContainer hasn't been created yet)
                }

                break;
        }
    }

    public static function getLastError()
    {
        $lastError = error_get_last();

        if (!empty($lastError['message'])) {
            return $lastError['message'];
        }

        return self::$lastError;
    }

    private static function createLogMessage($errno, $errstr, $errfile, $errline)
    {
        return sprintf(
            "%s(%d): %s - %s - Matomo " . (class_exists('Piwik\Version') ? Version::VERSION : '') . " - Please report this message in the Matomo forums: https://forum.matomo.org (please do a search first as it might have been reported already)",
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

        $html = "<p>There is an error. Please report the message (Matomo " . (class_exists('Piwik\Version') ? Version::VERSION : '') . ")
        and full backtrace in the <a target='_blank' rel='noreferrer noopener' href='https://forum.matomo.org'>Matomo forums</a> (please do a search first as it might have been reported already!).</p>";
        $html .= "<p><strong>{$message}</strong> in <em>{$errfile}</em>";
        $html .= " on line {$errline}</p>";
        $html .= "Backtrace:<pre>";
        $html .= str_replace("\n", "\n", $trace);
        $html .= "</pre>";

        return $html;
    }
}
