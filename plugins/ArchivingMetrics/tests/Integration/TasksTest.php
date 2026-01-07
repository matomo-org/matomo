<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ArchivingMetrics\tests\Integration;

use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\ArchivingMetrics\Tasks;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ArchivingMetrics
 * @group ArchivingMetrics_Tasks
 * @group Plugins
 */
class TasksTest extends IntegrationTestCase
{
    public function testPurgeOldMetricsDeletesRowsOlderThanRetention(): void
    {
        $config = Config::getInstance();
        $config->ArchivingMetrics = ['retention_days' => 30];

        $this->insertRow(Date::now()->subDay(31)->getDatetime());
        $this->insertRow(Date::now()->subDay(5)->getDatetime());

        $task = new Tasks();
        $task->purgeOldMetrics();

        $count = (int) Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('archiving_metrics'));
        $this->assertSame(1, $count);
    }

    public function testPurgeOldMetricsDisabledKeepsRows(): void
    {
        $config = Config::getInstance();
        $config->ArchivingMetrics = ['retention_days' => 0];

        $this->insertRow(Date::now()->subDay(400)->getDatetime());

        $task = new Tasks();
        $task->purgeOldMetrics();

        $count = (int) Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('archiving_metrics'));
        $this->assertSame(1, $count);
    }

    public function testPurgeMetricsForDeletedSitesRemovesOrphanedRows(): void
    {
        $idSite = Fixture::createWebsite('2024-01-01 00:00:00');

        $this->insertRow(Date::now()->subDay(5)->getDatetime(), $idSite);
        $this->insertRow(Date::now()->subDay(5)->getDatetime(), 9999);

        $task = new Tasks();
        $task->purgeMetricsForDeletedSites();

        $count = (int) Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('archiving_metrics'));
        $this->assertSame(1, $count);
    }

    private function insertRow(string $tsStarted, int $idSite = 1): void
    {
        $table = Common::prefixTable('archiving_metrics');
        Db::query(
            "INSERT INTO {$table} (idarchive, idsite, archive_name, date1, date2, period, ts_started, ts_finished, total_time, total_time_exclusive)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                1,
                $idSite,
                'done',
                '2025-01-01',
                '2025-01-01',
                1,
                $tsStarted,
                $tsStarted,
                123,
                100,
            ]
        );
    }
}
