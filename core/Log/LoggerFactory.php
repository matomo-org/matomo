<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log;

use Interop\Container\ContainerInterface;
use Piwik\Config;
use Piwik\Log;
use Piwik\Log\Backend\StdErrBackend;

class LoggerFactory
{
    /**
     * @param ContainerInterface $container
     * @return Log
     */
    public static function createLogger(ContainerInterface $container)
    {
        $logConfig = Config::getInstance()->log;

        $logLevel = $container->get('log.level.piwik');
        $writers = self::getLogWriters($logConfig, $container);
        $processors = $container->get('log.processors');

        return new Log($writers, $logLevel, $processors);
    }

    private static function getLogWriters($logConfig, ContainerInterface $container)
    {
        $writerNames = @$logConfig[Log::LOG_WRITERS_CONFIG_OPTION];

        if (empty($writerNames)) {
            return array();
        }

        $classes = array(
            'file'     => 'Piwik\Log\Backend\FileBackend',
            'screen'   => 'Piwik\Log\Backend\StdOutBackend',
            'database' => 'Piwik\Log\Backend\DatabaseBackend',
        );

        $writerNames = array_map('trim', $writerNames);
        $writers = array();

        foreach ($writerNames as $writerName) {
            if (isset($classes[$writerName])) {
                $class = $classes[$writerName];
                $writers[$writerName] = $container->get($class);
            }
        }

        // Always add the stderr backend
        $isLoggingToStdOut = isset($writers['screen']);
        $writers['stderr'] = new StdErrBackend($container->get('log.formatter.html'), $isLoggingToStdOut);

        return $writers;
    }
}
