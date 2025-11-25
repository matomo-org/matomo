<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\Tracker;

use Piwik\Date;
use Piwik\Log\LoggerInterface;
use Piwik\Plugins\BotTracking\BotDetector;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;
use Piwik\Tracker\Request;
use Piwik\Tracker\TableLogAction;

/**
 * Handles bot tracking requests
 */
class BotRequestProcessor extends \Piwik\Tracker\BotRequestProcessor
{
    /**
     * @var BotRequestsDao
     */
    private $dao;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(BotRequestsDao $dao, LoggerInterface $logger)
    {
        $this->dao = $dao;
        $this->logger = $logger;
    }

    public function handleRequest(Request $request): bool
    {
        $userAgent = $request->getUserAgent();
        $botDetector = new BotDetector($userAgent);

        if (!$botDetector->isBot()) {
            return false;
        }

        $botInfo = $botDetector->getDetectionResult();

        if (empty($botInfo)) {
            return false;
        }

        try {
            $idSite = $request->getIdSite();

            $timestamp = $request->getCurrentTimestamp();
            $serverTime = Date::factory($timestamp)->getDatetime();

            $idActionUrl = $this->getActionId($request);

            $rawParams = new \Piwik\Request($request->getRawParams());

            $httpStatusCode = $rawParams->getIntegerParameter('http_status', -1);
            if ($httpStatusCode < 0) {
                $httpStatusCode = null;
            }

            $responseSizeBytes = $rawParams->getIntegerParameter('bw_bytes', -1);
            if ($responseSizeBytes < 0) {
                $responseSizeBytes = null;
            }

            $serverTimeMs = $rawParams->getIntegerParameter('pf_srv', -1);
            if ($serverTimeMs < 0) {
                $serverTimeMs = null;
            }

            $source = $rawParams->getStringParameter('source', '');
            if ($source === '') {
                $source = null;
            }

            $data = [
                'idsite' => $idSite,
                'server_time' => $serverTime,
                'idaction_url' => $idActionUrl,
                'bot_name' => $botInfo['bot_name'],
                'bot_type' => $botInfo['bot_type'],
                'http_status_code' => $httpStatusCode,
                'response_size_bytes' => $responseSizeBytes,
                'response_time_ms' => $serverTimeMs,
                'source' => $source,
            ];

            $idRequest = $this->dao->insert($data);

            $this->logger->debug('Bot request recorded: idrequest=' . $idRequest);
        } catch (\Exception $e) {
            $this->logger->debug('Error recording bot request: ' . $e->getMessage());
            // Don't throw - we don't want to break tracking for other processors
        }

        return true;
    }

    /**
     * Get or create action ID for the URL
     *
     * @param Request $request
     * @return int|null
     */
    private function getActionId(Request $request): ?int
    {
        $url = $request->getParam('url');
        $actionType = Action::TYPE_PAGE_URL;

        $downloadUrl = $request->getParam('download');
        if ($downloadUrl !== false && $downloadUrl !== '') {
            $url = $downloadUrl;
            $actionType = Action::TYPE_DOWNLOAD;
        }

        if ($url === false || $url === '') {
            return null;
        }

        $urlInfo = PageUrl::normalizeUrl($url);

        $actionsToLookup = [
            [$urlInfo['url'], $actionType, $urlInfo['prefixId']],
        ];

        try {
            [$actionId] = TableLogAction::loadIdsAction($actionsToLookup);

            if (isset($actionId)) {
                return (int) $actionId;
            }
        } catch (\Exception $e) {
            $this->logger->debug('Error resolving action ID: ' . $e->getMessage());
        }

        return null;
    }
}
