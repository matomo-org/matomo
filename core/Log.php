<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Log\Backend\ScreenBackend;

/**
 * Logging utility class.
 *
 * Log entries are made with a message and log level. The logging utility will tag each
 * log entry with the name of the plugin that's doing the logging. If no plugin is found,
 * the name of the current class is used.
 *
 * You can log messages using one of the public static functions (eg, 'error', 'warning',
 * 'info', etc.). Messages logged with the **error** level will **always** be logged to
 * the screen, regardless of whether the [log] log_writer config option includes the
 * screen writer.
 *
 * Currently, Piwik supports the following logging backends:
 *
 * - **screen**: logging to the screen
 * - **file**: logging to a file
 * - **database**: logging to Piwik's MySQL database
 *
 * ### Logging configuration
 *
 * The logging utility can be configured by manipulating the INI config options in the
 * `[log]` section.
 *
 * The following configuration options can be set:
 *
 * - `log_writers[]`: This is an array of log writer IDs. The three log writers provided
 *                    by Piwik core are **file**, **screen** and **database**. You can
 *                    get more by installing plugins. The default value is **screen**.
 * - `log_level`: The current log level. Can be **ERROR**, **WARN**, **INFO**, **DEBUG**,
 *                or **VERBOSE**. Log entries made with a log level that is as or more
 *                severe than the current log level will be outputted. Others will be
 *                ignored. The default level is **WARN**.
 * - `log_only_when_cli`: 0 or 1. If 1, logging is only enabled when Piwik is executed
 *                        in the command line (for example, by the core:archive command
 *                        script). Default: 0.
 * - `log_only_when_debug_parameter`: 0 or 1. If 1, logging is only enabled when the
 *                                    `debug` query parameter is 1. Default: 0.
 * - `logger_file_path`: For the file log writer, specifies the path to the log file
 *                       to log to or a path to a directory to store logs in. If a
 *                       directory, the file name is piwik.log. Can be relative to
 *                       Piwik's root dir or an absolute path. Defaults to **tmp/logs**.
 *
 * ### Custom message formatting
 *
 * If you'd like to format log messages differently for different backends, you can use
 * one of the `'Log.format...Message'` events.
 *
 * These events are fired when an object is logged. You can create your own custom class
 * containing the information to log and listen to these events to format it correctly for
 * different backends.
 *
 * If you don't care about the backend when formatting an object, implement a `__toString()`
 * in the custom class.
 *
 * ### Custom log writers
 *
 * New logging backends can be added via the {@hook Log.getAvailableWriters}` event. A log
 * writer is just a callback that accepts log entry information (such as the message,
 * level, etc.), so any backend could conceivably be used (including existing PSR3
 * backends).
 *
 * ### Examples
 *
 * **Basic logging**
 *
 *     Log::error("This log message will end up on the screen and in a file.")
 *     Log::verbose("This log message uses %s params, but %s will only be called if the"
 *                . " configured log level includes %s.", "sprintf", "sprintf", "verbose");
 *
 * **Logging objects**
 *
 *     class MyDebugInfo
 *     {
 *         // ...
 *
 *         public function __toString()
 *         {
 *             return // ...
 *         }
 *     }
 *
 *     try {
 *         $myThirdPartyServiceClient->doSomething();
 *     } catch (Exception $unexpectedError) {
 *         $debugInfo = new MyDebugInfo($unexpectedError, $myThirdPartyServiceClient);
 *         Log::debug($debugInfo);
 *     }
 */
class Log extends Singleton
{
    // log levels
    const NONE = 0;
    const ERROR = 1;
    const WARN = 2;
    const INFO = 3;
    const DEBUG = 4;
    const VERBOSE = 5;

    // config option names
    const LOG_LEVEL_CONFIG_OPTION = 'log_level';
    const LOG_WRITERS_CONFIG_OPTION = 'log_writers';
    const LOGGER_FILE_PATH_CONFIG_OPTION = 'logger_file_path';
    const STRING_MESSAGE_FORMAT_OPTION = 'string_message_format';

    const FORMAT_FILE_MESSAGE_EVENT = 'Log.formatFileMessage';

    const FORMAT_SCREEN_MESSAGE_EVENT = 'Log.formatScreenMessage';

    const FORMAT_DATABASE_MESSAGE_EVENT = 'Log.formatDatabaseMessage';

    const GET_AVAILABLE_WRITERS_EVENT = 'Log.getAvailableWriters';

    /**
     * Singleton instance.
     *
     * @var Log
     */
    private static $instance;

    /**
     * The current logging level. Everything of equal or greater priority will be logged.
     * Everything else will be ignored.
     *
     * @var int
     */
    private $currentLogLevel = self::WARN;

    /**
     * Processors process log messages before they are being sent to backends.
     *
     * @var callable[]
     */
    private $processors = array();

    /**
     * The array of callbacks executed when logging a message. Each callback writes a log
     * message to a logging backend.
     *
     * @var callable[]
     */
    private $writers = array();

    /**
     * The log message format string that turns a tag name, date-time and message into
     * one string to log.
     *
     * @var string
     */
    private $logMessageFormat;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = StaticContainer::getContainer()->get(__CLASS__);
        }
        return self::$instance;
    }
    public static function unsetInstance()
    {
        self::$instance = null;
    }
    public static function setSingletonInstance($instance)
    {
        self::$instance = $instance;
    }

    /**
     * @param callable[] $writers
     * @param string $logMessageFormat
     * @param int $logLevel
     * @param callable[] $processors
     */
    public function __construct(array $writers, $logMessageFormat, $logLevel, array $processors)
    {
        $this->writers = $writers;
        $this->logMessageFormat = $logMessageFormat;
        $this->currentLogLevel = $logLevel;
        $this->processors = $processors;
    }

    /**
     * Logs a message using the ERROR log level.
     *
     * _Note: Messages logged with the ERROR level are always logged to the screen in addition
     * to configured writers._
     *
     * @param string $message The log message. This can be a sprintf format string.
     * @param ... mixed Optional sprintf params.
     * @api
     */
    public static function error($message /* ... */)
    {
        self::logMessage(self::ERROR, $message, array_slice(func_get_args(), 1));
    }

    /**
     * Logs a message using the WARNING log level.
     *
     * @param string $message The log message. This can be a sprintf format string.
     * @param ... mixed Optional sprintf params.
     * @api
     */
    public static function warning($message /* ... */)
    {
        self::logMessage(self::WARN, $message, array_slice(func_get_args(), 1));
    }

    /**
     * Logs a message using the INFO log level.
     *
     * @param string $message The log message. This can be a sprintf format string.
     * @param ... mixed Optional sprintf params.
     * @api
     */
    public static function info($message /* ... */)
    {
        self::logMessage(self::INFO, $message, array_slice(func_get_args(), 1));
    }

    /**
     * Logs a message using the DEBUG log level.
     *
     * @param string $message The log message. This can be a sprintf format string.
     * @param ... mixed Optional sprintf params.
     * @api
     */
    public static function debug($message /* ... */)
    {
        self::logMessage(self::DEBUG, $message, array_slice(func_get_args(), 1));
    }

    /**
     * Logs a message using the VERBOSE log level.
     *
     * @param string $message The log message. This can be a sprintf format string.
     * @param ... mixed Optional sprintf params.
     * @api
     */
    public static function verbose($message /* ... */)
    {
        self::logMessage(self::VERBOSE, $message, array_slice(func_get_args(), 1));
    }

    /**
     * Creates log message combining logging info including a log level, tag name,
     * date time, and caller-provided log message. The log message can be set through
     * the `[log] string_message_format` INI config option. By default it will
     * create log messages like:
     *
     * **LEVEL [tag:datetime] log message**
     *
     * @param int $level
     * @param string $tag
     * @param string $datetime
     * @param string $message
     * @return string
     */
    public function formatMessage($level, $tag, $datetime, $message)
    {
        return str_replace(
            array("%tag%", "%message%", "%datetime%", "%level%"),
            array($tag, trim($message), $datetime, $this->getStringLevel($level)),
            $this->logMessageFormat
        );
    }

    public function setLogLevel($logLevel)
    {
        $this->currentLogLevel = $logLevel;
    }

    public function getLogLevel()
    {
        return $this->currentLogLevel;
    }

    private function doLog($level, $message, $sprintfParams = array())
    {
        if (!$this->shouldLoggerLog($level)) {
            return;
        }

        $datetime = date("Y-m-d H:i:s");

        foreach ($this->processors as $processor) {
            $message = $processor($message, $sprintfParams, $level);
        }

        if (version_compare(phpversion(), '5.3.6', '>=')) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS | DEBUG_BACKTRACE_PROVIDE_OBJECT);
        } else {
            $backtrace = debug_backtrace();
        }
        $tag = Plugin::getPluginNameFromBacktrace($backtrace);

        // if we can't determine the plugin, use the name of the calling class
        if ($tag == false) {
            $tag = $this->getClassNameThatIsLogging($backtrace);
        }

        $this->writeMessage($level, $tag, $datetime, $message);
    }

    private function writeMessage($level, $tag, $datetime, $message)
    {
        foreach ($this->writers as $writer) {
            call_user_func($writer, $level, $tag, $datetime, $message, $this);
        }

        // TODO this hack should be removed
        if ($level == self::ERROR) {
            $screenBackend = new ScreenBackend($this->logMessageFormat);
            $message = $screenBackend->getMessageFormattedScreen($level, $tag, $datetime, $message, $this);
            $this->writeErrorToStandardErrorOutput($message);
            if (!isset($this->writers['screen'])) {
                echo $message;
            }
        }
    }

    private static function logMessage($level, $message, $sprintfParams)
    {
        self::getInstance()->doLog($level, $message, $sprintfParams);
    }

    private function shouldLoggerLog($level)
    {
        return $level <= $this->currentLogLevel;
    }

    private function getStringLevel($level)
    {
        static $levelToName = array(
            self::NONE    => 'NONE',
            self::ERROR   => 'ERROR',
            self::WARN    => 'WARN',
            self::INFO    => 'INFO',
            self::DEBUG   => 'DEBUG',
            self::VERBOSE => 'VERBOSE'
        );
        return $levelToName[$level];
    }

    private function getClassNameThatIsLogging($backtrace)
    {
        foreach ($backtrace as $tracepoint) {
            if (isset($tracepoint['class'])
                && $tracepoint['class'] != "Piwik\\Log"
                && $tracepoint['class'] != "Piwik\\Piwik"
                && $tracepoint['class'] != "Piwik\\CronArchive"
            ) {
                return $tracepoint['class'];
            }
        }
        return false;
    }

    /**
     * @param $message
     */
    private function writeErrorToStandardErrorOutput($message)
    {
        if (defined('PIWIK_TEST_MODE')) {
            // do not log on stderr during tests (prevent display of errors in CI output)
            return;
        }
        $fe = fopen('php://stderr', 'w');
        fwrite($fe, $message);
    }
}
