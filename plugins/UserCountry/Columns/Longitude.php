<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Columns;

use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class Longitude extends Base
{
    protected $columnName = 'location_longitude';
    protected $columnType = 'decimal(9, 6) DEFAULT NULL';
    protected $type = self::TYPE_TEXT;
    protected $category = 'UserCountry_VisitLocation';
    protected $acceptValues = '-70.664, 14.326, etc.';
    protected $segmentName = 'longitude';
    protected $nameSingular = 'UserCountry_Longitude';
    protected $namePlural = 'UserCountry_Longitudes';

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $value = $this->getUrlOverrideValueIfAllowed('long', $request);

        if ($value !== false) {
            return $value;
        }

        $userInfo = $this->getUserInfo($request, $visitor);

        $longitude = $this->getLocationDetail($userInfo, LocationProvider::LONGITUDE_KEY);

        return $longitude;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        return $this->getUrlOverrideValueIfAllowed('long', $request);
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
}
