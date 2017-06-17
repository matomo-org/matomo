<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Archive\ArchiveQueryFactory;
use Piwik\Archive\ArchiveTableStore;
use Piwik\Archive\Parameters;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveSelector;
use Piwik\Period\Factory as PeriodFactory;

/**
 * The **Archive** class is used to query cached analytics statistics
 * (termed "archive data").
 *
 * You can use **Archive** instances to get data that was archived for one or more sites,
 * for one or more periods and one optional segment.
 *
 * If archive data is not found, this class will initiate the archiving process. [1](#footnote-1)
 *
 * **Archive** instances must be created using the {@link build()} factory method;
 * they cannot be constructed.
 *
 * You can search for metrics (such as `nb_visits`) using the {@link getNumeric()} and
 * {@link getDataTableFromNumeric()} methods. You can search for
 * reports using the {@link getBlob()}, {@link getDataTable()} and {@link getDataTableExpanded()} methods.
 *
 * If you're creating an API that returns report data, you may want to use the
 * {@link createDataTableFromArchive()} helper function.
 *
 * ### Learn more
 *
 * Learn more about _archiving_ [here](/guides/all-about-analytics-data).
 *
 * ### Limitations
 *
 * - You cannot get data for multiple range periods in a single query.
 * - You cannot get data for periods of different types in a single query.
 *
 * ### Examples
 *
 * **_Querying metrics for an API method_**
 *
 *     // one site and one period
 *     $archive = Archive::build($idSite = 1, $period = 'week', $date = '2013-03-08');
 *     return $archive->getDataTableFromNumeric(array('nb_visits', 'nb_actions'));
 *
 *     // all sites and multiple dates
 *     $archive = Archive::build($idSite = 'all', $period = 'month', $date = '2013-01-02,2013-03-08');
 *     return $archive->getDataTableFromNumeric(array('nb_visits', 'nb_actions'));
 *
 * **_Querying and using metrics immediately_**
 *
 *     // one site and one period
 *     $archive = Archive::build($idSite = 1, $period = 'week', $date = '2013-03-08');
 *     $data = $archive->getNumeric(array('nb_visits', 'nb_actions'));
 *
 *     $visits = $data['nb_visits'];
 *     $actions = $data['nb_actions'];
 *
 *     // ... do something w/ metric data ...
 *
 *     // multiple sites and multiple dates
 *     $archive = Archive::build($idSite = '1,2,3', $period = 'month', $date = '2013-01-02,2013-03-08');
 *     $data = $archive->getNumeric('nb_visits');
 *
 *     $janSite1Visits = $data['1']['2013-01-01,2013-01-31']['nb_visits'];
 *     $febSite1Visits = $data['1']['2013-02-01,2013-02-28']['nb_visits'];
 *     // ... etc.
 *
 * **_Querying for reports_**
 *
 *     $archive = Archive::build($idSite = 1, $period = 'week', $date = '2013-03-08');
 *     $dataTable = $archive->getDataTable('MyPlugin_MyReport');
 *     // ... manipulate $dataTable ...
 *     return $dataTable;
 *
 * **_Querying a report for an API method_**
 *
 *     public function getMyReport($idSite, $period, $date, $segment = false, $expanded = false)
 *     {
 *         $dataTable = Archive::createDataTableFromArchive('MyPlugin_MyReport', $idSite, $period, $date, $segment, $expanded);
 *         return $dataTable;
 *     }
 *
 * **_Querying data for multiple range periods_**
 *
 *     // get data for first range
 *     $archive = Archive::build($idSite = 1, $period = 'range', $date = '2013-03-08,2013-03-12');
 *     $dataTable = $archive->getDataTableFromNumeric(array('nb_visits', 'nb_actions'));
 *
 *     // get data for second range
 *     $archive = Archive::build($idSite = 1, $period = 'range', $date = '2013-03-15,2013-03-20');
 *     $dataTable = $archive->getDataTableFromNumeric(array('nb_visits', 'nb_actions'));
 *
 * <a name="footnote-1"></a>
 * [1]: The archiving process will not be launched if browser archiving is disabled
 *      and the current request came from a browser.
 *
 *
 * @api
 */
class Archive
{
    const REQUEST_ALL_WEBSITES_FLAG = 'all';
    const ARCHIVE_ALL_PLUGINS_FLAG = 'all';
    const ID_SUBTABLE_LOAD_ALL_SUBTABLES = 'all';

    /**
     * If set to true, the result of all get functions (ie, getNumeric, getBlob, etc.)
     * will be indexed by the site ID, even if we're only querying data for one site.
     *
     * @var bool
     */
    private $forceIndexedBySite;

    /**
     * If set to true, the result of all get functions (ie, getNumeric, getBlob, etc.)
     * will be indexed by the period, even if we're only querying data for one period.
     *
     * @var bool
     */
    private $forceIndexedByDate;

    /**
     * @var Parameters
     */
    private $params;

    /**
     * @var ArchiveTableStore
     */
    private $archiveTableStore;

    /**
     * @param Parameters $params
     * @param bool $forceIndexedBySite Whether to force index the result of a query by site ID.
     * @param bool $forceIndexedByDate Whether to force index the result of a query by period.
     */
    public function __construct(Parameters $params, $forceIndexedBySite = false,
                                   $forceIndexedByDate = false, ArchiveTableStore $archiveTableStore = null)
    {
        $this->params = $params;
        $this->forceIndexedBySite = $forceIndexedBySite;
        $this->forceIndexedByDate = $forceIndexedByDate;

        $this->archiveTableStore = $archiveTableStore ?: StaticContainer::get(ArchiveTableStore::class);
    }

    /**
     * Returns a new Archive instance that will query archive data for the given set of
     * sites and periods, using an optional Segment.
     *
     * This method uses data that is found in query parameters, so the parameters to this
     * function can be string values.
     *
     * If you want to create an Archive instance with an array of Period instances, use
     * {@link Archive::factory()}.
     *
     * @param string|int|array $idSites A single ID (eg, `'1'`), multiple IDs (eg, `'1,2,3'` or `array(1, 2, 3)`),
     *                                  or `'all'`.
     * @param string $period 'day', `'week'`, `'month'`, `'year'` or `'range'`
     * @param Date|string $strDate 'YYYY-MM-DD', magic keywords (ie, 'today'; {@link Date::factory()}
     *                             or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD').
     * @param bool|false|string $segment Segment definition or false if no segment should be used. {@link Piwik\Segment}
     * @param bool|false|string $_restrictSitesToLogin Used only when running as a scheduled task.
     * @return static
     */
    public static function build($idSites, $period, $strDate, $segment = false, $_restrictSitesToLogin = false)
    {
        return StaticContainer::get(ArchiveQueryFactory::class)->build($idSites, $period, $strDate, $segment,
            $_restrictSitesToLogin);
    }

    /**
     * Returns a new Archive instance that will query archive data for the given set of
     * sites and periods, using an optional segment.
     *
     * This method uses an array of Period instances and a Segment instance, instead of strings
     * like {@link build()}.
     *
     * If you want to create an Archive instance using data found in query parameters,
     * use {@link build()}.
     *
     * @param Segment $segment The segment to use. For no segment, use `new Segment('', $idSites)`.
     * @param array $periods An array of Period instances.
     * @param array $idSites An array of site IDs (eg, `array(1, 2, 3)`).
     * @param bool $idSiteIsAll Whether `'all'` sites are being queried or not. If true, then
     *                          the result of querying functions will be indexed by site, regardless
     *                          of whether `count($idSites) == 1`.
     * @param bool $isMultipleDate Whether multiple dates are being queried or not. If true, then
     *                             the result of querying functions will be indexed by period,
     *                             regardless of whether `count($periods) == 1`.
     *
     * @return Archive
     */
    public static function factory(Segment $segment, array $periods, array $idSites, $idSiteIsAll = false, $isMultipleDate = false)
    {
        return StaticContainer::get(ArchiveQueryFactory::class)->factory($segment, $periods, $idSites, $idSiteIsAll,
            $isMultipleDate);
    }

    /**
     * Queries and returns metric data in an array.
     *
     * If multiple sites were requested in {@link build()} or {@link factory()} the result will
     * be indexed by site ID.
     *
     * If multiple periods were requested in {@link build()} or {@link factory()} the result will
     * be indexed by period.
     *
     * The site ID index is always first, so if multiple sites & periods were requested, the result
     * will be indexed by site ID first, then period.
     *
     * @param string|array $names One or more archive names, eg, `'nb_visits'`, `'Referrers_distinctKeywords'`,
     *                            etc.
     * @return false|integer|array `false` if there is no data to return, a single numeric value if we're not querying
     *                             for multiple sites/periods, or an array if multiple sites, periods or names are
     *                             queried for.
     */
    public function getNumeric($names)
    {
        $data = $this->get($names, 'numeric');

        $resultIndices = $this->getResultIndices();
        $result = $data->getIndexedArray($resultIndices);

        // if only one metric is returned, just return it as a numeric value
        if (empty($resultIndices)
            && count($result) <= 1
            && (!is_array($names) || count($names) == 1)
        ) {
            $result = (float)reset($result); // convert to float in case $result is empty
        }

        return $result;
    }

    /**
     * Queries and returns metric data in a DataTable instance.
     *
     * If multiple sites were requested in {@link build()} or {@link factory()} the result will
     * be a DataTable\Map that is indexed by site ID.
     *
     * If multiple periods were requested in {@link build()} or {@link factory()} the result will
     * be a {@link DataTable\Map} that is indexed by period.
     *
     * The site ID index is always first, so if multiple sites & periods were requested, the result
     * will be a {@link DataTable\Map} indexed by site ID which contains {@link DataTable\Map} instances that are
     * indexed by period.
     *
     * _Note: Every DataTable instance returned will have at most one row in it. The contents of each
     *        row will be the requested metrics for the appropriate site and period._
     *
     * @param string|array $names One or more archive names, eg, 'nb_visits', 'Referrers_distinctKeywords',
     *                            etc.
     * @return DataTable|DataTable\Map A DataTable if multiple sites and periods were not requested.
     *                                 An appropriately indexed DataTable\Map if otherwise.
     */
    public function getDataTableFromNumeric($names)
    {
        $data = $this->get($names, 'numeric');
        return $data->getDataTable($this->getResultIndices());
    }

    /**
     * Similar to {@link getDataTableFromNumeric()} but merges all children on the created DataTable.
     *
     * This is the same as doing `$this->getDataTableFromNumeric()->mergeChildren()` but this way it is much faster.
     *
     * @return DataTable|DataTable\Map
     *
     * @internal Currently only used by MultiSites.getAll plugin. Feel free to remove internal tag if needed somewhere
     *           else. If no longer needed by MultiSites.getAll please remove this method. If you need this to work in
     *           a bit different way feel free to refactor as always.
     */
    public function getDataTableFromNumericAndMergeChildren($names)
    {
        $data  = $this->get($names, 'numeric');
        $resultIndexes = $this->getResultIndices();
        return $data->getMergedDataTable($resultIndexes);
    }

    /**
     * Queries and returns one or more reports as DataTable instances.
     *
     * This method will query blob data that is a serialized array of of {@link DataTable\Row}'s and
     * unserialize it.
     *
     * If multiple sites were requested in {@link build()} or {@link factory()} the result will
     * be a {@link DataTable\Map} that is indexed by site ID.
     *
     * If multiple periods were requested in {@link build()} or {@link factory()} the result will
     * be a DataTable\Map that is indexed by period.
     *
     * The site ID index is always first, so if multiple sites & periods were requested, the result
     * will be a {@link DataTable\Map} indexed by site ID which contains {@link DataTable\Map} instances that are
     * indexed by period.
     *
     * @param string $name The name of the record to get. This method can only query one record at a time.
     * @param int|string|null $idSubtable The ID of the subtable to get (if any).
     * @return DataTable|DataTable\Map A DataTable if multiple sites and periods were not requested.
     *                                 An appropriately indexed {@link DataTable\Map} if otherwise.
     */
    public function getDataTable($name, $idSubtable = null)
    {
        $data = $this->get($name, 'blob', $idSubtable);
        return $data->getDataTable($this->getResultIndices());
    }

    /**
     * Queries and returns one report with all of its subtables loaded.
     *
     * If multiple sites were requested in {@link build()} or {@link factory()} the result will
     * be a DataTable\Map that is indexed by site ID.
     *
     * If multiple periods were requested in {@link build()} or {@link factory()} the result will
     * be a DataTable\Map that is indexed by period.
     *
     * The site ID index is always first, so if multiple sites & periods were requested, the result
     * will be a {@link DataTable\Map indexed} by site ID which contains {@link DataTable\Map} instances that are
     * indexed by period.
     *
     * @param string $name The name of the record to get.
     * @param int|string|null $idSubtable The ID of the subtable to get (if any). The subtable will be expanded.
     * @param int|null $depth The maximum number of subtable levels to load. If null, all levels are loaded.
     *                        For example, if `1` is supplied, then the DataTable returned will have its subtables
     *                        loaded. Those subtables, however, will NOT have their subtables loaded.
     * @param bool $addMetadataSubtableId Whether to add the database subtable ID as metadata to each datatable,
     *                                    or not.
     * @return DataTable|DataTable\Map
     */
    public function getDataTableExpanded($name, $idSubtable = null, $depth = null, $addMetadataSubtableId = true)
    {
        $data = $this->get($name, 'blob', self::ID_SUBTABLE_LOAD_ALL_SUBTABLES);
        return $data->getExpandedDataTable($this->getResultIndices(), $idSubtable, $depth, $addMetadataSubtableId);
    }

    /**
     * Returns an object describing the set of sites, the set of periods and the segment
     * this Archive will query data for.
     *
     * @return Parameters
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Helper function that creates an Archive instance and queries for report data using
     * query parameter data. API methods can use this method to reduce code redundancy.
     *
     * @param string $recordName The name of the report to return.
     * @param int|string|array $idSite @see {@link build()}
     * @param string $period @see {@link build()}
     * @param string $date @see {@link build()}
     * @param string $segment @see {@link build()}
     * @param bool $expanded If true, loads all subtables. See {@link getDataTableExpanded()}
     * @param bool $flat If true, loads all subtables and disabled all recursive filters.
     * @param int|null $idSubtable See {@link getDataTableExpanded()}
     * @param int|null $depth See {@link getDataTableExpanded()}
     * @return DataTable|DataTable\Map
     */
    public static function createDataTableFromArchive($recordName, $idSite, $period, $date, $segment, $expanded = false, $flat = false, $idSubtable = null, $depth = null)
    {
        Piwik::checkUserHasViewAccess($idSite);

        if ($flat && !$idSubtable) {
            $expanded = true;
        }

        $archive = Archive::build($idSite, $period, $date, $segment, $_restrictSitesToLogin = false);
        if ($idSubtable === false) {
            $idSubtable = null;
        }

        if ($expanded) {
            $dataTable = $archive->getDataTableExpanded($recordName, $idSubtable, $depth);
        } else {
            $dataTable = $archive->getDataTable($recordName, $idSubtable);
        }

        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        $dataTable->queueFilter('ReplaceColumnNames');

        if ($expanded) {
            $dataTable->queueFilterSubtables('ReplaceColumnNames');
        }

        if ($flat) {
            $dataTable->disableRecursiveFilters();
        }

        return $dataTable;
    }

    /**
     * Queries archive tables for data and returns the result.
     * @param array|string $archiveNames
     * @param $archiveDataType
     * @param null|int $idSubtable
     * @return Archive\DataCollection
     */
    protected function get($archiveNames, $archiveDataType, $idSubtable = null)
    {
        if (!is_array($archiveNames)) {
            $archiveNames = array($archiveNames);
        }

        // apply idSubtable
        if ($idSubtable !== null
            && $idSubtable != self::ID_SUBTABLE_LOAD_ALL_SUBTABLES
        ) {
            // this is also done in ArchiveSelector. It should be actually only done in ArchiveSelector but DataCollection
            // does require to have the subtableId appended. Needs to be changed in refactoring to have it only in one
            // place.
            $dataNames = array();
            foreach ($archiveNames as $name) {
                $dataNames[] = ArchiveSelector::appendIdsubtable($name, $idSubtable);
            }
        } else {
            $dataNames = $archiveNames;
        }

        $result = new Archive\DataCollection(
            $dataNames, $archiveDataType, $this->params->getIdSites(), $this->params->getPeriods(), $defaultRow = null);

        $archiveIds = $this->archiveTableStore->getArchiveIds($this->params, $archiveNames);

        if (empty($archiveIds)) {
            return $result;
        }

        $archiveData = $this->archiveTableStore->getArchiveData($archiveIds, $archiveNames, $archiveDataType, $idSubtable);

        $isNumeric = $archiveDataType == 'numeric';

        foreach ($archiveData as $row) {
            // values are grouped by idsite (site ID), date1-date2 (date range), then name (field name)
            $periodStr = $row['date1'] . ',' . $row['date2'];

            if ($isNumeric) {
                $row['value'] = $this->formatNumericValue($row['value']);
            } else {
                $result->addMetadata($row['idsite'], $periodStr, DataTable::ARCHIVED_DATE_METADATA_NAME, $row['ts_archived']);
            }

            $result->set($row['idsite'], $periodStr, $row['name'], $row['value']);
        }

        return $result;
    }



    /**
     * Returns an array describing what metadata to use when indexing a query result.
     * For use with DataCollection.
     *
     * @return array
     */
    private function getResultIndices()
    {
        $indices = array();

        if (count($this->params->getIdSites()) > 1
            || $this->forceIndexedBySite
        ) {
            $indices['site'] = 'idSite';
        }

        if (count($this->params->getPeriods()) > 1
            || $this->forceIndexedByDate
        ) {
            $indices['period'] = 'date';
        }

        return $indices;
    }

    private function formatNumericValue($value)
    {
        // If there is no dot, we return as is
        // Note: this could be an integer bigger than 32 bits
        if (strpos($value, '.') === false) {
            if ($value === false) {
                return 0;
            }
            return (float)$value;
        }

        // Round up the value with 2 decimals
        // we cast the result as float because returns false when no visitors
        return round((float)$value, 2);
    }
}
