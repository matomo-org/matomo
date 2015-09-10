<?php

use Interop\Container\ContainerInterface;

return array(

    'Psr\Log\LoggerInterface' => function (ContainerInterface $c) {
        if (\Piwik\SettingsServer::isTrackerDebugEnabled($c)) {
            return $c->get('Monolog\Logger');
        } else {
            return new \Psr\Log\NullLogger();
        }
    },

    'log.handler.classes' => DI\decorate(function ($previous) {
        if (isset($previous['screen'])) {
            $previous['screen'] = 'Piwik\Plugins\Monolog\Handler\EchoHandler';
        }

        return $previous;
    }),

    'log.level' => DI\decorate(function ($previous, ContainerInterface $c) {
        if (\Piwik\SettingsServer::isTrackerDebugEnabled($c)) {
            return \Monolog\Logger::DEBUG;
        }

        return $previous;
    })

);
