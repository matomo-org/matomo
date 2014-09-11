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
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugins\UserCountry\LocationProvider;
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

        // apply filter on the whole datatable in order the inline search to work (searches are done on "beautiful" label)
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'code'));
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', __NAMESPACE__ . '\getFlagFromCode'));
        $dataTable->filter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\countryTranslate'));

        $dataTable->queueFilter('ColumnCallbackAddMetadata', array(array(), 'logoWidth', function () { return 16; }));
        $dataTable->queueFilter('ColumnCallbackAddMetadata', array(array(), 'logoHeight', function () { return 11; }));

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

        $getRegionName = '\\Piwik\\Plugins\\UserCountry\\LocationProvider\\GeoIp::getRegionNameFromCodes';
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

        if ($provider === false) {
            $provider = LocationProvider::getCurrentProviderId();
        }

        $oProvider = LocationProvider::getProviderById($provider);
        if ($oProvider === false) {
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

    protected function getDataTable($name, $idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable($name);
        $dataTable->filter('Sort', array(Metrics::INDEX_NB_VISITS));
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
