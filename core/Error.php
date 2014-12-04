<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

require_once PIWIK_INCLUDE_PATH . '/core/Log.php';

/**
 * Piwik's error handler function.
 */
class Error
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

    public static function setErrorHandler()
    {
        set_error_handler(array('Piwik\Error', 'errorHandler'));
    }

    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        // if the error has been suppressed by the @ we don't handle the error
        if (error_reporting() == 0) {
            return;
        }

        $error = new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        Log::error($error);

        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_CORE_WARNING:
            case E_COMPILE_ERROR:
            case E_COMPILE_WARNING:
            case E_USER_ERROR:
                exit;
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
                // do not exit
                break;
        }
    }
}
