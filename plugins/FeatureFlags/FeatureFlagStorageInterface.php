<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeatureFlags;

interface FeatureFlagStorageInterface
{
    /**
     * Returns true/false depending on if enabled
     *
     * If the flag isn't set for the particular storage context then will return null
     *
     * @param FeatureFlagInterface $feature
     *
     * @return bool|null
     */
    public function isFeatureActive(FeatureFlagInterface $feature): ?bool;

    /**
     * @internal
     * @param FeatureFlagInterface $feature
     * @return void
     */
    public function disableFeatureFlag(FeatureFlagInterface $feature): void;

    /**
     * @internal
     * @param FeatureFlagInterface $feature
     * @return void
     */
    public function enableFeatureFlag(FeatureFlagInterface $feature): void;

    /**
     * Delete a feature flag even if it doesn't exist in code as a class
     *
     * @internal
     * @param string $feature
     * @return void
     */
    public function deleteFeatureFlag(string $featureName): void;
}
