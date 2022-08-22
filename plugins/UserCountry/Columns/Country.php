<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserCountry\Columns;

use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\Segment;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Segment\SegmentsList;
use Piwik\Tracker\Visit;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';

class Country extends Base
{
    protected $columnName = 'location_country';
    protected $columnType = 'CHAR(3) NULL';
    protected $type = self::TYPE_TEXT;

    protected $category =  'UserCountry_VisitLocation';
    protected $nameSingular = 'UserCountry_Country';
    protected $namePlural = 'UserCountryMap_Countries';
    protected $segmentName = 'countryCode';
    protected $acceptValues = 'ISO 3166-1 alpha-2 country codes (de, us, fr, in, es, etc.)';

    public function configureSegments(SegmentsList $segmentsList, DimensionSegmentFactory $dimensionSegmentFactory)
    {
        $segment = new Segment();
        $segment->setName('UserCountry_CountryCode');
        $segmentsList->addSegment($dimensionSegmentFactory->createSegment($segment));

        $segment = new Segment();
        $segment->setSegment('countryName');
        $segment->setName('UserCountry_Country');
        $segment->setAcceptedValues('Germany, France, Spain, ...');
        $segment->setNeedsMostFrequentValues(false);
        $regionDataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider');
        $countryList = $regionDataProvider->getCountryList();
        array_walk($countryList, function (&$item, $key) {
            $item = Piwik::translate('Intl_Country_' . strtoupper($key), [], 'en');
        });

        $segment->setSqlFilterValue(function ($val) use ($countryList) {
            $result   = array_search($val, $countryList);
            if ($result === false) {
                $result = 'UNK';
            }
            return $result;
        });
        $segment->setSuggestedValuesCallback(function ($idSite, $maxValuesToReturn, $table) use ($countryList) {
            return $this->sortStaticListByUsage($countryList, $table, 'countryCode', $maxValuesToReturn);
        });
        $segmentsList->addSegment($dimensionSegmentFactory->createSegment($segment));
    }


    public function formatValue($value, $idSite, Formatter $formatter)
    {
        return \Piwik\Plugins\UserCountry\countryTranslate($value);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $value = $this->getUrlOverrideValueIfAllowed('country', $request);
        if ($value !== false) {
            $value = substr($value, 0, 3);
            return $value;
        }

        $userInfo = $this->getUserInfo($request, $visitor);
        $country  = $this->getLocationDetail($userInfo, LocationProvider::COUNTRY_CODE_KEY);

        if (!empty($country) && $country != Visit::UNKNOWN_CODE) {
            return strtolower($country);
        }

        return Visit::UNKNOWN_CODE;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        return $this->getUrlOverrideValueIfAllowed('country', $request);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onAnyGoalConversion(Request $request, Visitor $visitor, $action)
    {
        $country = $visitor->getVisitorColumn($this->columnName);

        if (isset($country) && false !== $country) {
            return $country;
        }

        $browserLanguage = $request->getBrowserLanguage();
        $enableLanguageToCountryGuess = Config::getInstance()->Tracker['enable_language_to_country_guess'];
        $locationIp = $visitor->getVisitorColumn('location_ip');

        return Common::getCountry($browserLanguage, $enableLanguageToCountryGuess, $locationIp);
    }
}
