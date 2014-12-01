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
 * Holds PHP error information (non-exception errors). Also contains log formatting logic
 * for PHP errors and Piwik's error handler function.
 */
class Error
{
    /**
     * The backtrace string to use when testing.
     *
     * @var string
     */
    public static $debugBacktraceForTests = null;

    /**
     * The error number. See http://php.net/manual/en/errorfunc.constants.php#errorfunc.constants.errorlevels
     *
     * @var int
     */
    public $errno;

    /**
     * The error message.
     *
     * @var string
     */
    public $errstr;

    /**
     * The file in which the error occurred.
     *
     * @var string
     */
    public $errfile;

    /**
     * The line number on which the error occurred.
     *
     * @var int
     */
    public $errline;

    /**
     * The error backtrace.
     *
     * @var string
     */
    public $backtrace;

    /**
     * Constructor.
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param string $backtrace
     */
    public function __construct($errno, $errstr, $errfile, $errline, $backtrace)
    {
        $this->errno = $errno;
        $this->errstr = $errstr;
        $this->errfile = $errfile;
        $this->errline = $errline;
        $this->backtrace = $backtrace;
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

        $backtrace = '';
        if (empty(self::$debugBacktraceForTests)) {
            $bt = @debug_backtrace();
            if ($bt !== null && isset($bt[0])) {
                foreach ($bt as $i => $debug) {
                    $backtrace .= "#$i  "
                        . (isset($debug['class']) ? $debug['class'] : '')
                        . (isset($debug['type']) ? $debug['type'] : '')
                        . (isset($debug['function']) ? $debug['function'] : '')
                        . '(...) called at ['
                        . (isset($debug['file']) ? $debug['file'] : '') . ':'
                        . (isset($debug['line']) ? $debug['line'] : '') . ']' . "\n";
                }
            }
        } else {
            $backtrace = self::$debugBacktraceForTests;
        }

        $error = new Error($errno, $errstr, $errfile, $errline, $backtrace);
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
