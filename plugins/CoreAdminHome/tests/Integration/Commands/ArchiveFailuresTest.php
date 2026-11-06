<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\ArchiveFailures;
use Piwik\Date;
use Piwik\Db;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

/**
 * @group CoreAdminHome
 * @group ArchiveFailures
 */
class ArchiveFailuresTest extends ConsoleCommandTestCase
{
    private ArchiveFailures $archiveFailures;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Fixture::createWebsite('2012-01-01 00:00:00');
        Fixture::createWebsite('2012-01-01 00:00:00');
    }

    public function setUp(): void
    {
        parent::setUp();

        Db::query('DELETE FROM ' . ArchiveFailures::getTable());

        Date::$now = strtotime('2020-02-03 04:05:06');
        Config::setSetting('General', 'archive_failure_max_attempts', 2);

        $this->archiveFailures = new ArchiveFailures();
    }

    public function tearDown(): void
    {
        Db::query('DELETE FROM ' . ArchiveFailures::getTable());
        Date::$now = null;
        parent::tearDown();
    }

    public function testListsNothingWithoutFailures(): void
    {
        $code = $this->applicationTester->run(['command' => 'core:archive-failures']);

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $this->assertStringContainsString('No failed archives found.', $this->applicationTester->getDisplay());
    }

    public function testListsFailures(): void
    {
        $this->recordFailures();

        $code = $this->applicationTester->run(['command' => 'core:archive-failures']);
        $output = $this->applicationTester->getDisplay();

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $this->assertStringContainsString('3 failed archives found:', $output);
        $this->assertMatchesRegularExpression('/\| 1 +\| day +\| 2020-01-01 \| 2020-01-01 \| done +\| +\| 2 +\| given up +\| +\| 2020-02-03 04:05:06 \| Query execution was interrupted +\|/', $output);
        $this->assertMatchesRegularExpression('/\| 1 +\| week +\| 2020-01-06 \| 2020-01-12 \| done\.Goals \| getMetrics \| 1 +\| waiting +\| 2020-02-03 05:05:07 \|/', $output);
        $this->assertStringContainsString(str_repeat('x', 100) . '...', $output);
    }

    public function testFiltersFailures(): void
    {
        $this->recordFailures();

        $this->applicationTester->run(['command' => 'core:archive-failures', '--idsite' => ['2']]);
        $this->assertStringContainsString('1 failed archives found:', $this->applicationTester->getDisplay());

        $this->applicationTester->run(['command' => 'core:archive-failures', '--given-up' => true]);
        $this->assertStringContainsString('1 failed archives found:', $this->applicationTester->getDisplay());

        $this->applicationTester->run(['command' => 'core:archive-failures', '--idsite' => ['2'], '--given-up' => true]);
        $this->assertStringContainsString('No failed archives found.', $this->applicationTester->getDisplay());
    }

    public function testResetsMatchingFailures(): void
    {
        $this->recordFailures();

        $code = $this->applicationTester->run(['command' => 'core:archive-failures', '--idsite' => ['1'], '--reset' => true]);

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $this->assertStringContainsString('Number of failed archives that were reset: 2.', $this->applicationTester->getDisplay());
        $this->assertSame(['2'], array_map('strval', array_column($this->archiveFailures->getFailures(), 'idsite')));
    }

    public function testResetReleasesTheInvalidationsOnHoldOfTheResetSitesOnly(): void
    {
        $this->recordFailures();
        $table = Common::prefixTable('archive_invalidations');
        foreach ([1, 2] as $idSite) {
            Db::query(
                "INSERT INTO `$table` (name, idsite, date1, date2, period, ts_invalidated, status) VALUES ('done', ?, '2020-01-01', '2020-01-01', 1, ?, ?)",
                [$idSite, Date::now()->getDatetime(), ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD]
            );
        }

        $this->applicationTester->run(['command' => 'core:archive-failures', '--idsite' => ['1'], '--reset' => true]);

        $this->assertEquals([
            ['idsite' => 1, 'status' => ArchiveInvalidator::INVALIDATION_STATUS_QUEUED],
            ['idsite' => 2, 'status' => ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD],
        ], Db::fetchAll("SELECT idsite, status FROM `$table` ORDER BY idsite"));
        Db::query("DELETE FROM `$table`");
    }

    public function testInvalidSiteDoesNotSelectAllSites(): void
    {
        $this->recordFailures();

        $code = $this->applicationTester->run(['command' => 'core:archive-failures', '--idsite' => ['abc'], '--reset' => true]);

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $this->assertCount(3, $this->archiveFailures->getFailures());
    }

    private function recordFailures(): void
    {
        $day = ['idsite' => 1, 'name' => 'done', 'period' => 1, 'date1' => '2020-01-01', 'date2' => '2020-01-01', 'report' => null];
        $this->archiveFailures->recordFailure($day, 'Query execution was interrupted');
        $this->archiveFailures->recordFailure($day, 'Query execution was interrupted');

        Date::$now = strtotime('2020-02-03 04:05:07');
        $this->archiveFailures->recordFailure(
            ['idsite' => 1, 'name' => 'done.Goals', 'period' => 2, 'date1' => '2020-01-06', 'date2' => '2020-01-12', 'report' => 'getMetrics'],
            str_repeat('x', 200)
        );
        $this->archiveFailures->recordFailure(
            ['idsite' => 2, 'name' => 'done', 'period' => 1, 'date1' => '2020-01-01', 'date2' => '2020-01-01', 'report' => null],
            'error'
        );
        Date::$now = strtotime('2020-02-03 04:05:06');
    }
}
