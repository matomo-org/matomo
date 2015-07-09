<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\VisitExcluded;
use Piwik\Tracker\VisitorRecognizer;

/**
 * Encapsulates core tracking logic related to visits.
 */
class VisitRequestProcessor extends RequestProcessor
{
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

    public function __construct(EventDispatcher $eventDispatcher, VisitorRecognizer $visitorRecognizer, Settings $userSettings,
                                $visitStandardLength)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->visitorRecognizer = $visitorRecognizer;
        $this->userSettings = $userSettings;
        $this->visitStandardLength = $visitStandardLength;
    }

    public function processRequestParams(VisitProperties $visitProperties, Request $request)
    {
        // the IP is needed by isExcluded() and GoalManager->recordGoals()
        $visitProperties->visitorInfo['location_ip'] = $request->getIp();

        // TODO: move VisitExcluded logic to here (or break into other request processors)
        $excluded = new VisitExcluded($request, $visitProperties->visitorInfo['location_ip']);
        if ($excluded->isExcluded()) {
            return true;
        }

        // visitor recognition
        $visitorId = $this->userSettings->getConfigId($request, $visitProperties->visitorInfo['location_ip']);
        $visitProperties->setRequestMetadata('CoreHome', 'visitorId', $visitorId);

        $isKnown = $this->visitorRecognizer->findKnownVisitor($visitorId, $visitProperties, $request);
        $visitProperties->setRequestMetadata('CoreHome', 'isVisitorKnown', $isKnown);

        $isNewVisit = $this->isVisitNew($visitProperties, $request);
        $visitProperties->setRequestMetadata('CoreHome', 'isNewVisit', $isNewVisit);

        return false;
    }

    public function manipulateVisitProperties(VisitProperties $visitProperties, Request $request)
    {
        /**
         * Triggered after visits are tested for exclusion so plugins can modify the IP address
         * persisted with a visit.
         *
         * This event is primarily used by the **PrivacyManager** plugin to anonymize IP addresses.
         *
         * @param string &$ip The visitor's IP address.
         */
        $this->eventDispatcher->postEvent('Tracker.setVisitorIp', array(&$visitProperties->visitorInfo['location_ip']));
    }

    /**
     * Determines if the tracker if the current action should be treated as the start of a new visit or
     * an action in an existing visit.
     *
     * @param VisitProperties $visitProperties The current visit/visitor information.
     * @param Request $request
     * @return bool
     */
    private function isVisitNew(VisitProperties $visitProperties, Request $request)
    {
        $isKnown = $visitProperties->getRequestMetadata('CoreHome', 'isVisitorKnown');
        if (!$isKnown) {
            return true;
        }

        $isLastActionInTheSameVisit = $this->isLastActionInTheSameVisit($visitProperties, $request);
        if (!$isLastActionInTheSameVisit) {
            Common::printDebug("Visitor detected, but last action was more than 30 minutes ago...");

            return true;
        }

        $wasLastActionYesterday = $this->wasLastActionNotToday($visitProperties, $request);
        if ($wasLastActionYesterday) {
            Common::printDebug("Visitor detected, but last action was yesterday...");

            return true;
        }

        return false;
    }

    /**
     * Returns true if the last action was done during the last 30 minutes
     * @return bool
     */
    protected function isLastActionInTheSameVisit(VisitProperties $visitProperties, Request $request)
    {
        $lastActionTime = $visitProperties->visitorInfo['visit_last_action_time'];

        return isset($lastActionTime)
            && false !== $lastActionTime
            && ($lastActionTime > ($request->getCurrentTimestamp() - $this->visitStandardLength)); // TODO: move to DI
    }

    /**
     * Returns true if the last action was not today.
     * @param VisitProperties $visitor
     * @return bool
     */
    private function wasLastActionNotToday(VisitProperties $visitProperties, Request $request)
    {
        $lastActionTime = $visitProperties->visitorInfo['visit_last_action_time'];

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
}
