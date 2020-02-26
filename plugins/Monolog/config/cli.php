<?php

use Interop\Container\ContainerInterface;
use Monolog\Logger;
use Piwik\Plugins\Monolog\Handler\EchoHandler;
use Piwik\Plugins\Monolog\Handler\FailureLogMessageDetector;
use Symfony\Bridge\Monolog\Formatter\ConsoleFormatter;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Output\OutputInterface;

return array(

    // Log
    'log.handlers' => function (ContainerInterface $c) {
        $result = [];

        $logConfig = $c->get(\Piwik\Config::class)->log;
        $enableFingersCrossed = isset($logConfig['enable_fingers_crossed_handler_cli']) && $logConfig['enable_fingers_crossed_handler_cli'] == 1;
        if ($enableFingersCrossed) {
            $handler = new EchoHandler();
            $handler->setLevel(Logger::DEBUG);

            $passthruLevel = Logger::WARNING;

            $handler = new \Monolog\Handler\FingersCrossedHandler($handler, $activationStrategy = null, $bufferSize = 0,
                $bubble = true, false, $passthruLevel);

            $result[] = $handler;
        }

        $result[] = $c->get(FailureLogMessageDetector::class);
        $result[] = $c->get(ConsoleHandler::class);

        return $result;
    },
    'Symfony\Bridge\Monolog\Handler\ConsoleHandler' => function (ContainerInterface $c) {
        // Override the default verbosity map to make it more verbose by default
        $verbosityMap = array(
            OutputInterface::VERBOSITY_NORMAL => Logger::INFO,
            OutputInterface::VERBOSITY_VERBOSE => Logger::DEBUG,
            OutputInterface::VERBOSITY_VERY_VERBOSE => Logger::DEBUG,
            OutputInterface::VERBOSITY_DEBUG => Logger::DEBUG,
        );
        $handler = new ConsoleHandler(null, true, $verbosityMap);
        $handler->setFormatter(new ConsoleFormatter($c->get('log.console.format'), null, true, true));
        return $handler;
    },
    'log.console.format' => '%start_tag%%level_name% [%datetime%] %extra.request_id% %end_tag% %message%' . PHP_EOL,

);
