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
        $this->configId = $configId;
        $this->visitorInfo = $visitorInfo;
        $this->customVariables = $customVariables;
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

        $configId  = $this->configId;
        $idSite    = $this->request->getIdSite();
        $idVisitor = $this->request->getVisitorId();

        $isVisitorIdToLookup = !empty($idVisitor);

        if ($isVisitorIdToLookup) {
            $this->visitorInfo['idvisitor'] = $idVisitor;
            Common::printDebug("Matching visitors with: visitorId=" . bin2hex($idVisitor) . " OR configId=" . bin2hex($configId));
        } else {
            Common::printDebug("Visitor doesn't have the piwik cookie...");
        }

        $numCustomVarsToRead = 0;
        if (!$this->customVariables) {
            // No custom var were found in the request, so let's copy the previous one in a potential conversion later
            $numCustomVarsToRead = CustomVariables::getMaxCustomVariables();
        }

        $persistedVisitAttributes = $this->getVisitFieldsPersist();
        $shouldMatchOneFieldOnly  = $this->shouldLookupOneVisitorFieldOnly($isVisitorIdToLookup);
        list($timeLookBack, $timeLookAhead) = $this->getWindowLookupThisVisit();

        $model    = $this->getModel();
        $visitRow = $model->findVisitor($idSite, $configId, $idVisitor, $persistedVisitAttributes, $numCustomVarsToRead, $shouldMatchOneFieldOnly, $isVisitorIdToLookup, $timeLookBack, $timeLookAhead);

        $isNewVisitForced = $this->request->getParam('new_visit');
        $isNewVisitForced = !empty($isNewVisitForced);
        $enforceNewVisit  = $isNewVisitForced || Config::getInstance()->Debug['tracker_always_new_visitor'];

        if (!$enforceNewVisit
            && $visitRow
            && count($visitRow) > 0
        ) {

            // These values will be used throughout the request
            foreach ($persistedVisitAttributes as $field) {
                $this->visitorInfo[$field] = $visitRow[$field];
            }

            $this->visitorInfo['visit_last_action_time']  = strtotime($visitRow['visit_last_action_time']);
            $this->visitorInfo['visit_first_action_time'] = strtotime($visitRow['visit_first_action_time']);

            // Custom Variables copied from Visit in potential later conversion
            if (!empty($numCustomVarsToRead)) {
                for ($i = 1; $i <= $numCustomVarsToRead; $i++) {
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
        $visitStandardLength    = Config::getInstance()->Tracker['visit_standard_length'];
        $lookBackNSecondsCustom = Config::getInstance()->Tracker['window_look_back_for_visitor'];

        $lookAheadNSeconds = $visitStandardLength;
        $lookBackNSeconds  = $visitStandardLength;
        if ($lookBackNSecondsCustom > $lookBackNSeconds) {
            $lookBackNSeconds = $lookBackNSecondsCustom;
        }

        $timeLookBack  = date('Y-m-d H:i:s', $this->request->getCurrentTimestamp() - $lookBackNSeconds);
        $timeLookAhead = date('Y-m-d H:i:s', $this->request->getCurrentTimestamp() + $lookAheadNSeconds);

        return array($timeLookBack, $timeLookAhead);
    }

    protected function shouldLookupOneVisitorFieldOnly($isVisitorIdToLookup)
    {
        $isForcedUserIdMustMatch = (false !== $this->request->getForcedUserId());

        if ($isForcedUserIdMustMatch) {
            // if &iud was set, we must try and match both idvisitor and config_id
            return false;
        }

        // This setting would be enabled for Intranet websites, to ensure that visitors using all the same computer config, same IP
        // are not counted as 1 visitor. In this case, we want to enforce and trust the visitor ID from the cookie.
        $trustCookiesOnly = Config::getInstance()->Tracker['trust_visitors_cookies'];
        if ($isVisitorIdToLookup && $trustCookiesOnly) {
            return true;
        }

        // If a &cid= was set, we force to select this visitor (or create a new one)
        $isForcedVisitorIdMustMatch = ($this->request->getForcedVisitorId() != null);

        if ($isForcedVisitorIdMustMatch) {
            return true;
        }

        if (!$isVisitorIdToLookup ) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    private function getVisitFieldsPersist()
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

        array_unshift($fields, 'visit_first_action_time');
        array_unshift($fields, 'visit_last_action_time');
        $fields = array_unique($fields);

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

    private function getModel()
    {
        return new Model();
    }
}
