<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Tracker;

use Piwik\Common;
use Piwik\Date;
use Piwik\EventDispatcher;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Settings;
use Piwik\Tracker\TrackerConfig;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\VisitExcluded;
use Piwik\Tracker\VisitorRecognizer;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;

/**
 * Encapsulates core tracking logic related to visits.
 *
 * ## Request Metadata
 *
 * This RequestProcessor exposes the following metadata for the **CoreHome** plugin:
 *
 * * **visitorId**: A hash that identifies the current visitor being tracked. This value is
 *                  calculated using the Piwik\Tracker\Settings;:getConfigId() method.
 *
 *                  Set in `processRequestParams()`.
 *
 * * **isVisitorKnown**: True if the current visitor has visited the site before. False if
 *                       otherwise.
 *
 *                       Set in `processRequestParams()`.
 *
 * * **isNewVisit**: True if the current action is the start of a new visit, false if it
 *                   is part of an ongoing visit.
 *
 *                   Set in `processRequestParams()`. Other RequestProcessors can override
 *                   this value to force a new visit or stop a new visit.
 *
 * * **visitorNotFoundInDb**: True if the current visit could not be updated.
 *
 *                            Set by the Visit object.
 */
class VisitRequestProcessor extends RequestProcessor
{
    // TODO: much of the logic in this class should be moved to new service class

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var VisitorRecognizer
     */
    private $visitorRecognizer;

    /**
     * @var Settings
     */
    private $userSettings;

    /**
     * @var int
     */
    private $visitStandardLength;

    /**
     * Forces all requests to result in new visits. For debugging only.
     *
     * @var int
     */
    private $trackerAlwaysNewVisitor;

    public function __construct(
        EventDispatcher $eventDispatcher,
        VisitorRecognizer $visitorRecognizer,
        Settings $userSettings,
        $visitStandardLength,
        $trackerAlwaysNewVisitor
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->visitorRecognizer = $visitorRecognizer;
        $this->userSettings = $userSettings;
        $this->visitStandardLength = $visitStandardLength;
        $this->trackerAlwaysNewVisitor = $trackerAlwaysNewVisitor;
    }

    public function processRequestParams(VisitProperties $visitProperties, Request $request)
    {
        // the IP is needed by isExcluded() and GoalManager->recordGoals()
        $visitProperties->setProperty('location_ip', $request->getIp());

        $excluded = new VisitExcluded($request);
        $isExcluded = $excluded->isExcluded();
        $request->setMetadata('CoreHome', 'isVisitExcluded', $isExcluded);

        if ($isExcluded) {
            return true;
        }

        $privacyConfig = new PrivacyManagerConfig();

        if ($privacyConfig->randomizeConfigId) {
            // always new visit when randomising config id
            $request->setMetadata('CoreHome', 'visitorId', $this->userSettings->getRandomConfigId());
            $request->setMetadata('CoreHome', 'isVisitorKnown', false);
            $request->setMetadata('CoreHome', 'isNewVisit', true);
            $request->setMetadata('CoreHome', 'lastKnownVisit', false);

            return false;
        }

        $ip = $request->getIpString();
        if ($privacyConfig->useAnonymizedIpForVisitEnrichment) {
            $ip = $visitProperties->getProperty('location_ip');
        }

        // visitor recognition
        $visitorId = $this->userSettings->getConfigId($request, $ip);
        $request->setMetadata('CoreHome', 'visitorId', $visitorId);

        $isKnown = $this->visitorRecognizer->findKnownVisitor($visitorId, $visitProperties, $request);
        $request->setMetadata('CoreHome', 'isVisitorKnown', $isKnown);

        $isNewVisit = $this->isVisitNew($visitProperties, $request, $this->visitorRecognizer->getLastKnownVisit());
        $request->setMetadata('CoreHome', 'isNewVisit', $isNewVisit);

        $request->setMetadata('CoreHome', 'lastKnownVisit', $this->visitorRecognizer->getLastKnownVisit());

        if (!$isNewVisit) { // only copy over known visitor's information, if this is for an ongoing visit
            $this->visitorRecognizer->updateVisitPropertiesFromLastVisitRow($visitProperties);
        }

        return false;
    }

    public function afterRequestProcessed(VisitProperties $visitProperties, Request $request)
    {
        $ip = $visitProperties->getProperty('location_ip');

        /**
         * Triggered after visits are tested for exclusion so plugins can modify the IP address
         * persisted with a visit.
         *
         * This event is primarily used by the **PrivacyManager** plugin to anonymize IP addresses.
         *
         * @param string &$ip The visitor's IP address.
         */
        $this->eventDispatcher->postEvent('Tracker.setVisitorIp', array(&$ip));

        $visitProperties->setProperty('location_ip', $ip);
    }

    /**
     * Determines if the tracker if the current action should be treated as the start of a new visit or
     * an action in an existing visit.
     *
     * Note: public only for tests.
     *
     * @param VisitProperties $visitProperties The current visit/visitor information.
     * @param Request $request
     * @return bool
     */
    public function isVisitNew(VisitProperties $visitProperties, Request $request, $lastKnownVisit)
    {
        $isKnown = $request->getMetadata('CoreHome', 'isVisitorKnown');
        if (!$isKnown) {
            return true;
        }

        $isNewVisitForced = $request->getParam('new_visit');
        $isNewVisitForced = !empty($isNewVisitForced);
        if ($isNewVisitForced) {
            Common::printDebug("-> New visit forced: &new_visit=1 in request");
            return true;
        }

        if ($this->trackerAlwaysNewVisitor) {
            Common::printDebug("-> New visit forced: Debug.tracker_always_new_visitor = 1 in config.ini.php");
            return true;
        }

        $isLastActionInTheSameVisit = $this->isLastActionInTheSameVisit($visitProperties, $request, $lastKnownVisit);
        if (!$isLastActionInTheSameVisit) {
            Common::printDebug("Visitor detected, but last action was more than 30 minutes ago...");

            return true;
        }

        $wasLastActionYesterday = $this->wasLastActionNotToday($visitProperties, $request, $lastKnownVisit);
        $forceNewVisitAtMidnight = (bool) TrackerConfig::getConfigValue('create_new_visit_after_midnight', $request->getIdSiteIfExists());

        if ($wasLastActionYesterday && $forceNewVisitAtMidnight) {
            Common::printDebug("Visitor detected, but last action was yesterday...");

            return true;
        }

        if (
            !TrackerConfig::getConfigValue('enable_userid_overwrites_visitorid', $request->getIdSiteIfExists())
            && !$this->lastUserIdWasSetAndDoesMatch($visitProperties, $request)
        ) {
            Common::printDebug("Visitor detected, but last user_id does not match...");
            return true;
        }

        return false;
    }

    /**
     * Returns true if the last action was done during the last 30 minutes
     * @return bool
     */
    protected function isLastActionInTheSameVisit(VisitProperties $visitProperties, Request $request, $lastKnownVisit)
    {
        $lastActionTime = $this->getLastKnownActionTime($visitProperties, $lastKnownVisit);

        return isset($lastActionTime)
            && false !== $lastActionTime
            && ($lastActionTime > ($request->getCurrentTimestamp() - $this->visitStandardLength));
    }

    /**
     * Returns true if the last action was not today.
     * @param VisitProperties $visitor
     * @return bool
     */
    private function wasLastActionNotToday(VisitProperties $visitProperties, Request $request, $lastKnownVisit)
    {
        $lastActionTime = $this->getLastKnownActionTime($visitProperties, $lastKnownVisit);

        if (empty($lastActionTime)) {
            return false;
        }

        $idSite = $request->getIdSite();
        $timezone = $this->getTimezoneForSite($idSite);

        if (empty($timezone)) {
            throw new UnexpectedWebsiteFoundException('An unexpected website was found, check idSite in the request');
        }

        $date = Date::factory((int)$lastActionTime, $timezone);
        $now = $request->getCurrentTimestamp();
        $now = Date::factory((int)$now, $timezone);

        return $date->toString() !== $now->toString();
    }

    private function getTimezoneForSite($idSite) // TODO: duplicate function in Visit
    {
        try {
            $site = Cache::getCacheWebsiteAttributes($idSite);
        } catch (UnexpectedWebsiteFoundException $e) {
            return null;
        }

        if (!empty($site['timezone'])) {
            return $site['timezone'];
        }

        return null;
    }

    /**
     * Returns the last action time for the last recorded visit of this visitor, or if the visitor is new,
     * the current request's timestamp.
     *
     * @param VisitProperties $visitProperties
     * @param $lastKnownVisit
     * @return false|int|mixed
     */
    private function getLastKnownActionTime(VisitProperties $visitProperties, $lastKnownVisit)
    {
        if (isset($lastKnownVisit['visit_last_action_time'])) {
            return strtotime($lastKnownVisit['visit_last_action_time']);
        }

        return $visitProperties->getProperty('visit_last_action_time');
    }

    /**
     * Returns true if the last user_id did not change.
     * @return bool
     */
    protected function lastUserIdWasSetAndDoesMatch(VisitProperties $visitProperties, Request $request)
    {
        $lastUserId = $visitProperties->getProperty('user_id');

        if (empty($lastUserId)) {
            return true;
        }

        $currentUserId = $request->getForcedUserId();

        if (empty($currentUserId)) {
            return true;
        }

        return $lastUserId === $currentUserId;
    }
}
