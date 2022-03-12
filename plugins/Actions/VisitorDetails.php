<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions;

use Piwik\Common;
use Piwik\Date;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\Live\VisitorDetailsAbstract;
use Piwik\Site;
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendVisitorDetails(&$visitor)
    {
        $visitor['searches']     = $this->details['visit_total_searches'];
        $visitor['actions']      = $this->details['visit_total_actions'];
        $visitor['interactions'] = $this->details['visit_total_interactions'];
    }

    public function provideActionsForVisitIds(&$actions, $visitIds)
    {
        $actionDetails = $this->queryActionsForVisits($visitIds);
        // use while / array_shift combination instead of foreach to save memory
        while (is_array($actionDetails) && count($actionDetails)) {
            $action  = array_shift($actionDetails);
            $idVisit = $action['idvisit'];
            unset($action['idvisit']);
            $actions[$idVisit][] = $action;
        }
    }


    public function provideActionsForVisit(&$actions, $visitorDetails)
    {
        $actionDetails = $actions;

        $formatter = new Formatter();

        // Enrich with time spent per action
        $nextActionId = 0;
        foreach ($actionDetails as $idx => &$action) {

            if ($idx < $nextActionId || !$this->isPageView($action)) {
                unset($action['timeSpentRef']);
                continue; // skip to next page view
            }

            $action['timeSpent'] = 0;

            // search for next action with timeSpentRef
            $nextActionId = $idx;
            $nextAction   = null;

            do {
                $nextActionId++;

                $nextAction = isset($actionDetails[$nextActionId]) ? $actionDetails[$nextActionId] : null;

                if (is_null($nextAction)) {
                    // Last action of a visit.
                    // By default, Matomo does not know how long the user stayed on the page
                    // If enableHeartBeatTimer() is used in piwik.js then we can find the accurate time on page for the last pageview
                    $visitTotalTime   = $visitorDetails['visitDuration'];
                    $timeOfLastAction = Date::factory($action['serverTimePretty'])->getTimestamp();

                    $timeSpentOnAllActionsApartFromLastOne = ($timeOfLastAction - $visitorDetails['firstActionTimestamp']);
                    $timeSpentOnPage                       = $visitTotalTime - $timeSpentOnAllActionsApartFromLastOne;

                    // Safe net, we assume the time is correct when it's more than 10 seconds
                    if ($timeSpentOnPage > 10) {
                        $action['timeSpent'] = $timeSpentOnPage;
                    }
                    break;
                }

                if (!array_key_exists('timeSpentRef', $nextAction)) {
                    continue;
                }

                // Set the time spent for this action (which is the timeSpentRef of the next action)
                if ($nextAction) {
                    $action['timeSpent'] += $nextAction['timeSpentRef'] ?? 0;
                }

                // sum spent time until next page view
                if ($this->isPageView($nextAction)) {
                    break;
                }

            } while (isset($actionDetails[$nextActionId]));

            if (isset($action['timeSpent'])) {
                $action['timeSpentPretty'] = $formatter->getPrettyTimeFromSeconds($action['timeSpent'], true);
            }

            unset($action['timeSpentRef']);
        }

        $actions = $actionDetails;
    }

    private function shouldHandleAction($action)
    {
        $actionTypesToHandle = array(
            Action::TYPE_PAGE_URL,
            Action::TYPE_PAGE_TITLE,
            Action::TYPE_SITE_SEARCH,
            Action::TYPE_EVENT,
            Action::TYPE_OUTLINK,
            Action::TYPE_DOWNLOAD
        );

        return in_array($action['type'], $actionTypesToHandle) || !empty($action['eventType']);
    }

    private function isPageView($action)
    {
        $pageViewTypes = array(
            Action::TYPE_PAGE_URL,
            Action::TYPE_PAGE_TITLE,
        );

        return in_array($action['type'], $pageViewTypes);
    }

    public function extendActionDetails(&$action, $nextAction, $visitorDetails)
    {
        $formatter = new Formatter();

        if ($action['type'] == Action::TYPE_SITE_SEARCH) {
            // Handle Site Search
            $action['siteSearchKeyword'] = $action['pageTitle'];
            $action['siteSearchCategory'] = $action['search_cat'];
            $action['siteSearchCount'] = $action['search_count'];
            unset($action['pageTitle']);
        }

        unset($action['search_cat']);
        unset($action['search_count']);

        // Generation time
        if ($this->shouldHandleAction($action) && empty($action['eventType']) && isset($action['custom_float']) && $action['custom_float'] > 0) {
            $action['generationTimeMilliseconds'] = $action['custom_float'];
            $action['generationTime'] = $formatter->getPrettyTimeFromSeconds($action['custom_float'] / 1000, true);
            unset($action['custom_float']);
        }

        if (array_key_exists('custom_float', $action) && is_null($action['custom_float'])) {
            unset($action['custom_float']);
        }

        if (!array_key_exists('pageLoadTime', $action) || $action['pageLoadTime'] <= 0) {
            unset($action['pageLoadTime']);
        } else {
            $action['pageLoadTimeMilliseconds'] = $action['pageLoadTime'];
            $action['pageLoadTime'] = $formatter->getPrettyTimeFromSeconds($action['pageLoadTime'] / 1000, true);
        }

        if (array_key_exists('pageview_position', $action)) {
            $action['pageviewPosition'] = $action['pageview_position'];
            unset($action['pageview_position']);
        }

        // Reconstruct url from prefix
        if (array_key_exists('url', $action) && array_key_exists('url_prefix', $action)) {
            if ($action['url'] && stripos($action['url'], 'http://') !== 0 && stripos($action['url'], 'https://') !== 0) {
                $url = PageUrl::reconstructNormalizedUrl($action['url'], $action['url_prefix']);
                $url = Common::unsanitizeInputValue($url);
                $action['url'] = $url;
            }

            unset($action['url_prefix']);
        }

        if (!empty($action['url']) && strpos($action['url'], 'http://') === 0) {
            $host = parse_url($action['url'], PHP_URL_HOST);

            if ($host && PageUrl::shouldUseHttpsHost($visitorDetails['idSite'], $host)) {
                $action['url'] = 'https://' . mb_substr($action['url'], 7 /* = strlen('http://') */);
            }
        }

        switch ($action['type']) {
            case 'goal':
                $action['icon'] = 'plugins/Morpheus/images/goal.png';
                $action['iconSVG'] = 'plugins/Morpheus/images/goal.svg';
                $action['title'] = Piwik::translate('Goals_GoalConversion');
                $action['subtitle'] = $action['goalName'];
                if (!empty($action['revenue'])) {
                    $action['subtitle'] .= ' (' . Piwik::translate('Goals_NRevenue', $formatter->getPrettyMoney($action['revenue'], $visitorDetails['idSite'])) . ')';
                }
                break;
            case Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER:
            case Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART:
                $action['icon'] = 'plugins/Morpheus/images/' . $action['type'] . '.png';
                $action['iconSVG'] = 'plugins/Morpheus/images/' . $action['type'] . '.svg';
                if ($action['type'] == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER) {
                    $action['title'] = Piwik::translate('CoreHome_VisitStatusOrdered') . ' (' . $action['orderId'] . ')';
                } else {
                    $action['title'] = Piwik::translate('Goals_AbandonedCart');
                }

                $itemNames = implode(', ', array_column($action['itemDetails'], 'itemName'));
                $action['subtitle'] = Piwik::translate('Goals_NRevenue', $formatter->getPrettyMoney($action['revenue'], $visitorDetails['idSite']));
                $action['subtitle'] .= ' - ' .  Piwik::translate('Goals_NItems', $action['items']) . ': ' . $itemNames .')';
                break;
            case Action::TYPE_CONTENT:
                if (!empty($action['contentInteraction'])) {
                    $action['icon'] = 'plugins/Morpheus/images/contentinteraction.png';
                    $action['iconSVG'] = 'plugins/Morpheus/images/contentinteraction.svg';
                    $action['title'] = Piwik::translate('Contents_ContentInteraction') . ' (' . $action['contentInteraction'] . ')';
                } else {
                    $action['icon'] = 'plugins/Morpheus/images/contentimpression.png';
                    $action['iconSVG'] = 'plugins/Morpheus/images/contentimpression.svg';
                    $action['title'] = Piwik::translate('Contents_ContentImpression');
                }

                $action['subtitle'] = $action['contentName'];
                if (!empty($action['contentPiece'])) {
                    $action['subtitle'] .= ' - ' . $action['contentPiece'];
                }
                break;
            case Action::TYPE_DOWNLOAD:
                $action['type'] = 'download';
                $action['icon'] = 'plugins/Morpheus/images/download.png';
                $action['iconSVG'] = 'plugins/Morpheus/images/download.svg';
                $action['title'] = Piwik::translate('General_Download');
                $action['subtitle'] = $action['url'];
                break;
            case Action::TYPE_OUTLINK:
                $action['type'] = 'outlink';
                $action['icon'] = 'plugins/Morpheus/images/link.png';
                $action['iconSVG'] = 'plugins/Morpheus/images/link.svg';
                $action['title'] = Piwik::translate('General_Outlink');
                $action['subtitle'] = $action['url'];
                break;
            case Action::TYPE_SITE_SEARCH:
                $action['type'] = 'search';
                $action['icon'] = 'plugins/Morpheus/images/search.png';
                $action['iconSVG'] = 'plugins/Morpheus/images/search.svg';
                $action['title'] = Piwik::translate('Actions_SubmenuSitesearch');
                $action['subtitle'] = $action['siteSearchKeyword'];

                if (!empty($action['siteSearchCategory'])) {
                    $action['subtitle'] .= ' - ' . Piwik::translate('Actions_ColumnSearchCategory') . ': ' . $action['siteSearchCategory'];
                }

                if (!empty($action['siteSearchCount'])) {
                    $action['subtitle'] .= ' - ' . Piwik::translate('Actions_ColumnSearchResultsCount') . ': ' . $action['siteSearchCount'];
                }

                break;
            case Action::TYPE_PAGE_URL:
            case Action::TYPE_PAGE_TITLE:
            case '':
                if (!isset($action['title'])) {
                    $action['title'] = $action['pageTitle'];
                    $action['subtitle'] = $action['url'];
                }
                $action['type'] = 'action';
                $action['icon'] = '';
                $action['iconSVG'] = 'plugins/Morpheus/images/action.svg';
                break;
        }

        // Convert datetimes to the site timezone
        $dateTimeVisit              = Date::factory($action['serverTimePretty'],
            Site::getTimezoneFor($visitorDetails['idSite']));
        $action['serverTimePretty'] = $dateTimeVisit->getLocalized(Date::DATETIME_FORMAT_SHORT);
        $action['timestamp']        = $dateTimeVisit->getTimestamp();

        unset($action['idlink_va']);
    }

    /**
     * @param $idVisit
     * @return array
     * @throws \Exception
     */
    protected function queryActionsForVisits($idVisits)
    {
        $customFields = array();
        $customJoins  = array();

        Piwik::postEvent('Actions.getCustomActionDimensionFieldsAndJoins', array(&$customFields, &$customJoins));

        $customFields = array_filter($customFields);
        array_unshift($customFields, ''); // add empty element at first
        $customActionDimensionFields = implode(', ', $customFields);

        // The second join is a LEFT join to allow returning records that don't have a matching page title
        // eg. Downloads, Outlinks. For these, idaction_name is set to 0
        $pagePerformanceSelect = '';
        if (Plugin\Manager::getInstance()->isPluginActivated('PagePerformance')) {
            $pagePerformanceSelect = '( log_link_visit_action.time_network +
					log_link_visit_action.time_server +
					log_link_visit_action.time_transfer +
					log_link_visit_action.time_dom_completion +
					log_link_visit_action.time_dom_processing +
					log_link_visit_action.time_on_load ) AS pageLoadTime,';
        }

        $sql           = "
				SELECT
					log_link_visit_action.idvisit,
					COALESCE(log_action.type, log_action_title.type) AS type,
					log_action.name AS url,
					log_action.url_prefix,
					log_action_title.name AS pageTitle,
					log_action.idaction AS pageIdAction,
					log_link_visit_action.idpageview,
					log_link_visit_action.idlink_va,
					log_link_visit_action.server_time as serverTimePretty,
					log_link_visit_action.time_spent_ref_action as timeSpentRef,
					log_link_visit_action.idlink_va AS pageId,
					log_link_visit_action.custom_float,
					$pagePerformanceSelect
					log_link_visit_action.pageview_position,
					log_link_visit_action.search_cat,
					log_link_visit_action.search_count
					" . $customActionDimensionFields . "
				FROM " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action
					LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action
					ON  log_link_visit_action.idaction_url = log_action.idaction
					LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action_title
					ON  log_link_visit_action.idaction_name = log_action_title.idaction
					" . implode(" ", $customJoins) . "
				WHERE log_link_visit_action.idvisit IN ('" . implode("','", $idVisits) . "')
				ORDER BY log_link_visit_action.idvisit, server_time ASC
				 ";
        $actionDetails = $this->getDb()->fetchAll($sql);
        return $actionDetails;
    }


    private $visitedPageUrls         = array();
    private $siteSearchKeywords      = array();
    private $pageGenerationTimeTotal = 0;
    private $pageLoadTimeTotal = 0;

    public function initProfile($visits, &$profile)
    {
        $this->visitedPageUrls                 = array();
        $this->siteSearchKeywords              = array();
        $this->pageGenerationTimeTotal         = 0;
        $this->pageLoadTimeTotal               = 0;
        $profile['totalActions']               = 0;
        $profile['totalOutlinks']              = 0;
        $profile['totalDownloads']             = 0;
        $profile['totalSearches']              = 0;
        $profile['totalPageViews']             = 0;
        $profile['totalUniquePageViews']       = 0;
        $profile['totalRevisitedPages']        = 0;
        $profile['totalPageViewsWithTiming']   = 0;
        $profile['totalPageViewsWithLoadTime'] = 0;
        $profile['searches']                   = array();
    }

    public function handleProfileVisit($visit, &$profile)
    {
        $profile['totalActions'] += $visit->getColumn('actions');
    }

    public function handleProfileAction($action, &$profile)
    {
        $this->handleIfDownloadAction($action, $profile);
        $this->handleIfOutlinkAction($action, $profile);
        $this->handleIfSiteSearchAction($action, $profile);
        $this->handleIfPageViewAction($action, $profile);
        $this->handleIfPageGenerationTime($action, $profile);
        $this->handleIfPageLoadTime($action, $profile);
    }

    public function finalizeProfile($visits, &$profile)
    {
        $profile['visitedPages'] = [];

        foreach ($this->visitedPageUrls as $visitedPageUrl => $count) {
            $profile['visitedPages'][] = [
                'url' => $visitedPageUrl,
                'count' => $count
            ];
        }

        usort($profile['visitedPages'], function($a, $b) {
            if ($a['count'] == $b['count']) {
                return strcmp($a['url'], $b['url']);
            }

            return $a['count'] > $b['count'] ? -1 : 1;
        });

        $this->handleSiteSearches($profile);
        $this->handleAveragePageGenerationTime($profile);
        $this->handleAveragePageLoadTime($profile);
    }

    /**
     * @param $action
     */
    private function handleIfDownloadAction($action, &$profile)
    {
        if ($action['type'] != 'download') {
            return;
        }
        $profile['totalDownloads']++;
    }

    /**
     * @param $action
     */
    private function handleIfOutlinkAction($action, &$profile)
    {
        if ($action['type'] != 'outlink') {
            return;
        }
        $profile['totalOutlinks']++;
    }

    /**
     * @param $action
     */
    private function handleIfPageViewAction($action, &$profile)
    {
        if ($action['type'] != 'action') {
            return;
        }
        $profile['totalPageViews']++;
        $pageUrl = $action['url'];
        if (!empty($pageUrl)) {
            if (!array_key_exists($pageUrl, $this->visitedPageUrls)) {
                $this->visitedPageUrls[$pageUrl] = 0;
                $profile['totalUniquePageViews']++;
            }
            $this->visitedPageUrls[$pageUrl]++;
            if ($this->visitedPageUrls[$pageUrl] == 2) {
                $profile['totalRevisitedPages']++;
            }
        }
    }

    private function handleIfSiteSearchAction($action, &$profile)
    {
        if (!isset($action['siteSearchKeyword'])) {
            return;
        }
        $keyword = $action['siteSearchKeyword'];

        if (!isset($this->siteSearchKeywords[$keyword])) {
            $this->siteSearchKeywords[$keyword] = 0;
            ++$profile['totalSearches'];
        }
        ++$this->siteSearchKeywords[$keyword];
    }

    private function handleSiteSearches(&$profile)
    {
        // sort by visit/action
        arsort($this->siteSearchKeywords);

        foreach ($this->siteSearchKeywords as $keyword => $searchCount) {
            $profile['searches'][] = array(
                'keyword'  => $keyword,
                'searches' => $searchCount
            );
        }
    }

    private function handleIfPageGenerationTime($action, &$profile)
    {
        if (isset($action['generationTimeMilliseconds'])) {
            $this->pageGenerationTimeTotal += $action['generationTimeMilliseconds'];
            ++$profile['totalPageViewsWithTiming'];
        }
    }

    private function handleAveragePageGenerationTime(&$profile)
    {
        if ($profile['totalPageViewsWithTiming']) {
            $profile['averagePageGenerationTime'] =
                round($this->pageGenerationTimeTotal / (1000 * $profile['totalPageViewsWithTiming']), $precision = 3);
        }
    }

    private function handleIfPageLoadTime($action, &$profile)
    {
        if (isset($action['pageLoadTimeMilliseconds'])) {
            $this->pageLoadTimeTotal += $action['pageLoadTimeMilliseconds'];
            ++$profile['totalPageViewsWithLoadTime'];
        }
    }

    private function handleAveragePageLoadTime(&$profile)
    {
        if ($profile['totalPageViewsWithLoadTime']) {
            $profile['averagePageLoadTime'] =
                round($this->pageLoadTimeTotal / (1000 * $profile['totalPageViewsWithLoadTime']), $precision = 3);
        }
    }
}
