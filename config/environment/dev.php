<?php

return array(

    'Matomo\Cache\Backend' => DI\object('Matomo\Cache\Backend\ArrayCache'),

    'Piwik\Translation\Loader\LoaderInterface' => DI\object('Piwik\Translation\Loader\LoaderCache')
        ->constructor(DI\get('Piwik\Translation\Loader\DevelopmentLoader')),
    'Piwik\Translation\Loader\DevelopmentLoader' => DI\object()
        ->constructor(DI\get('Piwik\Translation\Loader\JsonFileLoader')),

);
