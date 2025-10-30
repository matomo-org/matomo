<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tracker;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Exception\UnexpectedWebsiteFoundException;

/**
 * Class used to handle a Bot request.
 */
class BotRequest
{
    /**
     * @var  Request
     */
    protected $request;

    /**
     * @var BotRequestProcessor[]
     */
    protected $botRequestProcessors;

    /**
     * @var RequestProcessor[]
     */
    protected $requestProcessors;

    /**
     * @var ArchiveInvalidator
     */
    private $invalidator;

    public function __construct()
    {
        $requestProcessors          = StaticContainer::get('Piwik\Plugin\RequestProcessors');
        $this->requestProcessors    = $requestProcessors->getRequestProcessors();
        $this->botRequestProcessors = $requestProcessors->getBotRequestProcessors();
        $this->invalidator = StaticContainer::get('Piwik\Archive\ArchiveInvalidator');
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    private function checkSiteExists(Request $request)
    {
        try {
            $request->getIdSite();
        } catch (UnexpectedWebsiteFoundException $e) {
            // we allow 0... the request will fail anyway as the site won't exist... allowing 0 will help us
            // reporting this tracking problem as it is a common issue. Otherwise we would not be able to report
            // this problem in tracking failures
            StaticContainer::get(Failures::class)->logFailure(Failures::FAILURE_ID_INVALID_SITE, $request);
            throw $e;
        }
    }

    private function validateRequest(Request $request)
    {
        // Special logic for timestamp as some overrides are OK without auth and others aren't
        $request->getCurrentTimestamp();
    }

    public function handle()
    {
        $this->checkSiteExists($this->request);

        /**
         * For BC reasons we iterate over all visit request processors as well, to ensure a possible request manipulation is applied
         * For Matomo 6 we should remove that and ensure plugins that also should manipulate bot requests implement a BotRequestProcessor for it
         * @deprecated
         */
        foreach ($this->requestProcessors as $processor) {
            Common::printDebug("Executing " . get_class($processor) . "::manipulateRequest()...");

            $processor->manipulateRequest($this->request);
        }

        foreach ($this->botRequestProcessors as $processor) {
            Common::printDebug("Executing " . get_class($processor) . "::manipulateRequest()...");

            $processor->manipulateRequest($this->request);
        }

        $this->validateRequest($this->request);

        $wasHandled = false;

        foreach ($this->botRequestProcessors as $processor) {
            Common::printDebug("Executing " . get_class($processor) . "::handleRequest()...");

            $wasHandled |= $processor->handleRequest($this->request);
        }

        if ($wasHandled) {
            $this->markArchivedReportsAsInvalidIfArchiveAlreadyFinished();
        }
    }

    private function markArchivedReportsAsInvalidIfArchiveAlreadyFinished()
    {
        $idSite = (int) $this->request->getIdSite();
        $time = $this->request->getCurrentTimestamp();

        $timezone = $this->getTimezoneForSite($idSite);

        if (!isset($timezone)) {
            return;
        }

        $date = Date::factory((int)$time, $timezone);

        // $date->isToday() is buggy when server and website timezones don't match - so we'll do our own checking
        $startOfToday = Date::factoryInTimezone('yesterday', $timezone)->addDay(1);
        $isLaterThanYesterday = $date->getTimestamp() >= $startOfToday->getTimestamp();
        if ($isLaterThanYesterday) {
            return; // don't try to invalidate archives for today or later
        }

        $this->invalidator->rememberToInvalidateArchivedReportsLater($idSite, $date);
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
}
