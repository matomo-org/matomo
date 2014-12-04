<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Monolog\Handler\AbstractHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Piwik\Container\StaticContainer;
use Piwik\Db;

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
 * If you'd like to format log messages differently for different backends, you can
 * implement a new `Piwik\Log\Formatter\Formatter`.
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

    /**
     * The backtrace string to use when testing.
     *
     * @var string
     */
    public static $debugBacktraceForTests;

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
     * The array of handlers called when logging a message. Each handler writes a log
     * message to a logging backend.
     *
     * @var HandlerInterface[]
     */
    private $handlers = array();

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
     * @param HandlerInterface[] $handlers
     * @param int $logLevel
     * @param callable[] $processors
     */
    public function __construct(array $handlers, $logLevel, array $processors)
    {
        $this->handlers = $handlers;
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
     * @param int $logLevel
     * @deprecated Will be removed
     */
    public function setLogLevel($logLevel)
    {
    }

    public function getLogLevel()
    {
        return $this->currentLogLevel;
    }

    private function doLog($level, $message, $parameters = array())
    {
        // To ensure the compatibility with PSR-3, the message must be a string
        if ($message instanceof \Exception) {
            $parameters['exception'] = $message;
            $message = $message->getMessage();
        }

        // Create a record similar to Monolog to ease future transition
        $record = array(
            'message'    => $message,
            'context'    => $parameters,
            'channel'    => 'piwik',
            'level'      => $this->getMonologLevel($level),
            'level_name' => self::getStringLevel($level),
            'time'       => new \DateTime(),
            'datetime'   => new \DateTime(),
            'extra'      => array(),
        );

        foreach ($this->processors as $processor) {
            $record = $processor($record);
        }

        foreach ($this->handlers as $handler) {
            if ($handler->isHandling($record)) {
                $handler->handle($record);
            }
        }
    }

    private static function logMessage($level, $message, $parameters)
    {
        self::getInstance()->doLog($level, $message, $parameters);
    }

    private function getStringLevel($level)
    {
        static $levelToName = array(
            Log::NONE    => 'NONE',
            Log::ERROR   => 'ERROR',
            Log::WARN    => 'WARN',
            Log::INFO    => 'INFO',
            Log::DEBUG   => 'DEBUG',
            Log::VERBOSE => 'VERBOSE'
        );
        return $levelToName[$level];
    }

    public static function getMonologLevel($level)
    {
        switch ($level) {
            case self::ERROR:
                return Logger::ERROR;
            case self::WARN:
                return Logger::WARNING;
            case self::INFO:
                return Logger::INFO;
            case self::DEBUG:
                return Logger::DEBUG;
            case self::VERBOSE:
                return Logger::DEBUG;
            case self::NONE:
            default:
                // Highest level possible, need to do better in the future...
                return Logger::EMERGENCY;
        }
    }
}
