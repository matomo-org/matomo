<?php

use Interop\Container\ContainerInterface;

return array(

    'Psr\Log\LoggerInterface' => function (ContainerInterface $c) {
        $trackerDebug = $c->get("ini.Tracker.debug");
        if ($trackerDebug == 1) {
            return $c->get('Monolog\Logger');
        } else {
            return new \Psr\Log\NullLogger();
        }
    }

);
