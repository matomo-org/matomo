<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\EventDispatcher;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * Tracker service that finds the last known visit for the visitor being tracked.
 */
class VisitorRecognizer
{
    /**
     * Set when a visit was found. Stores the original values of the row that is currently stored in the DB when
     * the visit was selected.
     */
    const KEY_ORIGINAL_VISIT_ROW = 'originalVisit';

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
     * @var Model
     */
    private $model;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var array
     */
    private $visitRow;

    public function __construct($trustCookiesOnly, $visitStandardLength, $lookbackNSecondsCustom,
                                Model $model, EventDispatcher $eventDispatcher)
    {
        $this->trustCookiesOnly = $trustCookiesOnly;
        $this->visitStandardLength = $visitStandardLength;
        $this->lookBackNSecondsCustom = $lookbackNSecondsCustom;

        $this->model = $model;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setTrustCookiesOnly($trustCookiesOnly)
    {
        $this->trustCookiesOnly = $trustCookiesOnly;
    }

    public function findKnownVisitor($configId, VisitProperties $visitProperties, Request $request)
    {
        $idSite    = $request->getIdSite();
        $idVisitor = $request->getVisitorId();
        $userId    = $request->getForcedUserId();

        $isVisitorIdToLookup = !empty($idVisitor);

        if ($isVisitorIdToLookup) {
            $visitProperties->setProperty('idvisitor', $idVisitor);
            Common::printDebug("Matching visitors with: visitorId=" . bin2hex($idVisitor) . " OR configId=" . bin2hex($configId));
        } else {
            Common::printDebug("Visitor doesn't have the piwik cookie...");
        }

        $persistedVisitAttributes = $this->getVisitorFieldsPersist();

        $shouldMatchOneFieldOnly  = $this->shouldLookupOneVisitorFieldOnly($isVisitorIdToLookup, $request);
        list($timeLookBack, $timeLookAhead) = $this->getWindowLookupThisVisit($request);

        $maxActions = TrackerConfig::getConfigValue('create_new_visit_after_x_actions', $request->getIdSiteIfExists());

        $visitRow = $this->model->findVisitor($idSite, $configId, $idVisitor, $userId, $persistedVisitAttributes, $shouldMatchOneFieldOnly, $isVisitorIdToLookup, $timeLookBack, $timeLookAhead);

        if (!empty($maxActions) && $maxActions > 0
            && !empty($visitRow['visit_total_actions'])
            && $maxActions <= $visitRow['visit_total_actions']) {
            $this->visitRow = false;
            return false;
        }

        $this->visitRow = $visitRow;

        if ($visitRow
            && count($visitRow) > 0
        ) {
            $visitProperties->setProperty('idvisitor', $visitRow['idvisitor']);
            $visitProperties->setProperty('user_id', $visitRow['user_id']);

            Common::printDebug("The visitor is known (idvisitor = " . bin2hex($visitProperties->getProperty('idvisitor')) . ",
                    config_id = " . bin2hex($configId) . ",
                    last action = " . date("r", $visitProperties->getProperty('visit_last_action_time')) . ",
                    first action = " . date("r", $visitProperties->getProperty('visit_first_action_time')) . ")");

            return true;
        } else {
            Common::printDebug("The visitor was not matched with an existing visitor...");

            return false;
        }
    }

    public function removeUnchangedValues($visit, VisitProperties $originalVisit = null)
    {
        if (empty($originalVisit)) {
            return $visit;
        }

        $originalRow = $originalVisit->getProperties();
        if (!empty($originalRow['idvisitor'])
            && !empty($visit['idvisitor'])
            && bin2hex($originalRow['idvisitor']) === bin2hex($visit['idvisitor'])) {
            unset($visit['idvisitor']);
        }

        $fieldsToCompareValue = array('user_id', 'visit_last_action_time', 'visit_total_time');
        foreach ($fieldsToCompareValue as $field) {
            if (!empty($originalRow[$field])
                && !empty($visit[$field])
                && $visit[$field] == $originalRow[$field]) {
                // we can't use === eg for visit_total_time which may be partially an integer and sometimes a string
                // because we check for !empty things should still work as expected though
                // (eg we wouldn't compare false with 0)
                unset($visit[$field]);
            }
        }

        return $visit;
    }

    public function updateVisitPropertiesFromLastVisitRow(VisitProperties $visitProperties)
    {
        // These values will be used throughout the request
        foreach ($this->getVisitorFieldsPersist() as $field) {
            $value = $this->visitRow[$field];
            if ($field == 'visit_last_action_time' || $field == 'visit_first_action_time') {
                $value = strtotime($value);
            }

            $visitProperties->setProperty($field, $value);
        }

        Common::printDebug("The visit is part of an existing visit (
            idvisit = {$visitProperties->getProperty('idvisit')},
            visit_goal_buyer' = " . $visitProperties->getProperty('visit_goal_buyer') . ")");
    }

    protected function shouldLookupOneVisitorFieldOnly($isVisitorIdToLookup, Request $request)
    {
        $isForcedUserIdMustMatch = (false !== $request->getForcedUserId());

        // This setting would be enabled for Intranet websites, to ensure that visitors using all the same computer config, same IP
        // are not counted as 1 visitor. In this case, we want to enforce and trust the visitor ID from the cookie.
        if ($isVisitorIdToLookup && $this->trustCookiesOnly) {
            return true;
        }

        if ($isForcedUserIdMustMatch) {
            // if &iud was set, we must try and match both idvisitor and config_id
            return false;
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
    private function getVisitorFieldsPersist()
    {
        if (is_null($this->visitFieldsToSelect)) {
            $fields = array(
                'idvisitor',
                'idvisit',
                'user_id',

                'visit_exit_idaction_url',
                'visit_exit_idaction_name',
                'visitor_returning',
                'visitor_seconds_since_first',
                'visitor_seconds_since_order',
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
                if ($dimension->hasImplementedEvent('onExistingVisit') || $dimension->hasImplementedEvent('onAnyGoalConversion')) {
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

            $this->visitFieldsToSelect = array_unique($fields);
        }

        return $this->visitFieldsToSelect;
    }

    public function getLastKnownVisit()
    {
        return $this->visitRow;
    }
}
