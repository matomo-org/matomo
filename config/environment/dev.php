<?php

return array(

    'Piwik\Translation\Loader\LoaderInterface' => DI\object('Piwik\Translation\Loader\LoaderCache')
        ->constructor(DI\link('Piwik\Translation\Loader\DevelopmentLoader')),
    'Piwik\Translation\Loader\DevelopmentLoader' => DI\object()
        ->constructor(DI\link('Piwik\Translation\Loader\JsonFileLoader')),

);
