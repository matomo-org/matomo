<?php

namespace Piwik\Plugins\FeatureFlags;

interface FeatureFlagStorageInterface
{
    /**
     * Returns true/false depending on if enabled
     *
     * If the flag isn't set for the particular storage context then will return null
     *
     * @param Feature $feature
     *
     * @return bool|null
     */
    public function isFeatureActive(Feature $feature): ?bool;
}
