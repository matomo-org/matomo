<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log;

use Interop\Container\ContainerInterface;
use Piwik\Common;
use Piwik\Config;
use Piwik\Log;
use Piwik\Piwik;

class LoggerFactory
{
    const DEFAULT_LOG_FILE = '/logs/piwik.log';

    /**
     * @param ContainerInterface $container
     * @return Log
     */
    public static function createLogger(ContainerInterface $container)
    {
        $logConfig = Config::getInstance()->log;

        $messageFormat = $container->get('log.format');
        $logFilePath = self::getLogFilePath($logConfig, $container);
        $logLevel = self::getLogLevel($logConfig, $container);
        $writers = self::getLogWriters($logConfig, $messageFormat, $logFilePath);

        return new Log($writers, $messageFormat, $logLevel);
    }

    private static function getLogLevel($logConfig, ContainerInterface $container)
    {
        if (self::isLoggingDisabled($logConfig)) {
            return Log::NONE;
        }

        if ($container->has('old_config.log.log_level')) {
            $configLogLevel = self::getLogLevelFromStringName($container->get('old_config.log.log_level'));

            // sanity check
            if ($configLogLevel >= Log::NONE && $configLogLevel <= Log::VERBOSE) {
                return $configLogLevel;
            }
        }

        return Log::WARN;
    }

    private static function getLogWriters($logConfig, $messageFormat, $logFilePath)
    {
        $writerNames = @$logConfig[Log::LOG_WRITERS_CONFIG_OPTION];

        if (empty($writerNames)) {
            return array();
        }

        $availableWriters = self::getAvailableWriters($messageFormat, $logFilePath);

        $writerNames = array_map('trim', $writerNames);
        $writers = array();

        foreach ($writerNames as $writerName) {
            if (! isset($availableWriters[$writerName])) {
                continue;
            }

            $writers[$writerName] = $availableWriters[$writerName];
        }

        return $writers;
    }

    private static function getLogFilePath($logConfig, ContainerInterface $container)
    {
        $logPath = @$logConfig[Log::LOGGER_FILE_PATH_CONFIG_OPTION];

        // Absolute path
        if (strpos($logPath, '/') === 0) {
            return $logPath;
        }

        // Remove 'tmp/' at the beginning
        if (strpos($logPath, 'tmp/') === 0) {
            $logPath = substr($logPath, strlen('tmp'));
        }

        if (empty($logPath)) {
            $logPath = self::DEFAULT_LOG_FILE;
        }

        $logPath = $container->get('path.tmp') . $logPath;
        if (is_dir($logPath)) {
            $logPath .= '/piwik.log';
        }

        return $logPath;
    }

    private static function isLoggingDisabled($logConfig)
    {
        if (!empty($logConfig['log_only_when_cli']) && !Common::isPhpCliMode()) {
            return true;
        }

        if (!empty($logConfig['log_only_when_debug_parameter']) && !isset($_REQUEST['debug'])) {
            return true;
        }

        return false;
    }

    private static function getLogLevelFromStringName($name)
    {
        $name = strtoupper($name);
        switch ($name) {
            case 'NONE':
                return Log::NONE;
            case 'ERROR':
                return Log::ERROR;
            case 'WARN':
                return Log::WARN;
            case 'INFO':
                return Log::INFO;
            case 'DEBUG':
                return Log::DEBUG;
            case 'VERBOSE':
                return Log::VERBOSE;
            default:
                return -1;
        }
    }

    private static function getAvailableWriters($logMessageFormat, $logFilePath)
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
        Piwik::postEvent(Log::GET_AVAILABLE_WRITERS_EVENT, array(&$writers));

        $writers['file'] = new FileBackend($logMessageFormat, $logFilePath);
        $writers['screen'] = new ScreenBackend($logMessageFormat);
        $writers['database'] = new DatabaseBackend($logMessageFormat);

        return $writers;
    }
}
