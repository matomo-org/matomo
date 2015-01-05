<?php

return array(

    // Disable logging
    'Psr\Log\LoggerInterface' => DI\object('Psr\Log\NullLogger'),

    // Disable translation cache
    'Piwik\Translation\Loader\LoaderInterface' => DI\object('Piwik\Translation\Loader\JsonFileLoader'),
    // Disable loading core translations
    'Piwik\Translation\Translator' => DI\object()
        ->constructorParameter('directories', array()),

);
