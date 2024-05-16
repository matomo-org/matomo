<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

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

    public function isFeatureActive(FeatureFlag $feature): bool
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
