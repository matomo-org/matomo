<?php

namespace Piwik\Plugins\FeatureFlags;

class FeatureFlagManager
{
    /**
     * @var FeatureFlagStorageInterface[]
     */
    private $storages;

    public function __construct(array $storages)
    {
        $this->storages = $storages;
    }

    public function isFeatureActive(Feature $feature): bool
    {
        $featureActive = false;

        foreach ($this->storages as $storage) {
            $isActive = $storage->isFeatureActive($feature);

            if ($isActive !== null) {
                $featureActive = $isActive;
            }
        }

        return $featureActive;
    }
}
