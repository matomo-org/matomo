<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions;

use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Plugins\Live\VisitorDetailsAbstract;
use Piwik\Site;
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;

class VisitorDetails extends VisitorDetailsAbstract
{
    const EVENT_VALUE_PRECISION = 3;

    public function extendVisitorDetails(&$visitor)
    {
        $visitor['searches']     = $this->details['visit_total_searches'];
        $visitor['actions']      = $this->details['visit_total_actions'];
        $visitor['interactions'] = $this->details['visit_total_interactions'];
    }

    public function provideActions(&$actions, $visitorDetails)
    {
        $actionDetails = $this->queryActionsForVisit($visitorDetails['idVisit']);

        $formatter = new Formatter();

        // Enrich with time spent per action
        $nextActionId = 0;
        foreach ($actionDetails as $idx => &$action) {

            if ($idx < $nextActionId) {
                continue; // skip to next action having timeSpentRef
            }

            // search for next action with timeSpentRef
            $nextActionId = $idx+1;
            $nextAction = null;

            while (isset($actionDetails[$nextActionId]) && $actionDetails[$nextActionId]['type'] == Action::TYPE_CONTENT) {
                $nextActionId++; // skip content interactions
            }
            $nextAction = isset($actionDetails[$nextActionId]) ? $actionDetails[$nextActionId] : null;

            // Set the time spent for this action (which is the timeSpentRef of the next action)
            if ($nextAction) {
                $action['timeSpent'] = $nextAction['timeSpentRef'];
            } else {

                // Last action of a visit.
                // By default, Piwik does not know how long the user stayed on the page
                // If enableHeartBeatTimer() is used in piwik.js then we can find the accurate time on page for the last pageview
                $visitTotalTime = $visitorDetails['visitDuration'];
                $timeOfLastAction = Date::factory($action['serverTimePretty'])->getTimestamp();

                $timeSpentOnAllActionsApartFromLastOne = ($timeOfLastAction - $visitorDetails['firstActionTimestamp']);
                $timeSpentOnPage = $visitTotalTime - $timeSpentOnAllActionsApartFromLastOne;

                // Safe net, we assume the time is correct when it's more than 10 seconds
                if ($timeSpentOnPage > 10) {
                    $action['timeSpent'] = $timeSpentOnPage;
                }
            }

            if (isset($action['timeSpent'])) {
                $action['timeSpentPretty'] = $formatter->getPrettyTimeFromSeconds($action['timeSpent'], true);
            }

            unset($action['timeSpentRef']); // not needed after timeSpent is added
        }

        $actions = array_merge($actions, $actionDetails);
    }

    public function extendActionDetails(&$action, $nextAction, $visitorDetails)
    {
        $formatter = new Formatter();

        if ($action['type'] == Action::TYPE_EVENT) {
            // Handle Event
            if (strlen($action['pageTitle']) > 0) {
                $action['eventName'] = $action['pageTitle'];
            }

            unset($action['pageTitle']);

        } else if ($action['type'] == Action::TYPE_SITE_SEARCH) {
            // Handle Site Search
            $action['siteSearchKeyword'] = $action['pageTitle'];
            unset($action['pageTitle']);
        }

        // Event value / Generation time
        if ($action['type'] == Action::TYPE_EVENT) {
            if (strlen($action['custom_float']) > 0) {
                $action['eventValue'] = round($action['custom_float'], self::EVENT_VALUE_PRECISION);
            }
        } elseif (isset($action['custom_float']) && $action['custom_float'] > 0) {
            $action['generationTimeMilliseconds'] = $action['custom_float'];
            $action['generationTime'] = $formatter->getPrettyTimeFromSeconds($action['custom_float'] / 1000, true);
        }
        unset($action['custom_float']);

        if ($action['type'] != Action::TYPE_EVENT) {
            unset($action['eventCategory']);
            unset($action['eventAction']);
        }

        if (array_key_exists('interaction_position', $action)) {
            $action['interactionPosition'] = $action['interaction_position'];
            unset($action['interaction_position']);
        }

        // Reconstruct url from prefix
        if (array_key_exists('url', $action) && array_key_exists('url_prefix', $action)) {
            $url = PageUrl::reconstructNormalizedUrl($action['url'], $action['url_prefix']);
            $url = Common::unsanitizeInputValue($url);

            $action['url'] = $url;
            unset($action['url_prefix']);
        }

        switch ($action['type']) {
            case 'goal':
                $action['icon'] = 'plugins/Morpheus/images/goal.png';
                break;
            case Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER:
            case Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART:
                $action['icon'] = 'plugins/Morpheus/images/' . $action['type'] . '.png';
                break;
            case Action::TYPE_DOWNLOAD:
                $action['type'] = 'download';
                $action['icon'] = 'plugins/Morpheus/images/download.png';
                break;
            case Action::TYPE_OUTLINK:
                $action['type'] = 'outlink';
                $action['icon'] = 'plugins/Morpheus/images/link.png';
                break;
            case Action::TYPE_SITE_SEARCH:
                $action['type'] = 'search';
                $action['icon'] = 'plugins/Morpheus/images/search_ico.png';
                break;
            case Action::TYPE_EVENT:
                $action['type'] = 'event';
                $action['icon'] = 'plugins/Morpheus/images/event.png';
                break;
            case Action::TYPE_PAGE_URL:
            case '':
                $action['type'] = 'action';
                $action['icon'] = null;
                break;
        }

        // Convert datetimes to the site timezone
        $dateTimeVisit = Date::factory($action['serverTimePretty'], Site::getTimezoneFor($visitorDetails['idSite']));
        $action['serverTimePretty'] = $dateTimeVisit->getLocalized(Date::DATETIME_FORMAT_SHORT);
        $action['timestamp'] = $dateTimeVisit->getTimestamp();

        unset($action['idlink_va']);
    }

    /**
     * @param $idVisit
     * @return array
     * @throws \Exception
     */
    protected function queryActionsForVisit($idVisit)
    {
        $actionsLimit = (int)Config::getInstance()->General['visitor_log_maximum_actions_per_visit'];
        $maxCustomVariables = CustomVariables::getNumUsableCustomVariables();

        $sqlCustomVariables = '';
        for ($i = 1; $i <= $maxCustomVariables; $i++) {
            $sqlCustomVariables .= ', custom_var_k' . $i . ', custom_var_v' . $i;
        }
        // The second join is a LEFT join to allow returning records that don't have a matching page title
        // eg. Downloads, Outlinks. For these, idaction_name is set to 0
        $sql = "
				SELECT
					COALESCE(log_action_event_category.type, log_action.type, log_action_title.type) AS type,
					log_action.name AS url,
					log_action.url_prefix,
					log_action_title.name AS pageTitle,
					log_action.idaction AS pageIdAction,
					log_link_visit_action.idlink_va,
					log_link_visit_action.server_time as serverTimePretty,
					log_link_visit_action.time_spent_ref_action as timeSpentRef,
					log_link_visit_action.idlink_va AS pageId,
					log_link_visit_action.custom_float,
					log_link_visit_action.interaction_position
					" . $sqlCustomVariables . ",
					log_action_event_category.name AS eventCategory,
					log_action_event_action.name as eventAction
				FROM " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action
					LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action
					ON  log_link_visit_action.idaction_url = log_action.idaction
					LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action_title
					ON  log_link_visit_action.idaction_name = log_action_title.idaction
					LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action_event_category
					ON  log_link_visit_action.idaction_event_category = log_action_event_category.idaction
					LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action_event_action
					ON  log_link_visit_action.idaction_event_action = log_action_event_action.idaction
				WHERE log_link_visit_action.idvisit = ?
				ORDER BY server_time ASC
				LIMIT 0, $actionsLimit
				 ";
        $actionDetails = Db::fetchAll($sql, array($idVisit));
        return $actionDetails;
    }
}