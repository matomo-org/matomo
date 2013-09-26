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
use Piwik\Db;

/**
 * TODO
 */
class Log
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

    /**
     * TODO
     */
    const FORMAT_FILE_MESSAGE_EVENT = 'Log.formatFileMessage';

    /**
     * TODO
     */
    const FORMAT_SCREEN_MESSAGE_EVENT = 'Log.formatScreenMessage';

    /**
     * TODO
     */
    const FORMAT_DATABASE_MESSAGE_EVENT = 'Log.formatDatabaseMessage';

    /**
     * TODO
     */
    private static $instance = null;

    /**
     * TODO
     */
    private static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Log();
        }
        return self::$instance;
    }

    /**
     * TODO
     */
    private $currentLogLevel = self::WARN;

    /**
     * TODO
     */
    private $writers = array();

    /**
     * TODO
     */
    private $logMessageFormat = "[%pluginName%:%datetime%] %message%";

    /**
     * TODO
     */
    private $logToFileFilename;

    /**
     * TODO
     */
    private $loggingToScreen;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $logConfig = Config::getInstance()->log;
        $this->setCurrentLogLevelFromConfig($logConfig);
        $this->setLogWritersFromConfig($logConfig);
        $this->setLogFilePathFromConfig($logConfig);
        $this->disableLoggingBasedOnConfig($logConfig);
    }

    private function setLogWritersFromConfig($logConfig)
    {
        // set the log writers
        $logWriters = $logConfig[self::LOG_WRITERS_CONFIG_OPTION];

        $logWriters = array_map('trim', $logWriters);
        foreach ($logWriters as $writerName) {
            $writer = $this->createWriterByName($writerName);
            if (!empty($writer)) {
                $this->writers[] = $writer;
            }

            if ($writer == 'screen') {
                $this->loggingToScreen = true;
            }
        }
    }

    private function setCurrentLogLevelFromConfig($logConfig)
    {
        if (!empty($logConfig[self::LOG_LEVEL_CONFIG_OPTION])) {
            $logLevel = $this->getLogLevelFromStringName(self::LOG_LEVEL_CONFIG_OPTION);

            if ($logLevel >= self::NONE // sanity check
                && $logLevel <= self::VERBOSE
            ) {
                $this->currentLogLevel = $logLevel;
            }
        }
    }

    private function setLogFilePathFromConfig($logConfig)
    {
        $logDir = $logConfig[self::LOGGER_FILE_PATH_CONFIG_OPTION];
        if ($logDir[0] != '/' && $logDir[0] != DIRECTORY_SEPARATOR) {
            $logDir = PIWIK_USER_PATH . '/' . $logDir;
        }
        $this->logToFileFilename = $logDir . '/piwik.log';
    }

    private function createWriterByName($writerName)
    {
        $writer = false;
        if ($writerName == 'file') {
            $writer = array($this, 'logToFile');
        } else if ($writerName == 'screen') {
            $writer = array($this, 'logToScreen');
        } else if ($writerName == 'db') {
            $writer = array($this, 'logToDatabase');
        }
        return $writer;
    }

    private function logToFile($level, $pluginName, $datetime, $message)
    {
        if (is_string($message)) {
            $message = $this->formatMessage($level, $pluginName, $datetime, $message);
        } else {
            Piwik_PostEvent(self::FORMAT_FILE_MESSAGE_EVENT, array(&$message, $level, $pluginName, $datetime, $this));
        }

        if (empty($message)) {
            return;
        }

        file_put_contents($this->logToFileFilename, $message . "\n", FILE_APPEND);
    }

    private function logToScreen($level, $pluginName, $datetime, $message)
    {
        if (is_string($message)) {
            $message = $this->formatMessage($level, $pluginName, $datetime, $message);
        } else {
            Piwik_PostEvent(self::FORMAT_SCREEN_MESSAGE_EVENT, array(&$message, $level, $pluginName, $datetime, $this));
        }

        if (empty($message)) {
            return;
        }

        echo $message . "\n";
    }

    private function logToDatabase($level, $pluginName, $datetime, $message)
    {
        if (is_string($message)) {
            $message = $this->formatMessage($level, $pluginName, $datetime, $message);
        } else {
            Piwik_PostEvent(self::FORMAT_DATABASE_MESSAGE_EVENT, array(&$message, $level, $pluginName, $datetime, $this));
        }

        if (empty($message)) {
            return;
        }

        // TODO: allow different columns
        $sql = "INSERT INTO " . Common::prefixTable('logger_message')
             . " (plugin, time, level, message)"
             . " VALUES (?, ?, ?, ?)";
        Db::query($sql, array($pluginName, $datetime, $level, (string)$message));
    }

    private function doLog($level, $message, $sprintfParams = array())
    {
        if ($this->shouldLoggerLog($level)) {
            $datetime = date("Y-m-d H:i:s");
            if (is_string($message)) {
                $message = vsprintf($message, $sprintfParams);
            }

            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $pluginName = Plugin::getPluginNameFromBacktrace($backtrace);

            $this->writeMessage($level, $pluginName, $datetime, $message);
        }
    }

    /**
     * TODO
     */
    public function formatMessage($level, $pluginName, $message, $datetime)
    {
        return str_replace(
            array("%pluginName%", "%message%", "%datetime%", "%level%"),
            array($pluginName, $message, $datetime, $this->getStringLevel($level)),
            $this->logMessageFormat
        );
    }

    /**
     * TODO
     */
    private function writeMessage($level, $pluginName, $datetime, $message)
    {
        foreach ($this->writers as $writer) {
            $writer($level, $pluginName, $datetime, $message);
        }

        // errors are always printed to screen
        if ($level == self::ERROR
            && !$this->loggingToScreen
        ) {
            $this->logToScreen($level, $pluginName, $datetime, $message);
        }
    }

    /**
     * TODO
     */
    private static function log($level, $message /* ... */)
    {
        self::getInstance()->doLog($level, $message, array_slice(func_get_args(), 2));
    }

    /**
     * TODO
     */
    public static function error($message /* ... */)
    {
        self::log(self::ERROR, $message, array_slice(func_get_args(), 2));
    }

    /**
     * TODO
     */
    public static function warning($message /* ... */)
    {
        self::log(self::WARN, $message, array_slice(func_get_args(), 2));
    }

    /**
     * TODO
     */
    public static function info($message /* ... */)
    {
        self::log(self::INFO, $message, array_slice(func_get_args(), 2));
    }

    /**
     * TODO
     */
    public static function debug($message /* ... */)
    {
        self::log(self::DEBUG, $message, array_slice(func_get_args(), 2));
    }

    /**
     * TODO
     */
    public static function verbose($message /* ... */)
    {
        self::log(self::VERBOSE, $message, array_slice(func_get_args(), 2));
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
        switch (strtoupper($name)) {
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
            self::NONE => 'NONE',
            self::ERROR => 'ERROR',
            self::WARN => 'WARN',
            self::INFO => 'INFO',
            self::DEBUG => 'DEBUG',
            self::VERBOSE => 'VERBOSE'
        );
        return $levelToName[$level];
    }
}