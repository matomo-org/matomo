<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package UserCountry
 */

namespace Piwik\Plugins\UserCountry;

use Piwik\ArchiveProcessor;
use Piwik\DataArray;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugins\UserCountry\LocationProvider;

class Archiver extends \Piwik\Plugin\Archiver
{
    const COUNTRY_RECORD_NAME = 'UserCountry_country';
    const REGION_RECORD_NAME = 'UserCountry_region';
    const CITY_RECORD_NAME = 'UserCountry_city';
    const DISTINCT_COUNTRIES_METRIC = 'UserCountry_distinctCountries';

    // separate region, city & country info in stored report labels
    const LOCATION_SEPARATOR = '|';

    private $latLongForCities = array();

    protected $maximumRows;

    const COUNTRY_FIELD = 'location_country';

    const REGION_FIELD = 'location_region';

    const CITY_FIELD = 'location_city';

    protected $dimensions = array(self::COUNTRY_FIELD, self::REGION_FIELD, self::CITY_FIELD);

    protected $arrays;
    const LATITUDE_FIELD = 'location_latitude';
    const LONGITUDE_FIELD = 'location_longitude';

    public function archiveDay()
    {
        foreach ($this->dimensions as $dimension) {
            $this->arrays[$dimension] = new DataArray();
        }
        $this->aggregateFromVisits();
        $this->aggregateFromConversions();
        $this->recordDayReports();
    }

    protected function aggregateFromVisits()
    {
        $additionalSelects = array('MAX(log_visit.location_latitude) as location_latitude',
                                   'MAX(log_visit.location_longitude) as location_longitude');
        $query = $this->getLogAggregator()->queryVisitsByDimension($this->dimensions, $where = false, $additionalSelects);
        if ($query === false) {
            return;
        }

        while ($row = $query->fetch()) {
            $this->makeRegionCityLabelsUnique($row);
            $this->rememberCityLatLong($row);

            /* @var $dataArray DataArray */
            foreach ($this->arrays as $dimension => $dataArray) {
                $dataArray->sumMetricsVisits($row[$dimension], $row);
            }
        }
    }

    /**
     * Makes sure the region and city of a query row are unique.
     *
     * @param array $row
     */
    private function makeRegionCityLabelsUnique(&$row)
    {
        // remove the location separator from the region/city/country we get from the query
        foreach ($this->dimensions as $column) {
            $row[$column] = str_replace(self::LOCATION_SEPARATOR, '', $row[$column]);
        }

        if (!empty($row[self::REGION_FIELD])) {
            $row[self::REGION_FIELD] = $row[self::REGION_FIELD] . self::LOCATION_SEPARATOR . $row[self::COUNTRY_FIELD];
        }

        if (!empty($row[self::CITY_FIELD])) {
            $row[self::CITY_FIELD] = $row[self::CITY_FIELD] . self::LOCATION_SEPARATOR . $row[self::REGION_FIELD];
        }
    }

    protected function rememberCityLatLong($row)
    {
        if (!empty($row[self::CITY_FIELD])
            && !empty($row[self::LATITUDE_FIELD])
            && !empty($row[self::LONGITUDE_FIELD])
            && empty($this->latLongForCities[$row[self::CITY_FIELD]])
        ) {
            $this->latLongForCities[$row[self::CITY_FIELD]] = array($row[self::LATITUDE_FIELD], $row[self::LONGITUDE_FIELD]);
        }
    }

    protected function aggregateFromConversions()
    {
        $query = $this->getLogAggregator()->queryConversionsByDimension($this->dimensions);

        if ($query === false) {
            return;
        }

        while ($row = $query->fetch()) {
            $this->makeRegionCityLabelsUnique($row);

            /* @var $dataArray DataArray */
            foreach ($this->arrays as $dimension => $dataArray) {
                $dataArray->sumMetricsGoals($row[$dimension], $row);
            }
        }

        /* @var $dataArray DataArray */
        foreach ($this->arrays as $dataArray) {
            $dataArray->enrichMetricsWithConversions();
        }
    }

    protected function recordDayReports()
    {
        $tableCountry = ArchiveProcessor\Day::getDataTableFromDataArray($this->arrays[self::COUNTRY_FIELD]);
        $this->getProcessor()->insertBlobRecord(self::COUNTRY_RECORD_NAME, $tableCountry->getSerialized());
        $this->getProcessor()->insertNumericRecord(self::DISTINCT_COUNTRIES_METRIC, $tableCountry->getRowsCount());

        $tableRegion = ArchiveProcessor\Day::getDataTableFromDataArray($this->arrays[self::REGION_FIELD]);
        $serialized = $tableRegion->getSerialized($this->maximumRows, $this->maximumRows, Metrics::INDEX_NB_VISITS);
        $this->getProcessor()->insertBlobRecord(self::REGION_RECORD_NAME, $serialized);

        $tableCity = ArchiveProcessor\Day::getDataTableFromDataArray($this->arrays[self::CITY_FIELD]);
        $this->setLatitudeLongitude($tableCity);
        $serialized = $tableCity->getSerialized($this->maximumRows, $this->maximumRows, Metrics::INDEX_NB_VISITS);
        $this->getProcessor()->insertBlobRecord(self::CITY_RECORD_NAME, $serialized);
    }

    /**
     * Utility method, appends latitude/longitude pairs to city table labels, if that data
     * exists for the city.
     */
    private function setLatitudeLongitude(DataTable $tableCity)
    {
        foreach ($tableCity->getRows() as $row) {
            $label = $row->getColumn('label');
            if (isset($this->latLongForCities[$label])) {
                // get lat/long for city
                list($lat, $long) = $this->latLongForCities[$label];
                $lat = round($lat, LocationProvider::GEOGRAPHIC_COORD_PRECISION);
                $long = round($long, LocationProvider::GEOGRAPHIC_COORD_PRECISION);

                // set latitude + longitude metadata
                $row->setMetadata('lat', $lat);
                $row->setMetadata('long', $long);
            }
        }
    }

    public function archivePeriod()
    {
        $dataTableToSum = array(
            self::COUNTRY_RECORD_NAME,
            self::REGION_RECORD_NAME,
            self::CITY_RECORD_NAME,
        );

        $nameToCount = $this->getProcessor()->aggregateDataTableReports($dataTableToSum);
        $this->getProcessor()->insertNumericRecord(self::DISTINCT_COUNTRIES_METRIC,
            $nameToCount[self::COUNTRY_RECORD_NAME]['level0']);
    }
}