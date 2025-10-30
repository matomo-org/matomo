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
