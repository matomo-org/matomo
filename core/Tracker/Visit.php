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
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker;

/**
 * Class used to handle a Visit.
 * A visit is either NEW or KNOWN.
 * - If a visit is NEW then we process the visitor information (settings, referrers, etc.) and save
 * a new line in the log_visit table.
 * - If a visit is KNOWN then we update the visit row in the log_visit table, updating the number of pages
 * views, time spent, etc.
 *
 * Whether a visit is NEW or KNOWN we also save the action in the DB.
 * One request to the piwik.php script is associated to one action.
 *
 */
class Visit implements VisitInterface
{
    const UNKNOWN_CODE = 'xx';

    /**
     * @var GoalManager
     */
    protected $goalManager;

    /**
     * @var  Request
     */
    protected $request;

    protected $visitorInfo = array();

    /**
     * @var Settings
     */
    protected $userSettings;
    protected $visitorCustomVariables = array();

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     *    Main algorithm to handle the visit.
     *
     *  Once we have the visitor information, we have to determine if the visit is a new or a known visit.
     *
     * 1) When the last action was done more than 30min ago,
     *      or if the visitor is new, then this is a new visit.
     *
     * 2) If the last action is less than 30min ago, then the same visit is going on.
     *    Because the visit goes on, we can get the time spent during the last action.
     *
     * NB:
     *  - In the case of a new visit, then the time spent
     *    during the last action of the previous visit is unknown.
     *
     *    - In the case of a new visit but with a known visitor,
     *    we can set the 'returning visitor' flag.
     *
     * In all the cases we set a cookie to the visitor with the new information.
     */
    public function handle()
    {
        // the IP is needed by isExcluded() and GoalManager->recordGoals()
        $this->visitorInfo['location_ip'] = $this->request->getIp();

        $excluded = new VisitExcluded($this->request, $this->visitorInfo['location_ip']);
        if ($excluded->isExcluded()) {
            return;
        }

        /**
         * Triggered after visits are tested for exclusion so plugins can modify the IP address
         * persisted with a visit.
         *
         * This event is primarily used by the **PrivacyManager** plugin to anonymize IP addresses.
         *
         * @param string &$ip The visitor's IP address.
         */
        Piwik::postEvent('Tracker.setVisitorIp', array(&$this->visitorInfo['location_ip']));

        $this->visitorCustomVariables = $this->request->getCustomVariables($scope = 'visit');
        if (!empty($this->visitorCustomVariables)) {
            Common::printDebug("Visit level Custom Variables: ");
            Common::printDebug($this->visitorCustomVariables);
        }

        $this->goalManager = new GoalManager($this->request);

        $visitIsConverted = false;
        $action = null;

        $requestIsManualGoalConversion = ($this->goalManager->idGoal > 0);
        $requestIsEcommerce = $this->goalManager->requestIsEcommerce;
        if ($requestIsEcommerce) {
            $someGoalsConverted = true;

            // Mark the visit as Converted only if it is an order (not for a Cart update)
            if ($this->goalManager->isGoalAnOrder()) {
                $visitIsConverted = true;
            }
        } // this request is from the JS call to piwikTracker.trackGoal()
        elseif ($requestIsManualGoalConversion) {
            $someGoalsConverted = $this->goalManager->detectGoalId($this->request->getIdSite());
            $visitIsConverted = $someGoalsConverted;
            // if we find a idgoal in the URL, but then the goal is not valid, this is most likely a fake request
            if (!$someGoalsConverted) {
                Common::printDebug('Invalid goal tracking request for goal id = ' . $this->goalManager->idGoal);
                return;
            }
        } // normal page view, potentially triggering a URL matching goal
        else {
            $action = Action::factory($this->request);

            $action->writeDebugInfo();

            $someGoalsConverted = $this->goalManager->detectGoalsMatchingUrl($this->request->getIdSite(), $action);
            $visitIsConverted = $someGoalsConverted;

            $action->loadIdsFromLogActionTable();
        }

        /***
         * Visitor recognition
         */
        $visitorId = $this->getSettingsObject()->getConfigId();
        $visitor   = new Visitor($this->request, $visitorId, $this->visitorInfo, $this->visitorCustomVariables);
        $visitor->recognize();

        $this->visitorInfo = $visitor->getVisitorInfo();

        $isLastActionInTheSameVisit = $this->isLastActionInTheSameVisit($visitor);

        if (!$isLastActionInTheSameVisit) {
            Common::printDebug("Visitor detected, but last action was more than 30 minutes ago...");
        }
        // Known visit when:
        // ( - the visitor has the Piwik cookie with the idcookie ID used by Piwik to match the visitor
        //   OR
        //   - the visitor doesn't have the Piwik cookie but could be match using heuristics @see recognizeTheVisitor()
        // )
        // AND
        // - the last page view for this visitor was less than 30 minutes ago @see isLastActionInTheSameVisit()
        if ($visitor->isVisitorKnown()
            && $isLastActionInTheSameVisit
        ) {
            $idReferrerActionUrl  = $this->visitorInfo['visit_exit_idaction_url'];
            $idReferrerActionName = $this->visitorInfo['visit_exit_idaction_name'];
            try {
                $this->goalManager->detectIsThereExistingCartInVisit($this->visitorInfo);
                $this->handleExistingVisit($visitor, $action, $visitIsConverted);
                if (!is_null($action)) {
                    $action->record($visitor, $idReferrerActionUrl, $idReferrerActionName);
                }
            } catch (VisitorNotFoundInDb $e) {

                // There is an edge case when:
                // - two manual goal conversions happen in the same second
                // - which result in handleExistingVisit throwing the exception
                //   because the UPDATE didn't affect any rows (one row was found, but not updated since no field changed)
                // - the exception is caught here and will result in a new visit incorrectly
                // In this case, we cancel the current conversion to be recorded:
                if ($requestIsManualGoalConversion
                    || $requestIsEcommerce
                ) {
                    $someGoalsConverted = $visitIsConverted = false;
                } // When the row wasn't found in the logs, and this is a pageview or
                // goal matching URL, we force a new visitor
                else {
                    $visitor->setIsVisitorKnown(false);
                }
            }
        }

        // New visit when:
        // - the visitor has the Piwik cookie but the last action was performed more than 30 min ago @see isLastActionInTheSameVisit()
        // - the visitor doesn't have the Piwik cookie, and couldn't be matched in @see recognizeTheVisitor()
        // - the visitor does have the Piwik cookie but the idcookie and idvisit found in the cookie didn't match to any existing visit in the DB
        if (!$visitor->isVisitorKnown()
            || !$isLastActionInTheSameVisit
        ) {
            $this->handleNewVisit($visitor, $action, $visitIsConverted);
            if (!is_null($action)) {
                $action->record($visitor, 0, 0);
            }
        }

        // update the cookie with the new visit information
        $this->request->setThirdPartyCookie($this->visitorInfo['idvisitor']);

        // record the goals if applicable
        if ($someGoalsConverted) {
            $this->goalManager->recordGoals(
                $visitor,
                $this->visitorInfo,
                $this->visitorCustomVariables,
                $action
            );
        }
        unset($this->goalManager);
        unset($action);
    }

    /**
     * In the case of a known visit, we have to do the following actions:
     *
     * 1) Insert the new action
     * 2) Update the visit information
     *
     * @param Visitor $visitor
     * @param Action $action
     * @param $visitIsConverted
     * @throws VisitorNotFoundInDb
     */
    protected function handleExistingVisit($visitor, $action, $visitIsConverted)
    {
        Common::printDebug("Visit is known (IP = " . IP::N2P($this->getVisitorIp()) . ")");

        $valuesToUpdate = $this->getExistingVisitFieldsToUpdate($visitor, $action, $visitIsConverted);

        // TODO we should not have to sync this->visitorInfo and $visitor columns.
        // TODO it should be its own dimension
        $this->visitorInfo['time_spent_ref_action'] = $this->getTimeSpentReferrerAction();
        $visitor->setVisitorColumn('time_spent_ref_action', $this->visitorInfo['time_spent_ref_action']);

        // update visitorInfo
        foreach ($valuesToUpdate as $name => $value) {
            $this->visitorInfo[$name] = $value;
        }

        /**
         * Triggered before a [visit entity](/guides/persistence-and-the-mysql-backend#visits) is updated when
         * tracking an action for an existing visit.
         *
         * This event can be used to modify the visit properties that will be updated before the changes
         * are persisted.
         *
         * @param array &$valuesToUpdate Visit entity properties that will be updated.
         * @param array $visit The entire visit entity. Read [this](/guides/persistence-and-the-mysql-backend#visits)
         *                     to see what it contains.
         */
        Piwik::postEvent('Tracker.existingVisitInformation', array(&$valuesToUpdate, $this->visitorInfo));

        $this->updateExistingVisit($valuesToUpdate);

        $this->visitorInfo['visit_last_action_time'] = $this->request->getCurrentTimestamp();
        $visitor->setVisitorColumn('visit_last_action_time', $this->visitorInfo['visit_last_action_time']);
    }

    /**
     * @return int Time in seconds
     */
    protected function getTimeSpentReferrerAction()
    {
        $timeSpent = $this->request->getCurrentTimestamp() - $this->visitorInfo['visit_last_action_time'];
        if ($timeSpent < 0) {
            $timeSpent = 0;
        }
        $visitStandardLength = Config::getInstance()->Tracker['visit_standard_length'];
        if($timeSpent > $visitStandardLength) {
            $timeSpent = $visitStandardLength;
        }
        return $timeSpent;
    }

    /**
     * In the case of a new visit, we have to do the following actions:
     *
     * 1) Insert the new action
     *
     * 2) Insert the visit information
     *
     * @param Visitor $visitor
     * @param Action $action
     * @param bool $visitIsConverted
     */
    protected function handleNewVisit($visitor, $action, $visitIsConverted)
    {
        Common::printDebug("New Visit (IP = " . IP::N2P($this->getVisitorIp()) . ")");

        $this->visitorInfo = $this->getNewVisitorInformation($visitor);

        // Add Custom variable key,value to the visitor array
        $this->visitorInfo = array_merge($this->visitorInfo, $this->visitorCustomVariables);

        $visitor->clearVisitorInfo();
        foreach ($this->visitorInfo as $key => $value) {
            $visitor->setVisitorColumn($key, $value);
        }

        $dimensions = $this->getAllVisitDimensions();

        $this->triggerHookOnDimensions($dimensions, 'onNewVisit', $visitor, $action);

        if ($visitIsConverted) {
            $this->triggerHookOnDimensions($dimensions, 'onConvertedVisit', $visitor, $action);
        }

        /**
         * Triggered before a new [visit entity](/guides/persistence-and-the-mysql-backend#visits) is persisted.
         *
         * This event can be used to modify the visit entity or add new information to it before it is persisted.
         * The UserCountry plugin, for example, uses this event to add location information for each visit.
         *
         * @param array &$visit The visit entity. Read [this](/guides/persistence-and-the-mysql-backend#visits) to see
         *                      what information it contains.
         * @param \Piwik\Tracker\Request $request An object describing the tracking request being processed.
         */
        Piwik::postEvent('Tracker.newVisitorInformation', array(&$this->visitorInfo, $this->request));

        $this->printVisitorInformation();

        $idVisit = $this->insertNewVisit( $this->visitorInfo );

        $this->visitorInfo['idvisit'] = $idVisit;
        $this->visitorInfo['visit_first_action_time'] = $this->request->getCurrentTimestamp();
        $this->visitorInfo['visit_last_action_time'] = $this->request->getCurrentTimestamp();

        $visitor->setVisitorColumn('idvisit', $this->visitorInfo['idvisit']);
        $visitor->setVisitorColumn('visit_first_action_time', $this->visitorInfo['visit_first_action_time']);
        $visitor->setVisitorColumn('visit_last_action_time', $this->visitorInfo['visit_last_action_time']);
    }

    /**
     *  Returns visitor cookie
     *
     * @return string  binary
     */
    protected function getVisitorIdcookie(Visitor $visitor)
    {
        if ($visitor->isVisitorKnown()) {
            return $this->visitorInfo['idvisitor'];
        }

        // If the visitor had a first party ID cookie, then we use this value
        if (!empty($this->visitorInfo['idvisitor'])
            && strlen($this->visitorInfo['idvisitor']) == Tracker::LENGTH_BINARY_ID
        ) {
            return $this->visitorInfo['idvisitor'];
        }

        return Common::hex2bin($this->generateUniqueVisitorId());
    }

    /**
     * @return string returns random 16 chars hex string
     */
    public static function generateUniqueVisitorId()
    {
        $uniqueId = substr(Common::generateUniqId(), 0, Tracker::LENGTH_HEX_ID_STRING);
        return $uniqueId;
    }

    /**
     * Returns the visitor's IP address
     *
     * @return string
     */
    protected function getVisitorIp()
    {
        return $this->visitorInfo['location_ip'];
    }

    /**
     * Gets the UserSettings object
     *
     * @return Settings
     */
    protected function getSettingsObject()
    {
        if(is_null($this->userSettings)) {
            $this->userSettings = new Settings( $this->request, $this->getVisitorIp() );
        }
        return $this->userSettings;
    }

    /**
     * Returns true if the last action was done during the last 30 minutes
     * @return bool
     */
    protected function isLastActionInTheSameVisit(Visitor $visitor)
    {
        $lastActionTime = $visitor->getVisitorColumn('visit_last_action_time');

        return isset($lastActionTime)
            && false !== $lastActionTime
            && ($lastActionTime > ($this->request->getCurrentTimestamp() - Config::getInstance()->Tracker['visit_standard_length']));
    }

    // is the referrer host any of the registered URLs for this website?
    public static function isHostKnownAliasHost($urlHost, $idSite)
    {
        $websiteData = Cache::getCacheWebsiteAttributes($idSite);
        if (isset($websiteData['hosts'])) {
            $canonicalHosts = array();
            foreach ($websiteData['hosts'] as $host) {
                $canonicalHosts[] = str_replace('www.', '', mb_strtolower($host, 'UTF-8'));
            }
            $canonicalHost = str_replace('www.', '', mb_strtolower($urlHost, 'UTF-8'));
            if (in_array($canonicalHost, $canonicalHosts)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    protected function insertNewVisit($visit)
    {
        $fields = implode(", ", array_keys($visit));
        $values = Common::getSqlStringFieldsArray($visit);

        $sql = "INSERT INTO " . Common::prefixTable('log_visit') . " ($fields) VALUES ($values)";
        $bind = array_values($visit);
        Tracker::getDatabase()->query($sql, $bind);

        $idVisit = Tracker::getDatabase()->lastInsertId();
        return $idVisit;
    }

    /**
     * @param $valuesToUpdate
     * @throws VisitorNotFoundInDb
     */
    protected function updateExistingVisit($valuesToUpdate)
    {
        $sqlQuery = "UPDATE " . Common::prefixTable('log_visit') . "
                    SET %s
                    WHERE idsite = ?
                        AND idvisit = ?";
        // build sql query
        $updateParts = $sqlBind = array();
        foreach ($valuesToUpdate as $name => $value) {
            // Case where bind parameters don't work
            if(strpos($value, $name) !== false) {
                //$name = 'visit_total_events'
                //$value = 'visit_total_events + 1';
                $updateParts[] = " $name = $value ";
            } else {
                $updateParts[] = $name . " = ?";
                $sqlBind[] = $value;
            }
        }
        $sqlQuery = sprintf($sqlQuery, implode($updateParts, ', ') );
        array_push($sqlBind, $this->request->getIdSite(), (int)$this->visitorInfo['idvisit']);

        $result = Tracker::getDatabase()->query($sqlQuery, $sqlBind);

        // Debug output
        if (isset($valuesToUpdate['idvisitor'])) {
            $valuesToUpdate['idvisitor'] = bin2hex($valuesToUpdate['idvisitor']);
        }
        Common::printDebug('Updating existing visit: ' . var_export($valuesToUpdate, true));

        if (Tracker::getDatabase()->rowCount($result) == 0) {
            Common::printDebug("Visitor with this idvisit wasn't found in the DB.");
            Common::printDebug("$sqlQuery --- ");
            Common::printDebug($sqlBind);
            throw new VisitorNotFoundInDb(
                "The visitor with idvisitor=" . bin2hex($this->visitorInfo['idvisitor']) . " and idvisit=" . $this->visitorInfo['idvisit']
                . " wasn't found in the DB, we fallback to a new visitor");
        }
    }

    protected function printVisitorInformation()
    {
        $debugVisitInfo = $this->visitorInfo;
        $debugVisitInfo['idvisitor'] = bin2hex($debugVisitInfo['idvisitor']);
        $debugVisitInfo['config_id'] = bin2hex($debugVisitInfo['config_id']);
        Common::printDebug($debugVisitInfo);
    }

    protected function getNewVisitorInformation($visitor)
    {
        return array(
            'idvisitor'   => $this->getVisitorIdcookie($visitor),
            'config_id'   => $this->getSettingsObject()->getConfigId(),
            'location_ip' => $this->getVisitorIp(),
        );
    }

    /**
     * Gather fields=>values that needs to be updated for the existing visit in log_visit
     *
     * @param Visitor $visitor
     * @param Action|null $action
     * @param $visitIsConverted
     * @return array
     */
    protected function getExistingVisitFieldsToUpdate($visitor, $action, $visitIsConverted)
    {
        $valuesToUpdate = array();

        // Might update the idvisitor when it was forced or overwritten for this visit
        if (strlen($this->visitorInfo['idvisitor']) == Tracker::LENGTH_BINARY_ID) {
            $valuesToUpdate['idvisitor'] = $this->visitorInfo['idvisitor'];
            $visitor->setVisitorColumn('idvisitor', $this->visitorInfo['idvisitor']);
        }

        $dimensions     = $this->getAllVisitDimensions();
        $valuesToUpdate = $this->triggerHookOnDimensions($dimensions, 'onExistingVisit', $visitor, $action, $valuesToUpdate);

        if ($visitIsConverted) {
            $valuesToUpdate = $this->triggerHookOnDimensions($dimensions, 'onConvertedVisit', $visitor, $action, $valuesToUpdate);
        }

        // Custom Variables overwrite previous values on each page view
        $valuesToUpdate = array_merge($valuesToUpdate, $this->visitorCustomVariables);
        return $valuesToUpdate;
    }

    /**
     * @param VisitDimension[] $dimensions
     * @param string $hook
     * @param Visitor $visitor
     * @param Action|null $action
     * @param array|null $valuesToUpdate If null, $this->visitorInfo will be updated
     *
     * @return array|null The updated $valuesToUpdate or null if no $valuesToUpdate given
     */
    private function triggerHookOnDimensions($dimensions, $hook, $visitor, $action, $valuesToUpdate = null)
    {
        foreach ($dimensions as $dimension) {
            $value = $dimension->$hook($this->request, $visitor, $action);

            if ($value !== false) {
                $fieldName = $dimension->getColumnName();
                $visitor->setVisitorColumn($fieldName, $value);

                if ($valuesToUpdate !== null) {
                    $valuesToUpdate[$fieldName] = $value;
                } else {
                    $this->visitorInfo[$fieldName] = $value;
                }
            }
        }

        return $valuesToUpdate;
    }

    protected function getAllVisitDimensions()
    {
        $dimensions = VisitDimension::getAllDimensions();

        $dimensionNames = array();
        foreach($dimensions as $dimension) {
            $dimensionNames[] = $dimension->getColumnName();
        }
        Common::printDebug("Following dimensions have been collected from plugins: " . implode(", ", $dimensionNames));
        return $dimensions;
    }
}
