<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

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
 *
 * @method static \Piwik\Log getInstance()
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
     * The current logging level. Everything of equal or greater priority will be logged.
     * Everything else will be ignored.
     *
     * @var int
     */
    private $currentLogLevel = self::WARN;

    /**
     * The array of callbacks executed when logging to a file. Each callback writes a log
     * message to a logging backend.
     *
     * @var array
     */
    private $writers = array();

    /**
     * The log message format string that turns a tag name, date-time and message into
     * one string to log.
     *
     * @var string
     */
    private $logMessageFormat = "%level% %tag%[%datetime%] %message%";

    /**
     * If we're logging to a file, this is the path to the file to log to.
     *
     * @var string
     */
    private $logToFilePath;

    /**
     * True if we're currently setup to log to a screen, false if otherwise.
     *
     * @var bool
     */
    private $loggingToScreen;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        $logConfig = Config::getInstance()->log;
        $this->setCurrentLogLevelFromConfig($logConfig);
        $this->setLogWritersFromConfig($logConfig);
        $this->setLogFilePathFromConfig($logConfig);
        $this->setStringLogMessageFormat($logConfig);
        $this->disableLoggingBasedOnConfig($logConfig);
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

    private function setLogWritersFromConfig($logConfig)
    {
        // set the log writers
        $logWriters = $logConfig[self::LOG_WRITERS_CONFIG_OPTION];

        $logWriters = array_map('trim', $logWriters);
        foreach ($logWriters as $writerName) {
            $this->addLogWriter($writerName);
        }
    }

    public function addLogWriter($writerName)
    {
        if (array_key_exists($writerName, $this->writers)) {
            return;
        }

        $availableWritersByName = $this->getAvailableWriters();

        if (empty($availableWritersByName[$writerName])) {
            return;
        }

        $this->writers[$writerName] = $availableWritersByName[$writerName];
    }

    private function setCurrentLogLevelFromConfig($logConfig)
    {
        if (!empty($logConfig[self::LOG_LEVEL_CONFIG_OPTION])) {
            $logLevel = $this->getLogLevelFromStringName($logConfig[self::LOG_LEVEL_CONFIG_OPTION]);

            if ($logLevel >= self::NONE // sanity check
                && $logLevel <= self::VERBOSE
            ) {
                $this->setLogLevel($logLevel);
            }
        }
    }

    private function setStringLogMessageFormat($logConfig)
    {
        if (isset($logConfig['string_message_format'])) {
            $this->logMessageFormat = $logConfig['string_message_format'];
        }
    }

    private function setLogFilePathFromConfig($logConfig)
    {
        $logPath = $logConfig[self::LOGGER_FILE_PATH_CONFIG_OPTION];
        if (!SettingsServer::isWindows()
            && $logPath[0] != '/'
        ) {
            $logPath = PIWIK_USER_PATH . DIRECTORY_SEPARATOR . $logPath;
        }
        $logPath = SettingsPiwik::rewriteTmpPathWithInstanceId($logPath);
        if (is_dir($logPath)) {
            $logPath .= '/piwik.log';
        }
        $this->logToFilePath = $logPath;
    }

    private function getAvailableWriters()
    {
        $writers = array();

        /**
         * This event is called when the Log instance is created. Plugins can use this event to
         * make new logging writers available.
         *
         * A logging writer is a callback with the following signature:
         *
         *     function (int $level, string $tag, string $datetime, string $message)
         *
         * `$level` is the log level to use, `$tag` is the log tag used, `$datetime` is the date time
         * of the logging call and `$message` is the formatted log message.
         *
         * Logging writers must be associated by name in the array passed to event handlers. The
         * name specified can be used in Piwik's INI configuration.
         *
         * **Example**
         *
         *     public function getAvailableWriters(&$writers) {
         *         $writers['myloggername'] = function ($level, $tag, $datetime, $message) {
         *             // ...
         *         };
         *     }
         *
         *     // 'myloggername' can now be used in the log_writers config option.
         *
         * @param array $writers Array mapping writer names with logging writers.
         */
        Piwik::postEvent(self::GET_AVAILABLE_WRITERS_EVENT, array(&$writers));

        $writers['file'] = array($this, 'logToFile');
        $writers['screen'] = array($this, 'logToScreen');
        $writers['database'] = array($this, 'logToDatabase');
        return $writers;
    }

    public function setLogLevel($logLevel)
    {
        $this->currentLogLevel = $logLevel;
    }

    public function getLogLevel()
    {
        return $this->currentLogLevel;
    }

    private function logToFile($level, $tag, $datetime, $message)
    {
        $message = $this->getMessageFormattedFile($level, $tag, $datetime, $message);
        if (empty($message)) {
            return;
        }

        if (!@file_put_contents($this->logToFilePath, $message, FILE_APPEND)
            && !defined('PIWIK_TEST_MODE')
        ) {
            $message = Filechecks::getErrorMessageMissingPermissions($this->logToFilePath);
            throw new \Exception($message);
        }
    }

    private function logToScreen($level, $tag, $datetime, $message)
    {
        $message = $this->getMessageFormattedScreen($level, $tag, $datetime, $message);
        if (empty($message)) {
            return;
        }

        echo $message;
    }

    private function logToDatabase($level, $tag, $datetime, $message)
    {
        $message = $this->getMessageFormattedDatabase($level, $tag, $datetime, $message);
        if (empty($message)) {
            return;
        }

        $sql = "INSERT INTO " . Common::prefixTable('logger_message')
            . " (tag, timestamp, level, message)"
            . " VALUES (?, ?, ?, ?)";
        Db::query($sql, array($tag, $datetime, self::getStringLevel($level), (string)$message));
    }

    private function doLog($level, $message, $sprintfParams = array())
    {
        if (!$this->shouldLoggerLog($level)) {
            return;
        }

        $datetime = date("Y-m-d H:i:s");
        if (is_string($message)
            && !empty($sprintfParams)
        ) {
            // handle array sprintf parameters
            foreach ($sprintfParams as &$param) {
                if (is_array($param)) {
                    $param = json_encode($param);
                }
            }

            $message = vsprintf($message, $sprintfParams);
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
            call_user_func($writer, $level, $tag, $datetime, $message);
        }

        if ($level == self::ERROR) {
            $message = $this->getMessageFormattedScreen($level, $tag, $datetime, $message);
            $this->writeErrorToStandardErrorOutput($message);
            if(!isset($this->writers['screen'])) {
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

    private function disableLoggingBasedOnConfig($logConfig)
    {
        $disableLogging = false;

        if (!empty($logConfig['log_only_when_cli'])
            && !Common::isPhpCliMode()
        ) {
            $disableLogging = true;
        }

        if (!empty($logConfig['log_only_when_debug_parameter'])
            && !isset($_REQUEST['debug'])
        ) {
            $disableLogging = true;
        }

        if ($disableLogging) {
            $this->currentLogLevel = self::NONE;
        }
    }

    private function getLogLevelFromStringName($name)
    {
        $name = strtoupper($name);
        switch ($name) {
            case 'NONE':
                return self::NONE;
            case 'ERROR':
                return self::ERROR;
            case 'WARN':
                return self::WARN;
            case 'INFO':
                return self::INFO;
            case 'DEBUG':
                return self::DEBUG;
            case 'VERBOSE':
                return self::VERBOSE;
            default:
                return -1;
        }
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
     * @param $level
     * @param $tag
     * @param $datetime
     * @param $message
     * @return string
     */
    private function getMessageFormattedScreen($level, $tag, $datetime, $message)
    {
        static $currentRequestKey;
        if (empty($currentRequestKey)) {
            $currentRequestKey = substr(Common::generateUniqId(), 0, 5);
        }

        if (is_string($message)) {
            if (!defined('PIWIK_TEST_MODE')) {
                $message = '[' . $currentRequestKey . '] ' . $message;
            }
            $message = $this->formatMessage($level, $tag, $datetime, $message);

            if (!Common::isPhpCliMode()) {
                $message = Common::sanitizeInputValue($message);
                $message = '<pre>' . $message . '</pre>';
            }
        } else {
            $logger = $this;

            /**
             * Triggered when trying to log an object to the screen. Plugins can use
             * this event to convert objects to strings before they are logged.
             *
             * The result of this callback can be HTML so no sanitization is done on the result.
             * This means **YOU MUST SANITIZE THE MESSAGE YOURSELF** if you use this event.
             *
             * **Example**
             *
             *     public function formatScreenMessage(&$message, $level, $tag, $datetime, $logger) {
             *         if ($message instanceof MyCustomDebugInfo) {
             *             $message = Common::sanitizeInputValue($message->formatForScreen());
             *         }
             *     }
             *
             * @param mixed &$message The object that is being logged. Event handlers should
             *                        check if the object is of a certain type and if it is,
             *                        set `$message` to the string that should be logged.
             * @param int $level The log level used with this log entry.
             * @param string $tag The current plugin that started logging (or if no plugin,
             *                    the current class).
             * @param string $datetime Datetime of the logging call.
             * @param Log $logger The Log singleton.
             */
            Piwik::postEvent(self::FORMAT_SCREEN_MESSAGE_EVENT, array(&$message, $level, $tag, $datetime, $logger));
        }
        $message = trim($message);
        return $message . "\n";
    }

    /**
     * @param $message
     */
    private function writeErrorToStandardErrorOutput($message)
    {
        if(defined('PIWIK_TEST_MODE')) {
            // do not log on stderr during tests (prevent display of errors in CI output)
            return;
        }
        $fe = fopen('php://stderr', 'w');
        fwrite($fe, $message);
    }

    /**
     * @param $level
     * @param $tag
     * @param $datetime
     * @param $message
     * @return string
     */
    private function getMessageFormattedDatabase($level, $tag, $datetime, $message)
    {
        if (is_string($message)) {
            $message = $this->formatMessage($level, $tag, $datetime, $message);
        } else {
            $logger = $this;

            /**
             * Triggered when trying to log an object to a database table. Plugins can use
             * this event to convert objects to strings before they are logged.
             *
             * **Example**
             *
             *     public function formatDatabaseMessage(&$message, $level, $tag, $datetime, $logger) {
             *         if ($message instanceof MyCustomDebugInfo) {
             *             $message = $message->formatForDatabase();
             *         }
             *     }
             *
             * @param mixed &$message The object that is being logged. Event handlers should
             *                        check if the object is of a certain type and if it is,
             *                        set `$message` to the string that should be logged.
             * @param int $level The log level used with this log entry.
             * @param string $tag The current plugin that started logging (or if no plugin,
             *                    the current class).
             * @param string $datetime Datetime of the logging call.
             * @param Log $logger The Log singleton.
             */
            Piwik::postEvent(self::FORMAT_DATABASE_MESSAGE_EVENT, array(&$message, $level, $tag, $datetime, $logger));
        }
        $message = trim($message);
        return $message;
    }

    /**
     * @param $level
     * @param $tag
     * @param $datetime
     * @param $message
     * @return string
     */
    private function getMessageFormattedFile($level, $tag, $datetime, $message)
    {
        if (is_string($message)) {
            $message = $this->formatMessage($level, $tag, $datetime, $message);
        } else {
            $logger = $this;

            /**
             * Triggered when trying to log an object to a file. Plugins can use
             * this event to convert objects to strings before they are logged.
             *
             * **Example**
             *
             *     public function formatFileMessage(&$message, $level, $tag, $datetime, $logger) {
             *         if ($message instanceof MyCustomDebugInfo) {
             *             $message = $message->formatForFile();
             *         }
             *     }
             *
             * @param mixed &$message The object that is being logged. Event handlers should
             *                        check if the object is of a certain type and if it is,
             *                        set `$message` to the string that should be logged.
             * @param int $level The log level used with this log entry.
             * @param string $tag The current plugin that started logging (or if no plugin,
             *                    the current class).
             * @param string $datetime Datetime of the logging call.
             * @param Log $logger The Log singleton.
             */
            Piwik::postEvent(self::FORMAT_FILE_MESSAGE_EVENT, array(&$message, $level, $tag, $datetime, $logger));
        }

        $message = trim($message);
        $message = str_replace("\n", "\n  ", $message);
        return $message . "\n";
    }
}
