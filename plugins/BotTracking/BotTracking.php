<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking;

use Piwik\Date;
use Piwik\Plugin;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Plugins\SitesManager\API;
use Piwik\Tracker\Request;

/**
 * BotTracking Plugin
 *
 * Tracks AI assistant and bot interactions without creating visits.
 * Stores telemetry data in dedicated tables for analysis of bot behavior
 * and system performance.
 */
class BotTracking extends Plugin
{
    /**
     * @return bool
     */
    public function isTrackerPlugin()
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function registerEvents(): array
    {
        return [
            'PrivacyManager.deleteLogsOlderThan' => 'deleteLogsOlderThan',
            'PrivacyManager.deleteDataSubjectsForDeletedSites' => 'deleteDataSubjectsForDeletedSites',
            'Tracker.isBotRequest' => 'isBotRequest',
        ];
    }

    /**
     * @return void
     */
    public function install()
    {
        (new BotRequestsDao())->createTable();
    }

    /**
     * @return void
     */
    public function uninstall()
    {
        (new BotRequestsDao())->dropTable();
    }

    public function deleteLogsOlderThan(Date $dateUpperLimit): void
    {
        (new BotRequestsDao())->deleteOldRecords($dateUpperLimit);
    }

    /**
     * @param array<string, int> $result
     */
    public function deleteDataSubjectsForDeletedSites(array &$result): void
    {
        $allExistingIdSites = API::getInstance()->getAllSitesId();
        $allExistingIdSites = array_map('intval', $allExistingIdSites);
        $maxIdSite          = max($allExistingIdSites);

        if (empty($maxIdSite)) {
            return;
        }

        $dao                     = new BotRequestsDao();
        $idSitesInTable          = $dao->getDistinctIdSitesInTable($maxIdSite);
        $idSitesNoLongerExisting = array_diff($idSitesInTable, $allExistingIdSites);

        if (count($idSitesNoLongerExisting) > 0) {
            $result[$dao::getTableName()] = $dao->deleteRecordsForIdSites($idSitesNoLongerExisting);
        }
    }

    /**
     * @todo Remove, once Device Detector is able to detect all known ai bots
     */
    public function isBotRequest(bool &$isBot, Request $request): void
    {
        $botDetector = new BotDetector($request->getUserAgent());

        if ($botDetector->isBot()) {
            $isBot = true;
        }
    }
}
