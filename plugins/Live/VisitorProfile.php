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
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Site;
use Piwik\Plugins\Referrers\API as APIReferrers;

class VisitorProfile
{
    const VISITOR_PROFILE_MAX_VISITS_TO_SHOW = 10;
    const VISITOR_PROFILE_DATE_FORMAT = '%day% %shortMonth% %longYear%';

    /**
     * @param $visits
     * @param $idSite
     * @param $visitorId
     * @param $segment
     * @param $checkForLatLong
     * @return array
     * @throws Exception
     */
    public function makeVisitorProfile(DataTable $visits, $idSite, $visitorId, $segment, $checkForLatLong)
    {
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
        $cities = array();
        $siteSearchKeywords = array();

        $pageGenerationTimeTotal = 0;

        // aggregate all requested visits info for total_* info
        /** @var DataTable\Row $visit */
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

            $countryInfo = array('country' => $countryCode,
                'nb_visits' => $nbVisits,
                'flag' => \Piwik\Plugins\UserCountry\getFlagFromCode($countryCode),
                'prettyName' => \Piwik\Plugins\UserCountry\countryTranslate($countryCode));
            if (!empty($cities[$countryCode])) {
                $countryInfo['cities'] = array_unique($cities[$countryCode]);
            }
            $result['countries'][] = $countryInfo;
        }
        foreach ($continents as $continentCode => $nbVisits) {
            $result['continents'][] = array('continent' => $continentCode,
                'nb_visits' => $nbVisits,
                'prettyName' => \Piwik\Plugins\UserCountry\continentTranslate($continentCode));
        }
        foreach ($siteSearchKeywords as $keyword => $searchCount) {
            $result['searches'][] = array('keyword' => $keyword,
                'searches' => $searchCount);
        }

        if ($result['totalPageViews']) {
            $result['averagePageGenerationTime'] =
                round($pageGenerationTimeTotal / $result['totalPageViews'], $precision = 2);
        }

        $formatter = new Formatter();
        $result['totalVisitDurationPretty'] = $formatter->getPrettyTimeFromSeconds($result['totalVisitDuration'], true);

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


        $model = new Model();
        $result['nextVisitorId'] = $model->queryAdjacentVisitorId($idSite, $visitorId, $latestVisitTime, $segment, $getNext = true);
        $result['previousVisitorId'] = $model->queryAdjacentVisitorId($idSite, $visitorId, $latestVisitTime, $segment, $getNext = false);
        return $result;
    }

    /**
     * Returns a summary for an important visit. Used to describe the first & last visits of a visitor.
     *
     * @param DataTable\Row $visit
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
     * @param DataTable\Row $visit
     * @return bool|mixed|string
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

} 