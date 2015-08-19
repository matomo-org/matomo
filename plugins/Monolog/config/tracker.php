<?php

use Interop\Container\ContainerInterface;

return array(

    'Psr\Log\LoggerInterface' => function (ContainerInterface $c) {
        $trackerDebug = $c->get("ini.Tracker.debug");
        if ($trackerDebug == 1 || !empty($GLOBALS['PIWIK_TRACKER_DEBUG'])) {
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

    'log.level' => DI\factory(function (ContainerInterface $c) {
        return \Monolog\Logger::DEBUG;
    })

);
