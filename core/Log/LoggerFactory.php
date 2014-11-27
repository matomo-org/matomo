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

class LoggerFactory
{
    /**
     * @param ContainerInterface $container
     * @return Log
     */
    public static function createLogger(ContainerInterface $container)
    {
        $logConfig = Config::getInstance()->log;

        $logFilePath = self::getLogFilePath($logConfig, $container);

        $logger = new Log($container->get('log.format'), $logFilePath);

        self::setCurrentLogLevelFromConfig($logger, $logConfig);
        self::setLogWritersFromConfig($logger, $logConfig);
        self::disableLoggingBasedOnConfig($logger, $logConfig);

        return $logger;
    }

    private static function setCurrentLogLevelFromConfig(Log $logger, $logConfig)
    {
        if (!empty($logConfig[Log::LOG_LEVEL_CONFIG_OPTION])) {
            $logLevel = self::getLogLevelFromStringName($logConfig[Log::LOG_LEVEL_CONFIG_OPTION]);

            if ($logLevel >= Log::NONE // sanity check
                && $logLevel <= Log::VERBOSE
            ) {
                $logger->setLogLevel($logLevel);
            }
        }
    }

    private static function setLogWritersFromConfig(Log $logger, $logConfig)
    {
        // set the log writers
        $logWriters = @$logConfig[Log::LOG_WRITERS_CONFIG_OPTION];
        if (empty($logWriters)) {
            return;
        }

        $logWriters = array_map('trim', $logWriters);
        foreach ($logWriters as $writerName) {
            $logger->addLogWriter($writerName);
        }
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
            return self::getDefaultFileLogPath();
        }

        $logPath = $container->get('path.tmp') . $logPath;
        if (is_dir($logPath)) {
            $logPath .= '/piwik.log';
        }

        return $logPath;
    }

    private static function disableLoggingBasedOnConfig(Log $logger, $logConfig)
    {
        $disableLogging = false;

        if (!empty($logConfig['log_only_when_cli']) && !Common::isPhpCliMode()) {
            $disableLogging = true;
        }

        if (!empty($logConfig['log_only_when_debug_parameter']) && !isset($_REQUEST['debug'])) {
            $disableLogging = true;
        }

        if ($disableLogging) {
            $logger->setLogLevel(Log::NONE);
        }
    }

    private static function getDefaultFileLogPath()
    {
        return '/logs/piwik.log';
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
}
