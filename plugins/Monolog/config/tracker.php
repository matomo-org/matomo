<?php

use Interop\Container\ContainerInterface;

function isTrackerDebugEnabled(ContainerInterface $c)
{
    $trackerDebug = $c->get("ini.Tracker.debug");
    return ($trackerDebug == 1 || !empty($GLOBALS['PIWIK_TRACKER_DEBUG']));
}

return array(

    'Psr\Log\LoggerInterface' => function (ContainerInterface $c) {
        if (isTrackerDebugEnabled($c)) {
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
        if (isTrackerDebugEnabled($c)) {
            return \Monolog\Logger::DEBUG;
        }

        return $previous;
    })

);
