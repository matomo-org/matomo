<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Network\IPUtils;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker;
use Piwik\Tracker\Visit\VisitProperties;

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

    /**
     * @var Settings
     */
    protected $userSettings;

    public static $dimensions;

    /**
     * @var RequestProcessor[]
     */
    protected $requestProcessors;

    /**
     * @var VisitProperties
     */
    protected $visitProperties;

    /**
     * @var VisitorRecognizer
     */
    private $visitorRecognizer;

    /**
     * @var ArchiveInvalidator
     */
    private $invalidator;

    public function __construct()
    {
        $this->requestProcessors = StaticContainer::get('tracker.request.processors');
        $this->visitorRecognizer = StaticContainer::get('Piwik\Tracker\VisitorRecognizer');
        $this->visitProperties = null;
        $this->userSettings = StaticContainer::get('Piwik\Tracker\Settings');
        $this->invalidator = StaticContainer::get('Piwik\Archive\ArchiveInvalidator');
    }

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
        foreach ($this->requestProcessors as $processor) {
            Common::printDebug("Executing " . get_class($processor) . "::manipulateRequest()...");

            $processor->manipulateRequest($this->request);
        }

        $this->visitProperties = new VisitProperties();

        foreach ($this->requestProcessors as $processor) {
            Common::printDebug("Executing " . get_class($processor) . "::processRequestParams()...");

            $abort = $processor->processRequestParams($this->visitProperties, $this->request);
            if ($abort) {
                Common::printDebug("-> aborting due to processRequestParams method");
                return;
            }
        }

        $isNewVisit = $this->request->getMetadata('CoreHome', 'isNewVisit');
        if (!$isNewVisit) {
            $isNewVisit = $this->triggerPredicateHookOnDimensions($this->getAllVisitDimensions(), 'shouldForceNewVisit');
            $this->request->setMetadata('CoreHome', 'isNewVisit', $isNewVisit);
        }

        foreach ($this->requestProcessors as $processor) {
            Common::printDebug("Executing " . get_class($processor) . "::afterRequestProcessed()...");

            $abort = $processor->afterRequestProcessed($this->visitProperties, $this->request);
            if ($abort) {
                Common::printDebug("-> aborting due to afterRequestProcessed method");
                return;
            }
        }

        $isNewVisit = $this->request->getMetadata('CoreHome', 'isNewVisit');

        // Known visit when:
        // ( - the visitor has the Piwik cookie with the idcookie ID used by Piwik to match the visitor
        //   OR
        //   - the visitor doesn't have the Piwik cookie but could be match using heuristics @see recognizeTheVisitor()
        // )
        // AND
        // - the last page view for this visitor was less than 30 minutes ago @see isLastActionInTheSameVisit()
        if (!$isNewVisit) {
            try {
                $this->handleExistingVisit($this->request->getMetadata('Goals', 'visitIsConverted'));
            } catch (VisitorNotFoundInDb $e) {
                $this->request->setMetadata('CoreHome', 'visitorNotFoundInDb', true); // TODO: perhaps we should just abort here?
            }
        }

        // New visit when:
        // - the visitor has the Piwik cookie but the last action was performed more than 30 min ago @see isLastActionInTheSameVisit()
        // - the visitor doesn't have the Piwik cookie, and couldn't be matched in @see recognizeTheVisitor()
        // - the visitor does have the Piwik cookie but the idcookie and idvisit found in the cookie didn't match to any existing visit in the DB
        if ($isNewVisit) {
            $this->handleNewVisit($this->request->getMetadata('Goals', 'visitIsConverted'));
        }

        // update the cookie with the new visit information
        $this->request->setThirdPartyCookie($this->visitProperties->getProperty('idvisitor'));

        foreach ($this->requestProcessors as $processor) {
            Common::printDebug("Executing " . get_class($processor) . "::recordLogs()...");

            $processor->recordLogs($this->visitProperties, $this->request);
        }

        $this->markArchivedReportsAsInvalidIfArchiveAlreadyFinished();
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
    protected function handleExistingVisit($visitIsConverted)
    {
        Common::printDebug("Visit is known (IP = " . IPUtils::binaryToStringIP($this->getVisitorIp()) . ")");

        // TODO it should be its own dimension
        $this->visitProperties->setProperty('time_spent_ref_action', $this->getTimeSpentReferrerAction());

        $valuesToUpdate = $this->getExistingVisitFieldsToUpdate($visitIsConverted);

        // update visitorInfo
        foreach ($valuesToUpdate as $name => $value) {
            $this->visitProperties->setProperty($name, $value);
        }

        /**
         * Triggered before a [visit entity](/guides/persistence-and-the-mysql-backend#visits) is updated when
         * tracking an action for an existing visit.
         *
         * This event can be used to modify the visit properties that will be updated before the changes
         * are persisted.
         *
         * This event is deprecated, use [Dimensions](http://developer.piwik.org/guides/dimensions) instead.
         *
         * @param array &$valuesToUpdate Visit entity properties that will be updated.
         * @param array $visit The entire visit entity. Read [this](/guides/persistence-and-the-mysql-backend#visits)
         *                     to see what it contains.
         * @deprecated
         */
        Piwik::postEvent('Tracker.existingVisitInformation', array(&$valuesToUpdate, $this->visitProperties->getProperties()));

        foreach ($this->requestProcessors as $processor) {
            $processor->onExistingVisit($valuesToUpdate, $this->visitProperties, $this->request);
        }

        $this->updateExistingVisit($valuesToUpdate);

        $this->visitProperties->setProperty('visit_last_action_time', $this->request->getCurrentTimestamp());
    }

    /**
     * @return int Time in seconds
     */
    protected function getTimeSpentReferrerAction()
    {
        $timeSpent = $this->request->getCurrentTimestamp() -
            $this->visitProperties->getProperty('visit_last_action_time');
        if ($timeSpent < 0) {
            $timeSpent = 0;
        }
        $visitStandardLength = $this->getVisitStandardLength();
        if ($timeSpent > $visitStandardLength) {
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
    protected function handleNewVisit($visitIsConverted)
    {
        Common::printDebug("New Visit (IP = " . IPUtils::binaryToStringIP($this->getVisitorIp()) . ")");

        $this->setNewVisitorInformation();

        $dimensions = $this->getAllVisitDimensions();

        $this->triggerHookOnDimensions($dimensions, 'onNewVisit');

        if ($visitIsConverted) {
            $this->triggerHookOnDimensions($dimensions, 'onConvertedVisit');
        }

        $properties = &$this->visitProperties->getProperties();

        /**
         * Triggered before a new [visit entity](/guides/persistence-and-the-mysql-backend#visits) is persisted.
         *
         * This event can be used to modify the visit entity or add new information to it before it is persisted.
         * The UserCountry plugin, for example, uses this event to add location information for each visit.
         *
         * This event is deprecated, use [Dimensions](http://developer.piwik.org/guides/dimensions) instead.
         *
         * @param array &$visit The visit entity. Read [this](/guides/persistence-and-the-mysql-backend#visits) to see
         *                      what information it contains.
         * @param \Piwik\Tracker\Request $request An object describing the tracking request being processed.
         *
         * @deprecated
         */
        Piwik::postEvent('Tracker.newVisitorInformation', array(&$properties, $this->request));

        foreach ($this->requestProcessors as $processor) {
            $processor->onNewVisit($this->visitProperties, $this->request);
        }

        $this->printVisitorInformation();

        $idVisit = $this->insertNewVisit($this->visitProperties->getProperties());

        $this->visitProperties->setProperty('idvisit', $idVisit);
        $this->visitProperties->setProperty('visit_first_action_time', $this->request->getCurrentTimestamp());
        $this->visitProperties->setProperty('visit_last_action_time', $this->request->getCurrentTimestamp());
    }

    private function getModel()
    {
        return new Model();
    }

    /**
     *  Returns visitor cookie
     *
     * @return string  binary
     */
    protected function getVisitorIdcookie()
    {
        $isKnown = $this->request->getMetadata('CoreHome', 'isVisitorKnown');
        if ($isKnown) {
            return $this->visitProperties->getProperty('idvisitor');
        }

        // If the visitor had a first party ID cookie, then we use this value
        $idVisitor = $this->visitProperties->getProperty('idvisitor');
        if (!empty($idVisitor)
            && Tracker::LENGTH_BINARY_ID == strlen($this->visitProperties->getProperty('idvisitor'))
        ) {
            return $this->visitProperties->getProperty('idvisitor');
        }

        return Common::hex2bin($this->generateUniqueVisitorId());
    }

    /**
     * @return string returns random 16 chars hex string
     */
    public static function generateUniqueVisitorId()
    {
        return substr(Common::generateUniqId(), 0, Tracker::LENGTH_HEX_ID_STRING);
    }

    /**
     * Returns the visitor's IP address
     *
     * @return string
     */
    protected function getVisitorIp()
    {
        return $this->visitProperties->getProperty('location_ip');
    }

    /**
     * Gets the UserSettings object
     *
     * @return Settings
     */
    protected function getSettingsObject()
    {
        return $this->userSettings;
    }

    // is the referrer host any of the registered URLs for this website?
    public static function isHostKnownAliasHost($urlHost, $idSite)
    {
        $websiteData = Cache::getCacheWebsiteAttributes($idSite);

        if (isset($websiteData['hosts'])) {
            $canonicalHosts = array();
            foreach ($websiteData['hosts'] as $host) {
                $canonicalHosts[] = self::toCanonicalHost($host);
            }

            $canonicalHost = self::toCanonicalHost($urlHost);
            if (in_array($canonicalHost, $canonicalHosts)) {
                return true;
            }
        }

        return false;
    }

    private static function toCanonicalHost($host)
    {
        $hostLower = Common::mb_strtolower($host);
        return str_replace('www.', '', $hostLower);
    }

    /**
     * @param $valuesToUpdate
     * @throws VisitorNotFoundInDb
     */
    protected function updateExistingVisit($valuesToUpdate)
    {
        $idSite = $this->request->getIdSite();
        $idVisit = (int)$this->visitProperties->getProperty('idvisit');

        $wasInserted = $this->getModel()->updateVisit($idSite, $idVisit, $valuesToUpdate);

        // Debug output
        if (isset($valuesToUpdate['idvisitor'])) {
            $valuesToUpdate['idvisitor'] = bin2hex($valuesToUpdate['idvisitor']);
        }

        if ($wasInserted) {
            Common::printDebug('Updated existing visit: ' . var_export($valuesToUpdate, true));
        } else {
            throw new VisitorNotFoundInDb(
                "The visitor with idvisitor=" . bin2hex($this->visitProperties->getProperty('idvisitor'))
                . " and idvisit=" . @$this->visitProperties->getProperty('idvisit')
                . " wasn't found in the DB, we fallback to a new visitor");
        }
    }

    private function printVisitorInformation()
    {
        $debugVisitInfo = $this->visitProperties->getProperties();
        $debugVisitInfo['idvisitor'] = bin2hex($debugVisitInfo['idvisitor']);
        $debugVisitInfo['config_id'] = bin2hex($debugVisitInfo['config_id']);
        $debugVisitInfo['location_ip'] = IPUtils::binaryToStringIP($debugVisitInfo['location_ip']);
        Common::printDebug($debugVisitInfo);
    }

    private function setNewVisitorInformation()
    {
        $idVisitor = $this->getVisitorIdcookie();
        $visitorIp = $this->getVisitorIp();
        $configId = $this->request->getMetadata('CoreHome', 'visitorId');

        $this->visitProperties->clearProperties();

        $this->visitProperties->setProperty('idvisitor', $idVisitor);
        $this->visitProperties->setProperty('config_id', $configId);
        $this->visitProperties->setProperty('location_ip', $visitorIp);
    }

    /**
     * Gather fields=>values that needs to be updated for the existing visit in log_visit
     *
     * @param $visitIsConverted
     * @return array
     */
    private function getExistingVisitFieldsToUpdate($visitIsConverted)
    {
        $valuesToUpdate = array();

        $valuesToUpdate = $this->setIdVisitorForExistingVisit($valuesToUpdate);

        $dimensions = $this->getAllVisitDimensions();
        $valuesToUpdate = $this->triggerHookOnDimensions($dimensions, 'onExistingVisit', $valuesToUpdate);

        if ($visitIsConverted) {
            $valuesToUpdate = $this->triggerHookOnDimensions($dimensions, 'onConvertedVisit', $valuesToUpdate);
        }

        // Custom Variables overwrite previous values on each page view
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
    private function triggerHookOnDimensions($dimensions, $hook, $valuesToUpdate = null)
    {
        $visitor = $this->makeVisitorFacade();

        /** @var Action $action */
        $action = $this->request->getMetadata('Actions', 'action');

        foreach ($dimensions as $dimension) {
            $value = $dimension->$hook($this->request, $visitor, $action);

            if ($value !== false) {
                $fieldName = $dimension->getColumnName();
                $visitor->setVisitorColumn($fieldName, $value);

                if (is_float($value)) {
                    $value = Common::forceDotAsSeparatorForDecimalPoint($value);
                }

                if ($valuesToUpdate !== null) {
                    $valuesToUpdate[$fieldName] = $value;
                } else {
                    $this->visitProperties->setProperty($fieldName, $value);
                }
            }
        }

        return $valuesToUpdate;
    }

    private function triggerPredicateHookOnDimensions($dimensions, $hook)
    {
        $visitor = $this->makeVisitorFacade();

        /** @var Action $action */
        $action = $this->request->getMetadata('Actions', 'action');

        foreach ($dimensions as $dimension) {
            if ($dimension->$hook($this->request, $visitor, $action)) {
                return true;
            }
        }
        return false;
    }

    protected function getAllVisitDimensions()
    {
        if (is_null(self::$dimensions)) {
            self::$dimensions = VisitDimension::getAllDimensions();

            $dimensionNames = array();
            foreach (self::$dimensions as $dimension) {
                $dimensionNames[] = $dimension->getColumnName();
            }

            Common::printDebug("Following dimensions have been collected from plugins: " . implode(", ",
                    $dimensionNames));
        }

        return self::$dimensions;
    }

    private function getVisitStandardLength()
    {
        return Config::getInstance()->Tracker['visit_standard_length'];
    }

    /**
     * @param $visitor
     * @param $valuesToUpdate
     * @return mixed
     */
    private function setIdVisitorForExistingVisit($valuesToUpdate)
    {
        // Might update the idvisitor when it was forced or overwritten for this visit
        if (strlen($this->visitProperties->getProperty('idvisitor')) == Tracker::LENGTH_BINARY_ID) {
            $binIdVisitor = $this->visitProperties->getProperty('idvisitor');
            $valuesToUpdate['idvisitor'] = $binIdVisitor;
        }

        // User ID takes precedence and overwrites idvisitor value
        $userId = $this->request->getForcedUserId();
        if ($userId) {
            $userIdHash = $this->request->getUserIdHashed($userId);
            $binIdVisitor = Common::hex2bin($userIdHash);
            $this->visitProperties->setProperty('idvisitor', $binIdVisitor);
            $valuesToUpdate['idvisitor'] = $binIdVisitor;
        }
        return $valuesToUpdate;
    }

    protected function insertNewVisit($visit)
    {
        return $this->getModel()->createVisit($visit);
    }

    private function markArchivedReportsAsInvalidIfArchiveAlreadyFinished()
    {
        $idSite = (int)$this->request->getIdSite();
        $time = $this->request->getCurrentTimestamp();

        $timezone = $this->getTimezoneForSite($idSite);

        if (!isset($timezone)) {
            return;
        }

        $date = Date::factory((int)$time, $timezone);

        if (!$date->isToday()) { // we don't have to handle in case date is in future as it is not allowed by tracker
            $this->invalidator->rememberToInvalidateArchivedReportsLater($idSite, $date);
        }
    }

    private function getTimezoneForSite($idSite)
    {
        try {
            $site = Cache::getCacheWebsiteAttributes($idSite);
        } catch (UnexpectedWebsiteFoundException $e) {
            return null;
        }

        if (!empty($site['timezone'])) {
            return $site['timezone'];
        }
    }

    private function makeVisitorFacade()
    {
        return Visitor::makeFromVisitProperties($this->visitProperties, $this->request);
    }
}
