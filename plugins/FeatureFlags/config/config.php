<?php

namespace Piwik\Plugins\FeatureFlags\config;

use Piwik\DI;
use Piwik\Log\Logger;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\FeatureFlags\FeatureFlags\Example;

return [
    /**
     * The order of the these storage mechanisms for the feature flags determines how they cascade down.
     *
     * The first one will be overwritten by the second one (if set).
     */
    'featureflag.storages' => [
        DI::get('Piwik\Plugins\FeatureFlags\Storage\ConfigFeatureFlagStorage'),
    ],
    FeatureFlagManager::class => DI::autowire()
        ->constructor(DI::get('featureflag.storages'), DI::get('featureflag.feature_flags'), DI::get(Logger::class)),
    'featureflag.feature_flags' => DI::add([
        Example::class,
    ]),
];
