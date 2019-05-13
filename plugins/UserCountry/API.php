<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry;

use Exception;
use Piwik\Archive;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Option;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\GeoIp2\Commands\ConvertRegionCodesToIso;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Tracker\Visit;

/**
 * @see plugins/UserCountry/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';

/**
 * The UserCountry API lets you access reports about your visitors' Countries and Continents.
 * @method static \Piwik\Plugins\UserCountry\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    public function getCountry($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::COUNTRY_RECORD_NAME, $idSite, $period, $date, $segment);

        $dataTables = [$dataTable];

        if ($dataTable instanceof DataTable\Map) {
            $dataTables = $dataTable->getDataTables();
        }

        foreach ($dataTables as $dt) {
            if ($dt->getRowFromLabel('ti')) {
                $dt->filter('GroupBy', array(
                    'label',
                    function ($label) {
                        if ($label == 'ti') {
                            return 'cn';
                        }
                        return $label;
                    }
                ));
            }
        }

        // apply filter on the whole datatable in order the inline search to work (searches are done on "beautiful" label)
        $dataTable->filter('AddSegmentValue');
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'code'));
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', __NAMESPACE__ . '\getFlagFromCode'));
        $dataTable->filter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\countryTranslate'));

        $dataTable->queueFilter('ColumnCallbackAddMetadata', array(array(), 'logoHeight', function () { return 16; }));

        return $dataTable;
    }

    public function getContinent($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::COUNTRY_RECORD_NAME, $idSite, $period, $date, $segment);

        $getContinent = array('Piwik\Common', 'getContinent');
        $dataTable->filter('GroupBy', array('label', $getContinent));

        $dataTable->filter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\continentTranslate'));
        $dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'code'));

        return $dataTable;
    }

    /**
     * Returns visit information for every region with at least one visit.
     *
     * @param int|string $idSite
     * @param string $period
     * @param string $date
     * @param string|bool $segment
     * @return DataTable
     */
    public function getRegion($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::REGION_RECORD_NAME, $idSite, $period, $date, $segment);

        $separator = Archiver::LOCATION_SEPARATOR;
        $unk = Visit::UNKNOWN_CODE;

        $dataTables = [$dataTable];

        if ($dataTable instanceof DataTable\Map) {
            $dataTables = $dataTable->getDataTables();
        }

        foreach ($dataTables as $dt) {
            $archiveDate = $dt->getMetadata(DataTable::ARCHIVED_DATE_METADATA_NAME);

            // convert fips region codes to iso if required
            if ($this->shouldRegionCodesBeConvertedToIso($archiveDate, $date, $period)) {
                $dt->filter('GroupBy', array(
                    'label',
                    function ($label) use ($separator, $unk) {
                        $regionCode = getElementFromStringArray($label, $separator, 0, '');
                        $countryCode = getElementFromStringArray($label, $separator, 1, '');

                        list($countryCode, $regionCode) = GeoIp2::convertRegionCodeToIso($countryCode,
                            $regionCode, true);

                        $splitLabel = explode($separator, $label);

                        if (isset($splitLabel[0])) {
                            $splitLabel[0] = $regionCode;
                        }

                        if (isset($splitLabel[1])) {
                            $splitLabel[1] = strtolower($countryCode);
                        }

                        return implode($separator, $splitLabel);
                    }
                ));
            } else if ($dt->getRowFromLabel('1|ti')) {
                $dt->filter('GroupBy', array(
                    'label',
                    function ($label) {
                        if ($label == '1|ti') {
                            return '14|cn';
                        }
                        return $label;
                    }
                ));
            }
        }

        $segments = array('regionCode', 'countryCode');
        $dataTable->filter('AddSegmentByLabel', array($segments, Archiver::LOCATION_SEPARATOR));

        // split the label and put the elements into the 'region' and 'country' metadata fields
        $dataTable->filter('ColumnCallbackAddMetadata',
            array('label', 'region', __NAMESPACE__ . '\getElementFromStringArray', array($separator, 0, $unk)));
        $dataTable->filter('ColumnCallbackAddMetadata',
            array('label', 'country', __NAMESPACE__ . '\getElementFromStringArray', array($separator, 1, $unk)));

        // add country name metadata
        $dataTable->filter('MetadataCallbackAddMetadata',
            array('country', 'country_name', __NAMESPACE__ . '\CountryTranslate', $applyToSummaryRow = false));

        // get the region name of each row and put it into the 'region_name' metadata
        $dataTable->filter('ColumnCallbackAddMetadata',
            array('label', 'region_name', __NAMESPACE__ . '\getRegionName', $params = null,
                  $applyToSummaryRow = false));

        // add the country flag as a url to the 'logo' metadata field
        $dataTable->filter('MetadataCallbackAddMetadata', array('country', 'logo', __NAMESPACE__ . '\getFlagFromCode'));

        // prettify the region label
        $dataTable->filter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\getPrettyRegionName'));

        $dataTable->queueFilter('ReplaceSummaryRowLabel');

        return $dataTable;
    }

    /**
     * Returns visit information for every city with at least one visit.
     *
     * @param int|string $idSite
     * @param string $period
     * @param string $date
     * @param string|bool $segment
     * @return DataTable
     */
    public function getCity($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::CITY_RECORD_NAME, $idSite, $period, $date, $segment);

        $separator = Archiver::LOCATION_SEPARATOR;
        $unk = Visit::UNKNOWN_CODE;

        $dataTables = [$dataTable];

        if ($dataTable instanceof DataTable\Map) {
            $dataTables = $dataTable->getDataTables();
        }

        foreach ($dataTables as $dt) {
            $archiveDate = $dt->getMetadata(DataTable::ARCHIVED_DATE_METADATA_NAME);

            // convert fips region codes to iso if required
            if ($this->shouldRegionCodesBeConvertedToIso($archiveDate, $date, $period)) {
                $dt->filter('GroupBy', array(
                    'label',
                    function ($label) use ($separator, $unk) {
                        $regionCode = getElementFromStringArray($label, $separator, 1, '');
                        $countryCode = getElementFromStringArray($label, $separator, 2, '');

                        list($countryCode, $regionCode) = GeoIp2::convertRegionCodeToIso($countryCode,
                            $regionCode, true);

                        $splitLabel = explode($separator, $label);

                        if (isset($splitLabel[1])) {
                            $splitLabel[1] = $regionCode;
                        }

                        if (isset($splitLabel[2])) {
                            $splitLabel[2] = strtolower($countryCode);
                        }

                        return implode($separator, $splitLabel);
                    }
                ));
            } else {
                $dt->filter('GroupBy', array(
                    'label',
                    function ($label) {
                        if (substr($label, -5) == '|1|ti') {
                            return substr($label, 0, -5) . '|14|cn';
                        }
                        return $label;
                    }
                ));
            }
        }

        $segments = array('city', 'regionCode', 'countryCode');
        $dataTable->filter('AddSegmentByLabel', array($segments, Archiver::LOCATION_SEPARATOR));

        // split the label and put the elements into the 'city_name', 'region', 'country',
        // 'lat' & 'long' metadata fields
        $strUnknown = Piwik::translate('General_Unknown');
        $dataTable->filter('ColumnCallbackAddMetadata',
            array('label', 'city_name', __NAMESPACE__ . '\getElementFromStringArray',
                  array($separator, 0, $strUnknown)));
        $dataTable->filter('MetadataCallbackAddMetadata',
            array('city_name', 'city', function ($city) use ($strUnknown) {
                if ($city == $strUnknown) {
                    return "xx";
                } else {
                    return false;
                }
            }));
        $dataTable->filter('ColumnCallbackAddMetadata',
            array('label', 'region', __NAMESPACE__ . '\getElementFromStringArray', array($separator, 1, $unk)));
        $dataTable->filter('ColumnCallbackAddMetadata',
            array('label', 'country', __NAMESPACE__ . '\getElementFromStringArray', array($separator, 2, $unk)));

        // backwards compatibility: for reports that have lat|long in label
        $dataTable->filter('ColumnCallbackAddMetadata',
            array('label', 'lat', __NAMESPACE__ . '\getElementFromStringArray', array($separator, 3, false)));
        $dataTable->filter('ColumnCallbackAddMetadata',
            array('label', 'long', __NAMESPACE__ . '\getElementFromStringArray', array($separator, 4, false)));

        // add country name & region name metadata
        $dataTable->filter('MetadataCallbackAddMetadata',
            array('country', 'country_name', __NAMESPACE__ . '\countryTranslate', $applyToSummaryRow = false));

        $getRegionName = '\\Piwik\\Plugins\\UserCountry\\getRegionNameFromCodes';
        $dataTable->filter('MetadataCallbackAddMetadata', array(
                                                               array('country', 'region'), 'region_name', $getRegionName, $applyToSummaryRow = false));

        // add the country flag as a url to the 'logo' metadata field
        $dataTable->filter('MetadataCallbackAddMetadata', array('country', 'logo', __NAMESPACE__ . '\getFlagFromCode'));

        // prettify the label
        $dataTable->filter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\getPrettyCityName'));

        $dataTable->queueFilter('ReplaceSummaryRowLabel');

        return $dataTable;
    }

    /**
     * if no switch to ISO was done --> no conversion as only FIPS codes are in use and handled correctly
     * if there has been a switch to ISO, we need to check the date:
     * - if the start date of the period is after the date we switched to ISO: no conversion needed
     * - if not we need to convert the codes to ISO, if the code is mappable
     * Note: as all old codes are mapped, not mappable codes need to be iso codes already, so we leave them
     * @param $date
     * @param $period
     * @return bool
     */
    private function shouldRegionCodesBeConvertedToIso($archiveDate, $date, $period)
    {
        $timeOfSwitch = Option::get(GeoIp2::SWITCH_TO_ISO_REGIONS_OPTION_NAME);

        if (empty($timeOfSwitch)) {
            return false; // if option was not set, all codes are fips codes, so leave them
        }

        try {
            $dateOfSwitch = Date::factory((int)$timeOfSwitch);
            $period = Period\Factory::build($period, $date);
            $periodStart = $period->getDateStart();
        } catch (Exception $e) {
            return false;
        }

        // if all region codes in log tables have been converted, check if archiving date was after the date of switch to iso
        // this check might not be fully correct in cases were only periods > day get recreated, but it should avoid some
        // double conversion if all archives have been recreated after converting all region codes
        $codesConverted = Option::get(ConvertRegionCodesToIso::OPTION_NAME);

        if ($codesConverted && $archiveDate) {
            try {
                $dateOfArchive = Date::factory($archiveDate);

                if ($dateOfArchive->isLater($dateOfSwitch)) {
                    return false;
                }
            } catch (Exception $e) {
            }
        }

        if ($dateOfSwitch->isEarlier($periodStart)) {
            return false;
        }

        return true;
    }

    /**
     * Returns a simple mapping from country code to country name
     *
     * @return \string[]
     */
    public function getCountryCodeMapping()
    {
        $regionDataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider');

        $countryCodeList = $regionDataProvider->getCountryList();

        array_walk($countryCodeList, function(&$item, $key) {
            $item = Piwik::translate('Intl_Country_'.strtoupper($key));
        });

        return $countryCodeList;
    }

    /**
     * Uses a location provider to find/guess the location of an IP address.
     *
     * See LocationProvider::getLocation to see the details
     * of the result of this function.
     *
     * @param string $ip The IP address.
     * @param bool|string $provider The ID of the provider to use or false to use the
     *                               currently configured one.
     * @throws Exception
     * @return array|false
     */
    public function getLocationFromIP($ip, $provider = false)
    {
        Piwik::checkUserHasSomeViewAccess();

        if (empty($provider)) {
            $provider = LocationProvider::getCurrentProviderId();
        }

        $oProvider = LocationProvider::getProviderById($provider);
        if (empty($oProvider)) {
            throw new Exception("Cannot find the '$provider' provider. It is either an invalid provider "
                . "ID or the ID of a provider that is not working.");
        }

        $location = $oProvider->getLocation(array('ip' => $ip));
        if (empty($location)) {
            throw new Exception("Could not geolocate '$ip'!");
        }
        $location['ip'] = $ip;
        return $location;
    }

    /**
     * Set the location provider
     *
     * @param string $providerId  The ID of the provider to use  eg 'default', 'geoip2_php', ...
     * @throws Exception if ID is invalid
     */
    public function setLocationProvider($providerId)
    {
        Piwik::checkUserHasSuperUserAccess();

        if (!UserCountry::isGeoLocationAdminEnabled()) {
            throw new \Exception('Setting geo location has been disabled in config.');
        }

        $provider = LocationProvider::setCurrentProvider($providerId);
        if ($provider === false) {
            throw new Exception("Invalid provider ID: '$providerId'.");
        }
    }

    protected function getDataTable($name, $idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable($name);
        $dataTable->queueFilter('ReplaceColumnNames');
        return $dataTable;
    }

    public function getNumberOfDistinctCountries($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        return $archive->getDataTableFromNumeric(Archiver::DISTINCT_COUNTRIES_METRIC);
    }
}
