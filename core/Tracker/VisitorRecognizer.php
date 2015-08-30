<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\EventDispatcher;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * Tracker service that finds the last known visit for the visitor being tracked.
 */
class VisitorRecognizer
{
    /**
     * Local variable cache for the getVisitFieldsPersist() method.
     *
     * @var array
     */
    private $visitFieldsToSelect;

    /**
     * See http://piwik.org/faq/how-to/faq_175/.
     *
     * @var bool
     */
    private $trustCookiesOnly;

    /**
     * Length of a visit in seconds.
     *
     * @var int
     */
    private $visitStandardLength;

    /**
     * Number of seconds that have to pass after an action before a new action from the same visitor is
     * considered a new visit. Defaults to $visitStandardLength.
     *
     * @var int
     */
    private $lookBackNSecondsCustom;

    /**
     * Forces all requests to result in new visits. For debugging only.
     *
     * @var int
     */
    private $trackerAlwaysNewVisitor;

    /**
     * @var Model
     */
    private $model;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct($trustCookiesOnly, $visitStandardLength, $lookbackNSecondsCustom, $trackerAlwaysNewVisitor,
                                Model $model, EventDispatcher $eventDispatcher)
    {
        $this->trustCookiesOnly = $trustCookiesOnly;
        $this->visitStandardLength = $visitStandardLength;
        $this->lookBackNSecondsCustom = $lookbackNSecondsCustom;
        $this->trackerAlwaysNewVisitor = $trackerAlwaysNewVisitor;

        $this->model = $model;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function findKnownVisitor($configId, VisitProperties $visitProperties, Request $request)
    {
        $idSite    = $request->getIdSite();
        $idVisitor = $request->getVisitorId();

        $isVisitorIdToLookup = !empty($idVisitor);

        if ($isVisitorIdToLookup) {
            $visitProperties->setProperty('idvisitor', $idVisitor);
            Common::printDebug("Matching visitors with: visitorId=" . bin2hex($idVisitor) . " OR configId=" . bin2hex($configId));
        } else {
            Common::printDebug("Visitor doesn't have the piwik cookie...");
        }

        $persistedVisitAttributes = $this->getVisitFieldsPersist();

        $shouldMatchOneFieldOnly  = $this->shouldLookupOneVisitorFieldOnly($isVisitorIdToLookup, $request);
        list($timeLookBack, $timeLookAhead) = $this->getWindowLookupThisVisit($request);

        $visitRow = $this->model->findVisitor($idSite, $configId, $idVisitor, $persistedVisitAttributes, $shouldMatchOneFieldOnly, $isVisitorIdToLookup, $timeLookBack, $timeLookAhead);

        $isNewVisitForced = $request->getParam('new_visit');
        $isNewVisitForced = !empty($isNewVisitForced);
        $enforceNewVisit  = $isNewVisitForced || $this->trackerAlwaysNewVisitor;

        if (!$enforceNewVisit
            && $visitRow
            && count($visitRow) > 0
        ) {

            // These values will be used throughout the request
            foreach ($persistedVisitAttributes as $field) {
                $visitProperties->setProperty($field, $visitRow[$field]);
            }

            $visitProperties->setProperty('visit_last_action_time', strtotime($visitRow['visit_last_action_time']));
            $visitProperties->setProperty('visit_first_action_time', strtotime($visitRow['visit_first_action_time']));

            // Custom Variables copied from Visit in potential later conversion
            if (!empty($numCustomVarsToRead)) {
                for ($i = 1; $i <= $numCustomVarsToRead; $i++) {
                    if (isset($visitRow['custom_var_k' . $i])
                        && strlen($visitRow['custom_var_k' . $i])
                    ) {
                        $visitProperties->setProperty('custom_var_k' . $i, $visitRow['custom_var_k' . $i]);
                    }
                    if (isset($visitRow['custom_var_v' . $i])
                        && strlen($visitRow['custom_var_v' . $i])
                    ) {
                        $visitProperties->setProperty('custom_var_v' . $i, $visitRow['custom_var_v' . $i]);
                    }
                }
            }

            Common::printDebug("The visitor is known (idvisitor = " . bin2hex($visitProperties->getProperty('idvisitor')) . ",
                    config_id = " . bin2hex($configId) . ",
                    idvisit = {$visitProperties->getProperty('idvisit')},
                    last action = " . date("r", $visitProperties->getProperty('visit_last_action_time')) . ",
                    first action = " . date("r", $visitProperties->getProperty('visit_first_action_time')) . ",
                    visit_goal_buyer' = " . $visitProperties->getProperty('visit_goal_buyer') . ")");

            return true;
        } else {
            Common::printDebug("The visitor was not matched with an existing visitor...");

            return false;
        }
    }

    protected function shouldLookupOneVisitorFieldOnly($isVisitorIdToLookup, Request $request)
    {
        $isForcedUserIdMustMatch = (false !== $request->getForcedUserId());

        if ($isForcedUserIdMustMatch) {
            // if &iud was set, we must try and match both idvisitor and config_id
            return false;
        }

        // This setting would be enabled for Intranet websites, to ensure that visitors using all the same computer config, same IP
        // are not counted as 1 visitor. In this case, we want to enforce and trust the visitor ID from the cookie.
        if ($isVisitorIdToLookup && $this->trustCookiesOnly) {
            return true;
        }

        // If a &cid= was set, we force to select this visitor (or create a new one)
        $isForcedVisitorIdMustMatch = ($request->getForcedVisitorId() != null);

        if ($isForcedVisitorIdMustMatch) {
            return true;
        }

        if (!$isVisitorIdToLookup) {
            return true;
        }

        return false;
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
    protected function getWindowLookupThisVisit(Request $request)
    {
        $lookAheadNSeconds = $this->visitStandardLength;
        $lookBackNSeconds  = $this->visitStandardLength;
        if ($this->lookBackNSecondsCustom > $lookBackNSeconds) {
            $lookBackNSeconds = $this->lookBackNSecondsCustom;
        }

        $timeLookBack  = date('Y-m-d H:i:s', $request->getCurrentTimestamp() - $lookBackNSeconds);
        $timeLookAhead = date('Y-m-d H:i:s', $request->getCurrentTimestamp() + $lookAheadNSeconds);

        return array($timeLookBack, $timeLookAhead);
    }

    /**
     * @return array
     */
    private function getVisitFieldsPersist()
    {
        if (is_null($this->visitFieldsToSelect)) {
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

                foreach ($dimension->getRequiredVisitFields() as $field) {
                    $fields[] = $field;
                }
            }

            /**
             * This event collects a list of [visit entity](/guides/persistence-and-the-mysql-backend#visits) properties that should be loaded when reading
             * the existing visit. Properties that appear in this list will be available in other tracking
             * events such as 'onExistingVisit'.
             *
             * Plugins can use this event to load additional visit entity properties for later use during tracking.
             *
             * This event is deprecated, use [Dimensions](http://developer.piwik.org/guides/dimensions) instead.
             *
             * @deprecated 
             */
            $this->eventDispatcher->postEvent('Tracker.getVisitFieldsToPersist', array(&$fields));

            array_unshift($fields, 'visit_first_action_time');
            array_unshift($fields, 'visit_last_action_time');

            for ($index = 1; $index <= CustomVariables::getNumUsableCustomVariables(); $index++) {
                $fields[] = 'custom_var_k' . $index;
                $fields[] = 'custom_var_v' . $index;
            }

            $this->visitFieldsToSelect = array_unique($fields);
        }

        return $this->visitFieldsToSelect;
    }
}