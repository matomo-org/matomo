<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DevicesDetection\Columns;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\DevicesDetection\Settings\DeviceModelDetectionDisabled;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\PrivacyManager\FeatureFlags\PrivacyCompliance;
use Piwik\Tracker\Cache as TrackerCache;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class DeviceModel extends Base
{
    protected $columnName = 'config_device_model';
    protected $columnType = 'VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL';
    protected $type = self::TYPE_TEXT;
    protected $nameSingular = 'DevicesDetection_DeviceModel';
    protected $namePlural = 'DevicesDetection_DeviceModels';
    protected $segmentName = 'deviceModel';
    protected $acceptValues = 'iPad, Nexus 5, Galaxy S5, Fire TV, etc.';

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return string
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $parser = $this->getUAParser($request->getUserAgent(), $request->getClientHints());
        $genericDevice = '';

        $deviceType = $parser->getDeviceName();
        if (!empty($deviceType)) {
            $genericDevice = 'generic ' . $deviceType;
        } elseif ($parser->isMobile()) {
            $genericDevice = 'generic mobile';
        }

        // in privacy compliance mode, we can only detect/return generic device type, but not the model
        if (self::isDisabledByCompliancePolicy($request->getIdSiteIfExists())) {
            return $genericDevice;
        }

        $model = $parser->getModel();

        if (!empty($model)) {
            return mb_substr($model, 0, 100);
        }

        return $genericDevice;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onAnyGoalConversion(Request $request, Visitor $visitor, $action)
    {
        return $visitor->getVisitorColumn($this->columnName);
    }

    /**
     * Check if compliance policy disables device model detection
     *
     * @param int|null $idSite
     * @return bool
     * @throws \Piwik\Exception\DI\DependencyException
     * @throws \Piwik\Exception\DI\NotFoundException
     */
    public static function isDisabledByCompliancePolicy(?int $idSite = null): bool
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
