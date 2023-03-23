<?php

use Matomo\Dependencies\Psr\Container\ContainerInterface;
use Matomo\Dependencies\Monolog\Logger;
use Piwik\Plugins\Monolog\Handler\FailureLogMessageDetector;
use Matomo\Dependencies\Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Matomo\Dependencies\Symfony\Component\Console\Output\OutputInterface;
use Matomo\Dependencies\DI;

return array(

    // Log
    'log.handlers' => DI\factory(function (DI\Container $c) {
        $writers = [];
        $writers[] = $c->get(FailureLogMessageDetector::class);
        $writers[] = $c->get('Matomo\Dependencies\Symfony\Bridge\Monolog\Handler\ConsoleHandler');
        if ($c->has('ini.log.log_writers')) {
            $writerNames = $c->get('ini.log.log_writers');
            if (in_array('file', $writerNames)) {
                $writers[] = $c->get('Piwik\Plugins\Monolog\Handler\FileHandler');
            }
        }
        return $writers;
    }),

    'Matomo\Dependencies\Symfony\Bridge\Monolog\Handler\ConsoleHandler' => function (ContainerInterface $c) {
        // Override the default verbosity map to make it more verbose by default
        $verbosityMap = array(
            OutputInterface::VERBOSITY_NORMAL => Logger::INFO,
            OutputInterface::VERBOSITY_VERBOSE => Logger::DEBUG,
            OutputInterface::VERBOSITY_VERY_VERBOSE => Logger::DEBUG,
            OutputInterface::VERBOSITY_DEBUG => Logger::DEBUG,
        );
        $handler = new ConsoleHandler(null, true, $verbosityMap);
        $handler->setFormatter(new \Piwik\Plugins\Monolog\Formatter\ConsoleFormatter([
            'format' => $c->get('log.console.format'),
            'multiline' => true
        ]));
        return $handler;
    },

    'log.console.format' => '%start_tag%%level_name% [%datetime%] %extra.request_id% %end_tag% %message%' . PHP_EOL,

);
