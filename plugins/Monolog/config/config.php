<?php

use Interop\Container\ContainerInterface;
use Monolog\Logger;
use Piwik\Log;

return array(

    'Monolog\Logger' => DI\object('Monolog\Logger')
        ->constructor('piwik', DI\get('log.handlers'), DI\get('log.processors')),

    'Psr\Log\LoggerInterface' => DI\get('Monolog\Logger'),

    'log.handler.classes' => array(
        'file'     => 'Piwik\Plugins\Monolog\Handler\FileHandler',
        'screen'   => 'Piwik\Plugins\Monolog\Handler\WebNotificationHandler',
        'database' => 'Piwik\Plugins\Monolog\Handler\DatabaseHandler',
    ),
    'log.handlers' => DI\factory(function (ContainerInterface $c) {
        if ($c->has('ini.log.log_writers')) {
            $writerNames = $c->get('ini.log.log_writers');
        } else {
            return array();
        }

        $classes = $c->get('log.handler.classes');

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
        DI\get('Piwik\Plugins\Monolog\Processor\SprintfProcessor'),
        DI\get('Piwik\Plugins\Monolog\Processor\ClassNameProcessor'),
        DI\get('Piwik\Plugins\Monolog\Processor\RequestIdProcessor'),
        DI\get('Piwik\Plugins\Monolog\Processor\ExceptionToTextProcessor'),
        DI\get('Monolog\Processor\PsrLogMessageProcessor'),
        DI\get('Piwik\Plugins\Monolog\Processor\TokenProcessor'),
    ),

    'Piwik\Plugins\Monolog\Handler\FileHandler' => DI\object()
        ->constructor(DI\get('log.file.filename'), DI\get('log.level'))
        ->method('setFormatter', DI\get('log.lineMessageFormatter.file')),

    'log.lineMessageFormatter.file' => DI\object('Piwik\Plugins\Monolog\Formatter\LineMessageFormatter')
        ->constructorParameter('allowInlineLineBreaks', false),

    'Piwik\Plugins\Monolog\Handler\DatabaseHandler' => DI\object()
        ->constructor(DI\get('log.level'))
        ->method('setFormatter', DI\get('Piwik\Plugins\Monolog\Formatter\LineMessageFormatter')),

    'Piwik\Plugins\Monolog\Handler\WebNotificationHandler' => DI\object()
        ->constructor(DI\get('log.level'))
        ->method('setFormatter', DI\get('Piwik\Plugins\Monolog\Formatter\LineMessageFormatter')),

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

    'Piwik\Plugins\Monolog\Formatter\LineMessageFormatter' => DI\object()
        ->constructor(DI\get('log.format')),

    'log.format' => DI\factory(function (ContainerInterface $c) {
        if ($c->has('ini.log.string_message_format')) {
            return $c->get('ini.log.string_message_format');
        }
        return '%level% %tag%[%datetime%] %message%';
    }),

);
