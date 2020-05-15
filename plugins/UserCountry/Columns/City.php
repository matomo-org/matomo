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

class City extends Base
{
    protected $columnName = 'location_city';
    protected $columnType = 'varchar(255) DEFAULT NULL';
    protected $type = self::TYPE_TEXT;
    protected $segmentName = 'city';
    protected $nameSingular = 'UserCountry_City';
    protected $namePlural = 'UserCountryMap_Cities';
    protected $acceptValues = 'Sydney, Sao Paolo, Rome, etc.';
    protected $category = 'UserCountry_VisitLocation';

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $value = $this->getUrlOverrideValueIfAllowed('city', $request);
        if ($value !== false) {
            $value = substr($value, 0, 255);
            return $value;
        }

        $userInfo = $this->getUserInfo($request, $visitor);

        return $this->getLocationDetail($userInfo, LocationProvider::CITY_NAME_KEY);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        return $this->getUrlOverrideValueIfAllowed('city', $request);
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