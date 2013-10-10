<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik;

use Piwik\Archive\Parameters;
use Piwik\ArchiveProcessor\Rules;

use Piwik\DataAccess\ArchiveSelector;
use Piwik\Period\Range;

/**
 * The archive object is used to query specific data for a day or a period of statistics for a given website.
 *
 * Limitations:
 * - If you query w/ a range period, you can only query for ONE at a time.
 * - If you query w/ a non-range period, you can query for multiple periods, but they must
 *   all be of the same type (ie, day, week, month, year).
 *
 * Example:
 * <pre>
 *        $archive = Archive::build($idSite = 1, $period = 'week', '2008-03-08');
 *        $dataTable = $archive->getDataTable('Provider_hostnameExt');
 *        $dataTable->queueFilter('ReplaceColumnNames');
 *        return $dataTable;
 * </pre>
 *
 * Example bis:
 * <pre>
 *        $archive = Archive::build($idSite = 3, $period = 'day', $date = 'today');
 *        $nbVisits = $archive->getNumeric('nb_visits');
 *        return $nbVisits;
 * </pre>
 *
 * If the requested statistics are not yet processed, Archive uses ArchiveProcessor to archive the statistics.
 *
 * TODO: create ticket for this: when building archives, should use each site's timezone (ONLY FOR 'now').
 *
 * @package Piwik
 * @subpackage Archive
 *
 * @api
 */
class Archive
{
    const REQUEST_ALL_WEBSITES_FLAG = 'all';
    const ARCHIVE_ALL_PLUGINS_FLAG = 'all';
    const ID_SUBTABLE_LOAD_ALL_SUBTABLES = 'all';

    /**
     * List of archive IDs for the site, periods and segment we are querying with.
     * Archive IDs are indexed by done flag and period, ie:
     *
     * array(
     *     'done.Referrers' => array(
     *         '2010-01-01' => 1,
     *         '2010-01-02' => 2,
     *     ),
     *     'done.VisitsSummary' => array(
     *         '2010-01-01' => 3,
     *         '2010-01-02' => 4,
     *     ),
     * )
     *
     * or,
     *
     * array(
     *     'done.all' => array(
     *         '2010-01-01' => 1,
     *         '2010-01-02' => 2
     *     )
     * )
     *
     * @var array
     */
    private $idarchives = array();

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
     * @param Parameters $params
     * @param bool $forceIndexedBySite Whether to force index the result of a query by site ID.
     * @param bool $forceIndexedByDate Whether to force index the result of a query by period.
     */
    protected function __construct(Parameters $params, $forceIndexedBySite = false,
                                   $forceIndexedByDate = false)
    {
        $this->params = $params;
        $this->forceIndexedBySite = $forceIndexedBySite;
        $this->forceIndexedByDate = $forceIndexedByDate;
    }

    /**
     * Builds an Archive object using query parameter values.
     *
     * @param $idSites
     * @param string $period 'day', 'week', 'month', 'year' or 'range'
     * @param Date|string $strDate 'YYYY-MM-DD', magic keywords (ie, 'today'; @see Date::factory())
     *                                   or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD').
     * @param bool|string $segment Segment definition - defaults to false for backward compatibility.
     * @param bool|string $_restrictSitesToLogin Used only when running as a scheduled task.
     * @return \Piwik\Archive
     */
    public static function build($idSites, $period, $strDate, $segment = false, $_restrictSitesToLogin = false)
    {
        $websiteIds = Site::getIdSitesFromIdSitesString($idSites, $_restrictSitesToLogin);

        if (Period::isMultiplePeriod($strDate, $period)) {
            $oPeriod = new Range($period, $strDate);
            $allPeriods = $oPeriod->getSubperiods();
        } else {
            $timezone = count($websiteIds) == 1 ? Site::getTimezoneFor($websiteIds[0]) : false;
            $oPeriod = Period::makePeriodFromQueryParams($timezone, $period, $strDate);
            $allPeriods = array($oPeriod);
        }
        $segment = new Segment($segment, $websiteIds);
        $idSiteIsAll = $idSites == self::REQUEST_ALL_WEBSITES_FLAG;
        $isMultipleDate = Period::isMultiplePeriod($strDate, $period);
        return Archive::factory($segment, $allPeriods, $websiteIds, $idSiteIsAll, $isMultipleDate);
    }

    public static function factory(Segment $segment, array $periods, $idSites, $idSiteIsAll = false, $isMultipleDate = false)
    {
        $forceIndexedBySite = false;
        $forceIndexedByDate = false;
        if ($idSiteIsAll || count($idSites) > 1) {
            $forceIndexedBySite = true;
        }
        if (count($periods) > 1 || $isMultipleDate) {
            $forceIndexedByDate = true;
        }

        $params = new Parameters();
        $params->setIdSites($idSites);
        $params->setPeriods($periods);
        $params->setSegment($segment);

        return new Archive($params, $forceIndexedBySite, $forceIndexedByDate);
    }

    /**
     * Returns the value of the element $name from the current archive
     * The value to be returned is a numeric value and is stored in the archive_numeric_* tables
     *
     * @param string|array $names One or more archive names, eg, 'nb_visits', 'Referrers_distinctKeywords',
     *                            etc.
     * @return mixed              False if no value with the given name, numeric if only one site
     *                            and date and we're not forcing an index, and array if multiple
     *                            sites/dates are queried.
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
     * Returns the value of the elements in $names from the current archive.
     *
     * The value to be returned is a blob value and is stored in the archive_blob_* tables.
     *
     * It can return anything from strings, to serialized PHP arrays or PHP objects, etc.
     *
     * @param string|array $names One or more archive names, eg, 'Referrers_keywordBySearchEngine'.
     * @param null $idSubtable
     * @return string|array|bool  False if no value with the given name, numeric if only one site
     *                            and date and we're not forcing an index, and array if multiple
     *                            sites/dates are queried.
     */
    public function getBlob($names, $idSubtable = null)
    {
        $data = $this->get($names, 'blob', $idSubtable);
        return $data->getIndexedArray($this->getResultIndices());
    }

    /**
     * Returns the numeric values of the elements in $names as a DataTable.
     *
     * Note: Every DataTable instance returned will have at most one row in it.
     *
     * @param string|array $names One or more archive names, eg, 'nb_visits', 'Referrers_distinctKeywords',
     *                            etc.
     * @return DataTable|DataTable\Map|bool False if no value with the given names. Based on the number
     *                                      of sites/periods, the result can be a DataTable\Map, which
     *                                      contains DataTable instances.
     */
    public function getDataTableFromNumeric($names)
    {
        $data = $this->get($names, 'numeric');
        return $data->getDataTable($this->getResultIndices());
    }

    /**
     * This method will build a dataTable from the blob value $name in the current archive.
     *
     * For example $name = 'Referrers_searchEngineByKeyword' will return a
     * DataTable containing all the keywords. If a $idSubtable is given, the method
     * will return the subTable of $name. If 'all' is supplied for $idSubtable every subtable
     * will be returned.
     *
     * @param string $name The name of the record to get.
     * @param int|string|null $idSubtable The subtable ID (if any) or 'all' if requesting every datatable.
     * @return DataTable|DataTable\Map|bool
     */
    public function getDataTable($name, $idSubtable = null)
    {
        $data = $this->get($name, 'blob', $idSubtable);
        return $data->getDataTable($this->getResultIndices());
    }

    /**
     * Same as getDataTable() except that it will also load in memory all the subtables
     * for the DataTable $name. You can then access the subtables by using the
     * Manager::getTable() function.
     *
     * @param string $name The name of the record to get.
     * @param int|string|null $idSubtable The subtable ID (if any) or self::ID_SUBTABLE_LOAD_ALL_SUBTABLES
     *                                    if requesting every datatable.
     * @param int|null $depth The maximum number of subtable levels to load. If null, all levels are loaded.
     * @param bool $addMetadataSubtableId Whether to add the DB subtable ID as metadata to each datatable,
     *                                    or not.
     * @return DataTable
     */
    public function getDataTableExpanded($name, $idSubtable = null, $depth = null, $addMetadataSubtableId = true)
    {
        $data = $this->get($name, 'blob', self::ID_SUBTABLE_LOAD_ALL_SUBTABLES);
        return $data->getExpandedDataTable($this->getResultIndices(), $idSubtable, $depth, $addMetadataSubtableId);
    }

    /**
     * Returns the list of plugins that archive the given reports.
     *
     * @param array $archiveNames
     * @return array
     */
    public function getRequestedPlugins($archiveNames)
    {
        $result = array();
        foreach ($archiveNames as $name) {
            $result[] = self::getPluginForReport($name);
        }
        return array_unique($result);
    }

    /**
     * Helper - Loads a DataTable from the Archive.
     * Optionally loads the table recursively,
     * or optionally fetches a given subtable with $idSubtable
     *
     * @param string $name
     * @param int $idSite
     * @param string $period
     * @param Date $date
     * @param string $segment
     * @param bool $expanded
     * @param int|null $idSubtable
     * @param int|null $depth
     *
     * @return DataTable|DataTable\Map
     */
    public static function getDataTableFromArchive($name, $idSite, $period, $date, $segment, $expanded,
                                                   $idSubtable = null, $depth = null)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        if ($idSubtable === false) {
            $idSubtable = null;
        }

        if ($expanded) {
            $dataTable = $archive->getDataTableExpanded($name, $idSubtable, $depth);
        } else {
            $dataTable = $archive->getDataTable($name, $idSubtable);
        }

        $dataTable->queueFilter('ReplaceSummaryRowLabel');

        return $dataTable;
    }

    private function appendIdSubtable($recordName, $id)
    {
        return $recordName . "_" . $id;
    }

    /**
     * Queries archive tables for data and returns the result.
     * @param array|string $archiveNames
     * @param $archiveDataType
     * @param null|int $idSubtable
     * @return Archive\DataCollection
     */
    private function get($archiveNames, $archiveDataType, $idSubtable = null)
    {
        if (!is_array($archiveNames)) {
            $archiveNames = array($archiveNames);
        }

        // apply idSubtable
        if ($idSubtable !== null
            && $idSubtable != self::ID_SUBTABLE_LOAD_ALL_SUBTABLES
        ) {
            foreach ($archiveNames as &$name) {
                $name = $this->appendIdsubtable($name, $idSubtable);
            }
        }

        $result = new Archive\DataCollection(
            $archiveNames, $archiveDataType, $this->params->getIdSites(), $this->params->getPeriods(), $defaultRow = null);

        $archiveIds = $this->getArchiveIds($archiveNames);
        if (empty($archiveIds)) {
            return $result;
        }

        $loadAllSubtables = $idSubtable == self::ID_SUBTABLE_LOAD_ALL_SUBTABLES;
        $archiveData = ArchiveSelector::getArchiveData($archiveIds, $archiveNames, $archiveDataType, $loadAllSubtables);
        foreach ($archiveData as $row) {
            // values are grouped by idsite (site ID), date1-date2 (date range), then name (field name)
            $idSite = $row['idsite'];
            $periodStr = $row['date1'] . "," . $row['date2'];

            if ($archiveDataType == 'numeric') {
                $value = $this->formatNumericValue($row['value']);
            } else {
                $value = $this->uncompress($row['value']);
                $result->addMetadata($idSite, $periodStr, 'ts_archived', $row['ts_archived']);
            }

            $resultRow = & $result->get($idSite, $periodStr);
            $resultRow[$row['name']] = $value;
        }

        return $result;
    }

    /**
     * Returns archive IDs for the sites, periods and archive names that are being
     * queried. This function will use the idarchive cache if it has the right data,
     * query archive tables for IDs w/o launching archiving, or launch archiving and
     * get the idarchive from ArchiveProcessor instances.
     */
    private function getArchiveIds($archiveNames)
    {
        $plugins = $this->getRequestedPlugins($archiveNames);

        // figure out which archives haven't been processed (if an archive has been processed,
        // then we have the archive IDs in $this->idarchives)
        $doneFlags = array();
        $archiveGroups = array();
        foreach ($plugins as $plugin) {
            $doneFlag = $this->getDoneStringForPlugin($plugin);

            $doneFlags[$doneFlag] = true;
            if (!isset($this->idarchives[$doneFlag])) {
                $archiveGroups[] = $this->getArchiveGroupOfPlugin($plugin);
            }
        }

        $archiveGroups = array_unique($archiveGroups);

        // cache id archives for plugins we haven't processed yet
        if (!empty($archiveGroups)) {
            if (!Rules::isArchivingDisabledFor($this->params->getSegment(), $this->getPeriodLabel())) {
                $this->cacheArchiveIdsAfterLaunching($archiveGroups, $plugins);
            } else {
                $this->cacheArchiveIdsWithoutLaunching($plugins);
            }
        }

        // order idarchives by the table month they belong to
        $idArchivesByMonth = array();
        foreach (array_keys($doneFlags) as $doneFlag) {
            if (empty($this->idarchives[$doneFlag])) {
                continue;
            }

            foreach ($this->idarchives[$doneFlag] as $dateRange => $idarchives) {
                foreach ($idarchives as $id) {
                    $idArchivesByMonth[$dateRange][] = $id;
                }
            }
        }

        return $idArchivesByMonth;
    }

    /**
     * @return Parameters
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Gets the IDs of the archives we're querying for and stores them in $this->archives.
     * This function will launch the archiving process for each period/site/plugin if
     * metrics/reports have not been calculated/archived already.
     *
     * @param array $archiveGroups @see getArchiveGroupOfReport
     * @param array $plugins List of plugin names to archive.
     */
    private function cacheArchiveIdsAfterLaunching($archiveGroups, $plugins)
    {
        $today = Date::today();

        /* @var Period $period */
        foreach ($this->params->getPeriods() as $period) {
            $periodStr = $period->getRangeString();

            $twoDaysBeforePeriod = $period->getDateStart()->subDay(2);
            $twoDaysAfterPeriod = $period->getDateEnd()->addDay(2);

            foreach ($this->params->getIdSites() as $idSite) {
                $site = new Site($idSite);

                // if the END of the period is BEFORE the website creation date
                // we already know there are no stats for this period
                // we add one day to make sure we don't miss the day of the website creation
                if ($twoDaysAfterPeriod->isEarlier($site->getCreationDate())) {
                    Log::verbose("Archive site %s, %s (%s) skipped, archive is before the website was created.",
                        $idSite, $period->getLabel(), $period->getPrettyString());
                    continue;
                }

                // if the starting date is in the future we know there is no visiidsite = ?t
                if ($twoDaysBeforePeriod->isLater($today)) {
                    Log::verbose("Archive site %s, %s (%s) skipped, archive is after today.",
                        $idSite, $period->getLabel(), $period->getPrettyString());
                    continue;
                }

                if ($period->getLabel() == 'day') {
                    $processing = new ArchiveProcessor\Day($period, $site, $this->params->getSegment());
                } else {
                    $processing = new ArchiveProcessor\Period($period, $site, $this->params->getSegment());
                }

                // process for each plugin as well
                foreach ($archiveGroups as $plugin) {
                    if ($plugin == self::ARCHIVE_ALL_PLUGINS_FLAG) {
                        $plugin = reset($plugins);
                    }

                    $doneFlag = $this->getDoneStringForPlugin($plugin);
                    $this->initializeArchiveIdCache($doneFlag);

                    $idArchive = $processing->preProcessArchive($plugin);

                    $visits = $processing->getNumberOfVisits();
                    if ($visits > 0) {
                        $this->idarchives[$doneFlag][$periodStr][] = $idArchive;
                    }
                }
            }
        }
    }

    /**
     * Gets the IDs of the archives we're querying for and stores them in $this->archives.
     * This function will not launch the archiving process (and is thus much, much faster
     * than cacheArchiveIdsAfterLaunching).
     *
     * @param array $plugins List of plugin names from which data is being requested.
     */
    private function cacheArchiveIdsWithoutLaunching($plugins)
    {
        $idarchivesByReport = ArchiveSelector::getArchiveIds(
            $this->params->getIdSites(), $this->params->getPeriods(), $this->params->getSegment(), $plugins);

        // initialize archive ID cache for each report
        foreach ($plugins as $plugin) {
            $doneFlag = $this->getDoneStringForPlugin($plugin);
            $this->initializeArchiveIdCache($doneFlag);
        }

        foreach ($idarchivesByReport as $doneFlag => $idarchivesByDate) {
            foreach ($idarchivesByDate as $dateRange => $idarchives) {
                foreach ($idarchives as $idarchive) {
                    $this->idarchives[$doneFlag][$dateRange][] = $idarchive;
                }
            }
        }
    }

    /**
     * Returns the done string flag for a plugin using this instance's segment & periods.
     * @param string $plugin
     * @return string
     */
    private function getDoneStringForPlugin($plugin)
    {
        return Rules::getDoneStringFlagFor($this->params->getSegment(), $this->getPeriodLabel(), $plugin);
    }

    private function getPeriodLabel()
    {
        $periods = $this->params->getPeriods();
        return reset($periods)->getLabel();
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

    private function uncompress($data)
    {
        return @gzuncompress($data);
    }

    /**
     * Initializes the archive ID cache ($this->idarchives) for a particular 'done' flag.
     *
     * It is necessary that each archive ID caching function call this method for each
     * unique 'done' flag it encounters, since the getArchiveIds function determines
     * whether archiving should be launched based on whether $this->idarchives has a
     * an entry for a specific 'done' flag.
     *
     * If this function is not called, then periods with no visits will not add
     * entries to the cache. If the archive is used again, SQL will be executed to
     * try and find the archive IDs even though we know there are none.
     */
    private function initializeArchiveIdCache($doneFlag)
    {
        if (!isset($this->idarchives[$doneFlag])) {
            $this->idarchives[$doneFlag] = array();
        }
    }

    /**
     * Returns the archiving group identifier given a plugin.
     *
     * More than one plugin can be called at once when archiving. In such a case
     * we don't want to launch archiving three times for three plugins if doing
     * it once is enough, so getArchiveIds makes sure to get the archive group of
     * all reports.
     *
     * If the period isn't a range, then all plugins' archiving code is executed.
     * If the period is a range, then archiving code is executed individually for
     * each plugin.
     */
    private function getArchiveGroupOfPlugin($plugin)
    {
        if ($this->getPeriodLabel() != 'range') {
            return self::ARCHIVE_ALL_PLUGINS_FLAG;
        }

        return $plugin;
    }

    /**
     * Returns the name of the plugin that archives a given report.
     *
     * @param string $report Archive data name, ie, 'nb_visits', 'UserSettings_...', etc.
     * @throws \Exception
     * @return string
     */
    public static function getPluginForReport($report)
    {
        // Core metrics are always processed in Core, for the requested date/period/segment
        if (in_array($report, Metrics::getVisitsMetricNames())) {
            $report = 'VisitsSummary_CoreMetrics';
        } // Goal_* metrics are processed by the Goals plugin (HACK)
        else if (strpos($report, 'Goal_') === 0) {
            $report = 'Goals_Metrics';
        }

        $plugin = substr($report, 0, strpos($report, '_'));
        if (empty($plugin)
            || !\Piwik\Plugin\Manager::getInstance()->isPluginActivated($plugin)
        ) {
            $pluginStr = empty($plugin) ? '' : "($plugin)";
            throw new \Exception("Error: The report '$report' was requested but it is not available "
                . "at this stage. You may also disable the related plugin $pluginStr "
                . "to avoid this error.");
        }
        return $plugin;
    }
}
