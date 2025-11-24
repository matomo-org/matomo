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
use Piwik\Plugins\SegmentEditor\Settings\LimitSegments;
use Piwik\Segment\SegmentsList;
use Piwik\Tracker\Cache as TrackerCache;

/**
 *
 */
class Resolution extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'Segment.filterSegments' => 'filterSegments',
        ];
    }

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

    public function filterSegments(SegmentsList &$list, array $idSites)
    {
        $featureFlagManager = StaticContainer::get(FeatureFlagManager::class);
        if ($featureFlagManager->isFeatureActive(PrivacyCompliance::class)) {
            $limitSegmentsSettingEnabled = false;
            if (empty($idSites)) {
                $limitSegmentsSettingEnabled = LimitSegments::getInstance()->getValue();
            } else {
                foreach ($idSites as $idsite) {
                    $limitSegmentsSettingEnabled |= LimitSegments::getInstance($idsite)->getValue();
                }
            }
            if ($limitSegmentsSettingEnabled) {
                $list->remove('General_Visitors', 'resolution');
            }
        }
    }
}
