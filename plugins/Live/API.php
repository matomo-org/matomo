<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Live
 */
namespace Piwik\Plugins\Live;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable\Filter\ColumnDelete;
use Piwik\DataTable\Row;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Db;
use Piwik\MetricsFormatter;
use Piwik\Period;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Plugins\Referrers\API as APIReferrers;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tracker\Action;
use Piwik\Tracker;
use Piwik\Tracker\GoalManager;

/**
 * @see plugins/Live/Visitor.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Live/Visitor.php';

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
 * @package Live
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
        $lastMinutes = (int)$lastMinutes;

        $select = "count(*) as visits,
				SUM(log_visit.visit_total_actions) as actions,
				SUM(log_visit.visit_goal_converted) as visitsConverted,
				COUNT(DISTINCT log_visit.idvisitor) as visitors";

        $from = "log_visit";

        $where = "log_visit.idsite = ?
				AND log_visit.visit_last_action_time >= ?";

        $bind = array(
            $idSite,
            Date::factory(time() - $lastMinutes * 60)->toString('Y-m-d H:i:s')
        );

        $segment = new Segment($segment, $idSite);
        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $data = Db::fetchAll($query['sql'], $query['bind']);

        // These could be unset for some reasons, ensure they are set to 0
        if (empty($data[0]['actions'])) {
            $data[0]['actions'] = 0;
        }
        if (empty($data[0]['visitsConverted'])) {
            $data[0]['visitsConverted'] = 0;
        }
        return $data;
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
        $visitorDetails = $this->loadLastVisitorDetailsFromDatabase($idSite, $period = false, $date = false, $segment = false, $filter_limit, $filter_offset = false, $visitorId);
        $table = $this->getCleanedVisitorsFromDetails($visitorDetails, $idSite, $flat);
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
     * @param bool|int $filter_limit (optional) Only return X visits
     * @param bool|int $filter_offset (optional) Skip the first X visits (useful when paginating)
     * @param bool|int $minTimestamp (optional) Minimum timestamp to restrict the query to (useful when paginating or refreshing visits)
     * @param bool $flat
     * @param bool $doNotFetchActions
     * @return DataTable
     */
    public function getLastVisitsDetails($idSite, $period = false, $date = false, $segment = false, $filter_limit = false,
                                         $filter_offset = false, $minTimestamp = false, $flat = false, $doNotFetchActions = false)
    {
        if (empty($filter_limit)) {
            $filter_limit = 10;
        }
        Piwik::checkUserHasViewAccess($idSite);
        $visitorDetails = $this->loadLastVisitorDetailsFromDatabase($idSite, $period, $date, $segment, $filter_limit, $filter_offset, $visitorId = false, $minTimestamp);
        $dataTable = $this->getCleanedVisitorsFromDetails($visitorDetails, $idSite, $flat, $doNotFetchActions);
        return $dataTable;
    }

    /**
     * Returns an array describing a visitor using her last visits (uses a maximum of 100).
     *
     * @param int $idSite Site ID
     * @param string|false $visitorId The ID of the visitor whose profile to retrieve.
     * @param string|false $segment
     * @param bool $checkForLatLong If true, hasLatLong will appear in the output and be true if
     *                              one of the first 100 visits has a latitude/longitude.
     * @return array
     */
    public function getVisitorProfile($idSite, $visitorId = false, $segment = false, $checkForLatLong = false)
    {
        if ($visitorId === false) {
            $visitorId = $this->getMostRecentVisitorId($idSite, $segment);
        }

        $newSegment = ($segment === false ? '' : $segment . ';') . 'visitorId==' . $visitorId;

        $visits = $this->getLastVisitsDetails($idSite, $period = false, $date = false, $newSegment,
            $filter_limit = self::VISITOR_PROFILE_MAX_VISITS_TO_AGGREGATE,
            $filter_offset = false, $overrideVisitorId = false,
            $minTimestamp = false);
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

        $countries = array();
        $continents = array();
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
                if ($action['type'] == 'goal') { // handle goal conversion
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
        }

        // sort countries/continents/search keywords by visit/action
        asort($countries);
        asort($continents);
        arsort($siteSearchKeywords);

        // transform country/continents/search keywords into something that will look good in XML
        $result['countries'] = $result['continents'] = $result['searches'] = array();
        foreach ($countries as $countryCode => $nbVisits) {
            $result['countries'][] = array('country'    => $countryCode,
                                           'nb_visits'  => $nbVisits,
                                           'flag'       => \Piwik\Plugins\UserCountry\getFlagFromCode($countryCode),
                                           'prettyName' => \Piwik\Plugins\UserCountry\countryTranslate($countryCode));
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

        // get visitor IDs that are adjacent to this one in log_visit
        // TODO: make sure order of visitor ids is not changed if a returning visitor visits while the user is
        //       looking at the popup.
        $latestVisitTime = reset($rows)->getColumn('lastActionDateTime');
        $result['nextVisitorId'] = $this->getAdjacentVisitorId($idSite, $visitorId, $latestVisitTime, $segment, $getNext = true);
        $result['previousVisitorId'] = $this->getAdjacentVisitorId($idSite, $visitorId, $latestVisitTime, $segment, $getNext = false);

        /**
         * This event is called in the Live.getVisitorProfile API method. Plugins can use this event
         * to discover and add extra data to visitor profiles.
         *
         * For example, if an email address is found in a custom variable, a plugin could load the
         * gravatar for the email and add it to the visitor profile so it will display in the
         * visitor profile popup.
         *
         * The following visitor profile elements can be set to augment the visitor profile popup:
         * - visitorAvatar: A URL to an image to display in the top left corner of the popup.
         * - visitorDescription: Text to be used as the tooltip of the avatar image.
         */
        Piwik::postEvent('Live.getExtraVisitorDetails', array(&$result));

        return $result;
    }

    /**
     * Returns the visitor ID of the most recent visit.
     *
     * @param int $idSite
     * @param string|false $segment
     * @return string
     */
    public function getMostRecentVisitorId($idSite, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $visitDetails = $this->loadLastVisitorDetailsFromDatabase(
            $idSite, $period = false, $date = false, $segment, $filter_limit = 1, $filter_offset = false,
            $visitorId = false, $minTimestamp = false
        );

        if (empty($visitDetails)) {
            return false;
        }

        $visitor = new Visitor($visitDetails[0]);
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

        $select = "log_visit.idvisitor, MAX(log_visit.visit_last_action_time) as visit_last_action_time";
        $from = "log_visit";
        $where = "log_visit.idsite = ? AND log_visit.idvisitor <> UNHEX(?)";
        $whereBind = array($idSite, $visitorId);
        $orderBy = "MAX(log_visit.visit_last_action_time) $orderByDir";
        $groupBy = "log_visit.idvisitor";

        $segment = new Segment($segment, $idSite);
        $queryInfo = $segment->getSelectQuery($select, $from, $where, $whereBind, $orderBy, $groupBy);

        $sql = "SELECT sub.idvisitor, sub.visit_last_action_time
                  FROM ({$queryInfo['sql']}) as sub
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

        $serverDate = $visit->getColumn('serverDate');
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
        return $this->getLastVisitsDetails($idSite, $period = false, $date = false, $segment = false, $filter_limit, $filter_offset = false, $minTimestamp, $flat = false);
    }

    /**
     * For an array of visits, query the list of pages for this visit
     * as well as make the data human readable
     * @param array $visitorDetails
     * @param int $idSite
     * @param bool $flat whether to flatten the array (eg. 'customVariables' names/values will appear in the root array rather than in 'customVariables' key
     * @param bool $doNotFetchActions If set to true, we only fetch visit info and not actions (much faster)
     *
     * @return DataTable
     */
    private function getCleanedVisitorsFromDetails($visitorDetails, $idSite, $flat = false, $doNotFetchActions = false)
    {
        $actionsLimit = (int)Config::getInstance()->General['visitor_log_maximum_actions_per_visit'];

        $table = new DataTable();

        $site = new Site($idSite);
        $timezone = $site->getTimezone();
        $currencies = APISitesManager::getInstance()->getCurrencySymbols();
        foreach ($visitorDetails as $visitorDetail) {
            $this->cleanVisitorDetails($visitorDetail, $idSite);
            $visitor = new Visitor($visitorDetail);
            $visitorDetailsArray = $visitor->getAllVisitorDetails();

            $visitorDetailsArray['siteCurrency'] = $site->getCurrency();
            $visitorDetailsArray['siteCurrencySymbol'] = @$currencies[$site->getCurrency()];
            $visitorDetailsArray['serverTimestamp'] = $visitorDetailsArray['lastActionTimestamp'];
            $dateTimeVisit = Date::factory($visitorDetailsArray['lastActionTimestamp'], $timezone);
            $visitorDetailsArray['serverTimePretty'] = $dateTimeVisit->getLocalized('%time%');
            $visitorDetailsArray['serverDatePretty'] = $dateTimeVisit->getLocalized(Piwik::translate('CoreHome_ShortDateFormat'));

            $dateTimeVisitFirstAction = Date::factory($visitorDetailsArray['firstActionTimestamp'], $timezone);
            $visitorDetailsArray['serverDatePrettyFirstAction'] = $dateTimeVisitFirstAction->getLocalized(Piwik::translate('CoreHome_ShortDateFormat'));
            $visitorDetailsArray['serverTimePrettyFirstAction'] = $dateTimeVisitFirstAction->getLocalized('%time%');

            $visitorDetailsArray['actionDetails'] = array();
            if (!$doNotFetchActions) {
                $visitorDetailsArray = $this->enrichVisitorArrayWithActions($visitorDetailsArray, $actionsLimit, $timezone);
            }

            if ($flat) {
                $visitorDetailsArray = $this->flattenVisitorDetailsArray($visitorDetailsArray);
            }
            $table->addRowFromArray(array(Row::COLUMNS => $visitorDetailsArray));
        }
        return $table;
    }

    private function getCustomVariablePrettyKey($key)
    {
        $rename = array(
            Action::CVAR_KEY_SEARCH_CATEGORY => Piwik::translate('Actions_ColumnSearchCategory'),
            Action::CVAR_KEY_SEARCH_COUNT    => Piwik::translate('Actions_ColumnSearchResultsCount'),
        );
        if (isset($rename[$key])) {
            return $rename[$key];
        }
        return $key;
    }

    /**
     * The &flat=1 feature is used by API.getSuggestedValuesForSegment
     *
     * @param $visitorDetailsArray
     * @return array
     */
    private function flattenVisitorDetailsArray($visitorDetailsArray)
    {
        // NOTE: if you flatten more fields from the "actionDetails" array
        //       ==> also update API/API.php getSuggestedValuesForSegment(), the $segmentsNeedActionsInfo array

        // flatten visit custom variables
        if (is_array($visitorDetailsArray['customVariables'])) {
            foreach ($visitorDetailsArray['customVariables'] as $thisCustomVar) {
                $visitorDetailsArray = array_merge($visitorDetailsArray, $thisCustomVar);
            }
            unset($visitorDetailsArray['customVariables']);
        }

        // flatten page views custom variables
        $count = 1;
        foreach ($visitorDetailsArray['actionDetails'] as $action) {
            if (!empty($action['customVariables'])) {
                foreach ($action['customVariables'] as $thisCustomVar) {
                    foreach ($thisCustomVar as $cvKey => $cvValue) {
                        $flattenedKeyName = $cvKey . ColumnDelete::APPEND_TO_COLUMN_NAME_TO_KEEP . $count;
                        $visitorDetailsArray[$flattenedKeyName] = $cvValue;
                        $count++;
                    }
                }
            }
        }

        // Flatten Goals
        $count = 1;
        foreach ($visitorDetailsArray['actionDetails'] as $action) {
            if (!empty($action['goalId'])) {
                $flattenedKeyName = 'visitConvertedGoalId' . ColumnDelete::APPEND_TO_COLUMN_NAME_TO_KEEP . $count;
                $visitorDetailsArray[$flattenedKeyName] = $action['goalId'];
                $count++;
            }
        }

        // Flatten Page Titles/URLs
        $count = 1;
        foreach ($visitorDetailsArray['actionDetails'] as $action) {
            if (!empty($action['url'])) {
                $flattenedKeyName = 'pageUrl' . ColumnDelete::APPEND_TO_COLUMN_NAME_TO_KEEP . $count;
                $visitorDetailsArray[$flattenedKeyName] = $action['url'];
            }

            if (!empty($action['pageTitle'])) {
                $flattenedKeyName = 'pageTitle' . ColumnDelete::APPEND_TO_COLUMN_NAME_TO_KEEP . $count;
                $visitorDetailsArray[$flattenedKeyName] = $action['pageTitle'];
            }

            if (!empty($action['siteSearchKeyword'])) {
                $flattenedKeyName = 'siteSearchKeyword' . ColumnDelete::APPEND_TO_COLUMN_NAME_TO_KEEP . $count;
                $visitorDetailsArray[$flattenedKeyName] = $action['siteSearchKeyword'];
            }
            $count++;
        }

        // Entry/exit pages
        $firstAction = $lastAction = false;
        foreach ($visitorDetailsArray['actionDetails'] as $action) {
            if ($action['type'] == 'action') {
                if (empty($firstAction)) {
                    $firstAction = $action;
                }
                $lastAction = $action;
            }
        }

        if (!empty($firstAction['pageTitle'])) {
            $visitorDetailsArray['entryPageTitle'] = $firstAction['pageTitle'];
        }
        if (!empty($firstAction['url'])) {
            $visitorDetailsArray['entryPageUrl'] = $firstAction['url'];
        }
        if (!empty($lastAction['pageTitle'])) {
            $visitorDetailsArray['exitPageTitle'] = $lastAction['pageTitle'];
        }
        if (!empty($lastAction['url'])) {
            $visitorDetailsArray['exitPageUrl'] = $lastAction['url'];
        }

        return $visitorDetailsArray;
    }

    private function sortByServerTime($a, $b)
    {
        $ta = strtotime($a['serverTimePretty']);
        $tb = strtotime($b['serverTimePretty']);
        return $ta < $tb
            ? -1
            : ($ta == $tb
                ? 0
                : 1);
    }

    private function loadLastVisitorDetailsFromDatabase($idSite, $period, $date, $segment = false, $filter_limit = false,
                                                        $filter_offset = false, $visitorId = false, $minTimestamp = false)
    {
        if (empty($filter_limit)) {
            $filter_limit = 100;
        }

        $where = $whereBind = array();
        $where[] = "log_visit.idsite = ? ";
        $whereBind[] = $idSite;
        $orderBy = "idsite, visit_last_action_time DESC";
        $orderByParent = "sub.visit_last_action_time DESC";
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
            && empty($filter_limit)
            && empty($filter_offset)
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
                $processedPeriod = Period::factory($period, $processedDate);
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

        $sqlLimit = $filter_limit >= 1 ? " LIMIT " . (int)$filter_offset . ", " . (int)$filter_limit : "";

        // Group by idvisit so that a visitor converting 2 goals only appears once
        $sql = "
			SELECT sub.*
			FROM (
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

        return $data;
    }

    /**
     * Removes fields that are not meant to be displayed (md5 config hash)
     * Or that the user should only access if he is super user or admin (cookie, IP)
     *
     * @param array $visitorDetails
     * @param int $idSite
     * @return void
     */
    private function cleanVisitorDetails(&$visitorDetails, $idSite)
    {
        $toUnset = array('config_id');
        if (Piwik::isUserIsAnonymous()) {
            $toUnset[] = 'idvisitor';
            $toUnset[] = 'location_ip';
        }
        foreach ($toUnset as $keyName) {
            if (isset($visitorDetails[$keyName])) {
                unset($visitorDetails[$keyName]);
            }
        }
    }

    /**
     * @param $visitorDetailsArray
     * @param $actionsLimit
     * @param $timezone
     * @return array
     */
    private function enrichVisitorArrayWithActions($visitorDetailsArray, $actionsLimit, $timezone)
    {
        $idVisit = $visitorDetailsArray['idVisit'];

        $sqlCustomVariables = '';
        for ($i = 1; $i <= Tracker::MAX_CUSTOM_VARIABLES; $i++) {
            $sqlCustomVariables .= ', custom_var_k' . $i . ', custom_var_v' . $i;
        }
        // The second join is a LEFT join to allow returning records that don't have a matching page title
        // eg. Downloads, Outlinks. For these, idaction_name is set to 0
        $sql = "
				SELECT
					COALESCE(log_action.type,log_action_title.type) AS type,
					log_action.name AS url,
					log_action.url_prefix,
					log_action_title.name AS pageTitle,
					log_action.idaction AS pageIdAction,
					log_link_visit_action.idlink_va AS pageId,
					log_link_visit_action.server_time as serverTimePretty,
					log_link_visit_action.time_spent_ref_action as timeSpentRef,
					log_link_visit_action.custom_float
					$sqlCustomVariables
				FROM " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action
					LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action
					ON  log_link_visit_action.idaction_url = log_action.idaction
					LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action_title
					ON  log_link_visit_action.idaction_name = log_action_title.idaction
				WHERE log_link_visit_action.idvisit = ?
				ORDER BY server_time ASC
				LIMIT 0, $actionsLimit
				 ";
        $actionDetails = Db::fetchAll($sql, array($idVisit));

        foreach ($actionDetails as $actionIdx => &$actionDetail) {
            $actionDetail =& $actionDetails[$actionIdx];
            $customVariablesPage = array();
            for ($i = 1; $i <= Tracker::MAX_CUSTOM_VARIABLES; $i++) {
                if (!empty($actionDetail['custom_var_k' . $i])) {
                    $cvarKey = $actionDetail['custom_var_k' . $i];
                    $cvarKey = $this->getCustomVariablePrettyKey($cvarKey);
                    $customVariablesPage[$i] = array(
                        'customVariablePageName' . $i  => $cvarKey,
                        'customVariablePageValue' . $i => $actionDetail['custom_var_v' . $i],
                    );
                }
                unset($actionDetail['custom_var_k' . $i]);
                unset($actionDetail['custom_var_v' . $i]);
            }
            if (!empty($customVariablesPage)) {
                $actionDetail['customVariables'] = $customVariablesPage;
            }

            // Reconstruct url from prefix
            $actionDetail['url'] = Action::reconstructNormalizedUrl($actionDetail['url'], $actionDetail['url_prefix']);
            unset($actionDetail['url_prefix']);

            // Set the time spent for this action (which is the timeSpentRef of the next action)
            if (isset($actionDetails[$actionIdx + 1])) {
                $actionDetail['timeSpent'] = $actionDetails[$actionIdx + 1]['timeSpentRef'];
                $actionDetail['timeSpentPretty'] = \Piwik\MetricsFormatter::getPrettyTimeFromSeconds($actionDetail['timeSpent']);
            }
            unset($actionDetails[$actionIdx]['timeSpentRef']); // not needed after timeSpent is added

            // Handle generation time
            if ($actionDetail['custom_float'] > 0) {
                $actionDetail['generationTime'] = \Piwik\MetricsFormatter::getPrettyTimeFromSeconds($actionDetail['custom_float'] / 1000);
            }
            unset($actionDetail['custom_float']);

            // Handle Site Search
            if ($actionDetail['type'] == Action::TYPE_SITE_SEARCH) {
                $actionDetail['siteSearchKeyword'] = $actionDetail['pageTitle'];
                unset($actionDetail['pageTitle']);
            }
        }

        // If the visitor converted a goal, we shall select all Goals
        $sql = "
				SELECT
						'goal' as type,
						goal.name as goalName,
						goal.idgoal as goalId,
						goal.revenue as revenue,
						log_conversion.idlink_va as goalPageId,
						log_conversion.server_time as serverTimePretty,
						log_conversion.url as url
				FROM " . Common::prefixTable('log_conversion') . " AS log_conversion
				LEFT JOIN " . Common::prefixTable('goal') . " AS goal
					ON (goal.idsite = log_conversion.idsite
						AND
						goal.idgoal = log_conversion.idgoal)
					AND goal.deleted = 0
				WHERE log_conversion.idvisit = ?
					AND log_conversion.idgoal > 0
                ORDER BY server_time ASC
				LIMIT 0, $actionsLimit
			";
        $goalDetails = Db::fetchAll($sql, array($idVisit));

        $sql = "SELECT
						case idgoal when " . GoalManager::IDGOAL_CART . " then '" . Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART . "' else '" . Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER . "' end as type,
						idorder as orderId,
						" . LogAggregator::getSqlRevenue('revenue') . " as revenue,
						" . LogAggregator::getSqlRevenue('revenue_subtotal') . " as revenueSubTotal,
						" . LogAggregator::getSqlRevenue('revenue_tax') . " as revenueTax,
						" . LogAggregator::getSqlRevenue('revenue_shipping') . " as revenueShipping,
						" . LogAggregator::getSqlRevenue('revenue_discount') . " as revenueDiscount,
						items as items,

						log_conversion.server_time as serverTimePretty
					FROM " . Common::prefixTable('log_conversion') . " AS log_conversion
					WHERE idvisit = ?
						AND idgoal <= " . GoalManager::IDGOAL_ORDER . "
					ORDER BY server_time ASC
					LIMIT 0, $actionsLimit";
        $ecommerceDetails = Db::fetchAll($sql, array($idVisit));

        foreach ($ecommerceDetails as &$ecommerceDetail) {
            if ($ecommerceDetail['type'] == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART) {
                unset($ecommerceDetail['orderId']);
                unset($ecommerceDetail['revenueSubTotal']);
                unset($ecommerceDetail['revenueTax']);
                unset($ecommerceDetail['revenueShipping']);
                unset($ecommerceDetail['revenueDiscount']);
            }

            // 25.00 => 25
            foreach ($ecommerceDetail as $column => $value) {
                if (strpos($column, 'revenue') !== false) {
                    if ($value == round($value)) {
                        $ecommerceDetail[$column] = round($value);
                    }
                }
            }
        }

        // Enrich ecommerce carts/orders with the list of products
        usort($ecommerceDetails, array($this, 'sortByServerTime'));
        foreach ($ecommerceDetails as $key => &$ecommerceConversion) {
            $sql = "SELECT
							log_action_sku.name as itemSKU,
							log_action_name.name as itemName,
							log_action_category.name as itemCategory,
							" . LogAggregator::getSqlRevenue('price') . " as price,
							quantity as quantity
						FROM " . Common::prefixTable('log_conversion_item') . "
							INNER JOIN " . Common::prefixTable('log_action') . " AS log_action_sku
							ON  idaction_sku = log_action_sku.idaction
							LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action_name
							ON  idaction_name = log_action_name.idaction
							LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action_category
							ON idaction_category = log_action_category.idaction
						WHERE idvisit = ?
							AND idorder = ?
							AND deleted = 0
						LIMIT 0, $actionsLimit
				";
            $bind = array($idVisit, isset($ecommerceConversion['orderId'])
                ? $ecommerceConversion['orderId']
                : GoalManager::ITEM_IDORDER_ABANDONED_CART
            );

            $itemsDetails = Db::fetchAll($sql, $bind);
            foreach ($itemsDetails as &$detail) {
                if ($detail['price'] == round($detail['price'])) {
                    $detail['price'] = round($detail['price']);
                }
            }
            $ecommerceConversion['itemDetails'] = $itemsDetails;
        }

        $actions = array_merge($actionDetails, $goalDetails, $ecommerceDetails);

        usort($actions, array($this, 'sortByServerTime'));

        $visitorDetailsArray['actionDetails'] = $actions;
        foreach ($visitorDetailsArray['actionDetails'] as &$details) {
            switch ($details['type']) {
                case 'goal':
                    $details['icon'] = 'plugins/Zeitgeist/images/goal.png';
                    break;
                case Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER:
                case Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART:
                    $details['icon'] = 'plugins/Zeitgeist/images/' . $details['type'] . '.gif';
                    break;
                case Tracker\ActionInterface::TYPE_DOWNLOAD:
                    $details['type'] = 'download';
                    $details['icon'] = 'plugins/Zeitgeist/images/download.png';
                    break;
                case Tracker\ActionInterface::TYPE_OUTLINK:
                    $details['type'] = 'outlink';
                    $details['icon'] = 'plugins/Zeitgeist/images/link.gif';
                    break;
                case Action::TYPE_SITE_SEARCH:
                    $details['type'] = 'search';
                    $details['icon'] = 'plugins/Zeitgeist/images/search_ico.png';
                    break;
                default:
                    $details['type'] = 'action';
                    $details['icon'] = null;
                    break;
            }
            // Convert datetimes to the site timezone
            $dateTimeVisit = Date::factory($details['serverTimePretty'], $timezone);
            $details['serverTimePretty'] = $dateTimeVisit->getLocalized(Piwik::translate('CoreHome_ShortDateFormat') . ' %time%');
        }
        $visitorDetailsArray['goalConversions'] = count($goalDetails);
        return $visitorDetailsArray;
    }
}
