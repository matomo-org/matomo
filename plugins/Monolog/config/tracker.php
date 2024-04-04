<?php

use Piwik\Container\Container;

function isTrackerDebugEnabled(Container $c)
{
    $trackerDebug = $c->get("ini.Tracker.debug");
    return ($trackerDebug == 1 || !empty($GLOBALS['PIWIK_TRACKER_DEBUG']));
}

return array(

    'ini.log.log_writers' => Piwik\DI::decorate(function ($previous, Container $c) {
        if (
            isTrackerDebugEnabled($c)
            && \Piwik\Common::isPhpCliMode()
        ) {
            $previous[] = 'screen';
            $previous = array_unique($previous);
        }
        return $previous;
    }),

    'log.handler.classes' => Piwik\DI::decorate(function ($previous, Container $c) {
        if (
            isset($previous['screen'])
            && isTrackerDebugEnabled($c)
        ) {
            $previous['screen'] = 'Piwik\Plugins\Monolog\Handler\EchoHandler';
        } else {
            unset($previous['screen']);
        }

        return $previous;
    }),

    'log.level' => Piwik\DI::decorate(function ($previous, Container $c) {
        if (isTrackerDebugEnabled($c)) {
            return \Piwik\Log\Logger::DEBUG;
        }

        return $previous;
    })

);
