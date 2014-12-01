<?php

use Interop\Container\ContainerInterface;
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
    'Piwik\Log' => DI\factory(array('Piwik\Log\LoggerFactory', 'createLogger')),
    'log.processors' => array(
        DI\link('Piwik\Log\Processor\SprintfProcessor'),
    ),
    'Piwik\Log\Backend\FileBackend' => DI\object()
        ->constructor(DI\link('log.formatter.text'), DI\link('log.file.filename')),
    'Piwik\Log\Backend\DatabaseBackend' => DI\object()
        ->constructor(DI\link('log.formatter.text')),
    'Piwik\Log\Backend\StdOutBackend' => DI\object()
        ->constructor(DI\link('log.formatter.html')),
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
