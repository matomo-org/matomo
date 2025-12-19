<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ArchivingMetrics\tests\Integration;

use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\ArchivingMetrics\Tasks;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ArchivingMetrics
 * @group ArchivingMetrics_Tasks
 * @group Plugins
 */
class TasksTest extends IntegrationTestCase
{
    private $originalConfig;

    public function setUp(): void
    {
        parent::setUp();
        $config = Config::getInstance();
        $this->originalConfig = $config->ArchivingMetrics ?? null;
        Db::query('DELETE FROM ' . Common::prefixTable('archiving_metrics'));
    }

    public function tearDown(): void
    {
        $config = Config::getInstance();
        if ($this->originalConfig !== null) {
            $config->ArchivingMetrics = $this->originalConfig;
        } else {
            $config->ArchivingMetrics = [];
        }

        parent::tearDown();
    }

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

    private function insertRow(string $tsStarted): void
    {
        $table = Common::prefixTable('archiving_metrics');
        Db::query(
            "INSERT INTO {$table} (idarchive, idsite, segment, date1, date2, period, ts_started, ts_finished, total_time, total_time_exclusive)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                null,
                1,
                null,
                '2025-01-01',
                '2025-01-01',
                'day',
                $tsStarted,
                $tsStarted,
                123,
                100,
            ]
        );
    }
}
