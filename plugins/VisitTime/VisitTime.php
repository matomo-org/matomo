<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitTime;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\PrivacyManager\FeatureFlags\PrivacyCompliance;
use Piwik\Plugins\SegmentEditor\Settings\LimitSegments;
use Piwik\Segment\SegmentsList;

// empty plugin definition, otherwise plugin won't be installed during test run
class VisitTime extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'Segment.filterSegments' => 'filterSegments',
        ];
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
                $list->remove('General_Visitors', 'visitLocalHour');
                $list->remove('General_Visitors', 'visitLocalMinute');
            }
        }
    }
}
