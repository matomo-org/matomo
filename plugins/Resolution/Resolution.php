<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Resolution;

use Piwik\Plugins\Resolution\Settings\ScreenResolutionDetectionDisabled;
use Piwik\Plugins\SegmentEditor\Settings\LimitSegments;
use Piwik\Segment\SegmentsList;
use Piwik\Tracker\Cache as TrackerCache;

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
     * @throws \Piwik\Exception\DI\DependencyException
     * @throws \Piwik\Exception\DI\NotFoundException
     */
    public static function isScreenResolutionDetectionDisabledByCompliancePolicy(?int $idSite = null): bool
    {
        $cache = TrackerCache::getCacheWebsiteAttributes($idSite);
        $cacheKey = ScreenResolutionDetectionDisabled::class;
        return (($cache[$cacheKey] ?? false) === true);
    }

    public function filterSegments(SegmentsList &$list, array $idSites)
    {
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
