<?php

use Interop\Container\ContainerInterface;
use Piwik\Common;
use Piwik\Log;
use Piwik\Log\Backend\StdErrBackend;
use Piwik\Log\Formatter\AddRequestIdFormatter;
use Piwik\Log\Formatter\ErrorHtmlFormatter;
use Piwik\Log\Formatter\ErrorTextFormatter;
use Piwik\Log\Formatter\ExceptionHtmlFormatter;
use Piwik\Log\Formatter\ExceptionTextFormatter;
use Piwik\Log\Formatter\HtmlPreFormatter;
use Piwik\Log\Formatter\LineMessageFormatter;

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
    'log.level.monolog' => DI\factory(function (ContainerInterface $c) {
        return Log::getMonologLevel($c->get('log.level.piwik'));
    }),
    'log.level.piwik' => DI\factory(function (ContainerInterface $c) {
        if ($c->get('log.disabled')) {
            return Log::NONE;
        }
        if ($c->has('old_config.log.log_level')) {
            $level = strtoupper($c->get('old_config.log.log_level'));
            if (!empty($level) && defined('Piwik\Log::'.strtoupper($level))) {
                return constant('Piwik\Log::'.strtoupper($level));
            }
        }
        return Log::WARN;
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
    'log.handlers' => DI\factory(function (ContainerInterface $c) {
        if ($c->has('old_config.log.log_writers')) {
            $writerNames = $c->get('old_config.log.log_writers');
        }
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
                $writers[$writerName] = $c->get($class);
            }
        }

        // Always add the stderr backend
        $isLoggingToStdOut = isset($writers['screen']);
        $writers['stderr'] = new StdErrBackend($c->get('log.formatter.html'), $isLoggingToStdOut);

        return array_values($writers);
    }),
    'log.processors' => array(
        DI\link('Piwik\Log\Processor\ClassNameProcessor'),
        DI\link('Piwik\Log\Processor\SprintfProcessor'),
    ),
    'Piwik\Log\Backend\FileBackend' => DI\object()
        ->constructor(DI\link('log.file.filename'), DI\link('log.level.monolog'))
        ->method('setFormatter', DI\link('log.formatter.text')),
    'Piwik\Log\Backend\DatabaseBackend' => DI\object()
        ->constructor(DI\link('log.level.monolog'))
        ->method('setFormatter', DI\link('log.formatter.text')),
    'Piwik\Log\Backend\StdOutBackend' => DI\object()
        ->constructor(DI\link('log.level.monolog'))
        ->method('setFormatter', DI\link('log.formatter.html')),
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
    'log.formatter.text' => DI\factory(function (ContainerInterface $c) {
        // Chain of responsibility pattern
        $lineFormatter = new LineMessageFormatter($c->get('log.format'));

        $exceptionFormatter = new ExceptionTextFormatter();
        $exceptionFormatter->setNext($lineFormatter);

        $errorFormatter = new ErrorTextFormatter();
        $errorFormatter->setNext($exceptionFormatter);

        return $errorFormatter;
    }),
    'log.formatter.html' => DI\factory(function (ContainerInterface $c) {
        // Chain of responsibility pattern
        $lineFormatter = new LineMessageFormatter($c->get('log.format'));

        $addRequestIdFormatter = new AddRequestIdFormatter();
        $addRequestIdFormatter->setNext($lineFormatter);

        $htmlPreFormatter = new HtmlPreFormatter();
        $htmlPreFormatter->setNext($addRequestIdFormatter);

        $exceptionFormatter = new ExceptionHtmlFormatter();
        $exceptionFormatter->setNext($htmlPreFormatter);

        $errorFormatter = new ErrorHtmlFormatter();
        $errorFormatter->setNext($exceptionFormatter);

        return $errorFormatter;
    }),
    'log.format' => DI\factory(function (ContainerInterface $c) {
        if ($c->has('old_config.log.string_message_format')) {
            return $c->get('old_config.log.string_message_format');
        }
        return '%level% %tag%[%datetime%] %message%';
    }),

);
