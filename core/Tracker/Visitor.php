<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\Config;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Piwik;
use Piwik\Tracker;

class Visitor
{
    private $visitorKnown = false;
    private $request;
    private $visitorInfo;
    private $configId;

    public function __construct(Request $request, $configId, $visitorInfo = array(), $customVariables = null)
    {
        $this->request = $request;
        $this->visitorInfo = $visitorInfo;
        $this->customVariables = $customVariables;
        $this->configId = $configId;
    }

    /**
     * This methods tries to see if the visitor has visited the website before.
     *
     * We have to split the visitor into one of the category
     * - Known visitor
     * - New visitor
     */
    public function recognize()
    {
        $this->setIsVisitorKnown(false);

        $configId = $this->configId;

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

        $persistedVisitAttributes = self::getVisitFieldsPersist();
        array_unshift($persistedVisitAttributes, 'visit_first_action_time');
        array_unshift($persistedVisitAttributes, 'visit_last_action_time');
        $persistedVisitAttributes = array_unique($persistedVisitAttributes);

        $selectFields = implode(", ", $persistedVisitAttributes);

        $select = "SELECT
                        $selectFields
                        $selectCustomVariables
        ";
        $from = "FROM " . Common::prefixTable('log_visit');

        list($timeLookBack, $timeLookAhead) = $this->getWindowLookupThisVisit();

        $shouldMatchOneFieldOnly = $this->shouldLookupOneVisitorFieldOnly($isVisitorIdToLookup);

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
        $enforceNewVisit = $isNewVisitForced || Config::getInstance()->Debug['tracker_always_new_visitor'];

        if (!$enforceNewVisit
            && $visitRow
            && count($visitRow) > 0
        ) {

            // These values will be used throughout the request
            foreach($persistedVisitAttributes as $field) {
                $this->visitorInfo[$field] = $visitRow[$field];
            }

            $this->visitorInfo['visit_last_action_time'] = strtotime($visitRow['visit_last_action_time']);
            $this->visitorInfo['visit_first_action_time'] = strtotime($visitRow['visit_first_action_time']);

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

            $this->setIsVisitorKnown(true);
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

    /**
     * By default, we look back 30 minutes to find a previous visitor (for performance reasons).
     * In some cases, it is useful to look back and count unique visitors more accurately. You can set custom lookback window in
     * [Tracker] window_look_back_for_visitor
     *
     * The returned value is the window range (Min, max) that the matched visitor should fall within
     *
     * @return array( datetimeMin, datetimeMax )
     */
    protected function getWindowLookupThisVisit()
    {
        $visitStandardLength = Config::getInstance()->Tracker['visit_standard_length'];
        $lookBackNSecondsCustom = Config::getInstance()->Tracker['window_look_back_for_visitor'];

        $lookAheadNSeconds = $visitStandardLength;
        $lookBackNSeconds = $visitStandardLength;
        if ($lookBackNSecondsCustom > $lookBackNSeconds) {
            $lookBackNSeconds = $lookBackNSecondsCustom;
        }

        $timeLookBack = date('Y-m-d H:i:s', $this->request->getCurrentTimestamp() - $lookBackNSeconds);
        $timeLookAhead = date('Y-m-d H:i:s', $this->request->getCurrentTimestamp() + $lookAheadNSeconds);

        return array($timeLookBack, $timeLookAhead);
    }

    protected function shouldLookupOneVisitorFieldOnly($isVisitorIdToLookup)
    {
        // This setting would be enabled for Intranet websites, to ensure that visitors using all the same computer config, same IP
        // are not counted as 1 visitor. In this case, we want to enforce and trust the visitor ID from the cookie.
        $trustCookiesOnly = Config::getInstance()->Tracker['trust_visitors_cookies'];

        // If a &cid= was set, we force to select this visitor (or create a new one)
        $isForcedVisitorIdMustMatch = ($this->request->getForcedVisitorId() != null);

        // if &iud was set, we force to select this visitor (or create new one)
        $isForcedUserIdMustMatch = ($this->request->getForcedUserId() !== null);

        $shouldMatchOneFieldOnly = (($isVisitorIdToLookup && $trustCookiesOnly)
            || $isForcedVisitorIdMustMatch
            || $isForcedUserIdMustMatch
            || !$isVisitorIdToLookup);
        return $shouldMatchOneFieldOnly;
    }

    /**
     * @return array
     */
    public static function getVisitFieldsPersist()
    {
        $fields = array(
            'idvisitor',
            'idvisit',
            'user_id',

            'visit_exit_idaction_url',
            'visit_exit_idaction_name',
            'visitor_returning',
            'visitor_days_since_first',
            'visitor_days_since_order',
            'visitor_count_visits',
            'visit_goal_buyer',

            'location_country',
            'location_region',
            'location_city',
            'location_latitude',
            'location_longitude',

            'referer_name',
            'referer_keyword',
            'referer_type',
        );

        $dimensions = VisitDimension::getAllDimensions();

        foreach ($dimensions as $dimension) {
            if ($dimension->hasImplementedEvent('onExistingVisit')) {
                $fields[] = $dimension->getColumnName();
            }

            /**
             * This event collects a list of [visit entity]() properties that should be loaded when reading
             * the existing visit. Properties that appear in this list will be available in other tracking
             * events such as 'onExistingVisit'.
             *
             * Plugins can use this event to load additional visit entity properties for later use during tracking.
             */
            foreach ($dimension->getRequiredVisitFields() as $field) {
                $fields[] = $field;
            }
        }

        /**
         * @ignore
         */
        Piwik::postEvent('Tracker.getVisitFieldsToPersist', array(&$fields));

        return $fields;
    }

    public function getVisitorInfo()
    {
        return $this->visitorInfo;
    }

    public function clearVisitorInfo()
    {
        $this->visitorInfo = array();
    }

    public function setVisitorColumn($column, $value)
    {
        $this->visitorInfo[$column] = $value;
    }

    public function getVisitorColumn($column)
    {
        if (array_key_exists($column, $this->visitorInfo)) {
            return $this->visitorInfo[$column];
        }

        return false;
    }

    public function isVisitorKnown()
    {
        return $this->visitorKnown === true;
    }

    public function setIsVisitorKnown($isVisitorKnown)
    {
        return $this->visitorKnown = $isVisitorKnown;
    }
}
