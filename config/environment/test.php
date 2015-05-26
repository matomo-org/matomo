<?php

return array(

    // Disable logging
    'Psr\Log\LoggerInterface' => DI\object('Psr\Log\NullLogger'),

    'Piwik\Cache\Backend' => function () {
        return \Piwik\Cache::buildBackend('file');
    },
    'cache.eager.cache_id' => 'eagercache-test-',

    // Disable loading core translations
    'Piwik\Translation\Translator' => DI\object()
        ->constructorParameter('directories', array()),

    'Piwik\Config' => DI\object('Piwik\Tests\Framework\Mock\TestConfig'),
);
