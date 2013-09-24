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

use Piwik\Log;

/**
 * TODO
 */
class Error
{
    /**
     * TODO
     */
    private $errno;

    /**
     * TODO
     */
    private $errstr;

    /**
     * TODO
     */
    private $errfile;

    /**
     * TODO
     */
    private $errline;

    /**
     * TODO
     */
    private $backtrace;

    /**
     * TODO
     */
    public function __construct($errno, $errstr, $errfile, $errline, $backtrace)
    {
        $this->errno = $errno;
        $this->errstr = $errstr;
        $this->errfile = $errfile;
        $this->errline = $errline;
        $this->backgrace = $backtrace;
    }

    public static function formatFileAndDBLogMessage(&$message, $level, $pluginName, $datetime)
    {
        if ($message instanceof Error) {
            // TODO
        }
    }

    public static function formatScreenMessage(&$message, $level, $pluginName, $datetime)
    {
        if ($message instanceof Error) {
            // TODO
        }
    }

    public static function setErrorHandler()
    {
        Piwik_AddAction('Log.formatFileMessage', array('Error', 'formatFileAndDBLogMessage'));
        Piwik_AddAction('Log.formatDatabaseMessage', array('Error', 'formatFileAndDBLogMessage'));
        Piwik_AddAction('Log.formatScreenMessage', array('Error', 'formatScreenMessage'));

        set_error_handler(array('Error', 'errorHandler'));
    }

    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        // if the error has been suppressed by the @ we don't handle the error
        if (error_reporting() == 0) {
            return;
        }

        $plugin = false;

        $backtrace = '';
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

                // try and discern the plugin name
                if (empty($plugin)) {
                    if (preg_match("/^Piwik\\Plugins\\([a-z_]+)\\/", $debug['class'], $matches)) {
                        $plugin = $matches[1];
                    }
                }
            }
        }

        $error = new Error($errno, $errstr, $errfile, $errline, $backtrace); // TODO (also logger_error formatting)
        Log::e($plugin ?: 'unknown', $error);

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