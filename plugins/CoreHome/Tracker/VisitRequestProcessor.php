<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Tracker;

use Piwik\EventDispatcher;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\VisitExcluded;

/**
 * Encapsulates core tracking logic related to visits.
 */
class VisitRequestProcessor extends RequestProcessor
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
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

        return false;
    }

    public function manipulateVisitProperties(VisitProperties $visitProperties)
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
}
