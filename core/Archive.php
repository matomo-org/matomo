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
 *        $archive = Piwik_Archive::build($idSite = 1, $period = 'week', '2008-03-08');
 *        $dataTable = $archive->getDataTable('Provider_hostnameExt');
 *        $dataTable->queueFilter('ReplaceColumnNames');
 *        return $dataTable;
 * </pre>
 *
 * Example bis:
 * <pre>
 *        $archive = Piwik_Archive::build($idSite = 3, $period = 'day', $date = 'today');
 *        $nbVisits = $archive->getNumeric('nb_visits');
 *        return $nbVisits;
 * </pre>
 *
 * If the requested statistics are not yet processed, Archive uses ArchiveProcessing to archive the statistics.
 * 
 * TODO: create ticket for this: when building archives, should use each site's timezone (ONLY FOR 'now'). 
 * 
 * @package Piwik
 * @subpackage Piwik_Archive
 */
class Piwik_Archive
{
    /**
     * When saving DataTables in the DB, we sometimes replace the columns name by these IDs so we save up lots of bytes
     * Eg. INDEX_NB_UNIQ_VISITORS is an integer: 4 bytes, but 'nb_uniq_visitors' is 16 bytes at least
     * (in php it's actually even much more)
     *
     */
    const INDEX_NB_UNIQ_VISITORS = 1;
    const INDEX_NB_VISITS = 2;
    const INDEX_NB_ACTIONS = 3;
    const INDEX_MAX_ACTIONS = 4;
    const INDEX_SUM_VISIT_LENGTH = 5;
    const INDEX_BOUNCE_COUNT = 6;
    const INDEX_NB_VISITS_CONVERTED = 7;
    const INDEX_NB_CONVERSIONS = 8;
    const INDEX_REVENUE = 9;
    const INDEX_GOALS = 10;
    const INDEX_SUM_DAILY_NB_UNIQ_VISITORS = 11;

    // Specific to the Actions reports
    const INDEX_PAGE_NB_HITS = 12;
    const INDEX_PAGE_SUM_TIME_SPENT = 13;

    const INDEX_PAGE_EXIT_NB_UNIQ_VISITORS = 14;
    const INDEX_PAGE_EXIT_NB_VISITS = 15;
    const INDEX_PAGE_EXIT_SUM_DAILY_NB_UNIQ_VISITORS = 16;

    const INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS = 17;
    const INDEX_PAGE_ENTRY_SUM_DAILY_NB_UNIQ_VISITORS = 18;
    const INDEX_PAGE_ENTRY_NB_VISITS = 19;
    const INDEX_PAGE_ENTRY_NB_ACTIONS = 20;
    const INDEX_PAGE_ENTRY_SUM_VISIT_LENGTH = 21;
    const INDEX_PAGE_ENTRY_BOUNCE_COUNT = 22;

    // Ecommerce Items reports
    const INDEX_ECOMMERCE_ITEM_REVENUE = 23;
    const INDEX_ECOMMERCE_ITEM_QUANTITY = 24;
    const INDEX_ECOMMERCE_ITEM_PRICE = 25;
    const INDEX_ECOMMERCE_ORDERS = 26;
    const INDEX_ECOMMERCE_ITEM_PRICE_VIEWED = 27;

    // Site Search
    const INDEX_SITE_SEARCH_HAS_NO_RESULT = 28;
    const INDEX_PAGE_IS_FOLLOWING_SITE_SEARCH_NB_HITS = 29;

    // Performance Analytics
    const INDEX_PAGE_SUM_TIME_GENERATION = 30;
    const INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION = 31;
    const INDEX_PAGE_MIN_TIME_GENERATION = 32;
    const INDEX_PAGE_MAX_TIME_GENERATION = 33;

    // Goal reports
    const INDEX_GOAL_NB_CONVERSIONS = 1;
    const INDEX_GOAL_REVENUE = 2;
    const INDEX_GOAL_NB_VISITS_CONVERTED = 3;

    const INDEX_GOAL_ECOMMERCE_REVENUE_SUBTOTAL = 4;
    const INDEX_GOAL_ECOMMERCE_REVENUE_TAX = 5;
    const INDEX_GOAL_ECOMMERCE_REVENUE_SHIPPING = 6;
    const INDEX_GOAL_ECOMMERCE_REVENUE_DISCOUNT = 7;
    const INDEX_GOAL_ECOMMERCE_ITEMS = 8;

    public static $mappingFromIdToName = array(
        Piwik_Archive::INDEX_NB_UNIQ_VISITORS                      => 'nb_uniq_visitors',
        Piwik_Archive::INDEX_NB_VISITS                             => 'nb_visits',
        Piwik_Archive::INDEX_NB_ACTIONS                            => 'nb_actions',
        Piwik_Archive::INDEX_MAX_ACTIONS                           => 'max_actions',
        Piwik_Archive::INDEX_SUM_VISIT_LENGTH                      => 'sum_visit_length',
        Piwik_Archive::INDEX_BOUNCE_COUNT                          => 'bounce_count',
        Piwik_Archive::INDEX_NB_VISITS_CONVERTED                   => 'nb_visits_converted',
        Piwik_Archive::INDEX_NB_CONVERSIONS                        => 'nb_conversions',
        Piwik_Archive::INDEX_REVENUE                               => 'revenue',
        Piwik_Archive::INDEX_GOALS                                 => 'goals',
        Piwik_Archive::INDEX_SUM_DAILY_NB_UNIQ_VISITORS            => 'sum_daily_nb_uniq_visitors',

        // Actions metrics
        Piwik_Archive::INDEX_PAGE_NB_HITS                          => 'nb_hits',
        Piwik_Archive::INDEX_PAGE_SUM_TIME_SPENT                   => 'sum_time_spent',
        Piwik_Archive::INDEX_PAGE_SUM_TIME_GENERATION              => 'sum_time_generation',
        Piwik_Archive::INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION     => 'nb_hits_with_time_generation',
        Piwik_Archive::INDEX_PAGE_MIN_TIME_GENERATION              => 'min_time_generation',
        Piwik_Archive::INDEX_PAGE_MAX_TIME_GENERATION              => 'max_time_generation',

        Piwik_Archive::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS            => 'exit_nb_uniq_visitors',
        Piwik_Archive::INDEX_PAGE_EXIT_NB_VISITS                   => 'exit_nb_visits',
        Piwik_Archive::INDEX_PAGE_EXIT_SUM_DAILY_NB_UNIQ_VISITORS  => 'sum_daily_exit_nb_uniq_visitors',

        Piwik_Archive::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS           => 'entry_nb_uniq_visitors',
        Piwik_Archive::INDEX_PAGE_ENTRY_SUM_DAILY_NB_UNIQ_VISITORS => 'sum_daily_entry_nb_uniq_visitors',
        Piwik_Archive::INDEX_PAGE_ENTRY_NB_VISITS                  => 'entry_nb_visits',
        Piwik_Archive::INDEX_PAGE_ENTRY_NB_ACTIONS                 => 'entry_nb_actions',
        Piwik_Archive::INDEX_PAGE_ENTRY_SUM_VISIT_LENGTH           => 'entry_sum_visit_length',
        Piwik_Archive::INDEX_PAGE_ENTRY_BOUNCE_COUNT               => 'entry_bounce_count',
        Piwik_Archive::INDEX_PAGE_IS_FOLLOWING_SITE_SEARCH_NB_HITS => 'nb_hits_following_search',

        // Items reports metrics
        Piwik_Archive::INDEX_ECOMMERCE_ITEM_REVENUE                => 'revenue',
        Piwik_Archive::INDEX_ECOMMERCE_ITEM_QUANTITY               => 'quantity',
        Piwik_Archive::INDEX_ECOMMERCE_ITEM_PRICE                  => 'price',
        Piwik_Archive::INDEX_ECOMMERCE_ITEM_PRICE_VIEWED           => 'price_viewed',
        Piwik_Archive::INDEX_ECOMMERCE_ORDERS                      => 'orders',
    );

    public static $mappingFromIdToNameGoal = array(
        Piwik_Archive::INDEX_GOAL_NB_CONVERSIONS             => 'nb_conversions',
        Piwik_Archive::INDEX_GOAL_NB_VISITS_CONVERTED        => 'nb_visits_converted',
        Piwik_Archive::INDEX_GOAL_REVENUE                    => 'revenue',
        Piwik_Archive::INDEX_GOAL_ECOMMERCE_REVENUE_SUBTOTAL => 'revenue_subtotal',
        Piwik_Archive::INDEX_GOAL_ECOMMERCE_REVENUE_TAX      => 'revenue_tax',
        Piwik_Archive::INDEX_GOAL_ECOMMERCE_REVENUE_SHIPPING => 'revenue_shipping',
        Piwik_Archive::INDEX_GOAL_ECOMMERCE_REVENUE_DISCOUNT => 'revenue_discount',
        Piwik_Archive::INDEX_GOAL_ECOMMERCE_ITEMS            => 'items',
    );

    /**
     * string indexed column name => Integer indexed column name
     * @var array
     */
    public static $mappingFromNameToId = array(
        'nb_uniq_visitors'           => Piwik_Archive::INDEX_NB_UNIQ_VISITORS,
        'nb_visits'                  => Piwik_Archive::INDEX_NB_VISITS,
        'nb_actions'                 => Piwik_Archive::INDEX_NB_ACTIONS,
        'max_actions'                => Piwik_Archive::INDEX_MAX_ACTIONS,
        'sum_visit_length'           => Piwik_Archive::INDEX_SUM_VISIT_LENGTH,
        'bounce_count'               => Piwik_Archive::INDEX_BOUNCE_COUNT,
        'nb_visits_converted'        => Piwik_Archive::INDEX_NB_VISITS_CONVERTED,
        'nb_conversions'             => Piwik_Archive::INDEX_NB_CONVERSIONS,
        'revenue'                    => Piwik_Archive::INDEX_REVENUE,
        'goals'                      => Piwik_Archive::INDEX_GOALS,
        'sum_daily_nb_uniq_visitors' => Piwik_Archive::INDEX_SUM_DAILY_NB_UNIQ_VISITORS,
    );

    /**
     * Metrics calculated and archived by the Actions plugin.
     *
     * @var array
     */
    public static $actionsMetrics = array(
        'nb_pageviews',
        'nb_uniq_pageviews',
        'nb_downloads',
        'nb_uniq_downloads',
        'nb_outlinks',
        'nb_uniq_outlinks',
        'nb_searches',
        'nb_keywords',
        'nb_hits',
        'nb_hits_following_search',
    );

    const LABEL_ECOMMERCE_CART = 'ecommerceAbandonedCart';
    const LABEL_ECOMMERCE_ORDER = 'ecommerceOrder';
    
    /**
     * List of archive IDs for the sites, periods and segment we are querying with.
     * Archive IDs are indexed by done flag and period, ie:
     * 
     * array(
     *     'done.Referers' => array(
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
     * Data Access Layer object.
     * 
     * @var Piwik_DataAccess_ArchiveQuery
     */
    private $dataAccess;
    
    /**
     * Cache of Piwik_ArchiveProcessing instances used when launching the archiving
     * process.
     * 
     * @var array
     */
    private $processingCache = array();

    /**
     * @var Piwik_Archive_Parameters
     */
    private $params;
    
    /**
     * Constructor.
     * 
     * @param Piwik_Archive_Parameters $params
     * @param bool $forceIndexedBySite Whether to force index the result of a query by site ID.
     * @param bool $forceIndexedByDate Whether to force index the result of a query by period.
     */
    public function __construct(Piwik_Archive_Parameters $params, $forceIndexedBySite = false,
                                  $forceIndexedByDate = false)
    {
        $this->params = $params;
        $this->forceIndexedBySite = $forceIndexedBySite;
        $this->forceIndexedByDate = $forceIndexedByDate;
        $this->dataAccess = new Piwik_DataAccess_ArchiveQuery();
    }

    /**
     * FIXMEA
     * Returns the IDs of sites we are querying archive data for.
     * 
     * @return array
     */
    public function getIdSites()
    {
        return $this->params->getIdSites();
    }
    
    /**
     * FIXMEA
     * Returns the periods we are querying archive data for.
     * 
     * @return array
     */
    public function getPeriods()
    {
        return $this->params->getPeriods();
    }
    
    /**
     * FIXMEA
     * Returns the segment used to limit the visit set.
     * 
     * @return Piwik_Segment|null
     */
    public function getSegment()
    {
        return $this->params->getSegment();
    }

    /**
     * Builds an Archive object using query parameter values.
     *
     * @param int|string $idSite Integer, or comma separated list of integer site IDs.
     * @param string $period 'day', 'week', 'month', 'year' or 'range'
     * @param Piwik_Date|string $strDate 'YYYY-MM-DD', magic keywords (ie, 'today'; @see Piwik_Date::factory())
     *                                   or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD').
     * @param false|string $segment Segment definition - defaults to false for backward compatibility.
     * @param false|string $_restrictSitesToLogin Used only when running as a scheduled task.
     * @return Piwik_Archive
     */
    public static function build($idSite, $period, $strDate, $segment = false, $_restrictSitesToLogin = false)
    {
        $forceIndexedBySite = false;
        $forceIndexedByDate = false;
        
        // determine site IDs to query from
        if (is_array($idSite)
            || $idSite == 'all'
        ) {
            $forceIndexedBySite = true;
        }
        $sites = Piwik_Site::getIdSitesFromIdSitesString($idSite, $_restrictSitesToLogin);

        if (self::isMultiplePeriod($strDate, $period)) {
            $oPeriod = new Piwik_Period_Range($period, $strDate);
            $allPeriods = $oPeriod->getSubperiods();
            $forceIndexedByDate = true;
        } else {
            $timezone = count($sites) == 1 ? Piwik_Site::getTimezoneFor($sites[0]) : false;
            $oPeriod = Piwik_Archive::makePeriodFromQueryParams($timezone, $period, $strDate);
            $allPeriods = array($oPeriod);
        }

        $segment = new Piwik_Segment($segment, $sites);

        $params = new Piwik_Archive_Parameters();
        $params->setIdSites($sites);
        $params->setPeriods($allPeriods);
        $params->setSegment($segment);
        
        return new Piwik_Archive($params, $forceIndexedBySite, $forceIndexedByDate);
    }

    /**
     * Creates a period instance using a Piwik_Site instance and two strings describing
     * the period & date.
     *
     * @param string $timezone
     * @param string $period The period string: day, week, month, year, range
     * @param string $strDate The date or date range string.
     * @return Piwik_Period
     */
    public static function makePeriodFromQueryParams($timezone, $period, $date)
    {
        if (empty($timezone)) {
            $timezone = 'UTC';
        }

        if ($period == 'range') {
            $oPeriod = new Piwik_Period_Range('range', $date, $timezone, Piwik_Date::factory('today', $timezone));
        } else {
            if (!($date instanceof Piwik_Date)) {
                if ($date == 'now' || $date == 'today') {
                    $date = date('Y-m-d', Piwik_Date::factory('now', $timezone)->getTimestamp());
                } elseif ($date == 'yesterday' || $date == 'yesterdaySameTime' ) {
                    $date = date('Y-m-d', Piwik_Date::factory('now', $timezone)->subDay(1)->getTimestamp());
                }
                $date = Piwik_Date::factory( $date );
            }
            $oPeriod = Piwik_Period::factory($period, $date);
        }
        return $oPeriod;
    }
    
    /**
     * Returns the value of the element $name from the current archive 
     * The value to be returned is a numeric value and is stored in the archive_numeric_* tables
     *
     * @param string|array $names One or more archive names, eg, 'nb_visits', 'Referers_distinctKeywords',
     *                            etc.
     * @return numeric|array|false False if no value with the given name, numeric if only one site
     *                             and date and we're not forcing an index, and array if multiple
     *                             sites/dates are queried.
     */
    public function getNumeric($names)
    {
        $data = $this->get($names, 'numeric');
        
        $resultIndices = $this->getResultIndices();
        $result = $data->getArray($resultIndices);
        
        // if only one metric is returned, just return it as a numeric value
        if (empty($resultIndices)
            && count($result) <= 1
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
     * @param string|array $names One or more archive names, eg, 'Referers_keywordBySearchEngine'.
     * @return string|array|false False if no value with the given name, numeric if only one site
     *                            and date and we're not forcing an index, and array if multiple
     *                            sites/dates are queried.
     */
    public function getBlob($names, $idSubtable = null)
    {
        $data = $this->get($names, 'blob', $idSubtable);
        return $data->getArray($this->getResultIndices());
    }
    
    /**
     * Returns the numeric values of the elements in $names as a DataTable.
     * 
     * Note: Every DataTable instance returned will have at most one row in it.
     * 
     * @param string|array $names One or more archive names, eg, 'nb_visits', 'Referers_distinctKeywords',
     *                            etc.
     * @return Piwik_DataTable|false False if no value with the given names. Based on the number
     *                               of sites/periods, the result can be a DataTable_Array, which
     *                               contains DataTable instances.
     */
    public function getDataTableFromNumeric($names)
    {
        $data = $this->get($names, 'numeric');
        return $data->getDataTable($this->getResultIndices());
    }

    /**
     * This method will build a dataTable from the blob value $name in the current archive.
     * 
     * For example $name = 'Referers_searchEngineByKeyword' will return a
     * Piwik_DataTable containing all the keywords. If a $idSubtable is given, the method
     * will return the subTable of $name. If 'all' is supplied for $idSubtable every subtable
     * will be returned.
     * 
     * @param string $name The name of the record to get.
     * @param int|string|null $idSubtable The subtable ID (if any) or 'all' if requesting every datatable.
     * @return Piwik_DataTable|false
     */
    public function getDataTable($name, $idSubtable = null)
    {
        $data = $this->get($name, 'blob', $idSubtable);
        return $data->getDataTable($this->getResultIndices());
    }
    
    /**
     * Same as getDataTable() except that it will also load in memory all the subtables
     * for the DataTable $name. You can then access the subtables by using the
     * Piwik_DataTable_Manager::getTable() function.
     *
     * @param string $name The name of the record to get.
     * @param int|string|null $idSubtable The subtable ID (if any) or 'all' if requesting every datatable.
     * @param bool $addMetadataSubtableId Whether to add the DB subtable ID as metadata to each datatable,
     *                                    or not.
     * @return Piwik_DataTable
     */
    public function getDataTableExpanded($name, $idSubtable = null, $addMetadataSubtableId = true)
    {
        $data = $this->get($name, 'blob', 'all');
        return $data->getExpandedDataTable($this->getResultIndices(), $idSubtable, $addMetadataSubtableId);
    }
    
    /**
     * Returns true if we shouldn't launch the archiving process and false if we should.
     * 
     * @return bool
     */
    public function isArchivingDisabled()
    {
        return Piwik_ArchiveProcessing::isArchivingDisabledFor($this->getSegment(), $this->getPeriodLabel());
    }

    /**
     * Returns true if Segmentation is allowed for this user
     *
     * @return bool
     */
    public static function isSegmentationEnabled()
    {
        return !Piwik::isUserIsAnonymous()
            || Piwik_Config::getInstance()->General['anonymous_user_enable_use_segments_API'];
    }

    /**
     * Indicate if $dateString and $period correspond to multiple periods
     *
     * @static
     * @param  $dateString
     * @param  $period
     * @return boolean
     */
    public static function isMultiplePeriod($dateString, $period)
    {
        return
            is_string($dateString)
            && (preg_match('/^(last|previous){1}([0-9]*)$/D', $dateString, $regs)
                || Piwik_Period_Range::parseDateRange($dateString))
            && $period != 'range';
    }

    /**
     * Indicate if $idSiteString corresponds to multiple sites.
     *
     * @param string $idSiteString
     * @return bool
     */
    public static function isMultipleSites($idSiteString)
    {
        return $idSiteString == 'all' || strpos($idSiteString, ',') !== false;
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
     * @param Piwik_Date $date
     * @param string $segment
     * @param bool $expanded
     * @param null $idSubtable
     * @return Piwik_DataTable|Piwik_DataTable_Array
     */
    public static function getDataTableFromArchive($name, $idSite, $period, $date, $segment, $expanded, $idSubtable = null)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Piwik_Archive::build($idSite, $period, $date, $segment);
        if ($idSubtable === false) {
            $idSubtable = null;
        }

        if ($expanded) {
            $dataTable = $archive->getDataTableExpanded($name, $idSubtable);
        } else {
            $dataTable = $archive->getDataTable($name, $idSubtable);
        }

        $dataTable->queueFilter('ReplaceSummaryRowLabel');

        return $dataTable;
    }
    
    /**
     * Queries archive tables for data and returns the result.
     */
    private function get($archiveNames, $archiveDataType, $idSubtable = null)
    {
        if (!is_array($archiveNames)) {
            $archiveNames = array($archiveNames);
        }
        
        // apply idSubtable
        if ($idSubtable !== null
            && $idSubtable != 'all'
        ) {
            foreach ($archiveNames as &$name) {
                $name .= "_$idSubtable";
            }
        }
        
        $result = new Piwik_Archive_DataCollection(
            $archiveNames, $archiveDataType, $this->getIdSites(), $this->getPeriods(), $defaultRow = null);
        
        $archiveIds = $this->getArchiveIds($archiveNames);
        if (empty($archiveIds)) {
            return $result;
        }
        
        $archiveData = $this->dataAccess->getArchiveData($archiveIds, $archiveNames, $archiveDataType, $idSubtable);
        foreach ($archiveData as $row) {
            // values are grouped by idsite (site ID), date1-date2 (date range), then name (field name)
            $idSite = $row['idsite'];
            $periodStr = $row['date1'].",".$row['date2'];
            
            if ($archiveDataType == 'numeric') {
                $value = $this->formatNumericValue($row['value']);
            } else {
                $value = $this->uncompress($row['value']);
                $result->addMetadata($idSite, $periodStr, 'ts_archived', $row['ts_archived']);
            }
            
            $resultRow = &$result->get($idSite, $periodStr);
            $resultRow[$row['name']] = $value;
        }
        
        return $result;
    }
    
    /**
     * Returns archive IDs for the sites, periods and archive names that are being
     * queried. This function will use the idarchive cache if it has the right data,
     * query archive tables for IDs w/o launching archiving, or launch archiving and
     * get the idarchive from Piwik_ArchiveProcessing instances.
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
            if (!$this->isArchivingDisabled()) {
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
     * Gets the IDs of the archives we're querying for and stores them in $this->archives.
     * This function will launch the archiving process for each period/site/plugin if 
     * metrics/reports have not been calculated/archived already.
     * 
     * @param array $archiveGroups @see getArchiveGroupOfReport
     * @param array $plugins List of plugin names to archive.
     */
    private function cacheArchiveIdsAfterLaunching($archiveGroups, $plugins)
    {
        $today = Piwik_Date::today();
        
        // for every individual query permutation, launch the archiving process and get the archive ID
        foreach ($this->getPeriods() as $period) {
            $periodStr = $period->getRangeString();
            
            $twoDaysBeforePeriod = $period->getDateStart()->subDay(2);
            $twoDaysAfterPeriod = $period->getDateEnd()->addDay(2);
            
            foreach ($this->getIdSites() as $idSite) {
                $site = new Piwik_Site($idSite);
                
                // if the END of the period is BEFORE the website creation date
                // we already know there are no stats for this period
                // we add one day to make sure we don't miss the day of the website creation
                if ($twoDaysAfterPeriod->isEarlier($site->getCreationDate())) {
                    $archiveDesc = $this->getArchiveDescriptor($idSite, $period);
                    Piwik::log("Archive $archiveDesc skipped, archive is before the website was created.");
                    continue;
                }
        
                // if the starting date is in the future we know there is no visit
                if ($twoDaysBeforePeriod->isLater($today)) {
                    $archiveDesc = $this->getArchiveDescriptor($idSite, $period);
                    Piwik::log("Archive $archiveDesc skipped, archive is after today.");
                    continue;
                }
                
                // prepare the ArchiveProcessing instance
                $processing = $this->getArchiveProcessingInstance($period);
                $processing->setSite($site);
                $processing->setPeriod($period);
                $processing->setSegment($this->getSegment());
                
                $processing->isThereSomeVisits = null;
                
                // process for each plugin as well
                foreach ($archiveGroups as $plugin) {
                    if ($plugin == 'all') {
                        $plugin = reset($plugins);
                    }
                    
                    $doneFlag = $this->getDoneStringForPlugin($plugin);
                    $this->initializeArchiveIdCache($doneFlag);

                    $processing->setRequestedPlugin($plugin);
                    
                    // launch archiving if the requested data hasn't been archived
                    $idArchive = $processing->loadArchive();
                    if (empty($idArchive)) {
                        $processing->launchArchiving();
                        $idArchive = $processing->getIdArchive();
                    }
                    
                    if (!$processing->isThereSomeVisits()) {
                        continue;
                    }
                    
                    $this->idarchives[$doneFlag][$periodStr][] = $idArchive;
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
        $idarchivesByReport = $this->dataAccess->getArchiveIds(
            $this->getIdSites(), $this->getPeriods(), $this->getSegment(), $plugins);
        
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
     * 
     * @see Piwik_ArchiveProcessing::getDoneStringFlagFor
     * 
     * @param string $plugin
     * @return string
     */
    private function getDoneStringForPlugin($plugin)
    {
        return Piwik_ArchiveProcessing::getDoneStringFlagFor($this->getSegment(), $this->getPeriodLabel(), $plugin);
    }
    
    /**
     * Returns an ArchiveProcessing instance that should be used for a specific
     * period.
     * 
     * @param Piwik_Period $period
     * @return Piwik_ArchiveProcessing
     */
    private function getArchiveProcessingInstance($period)
    {
        $label = $period->getLabel();
        if (!isset($this->processingCache[$label])) {
            $this->processingCache[$label] = Piwik_ArchiveProcessing::factory($label);
        }
        return $this->processingCache[$label];
    }
    
    private function getPeriodLabel()
    {
        $periods = $this->getPeriods();
        return reset($periods)->getLabel();
    }
    
    /**
     * Returns an array describing what metadata to use when indexing a query result.
     * For use with Piwik_Archive_DataCollection.
     * 
     * @return array
     */
    private function getResultIndices()
    {
        $indices = array();
        
        if (count($this->getIdSites()) > 1
            || $this->forceIndexedBySite
        ) {
            $indices['site'] = 'idSite';
        }
        
        if (count($this->getPeriods()) > 1
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
    
    private function getArchiveDescriptor($idSite, $period)
    {
        return "site $idSite, {$period->getLabel()} ({$period->getPrettyString()})";
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
            return 'all';
        }
        
        return $plugin;
    }
    
    /**
     * Returns the name of the plugin that archives a given report.
     * 
     * @param string $report Archive data name, ie, 'nb_visits', 'UserSettings_...', etc.
     * @return string
     */
    public static function getPluginForReport($report)
    {
        // Core metrics are always processed in Core, for the requested date/period/segment
        if (in_array($report, Piwik_ArchiveProcessing::getCoreMetrics())
            || $report == 'max_actions'
        ) {
            $report = 'VisitsSummary_CoreMetrics';
        }
        // Goal_* metrics are processed by the Goals plugin (HACK)
        else if(strpos($report, 'Goal_') === 0) {
            $report = 'Goals_Metrics';
        }
        
        $plugin = substr($report, 0, strpos($report, '_'));
        if (empty($plugin)
            || !Piwik_PluginsManager::getInstance()->isPluginActivated($plugin)
        ) {
            $pluginStr = empty($plugin) ? '' : "($plugin)";
            throw new Exception("Error: The report '$report' was requested but it is not available "
                               . "at this stage. You may also disable the related plugin $pluginStr "
                               . "to avoid this error.");
        }
        return $plugin;
    }
}
