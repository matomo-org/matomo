<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Resolution;

use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics;
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
     * @param bool $hasConsent Whether the visit being measured holds the visitor's consent. The
     *                         policy's restriction does not apply to a consented visit; callers
     *                         that are not measuring a visit leave it at false.
     * @throws \Piwik\Exception\DI\DependencyException
     * @throws \Piwik\Exception\DI\NotFoundException
     */
    public static function isScreenResolutionDetectionDisabledByCompliancePolicy(?int $idSite = null, bool $hasConsent = false): bool
    {
        if ($hasConsent) {
            return false;
        }

        $cache = TrackerCache::getCacheWebsiteAttributes($idSite);
        $cacheKey = ScreenResolutionDetectionDisabled::class;
        return (($cache[$cacheKey] ?? false) === true);
    }

    /**
     * Whether the screen resolution report and segment are withheld for the site.
     */
    public static function isScreenResolutionReportHiddenForSite(int $idSite): bool
    {
        return self::isScreenResolutionDetectionDisabledByCompliancePolicy($idSite)
            && !self::isScreenResolutionStoredForConsentedVisits($idSite);
    }

    /**
     * Whether the site stores the screen resolution for consented visitors while the policy
     * disables its detection for everyone else, so that the report and segment have something
     * to show.
     *
     * This follows what tracking does: consent is only measured on a site measuring
     * consent-aware analytics that does not force cookieless tracking. Both are read from the
     * tracker cache and configuration that tracking reads them from, so the two stay in step.
     */
    public static function isScreenResolutionStoredForConsentedVisits(int $idSite): bool
    {
        return self::isScreenResolutionDetectionDisabledByCompliancePolicy($idSite)
            && ConsentAwareAnalytics::isEnabled($idSite)
            && !(new PrivacyManagerConfig($idSite))->forceCookielessTracking;
    }

    public function filterSegments(SegmentsList &$list, array $idSites)
    {
        $limitSegmentsSettingEnabled = false;
        if (empty($idSites)) {
            $limitSegmentsSettingEnabled = LimitSegments::getInstance()->getValue();
        } else {
            foreach ($idSites as $idsite) {
                // a site storing the screen resolution for consented visitors keeps the segment, as
                // long as nothing but the policy's screen resolution restriction would hide it
                $limitSegmentsSettingEnabled |= LimitSegments::getInstance($idsite)->getValue()
                    && !self::isScreenResolutionStoredForConsentedVisits((int) $idsite);
            }
        }
        if ($limitSegmentsSettingEnabled) {
            $list->remove('General_Visitors', 'resolution');
        }
    }
}
