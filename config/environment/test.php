<?php

use Interop\Container\ContainerInterface;
use Piwik\Tests\Framework\Mock\TestConfig;

return array(

    'Piwik\Cache\Backend' => function () {
        return \Piwik\Cache::buildBackend('file');
    },
    'cache.eager.cache_id' => 'eagercache-test-',

    // Disable loading core translations
    'Piwik\Translation\Translator' => DI\object()
        ->constructorParameter('directories', array()),

    'Piwik\Config' => DI\decorate(function ($previous, ContainerInterface $c) {
        $testingEnvironment = $c->get('Piwik\Tests\Framework\TestingEnvironment');

        if (!$testingEnvironment->dontUseTestConfig) {
            return new TestConfig($c->get('Piwik\Application\Kernel\GlobalSettingsProvider'), $allowSave = false, $doSetTestEnvironment = true, $testingEnvironment);
        } else {
            return $previous;
        }
    })

);
