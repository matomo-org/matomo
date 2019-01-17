<?php

use Interop\Container\ContainerInterface;

function isTrackerDebugEnabled(ContainerInterface $c)
{
    $trackerDebug = $c->get("ini.Tracker.debug");
    return ($trackerDebug == 1 || !empty($GLOBALS['PIWIK_TRACKER_DEBUG']));
}

return array(

    'log.handler.classes' => DI\decorate(function ($previous, ContainerInterface $c) {
        if (isset($previous['screen'])
            && isTrackerDebugEnabled($c)
        ) {
            $previous['screen'] = 'Piwik\Plugins\Monolog\Handler\EchoHandler';
        } else {
            unset($previous['screen']);
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
