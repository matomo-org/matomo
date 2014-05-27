<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\Config;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Tracker;

class Visitor
{
    function __construct(Request $request, $visitorInfo = array(), $customVariables = null)
    {
        $this->request = $request;
        $this->visitorInfo = $visitorInfo;
        $this->customVariables = $customVariables;

        $settings = new Tracker\Settings($request);
        $this->userInfo = $settings->getInfo();
    }

    /**
     * This methods tries to see if the visitor has visited the website before.
     *
     * We have to split the visitor into one of the category
     * - Known visitor
     * - New visitor
     */
    function recognize()
    {
        $configId = $this->userInfo['config_id'];

        $idVisitor = $this->request->getVisitorId();
        $isVisitorIdToLookup = !empty($idVisitor);

        if ($isVisitorIdToLookup) {
            $this->visitorInfo['idvisitor'] = $idVisitor;
            Common::printDebug("Matching visitors with: visitorId=" . bin2hex($this->visitorInfo['idvisitor']) . " OR configId=" . bin2hex($configId));
        } else {
            Common::printDebug("Visitor doesn't have the piwik cookie...");
        }

        $selectCustomVariables = '';
        // No custom var were found in the request, so let's copy the previous one in a potential conversion later
        if (!$this->customVariables) {
            $maxCustomVariables = CustomVariables::getMaxCustomVariables();

            for ($index = 1; $index <= $maxCustomVariables; $index++) {
                $selectCustomVariables .= ', custom_var_k' . $index . ', custom_var_v' . $index;
            }
        }

        $persistedVisitAttributes = Visit::getVisitFieldsPersist();

        $selectFields = implode(", ", $persistedVisitAttributes);

        $select = "SELECT
                        visit_last_action_time,
                        visit_first_action_time,
                        $selectFields
                        $selectCustomVariables
        ";
        $from = "FROM " . Common::prefixTable('log_visit');

        list($timeLookBack, $timeLookAhead) = Visit::getWindowLookupThisVisit($this->request);

        $shouldMatchOneFieldOnly = Visit::shouldLookupOneVisitorFieldOnly($this->request, $isVisitorIdToLookup);

        // Two use cases:
        // 1) there is no visitor ID so we try to match only on config_id (heuristics)
        // 		Possible causes of no visitor ID: no browser cookie support, direct Tracking API request without visitor ID passed,
        //        importing server access logs with import_logs.py, etc.
        // 		In this case we use config_id heuristics to try find the visitor in tahhhe past. There is a risk to assign
        // 		this page view to the wrong visitor, but this is better than creating artificial visits.
        // 2) there is a visitor ID and we trust it (config setting trust_visitors_cookies, OR it was set using &cid= in tracking API),
        //      and in these cases, we force to look up this visitor id
        $whereCommon = "visit_last_action_time >= ? AND visit_last_action_time <= ? AND idsite = ?";
        $bindSql = array(
            $timeLookBack,
            $timeLookAhead,
            $this->request->getIdSite()
        );

        if ($shouldMatchOneFieldOnly) {
            if ($isVisitorIdToLookup) {
                $whereCommon .= ' AND idvisitor = ?';
                $bindSql[] = $this->visitorInfo['idvisitor'];
            } else {
                $whereCommon .= ' AND config_id = ?';
                $bindSql[] = $configId;
            }

            $sql = "$select
            $from
            WHERE " . $whereCommon . "
            ORDER BY visit_last_action_time DESC
            LIMIT 1";
        } // We have a config_id AND a visitor_id. We match on either of these.
        // 		Why do we also match on config_id?
        //		we do not trust the visitor ID only. Indeed, some browsers, or browser addons,
        // 		cause the visitor id from the 1st party cookie to be different on each page view!
        // 		It is not acceptable to create a new visit every time such browser does a page view,
        // 		so we also backup by searching for matching config_id.
        // We use a UNION here so that each sql query uses its own INDEX
        else {
            // will use INDEX index_idsite_config_datetime (idsite, config_id, visit_last_action_time)
            $where = ' AND config_id = ?';
            $bindSql[] = $configId;
            $sqlConfigId = "$select ,
                0 as priority
                $from
                WHERE $whereCommon $where
                ORDER BY visit_last_action_time DESC
                LIMIT 1
            ";
            // will use INDEX index_idsite_idvisitor (idsite, idvisitor)
            $bindSql[] = $timeLookBack;
            $bindSql[] = $timeLookAhead;
            $bindSql[] = $this->request->getIdSite();
            $where = ' AND idvisitor = ?';
            $bindSql[] = $this->visitorInfo['idvisitor'];
            $sqlVisitorId = "$select ,
                1 as priority
                $from
                WHERE $whereCommon $where
                ORDER BY visit_last_action_time DESC
                LIMIT 1
            ";

            // We join both queries and favor the one matching the visitor_id if it did match
            $sql = " ( $sqlConfigId )
                UNION
                ( $sqlVisitorId )
                ORDER BY priority DESC
                LIMIT 1";
        }

        $visitRow = Tracker::getDatabase()->fetch($sql, $bindSql);

        $isNewVisitForced = $this->request->getParam('new_visit');
        $isNewVisitForced = !empty($isNewVisitForced);
        $newVisitEnforcedAPI = $isNewVisitForced
            && ($this->request->isAuthenticated()
                || !Config::getInstance()->Tracker['new_visit_api_requires_admin']);
        $enforceNewVisit = $newVisitEnforcedAPI || Config::getInstance()->Debug['tracker_always_new_visitor'];

        if (!$enforceNewVisit
            && $visitRow
            && count($visitRow) > 0
        ) {
            // These values will be used throughout the request
            $this->visitorInfo['visit_last_action_time'] = strtotime($visitRow['visit_last_action_time']);
            $this->visitorInfo['visit_first_action_time'] = strtotime($visitRow['visit_first_action_time']);

            foreach($persistedVisitAttributes as $field) {
                $this->visitorInfo[$field] = $visitRow[$field];
            }

            // Custom Variables copied from Visit in potential later conversion
            if (!empty($selectCustomVariables)) {
                $maxCustomVariables = CustomVariables::getMaxCustomVariables();
                for ($i = 1; $i <= $maxCustomVariables; $i++) {
                    if (isset($visitRow['custom_var_k' . $i])
                        && strlen($visitRow['custom_var_k' . $i])
                    ) {
                        $this->visitorInfo['custom_var_k' . $i] = $visitRow['custom_var_k' . $i];
                    }
                    if (isset($visitRow['custom_var_v' . $i])
                        && strlen($visitRow['custom_var_v' . $i])
                    ) {
                        $this->visitorInfo['custom_var_v' . $i] = $visitRow['custom_var_v' . $i];
                    }
                }
            }

            Common::printDebug("The visitor is known (idvisitor = " . bin2hex($this->visitorInfo['idvisitor']) . ",
                    config_id = " . bin2hex($configId) . ",
                    idvisit = {$this->visitorInfo['idvisit']},
                    last action = " . date("r", $this->visitorInfo['visit_last_action_time']) . ",
                    first action = " . date("r", $this->visitorInfo['visit_first_action_time']) . ",
                    visit_goal_buyer' = " . $this->visitorInfo['visit_goal_buyer'] . ")");
            //Common::printDebug($this->visitorInfo);
        } else {
            Common::printDebug("The visitor was not matched with an existing visitor...");
        }
    }

    function getVisitorInfo()
    {
        return $this->visitorInfo;
    }
}