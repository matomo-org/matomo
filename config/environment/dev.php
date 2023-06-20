<?php

return array(

    'Matomo\Cache\Backend' => Piwik\DI::autowire('Matomo\Cache\Backend\ArrayCache'),

    'Piwik\Translation\Loader\LoaderInterface' => Piwik\DI::autowire('Piwik\Translation\Loader\LoaderCache')
        ->constructorParameter('loader', Piwik\DI::get('Piwik\Translation\Loader\DevelopmentLoader')),
    'Piwik\Translation\Loader\DevelopmentLoader' => Piwik\DI::create()
        ->constructor(Piwik\DI::get('Piwik\Translation\Loader\JsonFileLoader')),

);
