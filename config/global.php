<?php

use Interop\Container\ContainerInterface;
use Monolog\Logger;
use Piwik\Common;
use Piwik\Log;
use Piwik\Log\Handler\StdErrHandler;

return array(

    'path.root' => PIWIK_USER_PATH,

    'path.tmp' => DI\factory(function (ContainerInterface $c) {
        $root = $c->get('path.root');

        // TODO remove that special case and instead have plugins override 'path.tmp' to add the instance id
        if ($c->has('old_config.General.instance_id')) {
            $instanceId = $c->get('old_config.General.instance_id');
            $instanceId = $instanceId ? '/' . $instanceId : '';
        } else {
            $instanceId = '';
        }

        return $root . '/tmp' . $instanceId;
    }),

    // Log
    'Psr\Log\LoggerInterface' => DI\object('Monolog\Logger')
        ->constructor('piwik', DI\link('log.handlers'), DI\link('log.processors')),
    'Piwik\Log' => DI\object()
        ->constructor(DI\link('Psr\Log\LoggerInterface')),
    'log.handlers' => DI\factory(function (ContainerInterface $c) {
        if ($c->has('old_config.log.log_writers')) {
            $writerNames = $c->get('old_config.log.log_writers');
        }
        if (empty($writerNames)) {
            return array();
        }

        $classes = array(
            'file'     => 'Piwik\Log\Handler\FileHandler',
            'screen'   => 'Piwik\Log\Handler\StdOutHandler',
            'database' => 'Piwik\Log\Handler\DatabaseHandler',
        );

        $writerNames = array_map('trim', $writerNames);
        $writers = array();
        foreach ($writerNames as $writerName) {
            if (isset($classes[$writerName])) {
                $class = $classes[$writerName];
                $writers[$writerName] = $c->get($class);
            }
        }

        // Always add the stderr handler
        $isLoggingToStdOut = isset($writers['screen']);
        $writers['stderr'] = new StdErrHandler($c->get('Piwik\Log\Formatter\ExceptionHtmlFormatter'), $isLoggingToStdOut);

        return array_values($writers);
    }),
    'log.processors' => array(
        DI\link('Piwik\Log\Processor\ClassNameProcessor'),
        DI\link('Piwik\Log\Processor\RequestIdProcessor'),
        DI\link('Piwik\Log\Processor\SprintfProcessor'),
        DI\link('Monolog\Processor\PsrLogMessageProcessor'),
    ),
    'Piwik\Log\Handler\FileHandler' => DI\object()
        ->constructor(DI\link('log.file.filename'), DI\link('log.level'))
        ->method('setFormatter', DI\link('Piwik\Log\Formatter\ExceptionTextFormatter')),
    'Piwik\Log\Handler\DatabaseHandler' => DI\object()
        ->constructor(DI\link('log.level'))
        ->method('setFormatter', DI\link('Piwik\Log\Formatter\ExceptionTextFormatter')),
    'Piwik\Log\Handler\StdOutHandler' => DI\object()
        ->constructor(DI\link('log.level'))
        ->method('setFormatter', DI\link('Piwik\Log\Formatter\ExceptionHtmlFormatter')),
    'log.level' => DI\factory(function (ContainerInterface $c) {
        if ($c->get('log.disabled')) {
            return Log::getMonologLevel(Log::NONE);
        }
        if ($c->has('old_config.log.log_level')) {
            $level = strtoupper($c->get('old_config.log.log_level'));
            if (!empty($level) && defined('Piwik\Log::'.strtoupper($level))) {
                return Log::getMonologLevel(constant('Piwik\Log::'.strtoupper($level)));
            }
        }
        return Logger::WARNING;
    }),
    'log.disabled' => DI\factory(function (ContainerInterface $c) {
        $logOnlyCli = $c->has('old_config.log.log_only_when_cli') ? $c->get('old_config.log.log_only_when_cli') : false;
        if ($logOnlyCli && !Common::isPhpCliMode()) {
            return true;
        }
        $logOnlyWhenDebugParameter = $c->has('old_config.log.log_only_when_debug_parameter') ? $c->get('old_config.log.log_only_when_debug_parameter') : false;
        if ($logOnlyWhenDebugParameter && !isset($_REQUEST['debug'])) {
            return true;
        }
        return false;
    }),
    'log.file.filename' => DI\factory(function (ContainerInterface $c) {
        $logPath = $c->get('old_config.log.logger_file_path');

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
    'Piwik\Log\Formatter\ExceptionTextFormatter' => DI\object()
        ->constructor(DI\link('Piwik\Log\Formatter\LineMessageFormatter')),
    'Piwik\Log\Formatter\ExceptionHtmlFormatter' => DI\object()
        ->constructor(DI\link('Piwik\Log\Formatter\LineMessageFormatter')),
    'Piwik\Log\Formatter\LineMessageFormatter' => DI\object()
        ->constructor(DI\link('log.format')),
    'log.format' => DI\factory(function (ContainerInterface $c) {
        if ($c->has('old_config.log.string_message_format')) {
            return $c->get('old_config.log.string_message_format');
        }
        return '%level% %tag%[%datetime%] %message%';
    }),

);
