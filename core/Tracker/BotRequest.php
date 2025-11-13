<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tracker;

use Piwik\Log\LoggerInterface;
use Piwik\Plugin\RequestProcessors;

/**
 * Class used to handle a Bot request.
 */
class BotRequest
{
    use RequestHandlerTrait;

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
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(RequestProcessors $requestProcessors, LoggerInterface $logger)
    {
        $this->requestProcessors    = $requestProcessors->getRequestProcessors();
        $this->botRequestProcessors = $requestProcessors->getBotRequestProcessors();
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
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
            $this->logger->debug("Executing " . get_class($processor) . "::manipulateRequest()...");

            $processor->manipulateRequest($this->request);
        }

        foreach ($this->botRequestProcessors as $processor) {
            $this->logger->debug("Executing " . get_class($processor) . "::manipulateRequest()...");

            $processor->manipulateRequest($this->request);
        }

        $this->validateRequest($this->request);

        $wasHandled = false;

        foreach ($this->botRequestProcessors as $processor) {
            $this->logger->debug("Executing " . get_class($processor) . "::handleRequest()...");

            $wasHandled |= $processor->handleRequest($this->request);
        }

        if ($wasHandled) {
            $this->markArchivedReportsAsInvalidIfArchiveAlreadyFinished($this->request);
        }
    }
}
