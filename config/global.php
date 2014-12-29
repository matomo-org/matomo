<?php

use Interop\Container\ContainerInterface;
use Monolog\Logger;
use Piwik\Log;
use Piwik\Cache\Eager;
use Piwik\SettingsServer;

return array(

    'path.root' => PIWIK_USER_PATH,

    'path.tmp' => DI\factory(function (ContainerInterface $c) {
        $root = $c->get('path.root');

        // TODO remove that special case and instead have plugins override 'path.tmp' to add the instance id
        if ($c->has('ini.General.instance_id')) {
            $instanceId = $c->get('ini.General.instance_id');
            $instanceId = $instanceId ? '/' . $instanceId : '';
        } else {
            $instanceId = '';
        }

        return $root . '/tmp' . $instanceId;
    }),

    'path.cache' => DI\factory(function (ContainerInterface $c) {
        $root = $c->get('path.tmp');

        return $root . '/cache/tracker/';
    }),

    'cache.backend' => DI\factory(function (ContainerInterface $c) {
        if (defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE) { // todo replace this with isTest() instead of isCli()
            $backend = 'file';
        } elseif (\Piwik\Development::isEnabled()) {
            $backend = 'null';
        } else {
            $backend = $c->get('ini.Cache.backend');
        }

        return $backend;
    }),
    'Piwik\Cache\Lazy' => DI\object(),
    'Piwik\Cache\Transient' => DI\object(),
    'Piwik\Cache\Eager' => DI\factory(function (ContainerInterface $c) {

        $backend = $c->get('Piwik\Cache\Backend');

        if (defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE) {
            $cacheId = 'eagercache-test-';
        } else {
            $cacheId = 'eagercache-' . str_replace(array('.', '-'), '', \Piwik\Version::VERSION) . '-';
        }

        if (SettingsServer::isTrackerApiRequest()) {
            $eventToPersist = 'Tracker.end';
            $cacheId .= 'tracker';
        } else {
            $eventToPersist = 'Request.dispatch.end';
            $cacheId .= 'ui';
        }

        $cache = new Eager($backend, $cacheId);
        \Piwik\Piwik::addAction($eventToPersist, function () use ($cache) {
            $cache->persistCacheIfNeeded(43200);
        });

        return $cache;
    }),
    'Piwik\Cache\Backend' => DI\factory(function (ContainerInterface $c) {

        $type    = $c->get('cache.backend');
        $backend = \Piwik\Cache::buildBackend($type);

        return $backend;
    }),

    // Log
    'Psr\Log\LoggerInterface' => DI\object('Monolog\Logger')
        ->constructor('piwik', DI\link('log.handlers'), DI\link('log.processors')),
    'log.handlers' => DI\factory(function (ContainerInterface $c) {
        if ($c->has('ini.log.log_writers')) {
            $writerNames = $c->get('ini.log.log_writers');
        } else {
            return array();
        }
        $classes = array(
            'file'     => 'Piwik\Log\Handler\FileHandler',
            'screen'   => 'Piwik\Log\Handler\WebNotificationHandler',
            'database' => 'Piwik\Log\Handler\DatabaseHandler',
        );
        $writerNames = array_map('trim', $writerNames);
        $writers = array();
        foreach ($writerNames as $writerName) {
            if (isset($classes[$writerName])) {
                $writers[$writerName] = $c->get($classes[$writerName]);
            }
        }
        return array_values($writers);
    }),
    'log.processors' => array(
        DI\link('Piwik\Log\Processor\ClassNameProcessor'),
        DI\link('Piwik\Log\Processor\RequestIdProcessor'),
        DI\link('Piwik\Log\Processor\ExceptionToTextProcessor'),
        DI\link('Piwik\Log\Processor\SprintfProcessor'),
        DI\link('Monolog\Processor\PsrLogMessageProcessor'),
    ),
    'Piwik\Log\Handler\FileHandler' => DI\object()
        ->constructor(DI\link('log.file.filename'), DI\link('log.level'))
        ->method('setFormatter', DI\link('Piwik\Log\Formatter\LineMessageFormatter')),
    'Piwik\Log\Handler\DatabaseHandler' => DI\object()
        ->constructor(DI\link('log.level'))
        ->method('setFormatter', DI\link('Piwik\Log\Formatter\LineMessageFormatter')),
    'Piwik\Log\Handler\WebNotificationHandler' => DI\object()
        ->constructor(DI\link('log.level'))
        ->method('setFormatter', DI\link('Piwik\Log\Formatter\LineMessageFormatter')),
    'log.level' => DI\factory(function (ContainerInterface $c) {
        if ($c->has('ini.log.log_level')) {
            $level = strtoupper($c->get('ini.log.log_level'));
            if (!empty($level) && defined('Piwik\Log::'.strtoupper($level))) {
                return Log::getMonologLevel(constant('Piwik\Log::'.strtoupper($level)));
            }
        }
        return Logger::WARNING;
    }),
    'log.file.filename' => DI\factory(function (ContainerInterface $c) {
        $logPath = $c->get('ini.log.logger_file_path');

        // Absolute path
        if (strpos($logPath, '/') === 0) {
            return $logPath;
        }

        // Remove 'tmp/' at the beginning
        if (strpos($logPath, 'tmp/') === 0) {
            $logPath = substr($logPath, strlen('tmp'));
        }

        if (empty($logPath)) {
            // Default log file
            $logPath = '/logs/piwik.log';
        }

        $logPath = $c->get('path.tmp') . $logPath;
        if (is_dir($logPath)) {
            $logPath .= '/piwik.log';
        }

        return $logPath;
    }),
    'Piwik\Log\Formatter\LineMessageFormatter' => DI\object()
        ->constructor(DI\link('log.format')),
    'log.format' => DI\factory(function (ContainerInterface $c) {
        if ($c->has('ini.log.string_message_format')) {
            return $c->get('ini.log.string_message_format');
        }
        return '%level% %tag%[%datetime%] %message%';
    }),

    'Piwik\Translation\Loader\LoaderInterface' => DI\object('Piwik\Translation\Loader\LoaderCache')
        ->constructor(DI\link('Piwik\Translation\Loader\JsonFileLoader')),

);
