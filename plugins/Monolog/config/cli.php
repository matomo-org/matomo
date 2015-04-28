<?php

use Interop\Container\ContainerInterface;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Formatter\ConsoleFormatter;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Output\OutputInterface;

return array(

    // Log
    'log.handlers' => array(
        DI\get('Symfony\Bridge\Monolog\Handler\ConsoleHandler'),
    ),
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
    'log.console.format' => '%start_tag%%level_name% [%datetime%]%end_tag% %message%' . PHP_EOL,

);
