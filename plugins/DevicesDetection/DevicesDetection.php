<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DevicesDetection;

use Piwik\Plugins\DevicesDetection\Settings\OnlyMajorVersions;
use Piwik\Plugins\DevicesDetection\Settings\DeviceModelDetectionDisabled;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics;
use Piwik\Plugins\SegmentEditor\Settings\LimitSegments;
use Piwik\Segment\SegmentsList;
use Piwik\Tracker\Cache as TrackerCache;

require_once PIWIK_INCLUDE_PATH . '/plugins/DevicesDetection/functions.php';

class DevicesDetection extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'Segment.filterSegments'                 => 'filterSegments',
        ];
    }

    public function getClientSideTranslationKeys(&$translations)
    {
        $translations[] = 'DevicesDetection_UserAgent';
        $translations[] = 'General_Refresh';
        $translations[] = 'DevicesDetection_BotDetected';
        $translations[] = 'DevicesDetection_ColumnOperatingSystem';
        $translations[] = 'Mobile_ShowAll';
        $translations[] = 'CorePluginsAdmin_Version';
        $translations[] = 'DevicesDetection_OperatingSystemFamily';
        $translations[] = 'DevicesDetection_ColumnBrowser';
        $translations[] = 'DevicesDetection_BrowserFamily';
        $translations[] = 'DevicesDetection_Device';
        $translations[] = 'DevicesDetection_dataTableLabelTypes';
        $translations[] = 'DevicesDetection_dataTableLabelBrands';
        $translations[] = 'DevicesDetection_dataTableLabelModels';
        $translations[] = 'General_Close';
        $translations[] = 'DevicesDetection_DeviceDetection';
        $translations[] = 'DevicesDetection_ClientHints';
        $translations[] = 'DevicesDetection_ConsiderClientHints';
        $translations[] = 'DevicesDetection_ClientHintsNotSupported';
    }

    public function getStylesheetFiles(&$files)
    {
        $files[] = 'plugins/DevicesDetection/vue/src/DetectionPage/DetectionPage.less';
    }

    /**
     * Whether only major browser and OS versions may be stored for the site.
     *
     * @param bool $hasConsent Whether the visit being measured holds the visitor's consent. The
     *                         policy's restriction does not apply to a consented visit; callers
     *                         that are not measuring a visit leave it at false.
     */
    public static function shouldOnlyStoreMajorVersions(?int $idsite = null, bool $hasConsent = false): bool
    {
        if ($hasConsent) {
            return false;
        }

        $cache = TrackerCache::getCacheWebsiteAttributes($idsite);
        $cacheKey = OnlyMajorVersions::class;
        return (($cache[$cacheKey] ?? false) === true);
    }

    /**
     * Check if compliance policy disables device model detection
     *
     * @param bool $hasConsent Whether the visit being measured holds the visitor's consent. The
     *                         policy's restriction does not apply to a consented visit; callers
     *                         that are not measuring a visit leave it at false.
     * @throws \Piwik\Exception\DI\DependencyException
     * @throws \Piwik\Exception\DI\NotFoundException
     */
    public static function isDeviceModelDetectionDisabledByCompliancePolicy(?int $idSite = null, bool $hasConsent = false): bool
    {
        if ($hasConsent) {
            return false;
        }

        $cache = TrackerCache::getCacheWebsiteAttributes($idSite);
        $cacheKey = DeviceModelDetectionDisabled::class;
        return (($cache[$cacheKey] ?? false) === true);
    }

    /**
     * Whether the device model report and segment are withheld for the site.
     */
    public static function isDeviceModelReportHiddenForSite(int $idSite): bool
    {
        return self::isDeviceModelDetectionDisabledByCompliancePolicy($idSite)
            && !self::isDeviceModelStoredForConsentedVisits($idSite);
    }

    /**
     * Whether the site stores the device model for consented visitors while the policy disables
     * its detection for everyone else, so that the report and segment have something to show.
     *
     * This follows what tracking does: consent is only measured on a site measuring
     * consent-aware analytics that does not force cookieless tracking. Both are read from the
     * tracker cache and configuration that tracking reads them from, so the two stay in step.
     */
    public static function isDeviceModelStoredForConsentedVisits(int $idSite): bool
    {
        return self::isDeviceModelDetectionDisabledByCompliancePolicy($idSite)
            && ConsentAwareAnalytics::isMeasuredForSite($idSite);
    }

    public function filterSegments(SegmentsList &$list, array $idSites)
    {
        $limitSegmentsSettingEnabled = false;
        if (empty($idSites)) {
            $limitSegmentsSettingEnabled = LimitSegments::getInstance()->getValue();
        } else {
            foreach ($idSites as $idsite) {
                // a site storing the device model for consented visitors keeps the segment, as
                // long as nothing but the policy's device model restriction would hide it
                $limitSegmentsSettingEnabled |= LimitSegments::getInstance($idsite)->getValue()
                    && !self::isDeviceModelStoredForConsentedVisits((int) $idsite);
            }
        }
        if ($limitSegmentsSettingEnabled) {
            $list->remove('General_Visitors', 'deviceModel');
        }
    }
}
