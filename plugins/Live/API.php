<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Date;
use Piwik\Db;
use Piwik\MetricsFormatter;
use Piwik\Period\Range;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\Referrers\API as APIReferrers;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tracker;

/**
 * @see plugins/Live/Visitor.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Live/Visitor.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';

/**
 * The Live! API lets you access complete visit level information about your visitors. Combined with the power of <a href='http://piwik.org/docs/analytics-api/segmentation/' target='_blank'>Segmentation</a>,
 * you will be able to request visits filtered by any criteria.
 *
 * The method "getLastVisitsDetails" will return extensive data for each visit, which includes: server time, visitId, visitorId,
 * visitorType (new or returning), number of pages, list of all pages (and events, file downloaded and outlinks clicked),
 * custom variables names and values set to this visit, number of goal conversions (and list of all Goal conversions for this visit,
 * with time of conversion, revenue, URL, etc.), but also other attributes such as: days since last visit, days since first visit,
 * country, continent, visitor IP,
 * provider, referrer used (referrer name, keyword if it was a search engine, full URL), campaign name and keyword, operating system,
 * browser, type of screen, resolution, supported browser plugins (flash, java, silverlight, pdf, etc.), various dates & times format to make
 * it easier for API users... and more!
 *
 * With the parameter <a href='http://piwik.org/docs/analytics-api/segmentation/' target='_blank'>'&segment='</a> you can filter the
 * returned visits by any criteria (visitor IP, visitor ID, country, keyword used, time of day, etc.).
 *
 * The method "getCounters" is used to return a simple counter: visits, number of actions, number of converted visits, in the last N minutes.
 *
 * See also the documentation about <a href='http://piwik.org/docs/real-time/' target='_blank'>Real time widget and visitor level reports</a> in Piwik.
 * @method static \Piwik\Plugins\Live\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    const VISITOR_PROFILE_MAX_VISITS_TO_AGGREGATE = 100;
    const VISITOR_PROFILE_MAX_VISITS_TO_SHOW = 10;
    const VISITOR_PROFILE_DATE_FORMAT = '%day% %shortMonth% %longYear%';

    /**
     * This will return simple counters, for a given website ID, for visits over the last N minutes
     *
     * @param int $idSite Id Site
     * @param int $lastMinutes Number of minutes to look back at
     * @param bool|string $segment
     * @return array( visits => N, actions => M, visitsConverted => P )
     */
    public function getCounters($idSite, $lastMinutes, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $lastMinutes = (int) $lastMinutes;

        $counters = array(
            'visits'   => 0,
            'actions'  => 0,
            'visitors' => 0,
            'visitsConverted' => 0,
        );

        if (empty($lastMinutes)) {
            return array($counters);
        }

        list($whereIdSites, $idSites) = $this->getIdSitesWhereClause($idSite);

        $select  = "count(*) as visits, COUNT(DISTINCT log_visit.idvisitor) as visitors";
        $where   = $whereIdSites . "AND log_visit.visit_last_action_time >= ?";
        $bind    = $idSites;
        $bind[]  = Date::factory(time() - $lastMinutes * 60)->toString('Y-m-d H:i:s');

        $segment = new Segment($segment, $idSite);
        $query   = $segment->getSelectQuery($select, 'log_visit', $where, $bind);

        $data    = Db::fetchAll($query['sql'], $query['bind']);

        $counters['visits']   = $data[0]['visits'];
        $counters['visitors'] = $data[0]['visitors'];

        $select = "count(*)";
        $from   = 'log_link_visit_action';
        list($whereIdSites) = $this->getIdSitesWhereClause($idSite, $from);
        $where  = $whereIdSites . "AND log_link_visit_action.server_time >= ?";
        $query  = $segment->getSelectQuery($select, $from, $where, $bind);
        $counters['actions'] = Db::fetchOne($query['sql'], $query['bind']);

        $select = "count(*)";
        $from   = 'log_conversion';
        list($whereIdSites) = $this->getIdSitesWhereClause($idSite, $from);
        $where  = $whereIdSites . "AND log_conversion.server_time >= ?";
        $query  = $segment->getSelectQuery($select, $from, $where, $bind);
        $counters['visitsConverted'] = Db::fetchOne($query['sql'], $query['bind']);

        return array($counters);
    }

    /**
     * The same functionnality can be obtained using segment=visitorId==$visitorId with getLastVisitsDetails
     *
     * @deprecated
     * @ignore
     * @param int $visitorId
     * @param int $idSite
     * @param int $filter_limit
     * @param bool $flat Whether to flatten the visitor details array
     *
     * @return DataTable
     */
    public function getLastVisitsForVisitor($visitorId, $idSite, $filter_limit = 10, $flat = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $countVisitorsToFetch = $filter_limit;

        $table = $this->loadLastVisitorDetailsFromDatabase($idSite, $period = false, $date = false, $segment = false, $countVisitorsToFetch, $visitorId);
        $this->addFilterToCleanVisitors($table, $idSite, $flat);

        return $table;
    }

    /**
     * Returns the last visits tracked in the specified website
     * You can define any number of filters: none, one, many or all parameters can be defined
     *
     * @param int $idSite Site ID
     * @param bool|string $period Period to restrict to when looking at the logs
     * @param bool|string $date Date to restrict to
     * @param bool|int $segment (optional) Number of visits rows to return
     * @param bool|int $countVisitorsToFetch (optional) Only return the last X visits. By default the last GET['filter_offset']+GET['filter_limit'] are returned.
     * @param bool|int $minTimestamp (optional) Minimum timestamp to restrict the query to (useful when paginating or refreshing visits)
     * @param bool $flat
     * @param bool $doNotFetchActions
     * @return DataTable
     */
    public function getLastVisitsDetails($idSite, $period = false, $date = false, $segment = false, $countVisitorsToFetch = false, $minTimestamp = false, $flat = false, $doNotFetchActions = false)
    {
        if (false === $countVisitorsToFetch) {
            $filter_limit  = Common::getRequestVar('filter_limit', 10, 'int');
            $filter_offset = Common::getRequestVar('filter_offset', 0, 'int');

            $countVisitorsToFetch = $filter_limit + $filter_offset;
        }

        $filterSortOrder = Common::getRequestVar('filter_sort_order', false, 'string');

        Piwik::checkUserHasViewAccess($idSite);
        $dataTable = $this->loadLastVisitorDetailsFromDatabase($idSite, $period, $date, $segment, $countVisitorsToFetch, $visitorId = false, $minTimestamp, $filterSortOrder);
        $this->addFilterToCleanVisitors($dataTable, $idSite, $flat, $doNotFetchActions);

        $filterSortColumn = Common::getRequestVar('filter_sort_column', false, 'string');
        $filterSortOrder  = Common::getRequestVar('filter_sort_order', 'desc', 'string');

        if ($filterSortColumn) {
            $dataTable->queueFilter('Sort', array($filterSortColumn, $filterSortOrder));
        }

        return $dataTable;
    }

    /**
     * Returns an array describing a visitor using her last visits (uses a maximum of 100).
     *
     * @param int $idSite Site ID
     * @param bool|false|string $visitorId The ID of the visitor whose profile to retrieve.
     * @param bool|false|string $segment
     * @param bool $checkForLatLong If true, hasLatLong will appear in the output and be true if
     *                              one of the first 100 visits has a latitude/longitude.
     * @return array
     */
    public function getVisitorProfile($idSite, $visitorId = false, $segment = false, $checkForLatLong = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        if ($visitorId === false) {
            $visitorId = $this->getMostRecentVisitorId($idSite, $segment);
        }

        $newSegment = ($segment === false ? '' : $segment . ';') . 'visitorId==' . $visitorId;

        $visits = $this->loadLastVisitorDetailsFromDatabase($idSite, $period = false, $date = false, $newSegment,
            $numVisitorsToFetch = self::VISITOR_PROFILE_MAX_VISITS_TO_AGGREGATE,
            $overrideVisitorId = false,
            $minTimestamp = false);
        $this->addFilterToCleanVisitors($visits, $idSite, $flat = false, $doNotFetchActions = false, $filterNow = true);

        if ($visits->getRowsCount() == 0) {
            return array();
        }

        $isEcommerceEnabled = Site::isEcommerceEnabledFor($idSite);

        $result = array();
        $result['totalVisits'] = 0;
        $result['totalVisitDuration'] = 0;
        $result['totalActions'] = 0;
        $result['totalSearches'] = 0;
        $result['totalPageViews'] = 0;
        $result['totalGoalConversions'] = 0;
        $result['totalConversionsByGoal'] = array();

        if ($isEcommerceEnabled) {
            $result['totalEcommerceConversions'] = 0;
            $result['totalEcommerceRevenue'] = 0;
            $result['totalEcommerceItems'] = 0;
            $result['totalAbandonedCarts'] = 0;
            $result['totalAbandonedCartsRevenue'] = 0;
            $result['totalAbandonedCartsItems'] = 0;
        }

        $countries  = array();
        $continents = array();
        $cities     = array();
        $siteSearchKeywords = array();

        $pageGenerationTimeTotal = 0;

        // aggregate all requested visits info for total_* info
        foreach ($visits->getRows() as $visit) {
            ++$result['totalVisits'];

            $result['totalVisitDuration'] += $visit->getColumn('visitDuration');
            $result['totalActions'] += $visit->getColumn('actions');
            $result['totalGoalConversions'] += $visit->getColumn('goalConversions');

            // individual goal conversions are stored in action details
            foreach ($visit->getColumn('actionDetails') as $action) {
                if ($action['type'] == 'goal') {
                    // handle goal conversion
                    $idGoal = $action['goalId'];
                    $idGoalKey = 'idgoal=' . $idGoal;

                    if (!isset($result['totalConversionsByGoal'][$idGoalKey])) {
                        $result['totalConversionsByGoal'][$idGoalKey] = 0;
                    }
                    ++$result['totalConversionsByGoal'][$idGoalKey];

                    if (!empty($action['revenue'])) {
                        if (!isset($result['totalRevenueByGoal'][$idGoalKey])) {
                            $result['totalRevenueByGoal'][$idGoalKey] = 0;
                        }
                        $result['totalRevenueByGoal'][$idGoalKey] += $action['revenue'];
                    }
                } else if ($action['type'] == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER // handle ecommerce order
                    && $isEcommerceEnabled
                ) {
                    ++$result['totalEcommerceConversions'];
                    $result['totalEcommerceRevenue'] += $action['revenue'];
                    $result['totalEcommerceItems'] += $action['items'];
                } else if ($action['type'] == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART // handler abandoned cart
                    && $isEcommerceEnabled
                ) {
                    ++$result['totalAbandonedCarts'];
                    $result['totalAbandonedCartsRevenue'] += $action['revenue'];
                    $result['totalAbandonedCartsItems'] += $action['items'];
                }

                if (isset($action['siteSearchKeyword'])) {
                    $keyword = $action['siteSearchKeyword'];

                    if (!isset($siteSearchKeywords[$keyword])) {
                        $siteSearchKeywords[$keyword] = 0;
                        ++$result['totalSearches'];
                    }
                    ++$siteSearchKeywords[$keyword];
                }

                if (isset($action['generationTime'])) {
                    $pageGenerationTimeTotal += $action['generationTime'];
                    ++$result['totalPageViews'];
                }
            }

            $countryCode = $visit->getColumn('countryCode');
            if (!isset($countries[$countryCode])) {
                $countries[$countryCode] = 0;
            }
            ++$countries[$countryCode];

            $continentCode = $visit->getColumn('continentCode');
            if (!isset($continents[$continentCode])) {
                $continents[$continentCode] = 0;
            }
            ++$continents[$continentCode];

            if ($countryCode && !array_key_exists($countryCode, $cities)) {
                $cities[$countryCode] = array();
            }
            $city = $visit->getColumn('city');
            if (!empty($city)) {
                $cities[$countryCode][] = $city;
            }
        }

        // sort countries/continents/search keywords by visit/action
        asort($countries);
        asort($continents);
        arsort($siteSearchKeywords);

        // transform country/continents/search keywords into something that will look good in XML
        $result['countries'] = $result['continents'] = $result['searches'] = array();

        foreach ($countries as $countryCode => $nbVisits) {

            $countryInfo = array('country'    => $countryCode,
                                   'nb_visits'  => $nbVisits,
                                   'flag'       => \Piwik\Plugins\UserCountry\getFlagFromCode($countryCode),
                                   'prettyName' => \Piwik\Plugins\UserCountry\countryTranslate($countryCode));
            if (!empty($cities[$countryCode])) {
                $countryInfo['cities'] = array_unique($cities[$countryCode]);
            }
            $result['countries'][] = $countryInfo;
        }
        foreach ($continents as $continentCode => $nbVisits) {
            $result['continents'][] = array('continent'  => $continentCode,
                                            'nb_visits'  => $nbVisits,
                                            'prettyName' => \Piwik\Plugins\UserCountry\continentTranslate($continentCode));
        }
        foreach ($siteSearchKeywords as $keyword => $searchCount) {
            $result['searches'][] = array('keyword'  => $keyword,
                                          'searches' => $searchCount);
        }

        if ($result['totalPageViews']) {
            $result['averagePageGenerationTime'] =
                round($pageGenerationTimeTotal / $result['totalPageViews'], $precision = 2);
        }

        $result['totalVisitDurationPretty'] = MetricsFormatter::getPrettyTimeFromSeconds($result['totalVisitDuration']);

        // use requested visits for first/last visit info
        $rows = $visits->getRows();
        $result['firstVisit'] = $this->getVisitorProfileVisitSummary(end($rows));
        $result['lastVisit'] = $this->getVisitorProfileVisitSummary(reset($rows));

        // check if requested visits have lat/long
        if ($checkForLatLong) {
            $result['hasLatLong'] = false;
            foreach ($rows as $visit) {
                if ($visit->getColumn('latitude') !== false) { // realtime map only checks for latitude
                    $result['hasLatLong'] = true;
                    break;
                }
            }
        }

        // save count of visits we queries
        $result['visitsAggregated'] = count($rows);

        // use N most recent visits for last_visits
        $visits->deleteRowsOffset(self::VISITOR_PROFILE_MAX_VISITS_TO_SHOW);
        $result['lastVisits'] = $visits;

        // use the right date format for the pretty server date
        $timezone = Site::getTimezoneFor($idSite);
        foreach ($result['lastVisits']->getRows() as $visit) {
            $dateTimeVisitFirstAction = Date::factory($visit->getColumn('firstActionTimestamp'), $timezone);

            $datePretty = $dateTimeVisitFirstAction->getLocalized(self::VISITOR_PROFILE_DATE_FORMAT);
            $visit->setColumn('serverDatePrettyFirstAction', $datePretty);

            $dateTimePretty = $datePretty . ' ' . $visit->getColumn('serverTimePrettyFirstAction');
            $visit->setColumn('serverDateTimePrettyFirstAction', $dateTimePretty);
        }

        $result['userId'] = $visit->getColumn('userId');

        // get visitor IDs that are adjacent to this one in log_visit
        // TODO: make sure order of visitor ids is not changed if a returning visitor visits while the user is
        //       looking at the popup.
        $latestVisitTime = reset($rows)->getColumn('lastActionDateTime');
        $result['nextVisitorId'] = $this->getAdjacentVisitorId($idSite, $visitorId, $latestVisitTime, $segment, $getNext = true);
        $result['previousVisitorId'] = $this->getAdjacentVisitorId($idSite, $visitorId, $latestVisitTime, $segment, $getNext = false);

        /**
         * Triggered in the Live.getVisitorProfile API method. Plugins can use this event
         * to discover and add extra data to visitor profiles.
         *
         * For example, if an email address is found in a custom variable, a plugin could load the
         * gravatar for the email and add it to the visitor profile, causing it to display in the
         * visitor profile popup.
         *
         * The following visitor profile elements can be set to augment the visitor profile popup:
         *
         * - **visitorAvatar**: A URL to an image to display in the top left corner of the popup.
         * - **visitorDescription**: Text to be used as the tooltip of the avatar image.
         *
         * @param array &$visitorProfile The unaugmented visitor profile info.
         */
        Piwik::postEvent('Live.getExtraVisitorDetails', array(&$result));

        return $result;
    }

    /**
     * Returns the visitor ID of the most recent visit.
     *
     * @param int $idSite
     * @param bool|string $segment
     * @return string
     */
    public function getMostRecentVisitorId($idSite, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->loadLastVisitorDetailsFromDatabase(
            $idSite, $period = false, $date = false, $segment, $numVisitorsToFetch = 1,
            $visitorId = false, $minTimestamp = false
        );

        if (0 >= $dataTable->getRowsCount()) {
            return false;
        }

        $visitorFactory = new VisitorFactory();
        $visitDetails   = $dataTable->getFirstRow()->getColumns();
        $visitor        = $visitorFactory->create($visitDetails);

        return $visitor->getVisitorId();
    }

    /**
     * Returns the ID of a visitor that is adjacent to another visitor (by time of last action)
     * in the log_visit table.
     *
     * @param int $idSite The ID of the site whose visits should be looked at.
     * @param string $visitorId The ID of the visitor to get an adjacent visitor for.
     * @param string $visitLastActionTime The last action time of the latest visit for $visitorId.
     * @param string $segment
     * @param bool $getNext Whether to retrieve the next visitor or the previous visitor. The next
     *                      visitor will be the visitor that appears chronologically later in the
     *                      log_visit table. The previous visitor will be the visitor that appears
     *                      earlier.
     * @return string The hex visitor ID.
     */
    private function getAdjacentVisitorId($idSite, $visitorId, $visitLastActionTime, $segment, $getNext)
    {
        if ($getNext) {
            $visitLastActionTimeCondition = "sub.visit_last_action_time <= ?";
            $orderByDir = "DESC";
        } else {
            $visitLastActionTimeCondition = "sub.visit_last_action_time >= ?";
            $orderByDir = "ASC";
        }

        $visitLastActionDate = Date::factory($visitLastActionTime);
        $dateOneDayAgo       = $visitLastActionDate->subDay(1);
        $dateOneDayInFuture  = $visitLastActionDate->addDay(1);

        $select = "log_visit.idvisitor, MAX(log_visit.visit_last_action_time) as visit_last_action_time";
        $from = "log_visit";
        $where = "log_visit.idsite = ? AND log_visit.idvisitor <> ? AND visit_last_action_time >= ? and visit_last_action_time <= ?";
        $whereBind = array($idSite, @Common::hex2bin($visitorId), $dateOneDayAgo->toString('Y-m-d H:i:s'), $dateOneDayInFuture->toString('Y-m-d H:i:s'));
        $orderBy = "MAX(log_visit.visit_last_action_time) $orderByDir";
        $groupBy = "log_visit.idvisitor";

        $segment = new Segment($segment, $idSite);
        $queryInfo = $segment->getSelectQuery($select, $from, $where, $whereBind, $orderBy, $groupBy);

        $sql = "SELECT sub.idvisitor, sub.visit_last_action_time FROM ({$queryInfo['sql']}) as sub
                 WHERE $visitLastActionTimeCondition
                 LIMIT 1";
        $bind = array_merge($queryInfo['bind'], array($visitLastActionTime));

        $visitorId = Db::fetchOne($sql, $bind);
        if (!empty($visitorId)) {
            $visitorId = bin2hex($visitorId);
        }
        return $visitorId;
    }

    /**
     * Returns a summary for an important visit. Used to describe the first & last visits of a visitor.
     *
     * @param Row $visit
     * @return array
     */
    private function getVisitorProfileVisitSummary($visit)
    {
        $today = Date::today();

        $serverDate = $visit->getColumn('firstActionTimestamp');
        return array(
            'date'            => $serverDate,
            'prettyDate'      => Date::factory($serverDate)->getLocalized(self::VISITOR_PROFILE_DATE_FORMAT),
            'daysAgo'         => (int)Date::secondsToDays($today->getTimestamp() - Date::factory($serverDate)->getTimestamp()),
            'referrerType'    => $visit->getColumn('referrerType'),
            'referralSummary' => self::getReferrerSummaryForVisit($visit),
        );
    }

    /**
     * Returns a summary for a visit's referral.
     *
     * @param Row $visit
     * @return bool|mixed|string
     * @ignore
     */
    public static function getReferrerSummaryForVisit($visit)
    {
        $referrerType = $visit->getColumn('referrerType');
        if ($referrerType === false
            || $referrerType == 'direct'
        ) {
            $result = Piwik::translate('Referrers_DirectEntry');
        } else if ($referrerType == 'search') {
            $result = $visit->getColumn('referrerName');

            $keyword = $visit->getColumn('referrerKeyword');
            if ($keyword !== false
                && $keyword != APIReferrers::getKeywordNotDefinedString()
            ) {
                $result .= ' (' . $keyword . ')';
            }
        } else if ($referrerType == 'campaign') {
            $result = Piwik::translate('Referrers_ColumnCampaign') . ' (' . $visit->getColumn('referrerName') . ')';
        } else {
            $result = $visit->getColumn('referrerName');
        }

        return $result;
    }

    /**
     * @deprecated
     */
    public function getLastVisits($idSite, $filter_limit = 10, $minTimestamp = false)
    {
        return $this->getLastVisitsDetails($idSite, $period = false, $date = false, $segment = false, $countVisitorsToFetch = $filter_limit, $minTimestamp, $flat = false);
    }

    /**
     * For an array of visits, query the list of pages for this visit
     * as well as make the data human readable
     * @param DataTable $dataTable
     * @param int $idSite
     * @param bool $flat whether to flatten the array (eg. 'customVariables' names/values will appear in the root array rather than in 'customVariables' key
     * @param bool $doNotFetchActions If set to true, we only fetch visit info and not actions (much faster)
     * @param bool $filterNow If true, the visitors will be cleaned immediately
     */
    private function addFilterToCleanVisitors(DataTable $dataTable, $idSite, $flat = false, $doNotFetchActions = false, $filterNow = false)
    {
        $filter = 'queueFilter';
        if ($filterNow) {
            $filter = 'filter';
        }

        $dataTable->$filter(function ($table) use ($idSite, $flat, $doNotFetchActions) {
            /** @var DataTable $table */
            $actionsLimit = (int)Config::getInstance()->General['visitor_log_maximum_actions_per_visit'];

            $visitorFactory = new VisitorFactory();
            $website        = new Site($idSite);
            $timezone       = $website->getTimezone();
            $currency       = $website->getCurrency();
            $currencies     = APISitesManager::getInstance()->getCurrencySymbols();

            // live api is not summable, prevents errors like "Unexpected ECommerce status value"
            $table->deleteRow(DataTable::ID_SUMMARY_ROW);

            foreach ($table->getRows() as $visitorDetailRow) {
                $visitorDetailsArray = Visitor::cleanVisitorDetails($visitorDetailRow->getColumns());

                $visitor = $visitorFactory->create($visitorDetailsArray);
                $visitorDetailsArray = $visitor->getAllVisitorDetails();

                $visitorDetailsArray['siteCurrency'] = $currency;
                $visitorDetailsArray['siteCurrencySymbol'] = @$currencies[$visitorDetailsArray['siteCurrency']];
                $visitorDetailsArray['serverTimestamp'] = $visitorDetailsArray['lastActionTimestamp'];

                $dateTimeVisit = Date::factory($visitorDetailsArray['lastActionTimestamp'], $timezone);
                if ($dateTimeVisit) {
                    $visitorDetailsArray['serverTimePretty'] = $dateTimeVisit->getLocalized('%time%');
                    $visitorDetailsArray['serverDatePretty'] = $dateTimeVisit->getLocalized(Piwik::translate('CoreHome_ShortDateFormat'));
                }

                $dateTimeVisitFirstAction = Date::factory($visitorDetailsArray['firstActionTimestamp'], $timezone);
                $visitorDetailsArray['serverDatePrettyFirstAction'] = $dateTimeVisitFirstAction->getLocalized(Piwik::translate('CoreHome_ShortDateFormat'));
                $visitorDetailsArray['serverTimePrettyFirstAction'] = $dateTimeVisitFirstAction->getLocalized('%time%');

                $visitorDetailsArray['actionDetails'] = array();
                if (!$doNotFetchActions) {
                    $visitorDetailsArray = Visitor::enrichVisitorArrayWithActions($visitorDetailsArray, $actionsLimit, $timezone);
                }

                if ($flat) {
                    $visitorDetailsArray = Visitor::flattenVisitorDetailsArray($visitorDetailsArray);
                }

                $visitorDetailRow->setColumns($visitorDetailsArray);
            }
        });
    }

    private function loadLastVisitorDetailsFromDatabase($idSite, $period, $date, $segment = false, $countVisitorsToFetch = 100, $visitorId = false, $minTimestamp = false, $filterSortOrder = false)
    {
        $where = $whereBind = array();

        list($whereClause, $idSites) = $this->getIdSitesWhereClause($idSite);

        $where[] = $whereClause;
        $whereBind = $idSites;

        if (strtolower($filterSortOrder) !== 'asc') {
            $filterSortOrder = 'DESC';
        }

        $orderBy = "idsite, visit_last_action_time " . $filterSortOrder;
        $orderByParent = "sub.visit_last_action_time " . $filterSortOrder;

        if (!empty($visitorId)) {
            $where[] = "log_visit.idvisitor = ? ";
            $whereBind[] = @Common::hex2bin($visitorId);
        }

        if (!empty($minTimestamp)) {
            $where[] = "log_visit.visit_last_action_time > ? ";
            $whereBind[] = date("Y-m-d H:i:s", $minTimestamp);
        }

        // If no other filter, only look at the last 24 hours of stats
        if (empty($visitorId)
            && empty($countVisitorsToFetch)
            && empty($period)
            && empty($date)
        ) {
            $period = 'day';
            $date = 'yesterdaySameTime';
        }

        // SQL Filter with provided period
        if (!empty($period) && !empty($date)) {
            $currentSite = new Site($idSite);
            $currentTimezone = $currentSite->getTimezone();

            $dateString = $date;
            if ($period == 'range') {
                $processedPeriod = new Range('range', $date);
                if ($parsedDate = Range::parseDateRange($date)) {
                    $dateString = $parsedDate[2];
                }
            } else {
                $processedDate = Date::factory($date);
                if ($date == 'today'
                    || $date == 'now'
                    || $processedDate->toString() == Date::factory('now', $currentTimezone)->toString()
                ) {
                    $processedDate = $processedDate->subDay(1);
                }
                $processedPeriod = Period\Factory::build($period, $processedDate);
            }
            $dateStart = $processedPeriod->getDateStart()->setTimezone($currentTimezone);
            $where[] = "log_visit.visit_last_action_time >= ?";
            $whereBind[] = $dateStart->toString('Y-m-d H:i:s');

            if (!in_array($date, array('now', 'today', 'yesterdaySameTime'))
                && strpos($date, 'last') === false
                && strpos($date, 'previous') === false
                && Date::factory($dateString)->toString('Y-m-d') != Date::factory('now', $currentTimezone)->toString()
            ) {
                $dateEnd = $processedPeriod->getDateEnd()->setTimezone($currentTimezone);
                $where[] = " log_visit.visit_last_action_time <= ?";
                $dateEndString = $dateEnd->addDay(1)->toString('Y-m-d H:i:s');
                $whereBind[] = $dateEndString;
            }
        }

        if (count($where) > 0) {
            $where = join("
				AND ", $where);
        } else {
            $where = false;
        }

        $segment = new Segment($segment, $idSite);

        // Subquery to use the indexes for ORDER BY
        $select = "log_visit.*";
        $from = "log_visit";
        $subQuery = $segment->getSelectQuery($select, $from, $where, $whereBind, $orderBy);

        $sqlLimit = $countVisitorsToFetch >= 1 ? " LIMIT 0, " . (int)$countVisitorsToFetch : "";

        // Group by idvisit so that a visitor converting 2 goals only appears once
        $sql = "
			SELECT sub.* FROM (
				" . $subQuery['sql'] . "
				$sqlLimit
			) AS sub
			GROUP BY sub.idvisit
			ORDER BY $orderByParent
		";
        try {
            $data = Db::fetchAll($sql, $subQuery['bind']);
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }

        $dataTable = new DataTable();
        $dataTable->addRowsFromSimpleArray($data);
       // $dataTable->disableFilter('Truncate');

        if (!empty($data[0])) {
            $columnsToNotAggregate = array_map(function () {
                return 'skip';
            }, $data[0]);

            $dataTable->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $columnsToNotAggregate);
        }

        return $dataTable;
    }

    /**
     * @param $idSite
     * @param string $table
     * @return array
     */
    private function getIdSitesWhereClause($idSite, $table = 'log_visit')
    {
        $idSites = array($idSite);
        Piwik::postEvent('Live.API.getIdSitesString', array(&$idSites));

        $idSitesBind = Common::getSqlStringFieldsArray($idSites);
        $whereClause = $table . ".idsite in ($idSitesBind) ";
        return array($whereClause, $idSites);
    }
}
