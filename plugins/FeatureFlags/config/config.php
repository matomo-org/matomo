<?php

return [
    /**
     * The order of the these storage mechanisms for the feature flags determines how they cascade down.
     *
     * The first one will be overwritten by the second one (if set).
     */
    'featureflag.storages' => [
        Piwik\DI::get('Piwik\Plugins\FeatureFlags\Storage\ConfigFeatureFlagStorage'),
    ],
    'Piwik\Plugins\FeatureFlags\FeatureFlagManager' => Piwik\DI::autowire()
        ->constructor(Piwik\DI::get('featureflag.storages'))
];
