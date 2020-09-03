<?php

return array(

    'Matomo\Cache\Backend' => DI\autowire('Matomo\Cache\Backend\ArrayCache'),

    'Piwik\Translation\Loader\LoaderInterface' => DI\autowire('Piwik\Translation\Loader\LoaderCache')
        ->constructorParameter('loader', DI\get('Piwik\Translation\Loader\DevelopmentLoader')),
    'Piwik\Translation\Loader\DevelopmentLoader' => DI\create()
        ->constructor(DI\get('Piwik\Translation\Loader\JsonFileLoader')),

);
