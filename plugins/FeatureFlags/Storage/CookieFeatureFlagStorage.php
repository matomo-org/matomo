<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeatureFlags\Storage;

use Piwik\Plugins\FeatureFlags\FeatureFlagInterface;
use Piwik\Plugins\FeatureFlags\FeatureFlagStorageInterface;

class CookieFeatureFlagStorage implements FeatureFlagStorageInterface
{
    /**
     * @internal
     * @param FeatureFlagInterface $feature
     * @return bool|null
     */
    public function isFeatureActive(FeatureFlagInterface $feature): ?bool
    {
        if (!$feature->allowsCookieOverwrite()) {
            return false;
        }

        $cookieName = $this->getCookieNameForFeature($feature->getName());

        if (!isset($_COOKIE[$cookieName])) {
            return null;
        }

        return $_COOKIE[$cookieName] == '1';
    }

    /**
     * @internal
     * @param FeatureFlagInterface $feature
     * @return void
     */
    public function disableFeatureFlag(FeatureFlagInterface $feature): void
    {
        // do nothing, as cookie values should only be set in frontend
    }

    /**
     * @internal
     * @param FeatureFlagInterface $feature
     * @return void
     */
    public function enableFeatureFlag(FeatureFlagInterface $feature): void
    {
        // do nothing as cookie values should only be set in frontend
    }

    /**
     * @internal
     * @param string $feature
     * @return void
     */
    public function deleteFeatureFlag(string $featureName): void
    {
        // do nothing as cookie values should only be set in frontend
    }

    private function getCookieNameForFeature(string $featureName): string
    {
        return 'feature_' . $featureName;
    }
}
