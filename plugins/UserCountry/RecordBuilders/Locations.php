<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserCountry\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Config as PiwikConfig;
use Piwik\DataTable;
use Piwik\DataAccess\LogAggregator;
use Piwik\Metrics;
use Piwik\Plugins\UserCountry\Archiver;
use Piwik\Plugins\UserCountry\LocationProvider;

class Locations extends RecordBuilder
{
    protected $dimensions = [
        Archiver::COUNTRY_RECORD_NAME => Archiver::COUNTRY_FIELD,
        Archiver::REGION_RECORD_NAME => Archiver::REGION_FIELD,
        Archiver::CITY_RECORD_NAME => Archiver::CITY_FIELD,
    ];

    private $latLongForCities = [];

    public function __construct()
    {
        parent::__construct();

        $this->columnToSortByBeforeTruncation = Metrics::INDEX_NB_VISITS;
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        $maxRowsInTable = PiwikConfig::getInstance()->General['datatable_archiving_maximum_rows_standard'];

        return [
            Record::make(Record::TYPE_BLOB, Archiver::COUNTRY_RECORD_NAME),
            Record::make(Record::TYPE_BLOB, Archiver::REGION_RECORD_NAME)
                ->setMaxRowsInTable($maxRowsInTable),
            Record::make(Record::TYPE_BLOB, Archiver::CITY_RECORD_NAME)
                ->setMaxRowsInTable($maxRowsInTable),

            Record::make(Record::TYPE_NUMERIC, Archiver::DISTINCT_COUNTRIES_METRIC)
                ->setIsCountOfBlobRecordRows(Archiver::COUNTRY_RECORD_NAME),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        try {
            /** @var DataTable[] $records */
            $records = [];
            foreach ($this->dimensions as $recordName => $dimension) {
                $records[$recordName] = new DataTable();
            }

            $logAggregator = $archiveProcessor->getLogAggregator();

            $this->aggregateFromVisits($records, $logAggregator);
            $this->aggregateFromConversions($records, $logAggregator);

            $this->setLatitudeLongitude($records[Archiver::CITY_RECORD_NAME]);

            $records[Archiver::DISTINCT_COUNTRIES_METRIC] = $records[Archiver::COUNTRY_RECORD_NAME]->getRowsCount();

            return $records;
        } finally {
            unset($this->latLongForCities);
        }
    }

    /**
     * @param DataTable[] $records
     */
    protected function aggregateFromVisits(array $records, LogAggregator $logAggregator): void
    {
        $additionalSelects = array('MAX(log_visit.location_latitude) as location_latitude',
            'MAX(log_visit.location_longitude) as location_longitude');
        $query = $logAggregator->queryVisitsByDimension(array_values($this->dimensions), $where = false, $additionalSelects);
        if ($query === false) {
            return;
        }

        while ($row = $query->fetch()) {
            $this->makeRegionCityLabelsUnique($row);
            $this->rememberCityLatLong($row);

            $columns = [
                Metrics::INDEX_NB_UNIQ_VISITORS => $row[Metrics::INDEX_NB_UNIQ_VISITORS],
                Metrics::INDEX_NB_VISITS => $row[Metrics::INDEX_NB_VISITS],
                Metrics::INDEX_NB_ACTIONS => $row[Metrics::INDEX_NB_ACTIONS],
                Metrics::INDEX_NB_USERS => $row[Metrics::INDEX_NB_USERS],
                Metrics::INDEX_MAX_ACTIONS => $row[Metrics::INDEX_MAX_ACTIONS],
                Metrics::INDEX_SUM_VISIT_LENGTH => $row[Metrics::INDEX_SUM_VISIT_LENGTH],
                Metrics::INDEX_BOUNCE_COUNT => $row[Metrics::INDEX_BOUNCE_COUNT],
                Metrics::INDEX_NB_VISITS_CONVERTED => $row[Metrics::INDEX_NB_VISITS_CONVERTED],
            ];

            foreach ($records as $recordName => $dataTable) {
                $dimension = $this->dimensions[$recordName];
                $dataTable->sumRowWithLabel($row[$dimension], $columns);
            }
        }
    }

    /**
     * Makes sure the region and city of a query row are unique.
     *
     * @param array $row
     */
    private function makeRegionCityLabelsUnique(array &$row): void
    {
        // remove the location separator from the region/city/country we get from the query
        foreach ($this->dimensions as $column) {
            $row[$column] = str_replace(Archiver::LOCATION_SEPARATOR, '', $row[$column] ?? '');
        }

        // set city first, as containing region might be manipulated afterwards if not empty
        if (!empty($row[Archiver::CITY_FIELD])) {
            $row[Archiver::CITY_FIELD] = $row[Archiver::CITY_FIELD] . Archiver::LOCATION_SEPARATOR . $row[Archiver::REGION_FIELD] . Archiver::LOCATION_SEPARATOR . $row[Archiver::COUNTRY_FIELD];
        }

        if (!empty($row[Archiver::REGION_FIELD])) {
            $row[Archiver::REGION_FIELD] = $row[Archiver::REGION_FIELD] . Archiver::LOCATION_SEPARATOR . $row[Archiver::COUNTRY_FIELD];
        }
    }

    protected function rememberCityLatLong(array $row): void
    {
        if (!empty($row[Archiver::CITY_FIELD])
            && !empty($row[Archiver::LATITUDE_FIELD])
            && !empty($row[Archiver::LONGITUDE_FIELD])
            && empty($this->latLongForCities[$row[Archiver::CITY_FIELD]])
        ) {
            $this->latLongForCities[$row[Archiver::CITY_FIELD]] = array($row[Archiver::LATITUDE_FIELD], $row[Archiver::LONGITUDE_FIELD]);
        }
    }

    /**
     * @param DataTable[] $records
     */
    protected function aggregateFromConversions(array $records, LogAggregator $logAggregator): void
    {
        $query = $logAggregator->queryConversionsByDimension(array_values($this->dimensions));

        while ($row = $query->fetch()) {
            $this->makeRegionCityLabelsUnique($row);

            foreach ($records as $recordName => $table) {
                $idGoal = (int) $row['idgoal'];
                $columns = [
                    Metrics::INDEX_GOALS => [
                        $idGoal => Metrics::makeGoalColumnsRow($idGoal, $row),
                    ],
                ];

                $dimension = $this->dimensions[$recordName];
                $table->sumRowWithLabel($row[$dimension], $columns);
            }
        }

        foreach ($records as $table) {
            $table->filter(DataTable\Filter\EnrichRecordWithGoalMetricSums::class);
        }
    }

    /**
     * Utility method, appends latitude/longitude pairs to city table labels, if that data
     * exists for the city.
     */
    private function setLatitudeLongitude(DataTable $tableCity): void
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
}
