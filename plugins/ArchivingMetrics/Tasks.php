<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ArchivingMetrics;

use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;

class Tasks extends \Piwik\Plugin\Tasks
{
    public const DEFAULT_RETENTION_DAYS = 180;

    public function schedule()
    {
        $this->weekly('purgeOldMetrics');
    }

    /**
     * To test execute the following command:
     * `./console core:run-scheduled-tasks --force "Piwik\Plugins\ArchivingMetrics\Tasks.purgeOldMetrics"`
     */
    public function purgeOldMetrics()
    {
        $retentionDays = $this->getRetentionDays();
        if ($retentionDays <= 0) {
            return;
        }

        $cutoff = Date::now()->subDay($retentionDays)->getDatetime();
        $table = Common::prefixTable('archiving_metrics');
        Db::query("DELETE FROM {$table} WHERE ts_started < ?", [$cutoff]);
    }

    private function getRetentionDays(): int
    {
        $config = Config::getInstance();
        $retentionDays = $config->ArchivingMetrics['retention_days'] ?? self::DEFAULT_RETENTION_DAYS;
        return max(0, (int) $retentionDays);
    }
}
