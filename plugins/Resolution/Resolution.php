<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Resolution;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\PrivacyManager\FeatureFlags\PrivacyCompliance;
use Piwik\Plugins\Resolution\Settings\ScreenResolutionDetectionDisabled;
use Piwik\Tracker\Cache as TrackerCache;

/**
 *
 */
class Resolution extends \Piwik\Plugin
{
    /**
     * Check if compliance policy disables screen resolution detection
     *
     * @param int|null $idSite
     * @return bool
     * @throws \Piwik\Exception\DI\DependencyException
     * @throws \Piwik\Exception\DI\NotFoundException
     */
    public static function isScreenResolutionDetectionDisabledByCompliancePolicy(?int $idSite = null): bool
    {
        // in privacy compliance mode, we can only detect/return generic device type, but not the model
        $featureFlagManager = StaticContainer::get(FeatureFlagManager::class);
        if ($featureFlagManager->isFeatureActive(PrivacyCompliance::class)) {
            $cache = TrackerCache::getCacheWebsiteAttributes($idSite);
            $cacheKey = ScreenResolutionDetectionDisabled::class;
            return (($cache[$cacheKey] ?? false) === true);
        }

        return false;
    }
}
