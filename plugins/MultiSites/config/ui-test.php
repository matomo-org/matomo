<?php

use Piwik\Container\StaticContainer;
use Piwik\DI;

return [
    'observers.global' => DI::add([
        [
            'API.MultiSites.getAllWithGroups',
            DI::value(function (&$parameters) {
                if (StaticContainer::get('test.vars.forceMultiSitesDashboardFailure')) {
                    throw new Exception('Forced API error');
                }
            }),
        ],
    ]),
];
