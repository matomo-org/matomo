<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Resolution\Columns;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\PrivacyManager\FeatureFlags\PrivacyCompliance;
use Piwik\Plugins\Resolution\Settings\ScreenResolutionDetectionDisabled;
use Piwik\Tracker\Action;
use Piwik\Tracker\Cache as TrackerCache;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class Resolution extends VisitDimension
{
    protected $columnName = 'config_resolution';
    protected $columnType = 'VARCHAR(18) NULL';
    protected $acceptValues = '1280x1024, 800x600, etc.';
    protected $segmentName = 'resolution';
    protected $nameSingular = 'Resolution_ColumnResolution';
    protected $namePlural = 'Resolution_Resolutions';
    protected $type = self::TYPE_TEXT;

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        // in privacy compliance mode, we can't detect screen resolution
        if (self::isDisabledByCompliancePolicy($request->getIdSiteIfExists())) {
            return Request::UNKNOWN_RESOLUTION;
        }

        $resolution = $request->getParam('res');

        if (!empty($resolution)) {
            return substr($resolution, 0, 9);
        }

        return $resolution;
    }
    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        // In case the value was initially unknown, update it from a subsequent action
        if ($visitor->getVisitorColumn($this->columnName) === Request::UNKNOWN_RESOLUTION) {
            return $this->onNewVisit($request, $visitor, $action);
        } else {
            return false;
        }
    }

    /**
     * Check if compliance policy disables screen resolution detection
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
            $cacheKey = ScreenResolutionDetectionDisabled::class;
            return (($cache[$cacheKey] ?? false) === true);
        }

        return false;
    }
}
