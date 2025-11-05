<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DevicesDetection;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\DevicesDetection\Settings\OnlyMajorVersions;
use Piwik\Plugins\DevicesDetection\Settings\DeviceModelDetectionDisabled;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\PrivacyManager\FeatureFlags\PrivacyCompliance;
use Piwik\Tracker\Cache as TrackerCache;

require_once PIWIK_INCLUDE_PATH . '/plugins/DevicesDetection/functions.php';

class DevicesDetection extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
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

    public static function shouldOnlyStoreMajorVersions(?int $idsite = null): bool
    {
        $featureFlagManager = StaticContainer::get(FeatureFlagManager::class);
        if ($featureFlagManager->isFeatureActive(PrivacyCompliance::class)) {
            $cache = TrackerCache::getCacheWebsiteAttributes($idsite);
            $cacheKey = OnlyMajorVersions::class;
            return (($cache[$cacheKey] ?? false) === true);
        }
        return false;
    }

    /**
     * Check if compliance policy disables device model detection
     *
     * @param int|null $idSite
     * @return bool
     * @throws \Piwik\Exception\DI\DependencyException
     * @throws \Piwik\Exception\DI\NotFoundException
     */
    public static function isDeviceModelDetectionDisabledByCompliancePolicy(?int $idSite = null): bool
    {
        // in privacy compliance mode, we can only detect/return generic device type, but not the model
        $featureFlagManager = StaticContainer::get(FeatureFlagManager::class);
        if ($featureFlagManager->isFeatureActive(PrivacyCompliance::class)) {
            $cache = TrackerCache::getCacheWebsiteAttributes($idSite);
            $cacheKey = DeviceModelDetectionDisabled::class;
            return (($cache[$cacheKey] ?? false) === true);
        }

        return false;
    }
}
