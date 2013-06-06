<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_UserCountry
 */

class Piwik_UserCountry_Archiving
{
    const VISITS_BY_COUNTRY_RECORD_NAME = 'UserCountry_country';
    const VISITS_BY_REGION_RECORD_NAME = 'UserCountry_region';
    const VISITS_BY_CITY_RECORD_NAME = 'UserCountry_city';
    const DISTINCT_COUNTRIES_METRIC = 'UserCountry_distinctCountries';

    // separate region, city & country info in stored report labels
    const LOCATION_SEPARATOR = '|';

    private $latLongForCities = array();

    public function archiveDay($archiveProcessing)
    {
        $this->metricsByDimension = array('location_country' => array(),
                                          'location_region'  => array(),
                                          'location_city'    => array());
        $this->aggregateFromVisits($archiveProcessing);
        $this->aggregateFromConversions($archiveProcessing);
        $this->recordDayReports($archiveProcessing);
    }

    protected function aggregateFromVisits($archiveProcessing)
    {
        $dimensions = array_keys($this->metricsByDimension);
        $query = $archiveProcessing->queryVisitsByDimension(
            $dimensions,
            $where = '',
            $metrics = false,
            $orderBy = false,
            $rankingQuery = null,
            $addSelect = 'MAX(log_visit.location_latitude) as location_latitude,
						  MAX(log_visit.location_longitude) as location_longitude'
        );

        if ($query === false) {
            return;
        }

        while ($row = $query->fetch()) {
            $this->makeRegionCityLabelsUnique($row);
            $this->rememberCityLatLong($row);
            $this->aggregateVisit($archiveProcessing, $row);
        }
    }

    /**
     * Makes sure the region and city of a query row are unique.
     *
     * @param array $row
     */
    private function makeRegionCityLabelsUnique(&$row)
    {
        static $locationColumns = array('location_region', 'location_country', 'location_city');

        // to be on the safe side, remove the location separator from the region/city/country we
        // get from the query
        foreach ($locationColumns as $column) {
            $row[$column] = str_replace(self::LOCATION_SEPARATOR, '', $row[$column]);
        }

        if (!empty($row['location_region'])) // do not differentiate between unknown regions
        {
            $row['location_region'] = $row['location_region'] . self::LOCATION_SEPARATOR . $row['location_country'];
        }

        if (!empty($row['location_city'])) // do not differentiate between unknown cities
        {
            $row['location_city'] = $row['location_city'] . self::LOCATION_SEPARATOR . $row['location_region'];
        }
    }

    protected function rememberCityLatLong($row)
    {
        $lat = $long = false;
        if (!empty($row['location_city'])) {
            if (!empty($row['location_latitude'])) {
                $lat = $row['location_latitude'];
            }
            if (!empty($row['location_longitude'])) {
                $long = $row['location_longitude'];
            }
        }

        // store latitude/longitude, if we should
        if ($lat !== false && $long !== false
            && empty($this->latLongForCities[$row['location_city']])
        ) {
            $this->latLongForCities[$row['location_city']] = array($lat, $long);
        }
    }

    protected function aggregateVisit($archiveProcessing, $row)
    {
        foreach ($this->metricsByDimension as $dimension => &$table) {
            $label = (string)$row[$dimension];

            if (!isset($table[$label])) {
                $table[$label] = $archiveProcessing->makeEmptyRow();
            }
            $archiveProcessing->sumMetrics($row, $table[$label]);
        }
        return $table;
    }

    /**
     * @param Piwik_ArchiveProcessing_Day $archiveProcessing
     */
    protected function aggregateFromConversions($archiveProcessing)
    {
        $dimensions = array_keys($this->metricsByDimension);
        $query = $archiveProcessing->queryConversionsByDimension($dimensions);

        if ($query === false) {
            return;
        }

        while ($row = $query->fetch()) {
            $this->makeRegionCityLabelsUnique($row);

            $idGoal = $row['idgoal'];
            foreach ($this->metricsByDimension as $dimension => &$table) {
                $label = (string)$row[$dimension];

                if (!isset($table[$label][Piwik_Archive::INDEX_GOALS][$idGoal])) {
                    $table[$label][Piwik_Archive::INDEX_GOALS][$idGoal] = $archiveProcessing->makeEmptyGoalRow($idGoal);
                }
                $archiveProcessing->sumGoalMetrics($row, $table[$label][Piwik_Archive::INDEX_GOALS][$idGoal]);
            }
        }

        foreach ($this->metricsByDimension as &$table) {
            $archiveProcessing->enrichConversionsByLabelArray($table);
        }
    }

    protected function recordDayReports($archiveProcessing)
    {
        $maximumRows = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];

        $tableCountry = Piwik_ArchiveProcessing_Day::getDataTableFromArray($this->metricsByDimension['location_country']);
        $archiveProcessing->insertBlobRecord(self::VISITS_BY_COUNTRY_RECORD_NAME, $tableCountry->getSerialized());
        $archiveProcessing->insertNumericRecord(self::DISTINCT_COUNTRIES_METRIC, $tableCountry->getRowsCount());
        destroy($tableCountry);

        $tableRegion = Piwik_ArchiveProcessing_Day::getDataTableFromArray($this->metricsByDimension['location_region']);
        $serialized = $tableRegion->getSerialized($maximumRows, $maximumRows, Piwik_Archive::INDEX_NB_VISITS);
        $archiveProcessing->insertBlobRecord(self::VISITS_BY_REGION_RECORD_NAME, $serialized);
        destroy($tableRegion);

        $tableCity = Piwik_ArchiveProcessing_Day::getDataTableFromArray($this->metricsByDimension['location_city']);
        $this->setLatitudeLongitude($tableCity);
        $serialized = $tableCity->getSerialized($maximumRows, $maximumRows, Piwik_Archive::INDEX_NB_VISITS);
        $archiveProcessing->insertBlobRecord(self::VISITS_BY_CITY_RECORD_NAME, $serialized);
        destroy($tableCity);
    }

    /**
     * Utility method, appends latitude/longitude pairs to city table labels, if that data
     * exists for the city.
     */
    private function setLatitudeLongitude($tableCity)
    {
        foreach ($tableCity->getRows() as $row) {
            $label = $row->getColumn('label');
            if (isset($this->latLongForCities[$label])) {
                // get lat/long for city
                list($lat, $long) = $this->latLongForCities[$label];
                $lat = round($lat, Piwik_UserCountry_LocationProvider::GEOGRAPHIC_COORD_PRECISION);
                $long = round($long, Piwik_UserCountry_LocationProvider::GEOGRAPHIC_COORD_PRECISION);

                // set latitude + longitude metadata
                $row->setMetadata('lat', $lat);
                $row->setMetadata('long', $long);
            }
        }
    }

    public function archivePeriod($archiveProcessing)
    {
        $dataTableToSum = array(
            self::VISITS_BY_COUNTRY_RECORD_NAME,
            self::VISITS_BY_REGION_RECORD_NAME,
            self::VISITS_BY_CITY_RECORD_NAME,
        );

        $nameToCount = $archiveProcessing->archiveDataTable($dataTableToSum);
        $archiveProcessing->insertNumericRecord(self::DISTINCT_COUNTRIES_METRIC,
            $nameToCount[self::VISITS_BY_COUNTRY_RECORD_NAME]['level0']);
    }

}