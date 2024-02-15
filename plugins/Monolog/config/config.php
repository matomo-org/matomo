<?php

use Piwik\Container\Container;
use Piwik\Log\Logger;
use Piwik\Log;
use Piwik\Plugins\Monolog\Handler\FileHandler;
use Piwik\Plugins\Monolog\Handler\LogCaptureHandler;

return array(

    Logger::class => Piwik\DI::create(Logger::class)
        ->constructor('piwik', Piwik\DI::get('log.handlers'), Piwik\DI::get('log.processors')),

    Log\LoggerInterface::class => Piwik\DI::get(Logger::class),

    // For BC reasons
    'Monolog\Logger' =>  Piwik\DI::get(Logger::class),
    'Psr\Log\LoggerInterface' => Piwik\DI::get(Log\LoggerInterface::class),

    'log.handler.classes' => array(
        'file'     => 'Piwik\Plugins\Monolog\Handler\FileHandler',
        'screen'   => 'Piwik\Plugins\Monolog\Handler\WebNotificationHandler',
        'database' => 'Piwik\Plugins\Monolog\Handler\DatabaseHandler',
        'errorlog' => '\Monolog\Handler\ErrorLogHandler',
        'syslog' => '\Monolog\Handler\SyslogHandler',
    ),
    'log.handlers' => Piwik\DI::factory(function (Container $c) {
        if ($c->has('ini.log.log_writers')) {
            $writerNames = $c->get('ini.log.log_writers');
        } else {
            return array();
        }

        $classes = $c->get('log.handler.classes');

        $logConfig = $c->get(\Piwik\Config::class)->log;
        $enableFingersCrossed = isset($logConfig['enable_fingers_crossed_handler']) && $logConfig['enable_fingers_crossed_handler'] == 1;
        $fingersCrossedStopBuffering = isset($logConfig['fingers_crossed_stop_buffering_on_activation']) && $logConfig['fingers_crossed_stop_buffering_on_activation'] == 1;
        $enableLogCaptureHandler = isset($logConfig['enable_log_capture_handler']) && $logConfig['enable_log_capture_handler'] == 1;

        $isLogBufferingAllowed = !\Piwik\Common::isPhpCliMode()
            || \Piwik\SettingsServer::isArchivePhpTriggered()
            || \Piwik\CliMulti::isCliMultiRequest();

        $writerNames = array_map('trim', $writerNames);

        $writers = [];
        foreach ($writerNames as $writerName) {
            if ($writerName === 'screen'
                && \Piwik\Common::isPhpCliMode()
                && !defined('PIWIK_TEST_MODE')
                && !\Piwik\SettingsServer::isTrackerApiRequest()
            ) {
                continue; // screen writer is only valid for web requests (except for tracker CLI requests)
            }

            if (isset($classes[$writerName])) {
                // wrap the handler in FingersCrossedHandler if we can and this isn't the screen handler

                /** @var \Monolog\Handler\HandlerInterface $handler */
                $handler = $c->make($classes[$writerName]);
                if ($enableFingersCrossed
                    && $writerName !== 'screen'
                    && $handler instanceof \Monolog\Handler\AbstractHandler
                    && $isLogBufferingAllowed
                ) {
                    $passthruLevel = $handler->getLevel();

                    $handler->setLevel(Logger::DEBUG);

                    $handler = new \Monolog\Handler\FingersCrossedHandler(
                        $handler,
                        $activationStrategy = null,
                        $bufferSize = 0,
                        $bubble = true,
                        $fingersCrossedStopBuffering,
                        $passthruLevel
                    );
                }

                $writers[$writerName] = $handler;
            }
        }

        if ($enableLogCaptureHandler
            && $isLogBufferingAllowed
        ) {
            $writers[] = $c->get(LogCaptureHandler::class);
        }

        // we always add the null handler to make sure there is at least one handler specified. otherwise Monolog will
        // add a stream handler to stderr w/ a DEBUG log level, which will cause archiving requests to fail.
        if (empty($writers)) {
            $writers[] = $c->get(\Monolog\Handler\NullHandler::class);
        }

        return array_values($writers);
    }),

    'log.processors' => array(
        Piwik\DI::get('Piwik\Plugins\Monolog\Processor\SprintfProcessor'),
        Piwik\DI::get('Piwik\Plugins\Monolog\Processor\ClassNameProcessor'),
        Piwik\DI::get('Piwik\Plugins\Monolog\Processor\RequestIdProcessor'),
        Piwik\DI::get('Piwik\Plugins\Monolog\Processor\ExceptionToTextProcessor'),
        Piwik\DI::get('Monolog\Processor\PsrLogMessageProcessor'),
        Piwik\DI::get('Piwik\Plugins\Monolog\Processor\TokenProcessor'),
    ),

    'Piwik\Plugins\Monolog\Handler\FileHandler' => Piwik\DI::create()
        ->constructor(Piwik\DI::get('log.file.filename'), Piwik\DI::get('log.level.file'))
        ->method('setFormatter', Piwik\DI::get('log.lineMessageFormatter.file')),

    '\Monolog\Handler\ErrorLogHandler' => Piwik\DI::autowire()
        ->constructorParameter('level', Piwik\DI::get('log.level.errorlog'))
        ->method('setFormatter', Piwik\DI::get('log.lineMessageFormatter.file')),

    '\Monolog\Handler\SyslogHandler' => Piwik\DI::autowire()
        ->constructorParameter('ident', Piwik\DI::get('log.syslog.ident'))
        ->constructorParameter('level', Piwik\DI::get('log.level.syslog'))
        ->method('setFormatter', Piwik\DI::get('log.lineMessageFormatter.file')),

    'Piwik\Plugins\Monolog\Handler\DatabaseHandler' => Piwik\DI::create()
        ->constructor(Piwik\DI::get('log.level.database'))
        ->method('setFormatter', Piwik\DI::get('log.lineMessageFormatter')),

    'Piwik\Plugins\Monolog\Handler\WebNotificationHandler' => Piwik\DI::create()
        ->constructor(Piwik\DI::get('log.level.screen'))
        ->method('setFormatter', Piwik\DI::get('log.lineMessageFormatter')),

    'log.level' => Piwik\DI::factory(function (Container $c) {
        if ($c->has('ini.log.log_level')) {
            $level = strtoupper($c->get('ini.log.log_level'));
            if (!empty($level) && defined('Piwik\Log::' . strtoupper($level))) {
                return Log::getMonologLevel(constant('Piwik\Log::' . strtoupper($level)));
            }
        }

        return Logger::WARNING;
    }),

    'log.level.file' => Piwik\DI::factory(function (Container $c) {
        if ($c->has('ini.log.log_level_file')) {
            $level = Log::getMonologLevelIfValid($c->get('ini.log.log_level_file'));
            if ($level !== null) {
                return $level;
            }
        }
        return $c->get('log.level');
    }),

    'log.level.screen' => Piwik\DI::factory(function (Container $c) {
        if ($c->has('ini.log.log_level_screen')) {
            $level = Log::getMonologLevelIfValid($c->get('ini.log.log_level_screen'));
            if ($level !== null) {
                return $level;
            }
        }
        return $c->get('log.level');
    }),

    'log.level.database' => Piwik\DI::factory(function (Container $c) {
        if ($c->has('ini.log.log_level_database')) {
            $level = Log::getMonologLevelIfValid($c->get('ini.log.log_level_database'));
            if ($level !== null) {
                return $level;
            }
        }
        return $c->get('log.level');
    }),

    'log.level.syslog' => Piwik\DI::factory(function (Container $c) {
        if ($c->has('ini.log.log_level_syslog')) {
            $level = Log::getMonologLevelIfValid($c->get('ini.log.log_level_syslog'));
            if ($level !== null) {
                return $level;
            }
        }
        return $c->get('log.level');
    }),

    'log.level.errorlog' => Piwik\DI::factory(function (Container $c) {
        if ($c->has('ini.log.log_level_errorlog')) {
            $level = Log::getMonologLevelIfValid($c->get('ini.log.log_level_errorlog'));
            if ($level !== null) {
                return $level;
            }
        }
        return $c->get('log.level');
    }),

    'log.file.filename' => Piwik\DI::factory(function (Container $c) {
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

    'log.syslog.ident' => Piwik\DI::factory(function (Container $c) {
        $ident = $c->get('ini.log.logger_syslog_ident');
        if (empty($ident)) {
            $ident = 'matomo';
        }
        return $ident;
    }),

    'Piwik\Plugins\Monolog\Formatter\LineMessageFormatter' => Piwik\DI::create('Piwik\Plugins\Monolog\Formatter\LineMessageFormatter')
                                                                ->constructor(Piwik\DI::get('log.short.format')),
    'log.lineMessageFormatter' => Piwik\DI::create('Piwik\Plugins\Monolog\Formatter\LineMessageFormatter')
        ->constructor(Piwik\DI::get('log.short.format')),

    'log.lineMessageFormatter.file' => Piwik\DI::autowire('Piwik\Plugins\Monolog\Formatter\LineMessageFormatter')
        ->constructor(Piwik\DI::get('log.trace.format'))
        ->constructorParameter('allowInlineLineBreaks', false),

    'log.short.format' => Piwik\DI::factory(function (Container $c) {
        if ($c->has('ini.log.string_message_format')) {
            return $c->get('ini.log.string_message_format');
        }
        return '%level% %tag%[%datetime%] %message%';
    }),

    'log.trace.format' => Piwik\DI::factory(function (Container $c) {
        if ($c->has('ini.log.string_message_format_trace')) {
            return $c->get('ini.log.string_message_format_trace');
        }
        return '%level% %tag%[%datetime%] %message% %trace%';
    }),

    'archiving.performance.handlers' => function (Container $c) {
        $logFile = trim($c->get('ini.Debug.archive_profiling_log'));
        if (empty($logFile)) {
            return [new \Monolog\Handler\NullHandler()];
        }

        $fileHandler = new FileHandler($logFile, Logger::INFO);
        $fileHandler->setFormatter($c->get('log.lineMessageFormatter.file'));
        return [$fileHandler];
    },

    'archiving.performance.logger' => Piwik\DI::create(Logger::class)
        ->constructor('matomo.archiving.performance', Piwik\DI::get('archiving.performance.handlers'), Piwik\DI::get('log.processors')),
);
