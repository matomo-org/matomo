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

class Latitude extends Base
{
    protected $columnName = 'location_latitude';
    protected $columnType = 'decimal(9, 6) DEFAULT NULL';
    protected $type = self::TYPE_TEXT;
    protected $category = 'UserCountry_VisitLocation';
    protected $segmentName = 'latitude';
    protected $nameSingular = 'UserCountry_Latitude';
    protected $namePlural = 'UserCountry_Latitudes';
    protected $acceptValues = '-33.578, 40.830, etc.<br/>You can select visitors within a lat/long range using &segment=lat&gt;X;lat&lt;Y;long&gt;M;long&lt;N.';

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $value = $this->getUrlOverrideValueIfAllowed('lat', $request);

        if ($value !== false) {
            return $value;
        }

        $userInfo = $this->getUserInfo($request, $visitor);

        $latitude = $this->getLocationDetail($userInfo, LocationProvider::LATITUDE_KEY);

        return $latitude;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        return $this->getUrlOverrideValueIfAllowed('lat', $request);
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