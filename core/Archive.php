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
 * Example:
 * <pre>
 *        $archive = Piwik_Archive::build($idSite = 1, $period = 'week', '2008-03-08' );
 *        $dataTable = $archive->getDataTable('Provider_hostnameExt');
 *        $dataTable->queueFilter('ReplaceColumnNames');
 *        return $dataTable;
 * </pre>
 *
 * Example bis:
 * <pre>
 *        $archive = Piwik_Archive::build($idSite = 3, $period = 'day', $date = 'today' );
 *        $nbVisits = $archive->getNumeric('nb_visits');
 *        return $nbVisits;
 * </pre>
 *
 * If the requested statistics are not yet processed, Archive uses ArchiveProcessing to archive the statistics.
 *
 * @package Piwik
 * @subpackage Piwik_Archive
 */
abstract class Piwik_Archive
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
     * Website Piwik_Site
     *
     * @var Piwik_Site
     */
    protected $site = null;

    /**
     * Segment applied to the visits set
     * @var Piwik_Segment
     */
    protected $segment = false;

    /**
     * Builds an Archive object or returns the same archive if previously built.
     *
     * @param int|string $idSite                 integer, or comma separated list of integer
     * @param string $period                 'week' 'day' etc.
     * @param Piwik_Date|string $strDate                'YYYY-MM-DD' or magic keywords 'today' @see Piwik_Date::factory()
     * @param bool|string $segment                Segment definition - defaults to false for Backward Compatibility
     * @param bool|string $_restrictSitesToLogin  Used only when running as a scheduled task
     * @return Piwik_Archive
     */
    public static function build($idSite, $period, $strDate, $segment = false, $_restrictSitesToLogin = false)
    {
        if ($idSite === 'all') {
            $sites = Piwik_SitesManager_API::getInstance()->getSitesIdWithAtLeastViewAccess($_restrictSitesToLogin);
        } else {
            $sites = Piwik_Site::getIdSitesFromIdSitesString($idSite);
        }

        if (!($segment instanceof Piwik_Segment)) {
            $segment = new Piwik_Segment($segment, $idSite);
        }

        // idSite=1,3 or idSite=all
        if ($idSite === 'all'
            || is_array($idSite)
            || count($sites) > 1
        ) {
            $archive = new Piwik_Archive_Array_IndexedBySite($sites, $period, $strDate, $segment, $_restrictSitesToLogin);
        } // if a period date string is detected: either 'last30', 'previous10' or 'YYYY-MM-DD,YYYY-MM-DD'
        elseif (is_string($strDate) && self::isMultiplePeriod($strDate, $period)) {
            $oSite = new Piwik_Site($idSite);
            $archive = new Piwik_Archive_Array_IndexedByDate($oSite, $period, $strDate, $segment);
        } // case we request a single archive
        else {
            $oSite = new Piwik_Site($idSite);
            $oPeriod = Piwik_Archive::makePeriodFromQueryParams($oSite, $period, $strDate);

            $archive = new Piwik_Archive_Single();
            $archive->setPeriod($oPeriod);
            $archive->setSite($oSite);
            $archive->setSegment($segment);
        }
        return $archive;
    }

    /**
     * Creates a period instance using a Piwik_Site instance and two strings describing
     * the period & date.
     *
     * @param Piwik_Site $site
     * @param string $strPeriod The period string: day, week, month, year, range
     * @param string $strDate The date or date range string.
     * @return Piwik_Period
     */
    public static function makePeriodFromQueryParams($site, $strPeriod, $strDate)
    {
        $tz = $site->getTimezone();

        if ($strPeriod == 'range') {
            $oPeriod = new Piwik_Period_Range('range', $strDate, $tz, Piwik_Date::factory('today', $tz));
        } else {
            $oDate = $strDate;
            if (!($strDate instanceof Piwik_Date)) {
                if ($strDate == 'now' || $strDate == 'today') {
                    $strDate = date('Y-m-d', Piwik_Date::factory('now', $tz)->getTimestamp());
                } elseif ($strDate == 'yesterday' || $strDate == 'yesterdaySameTime') {
                    $strDate = date('Y-m-d', Piwik_Date::factory('now', $tz)->subDay(1)->getTimestamp());
                }
                $oDate = Piwik_Date::factory($strDate);
            }
            $date = $oDate->toString();
            $oPeriod = Piwik_Period::factory($strPeriod, $oDate);
        }

        return $oPeriod;
    }

    abstract public function prepareArchive();

    /**
     * Returns the value of the element $name from the current archive
     * The value to be returned is a numeric value and is stored in the archive_numeric_* tables
     *
     * @param string $name  For example Referers_distinctKeywords
     * @return float|int|false  False if no value with the given name
     */
    abstract public function getNumeric($name);

    /**
     * Returns the value of the element $name from the current archive
     *
     * The value to be returned is a blob value and is stored in the archive_numeric_* tables
     *
     * It can return anything from strings, to serialized PHP arrays or PHP objects, etc.
     *
     * @param string $name  For example Referers_distinctKeywords
     * @return mixed  False if no value with the given name
     */
    abstract public function getBlob($name);

    /**
     *
     * @param $fields
     * @return Piwik_DataTable
     */
    abstract public function getDataTableFromNumeric($fields);

    /**
     * This method will build a dataTable from the blob value $name in the current archive.
     *
     * For example $name = 'Referers_searchEngineByKeyword' will return a  Piwik_DataTable containing all the keywords
     * If a idSubTable is given, the method will return the subTable of $name
     *
     * @param string $name
     * @param int $idSubTable or null if requesting the parent table
     * @return Piwik_DataTable
     * @throws exception If the value cannot be found
     */
    abstract public function getDataTable($name, $idSubTable = null);

    /**
     * Same as getDataTable() except that it will also load in memory
     * all the subtables for the DataTable $name.
     * You can then access the subtables by using the Piwik_DataTable_Manager getTable()
     *
     * @param string $name
     * @param int|null $idSubTable  null if requesting the parent table
     * @return Piwik_DataTable
     */
    abstract public function getDataTableExpanded($name, $idSubTable = null);


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

    protected function formatNumericValue($value)
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
        $value = round((float)$value, 2);
        return $value;
    }

    public function getSegment()
    {
        return $this->segment;
    }

    public function setSegment(Piwik_Segment $segment)
    {
        $this->segment = $segment;
    }

    /**
     * Sets the site
     *
     * @param Piwik_Site $site
     */
    public function setSite(Piwik_Site $site)
    {
        $this->site = $site;
    }

    /**
     * Gets the site
     *
     * @return Piwik_Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Returns the Id site associated with this archive
     *
     * @return int
     */
    public function getIdSite()
    {
        return $this->site->getId();
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
        return (preg_match('/^(last|previous){1}([0-9]*)$/D', $dateString, $regs)
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
}
