<?php

use Piwik\Container\Container;
use Piwik\Log\Logger;
use Piwik\Plugins\Monolog\Handler\FailureLogMessageDetector;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Output\OutputInterface;

return array(

    // Log
    'log.handlers' => Piwik\DI::factory(function (Container $c) {
        $writers = [];
        $writers[] = $c->get(FailureLogMessageDetector::class);
        $writers[] = $c->get('Symfony\Bridge\Monolog\Handler\ConsoleHandler');
        if ($c->has('ini.log.log_writers')) {
            $writerNames = $c->get('ini.log.log_writers');
            if (in_array('file', $writerNames)) {
                $writers[] = $c->get('Piwik\Plugins\Monolog\Handler\FileHandler');
            }
        }
        return $writers;
    }),

    'Symfony\Bridge\Monolog\Handler\ConsoleHandler' => function (Container $c) {
        // Override the default verbosity map to make it more verbose by default
        $verbosityMap = array(
            OutputInterface::VERBOSITY_NORMAL => Logger::INFO,
            OutputInterface::VERBOSITY_VERBOSE => Logger::DEBUG,
            OutputInterface::VERBOSITY_VERY_VERBOSE => Logger::DEBUG,
            OutputInterface::VERBOSITY_DEBUG => Logger::DEBUG,
            OutputInterface::VERBOSITY_QUIET => Logger::ERROR,
        );
        $handler = new ConsoleHandler(null, true, $verbosityMap);
        $handler->setFormatter(new \Piwik\Plugins\Monolog\Formatter\ConsoleFormatter([
            'date_format' => 'Y-m-d H:i:s',
            'format' => $c->get('log.console.format'),
            'multiline' => true
        ]));
        return $handler;
    },

    'log.console.format' => '%start_tag%%level_name% [%datetime%] %extra.request_id% %end_tag% %message%' . PHP_EOL,

);
