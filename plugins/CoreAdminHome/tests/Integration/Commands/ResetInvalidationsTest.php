<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Monolog\Handler\AbstractProcessingHandler;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\Common;
use Piwik\Db;
use Piwik\Period\Day;
use Piwik\Period\Month;
use Piwik\Period\Week;
use Piwik\Period\Year;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

/**
 * @group CoreAdminHome
 * @group CoreAdminHome_Integration
 */
class ResetInvalidationsTest extends ConsoleCommandTestCase
{
    private static $captureHandler;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Fixture::createWebsite('2012-01-01 00:00:00');
        Fixture::createWebsite('2012-01-01 00:00:00');
        Fixture::createWebsite('2012-01-01 00:00:00');
    }

    public function setUp(): void
    {
        parent::setUp();
        self::$captureHandler->messages = [];
    }

    protected function tearDown(): void
    {
        self::$captureHandler->messages = [];
        parent::tearDown();
    }

    /**
     * @dataProvider getInvalidDates
     */
    public function testCommandFailsWhenInvalidDatesAreUsed($newerThan, $olderThan, $expectedMessage)
    {
        $code = $this->applicationTester->run([
            'command' => 'core:reset-invalidations',
            '--newer-than' => $newerThan,
            '--older-than' => $olderThan,
            '--dry-run' => true,
        ]);

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString($expectedMessage, $this->getLogOutput());
    }

    public function getInvalidDates(): iterable
    {
        yield 'invalid newer than date' => [
            'inValid', '', 'Invalid value for --newer-than provided.'
        ];

        yield 'invalid older than date' => [
            '', 'inValid', 'Invalid value for --older-than provided.'
        ];
    }


    /**
     * @dataProvider getDryRunTestData
     */
    public function testDryRunOutput($parameters, $expectedOutput, $expectedChangeInProgressInvalidations)
    {
        $this->prepareInvalidations();

        $inProgressInvalidationCount = $this->getInProgressInvalidationCount();

        $code = $this->applicationTester->run(array_merge([
            'command' => 'core:reset-invalidations',
            '--dry-run' => true,
        ], $parameters));

        $inProgressInvalidationCountAfterDryRun = $this->getInProgressInvalidationCount();

        self::assertEquals($inProgressInvalidationCount, $inProgressInvalidationCountAfterDryRun);

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString($expectedOutput, $this->applicationTester->getDisplay(true));

        $code = $this->applicationTester->run(array_merge([
            'command' => 'core:reset-invalidations',
        ], $parameters));

        $inProgressInvalidationCountAfterRun = $this->getInProgressInvalidationCount();

        self::assertEquals($expectedChangeInProgressInvalidations, $inProgressInvalidationCountAfterDryRun - $inProgressInvalidationCountAfterRun);

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
    }

    public function getDryRunTestData(): iterable
    {
        yield 'no limitations returns everything' => [
            [],
            "4 invalidations found:
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| name                                         | idsite | report | date1      | date2      | period | ts_invalidated      | ts_started          | processing_host | process_id |
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| done                                         | 2      |        | 2024-12-30 | 2025-01-05 | 2      | 2025-01-01 19:15:01 | 2025-02-05 09:11:00 | host1           | 2333       |
| done                                         | 1      |        | 2025-01-01 | 2025-01-01 | 1      | 2025-01-01 16:00:00 | 2025-02-05 11:30:00 | host1           | 1256       |
| donec7445c35d0f9b340f5851df27a15c5ef.Actions | 1      |        | 2025-01-01 | 2025-12-31 | 4      | 2025-01-01 16:00:02 | 2025-02-05 16:44:22 | anotherhost     | 662        |
| done.VisitsSummary                           | 3      |        | 2025-02-01 | 2025-02-28 | 3      | 2025-02-20 19:15:01 | 2025-02-23 18:35:00 | random          | 8558       |
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
",
            4
        ];

        yield 'limiting by all sites works as expected' => [
            ['--idsite' => ['all']],
            "4 invalidations found:
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| name                                         | idsite | report | date1      | date2      | period | ts_invalidated      | ts_started          | processing_host | process_id |
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| done                                         | 2      |        | 2024-12-30 | 2025-01-05 | 2      | 2025-01-01 19:15:01 | 2025-02-05 09:11:00 | host1           | 2333       |
| done                                         | 1      |        | 2025-01-01 | 2025-01-01 | 1      | 2025-01-01 16:00:00 | 2025-02-05 11:30:00 | host1           | 1256       |
| donec7445c35d0f9b340f5851df27a15c5ef.Actions | 1      |        | 2025-01-01 | 2025-12-31 | 4      | 2025-01-01 16:00:02 | 2025-02-05 16:44:22 | anotherhost     | 662        |
| done.VisitsSummary                           | 3      |        | 2025-02-01 | 2025-02-28 | 3      | 2025-02-20 19:15:01 | 2025-02-23 18:35:00 | random          | 8558       |
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
",
            4
        ];

        yield 'limiting by site works as expected' => [
            ['--idsite' => [1]],
            "2 invalidations found:
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| name                                         | idsite | report | date1      | date2      | period | ts_invalidated      | ts_started          | processing_host | process_id |
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| done                                         | 1      |        | 2025-01-01 | 2025-01-01 | 1      | 2025-01-01 16:00:00 | 2025-02-05 11:30:00 | host1           | 1256       |
| donec7445c35d0f9b340f5851df27a15c5ef.Actions | 1      |        | 2025-01-01 | 2025-12-31 | 4      | 2025-01-01 16:00:02 | 2025-02-05 16:44:22 | anotherhost     | 662        |
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
",
            2
        ];

        yield 'limiting by another site works as expected' => [
            ['--idsite' => [2]],
            "1 invalidations found:
+------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| name | idsite | report | date1      | date2      | period | ts_invalidated      | ts_started          | processing_host | process_id |
+------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| done | 2      |        | 2024-12-30 | 2025-01-05 | 2      | 2025-01-01 19:15:01 | 2025-02-05 09:11:00 | host1           | 2333       |
+------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
",
            1
        ];


        yield 'limiting by multiple sites works as expected' => [
            ['--idsite' => [1, 3]],
            "3 invalidations found:
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| name                                         | idsite | report | date1      | date2      | period | ts_invalidated      | ts_started          | processing_host | process_id |
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| done                                         | 1      |        | 2025-01-01 | 2025-01-01 | 1      | 2025-01-01 16:00:00 | 2025-02-05 11:30:00 | host1           | 1256       |
| donec7445c35d0f9b340f5851df27a15c5ef.Actions | 1      |        | 2025-01-01 | 2025-12-31 | 4      | 2025-01-01 16:00:02 | 2025-02-05 16:44:22 | anotherhost     | 662        |
| done.VisitsSummary                           | 3      |        | 2025-02-01 | 2025-02-28 | 3      | 2025-02-20 19:15:01 | 2025-02-23 18:35:00 | random          | 8558       |
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
",
            3
        ];


        yield 'limiting by a site without records works as expected' => [
            ['--idsite' => [4]],
            "No invalidations found.",
            0
        ];

        yield 'limiting by host works as expected' => [
            ['--processing-host' => ['host1']],
            "2 invalidations found:
+------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| name | idsite | report | date1      | date2      | period | ts_invalidated      | ts_started          | processing_host | process_id |
+------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| done | 2      |        | 2024-12-30 | 2025-01-05 | 2      | 2025-01-01 19:15:01 | 2025-02-05 09:11:00 | host1           | 2333       |
| done | 1      |        | 2025-01-01 | 2025-01-01 | 1      | 2025-01-01 16:00:00 | 2025-02-05 11:30:00 | host1           | 1256       |
+------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
",
            2
        ];

        yield 'limiting by multiple hosts works as expected' => [
            ['--processing-host' => ['host1', 'random']],
            "3 invalidations found:
+--------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| name               | idsite | report | date1      | date2      | period | ts_invalidated      | ts_started          | processing_host | process_id |
+--------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| done               | 2      |        | 2024-12-30 | 2025-01-05 | 2      | 2025-01-01 19:15:01 | 2025-02-05 09:11:00 | host1           | 2333       |
| done               | 1      |        | 2025-01-01 | 2025-01-01 | 1      | 2025-01-01 16:00:00 | 2025-02-05 11:30:00 | host1           | 1256       |
| done.VisitsSummary | 3      |        | 2025-02-01 | 2025-02-28 | 3      | 2025-02-20 19:15:01 | 2025-02-23 18:35:00 | random          | 8558       |
+--------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
",
            3
        ];

        yield 'limiting by hosts without records works as expected' => [
            ['--processing-host' => ['unknown']],
            "No invalidations found.",
            0
        ];

        yield 'limiting by newer-than works as expected' => [
            ['--newer-than' => '2025-02-05 12:00:00'],
            "2 invalidations found:
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| name                                         | idsite | report | date1      | date2      | period | ts_invalidated      | ts_started          | processing_host | process_id |
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| donec7445c35d0f9b340f5851df27a15c5ef.Actions | 1      |        | 2025-01-01 | 2025-12-31 | 4      | 2025-01-01 16:00:02 | 2025-02-05 16:44:22 | anotherhost     | 662        |
| done.VisitsSummary                           | 3      |        | 2025-02-01 | 2025-02-28 | 3      | 2025-02-20 19:15:01 | 2025-02-23 18:35:00 | random          | 8558       |
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
",
            2
        ];

        yield 'limiting by older-than works as expected' => [
            ['--older-than' => '2025-02-05 12:00:00'],
            "2 invalidations found:
+------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| name | idsite | report | date1      | date2      | period | ts_invalidated      | ts_started          | processing_host | process_id |
+------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| done | 2      |        | 2024-12-30 | 2025-01-05 | 2      | 2025-01-01 19:15:01 | 2025-02-05 09:11:00 | host1           | 2333       |
| done | 1      |        | 2025-01-01 | 2025-01-01 | 1      | 2025-01-01 16:00:00 | 2025-02-05 11:30:00 | host1           | 1256       |
+------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
",
            2
        ];

        yield 'limiting by older-than and newer-than works as expected' => [
            ['--newer-than' => '2025-02-05 12:00:00', '--older-than' => '2025-02-21 12:00:00'],
            "1 invalidations found:
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| name                                         | idsite | report | date1      | date2      | period | ts_invalidated      | ts_started          | processing_host | process_id |
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| donec7445c35d0f9b340f5851df27a15c5ef.Actions | 1      |        | 2025-01-01 | 2025-12-31 | 4      | 2025-01-01 16:00:02 | 2025-02-05 16:44:22 | anotherhost     | 662        |
+----------------------------------------------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
",
            1
        ];

        yield 'limiting by everything works as expected' => [
            ['--newer-than' => '2025-02-05 08:00:00', '--older-than' => '2025-02-21 12:00:00', '--idsite' => ['1'], '--processing-host' => ['host1']],
            "1 invalidations found:
+------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| name | idsite | report | date1      | date2      | period | ts_invalidated      | ts_started          | processing_host | process_id |
+------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
| done | 1      |        | 2025-01-01 | 2025-01-01 | 1      | 2025-01-01 16:00:00 | 2025-02-05 11:30:00 | host1           | 1256       |
+------+--------+--------+------------+------------+--------+---------------------+---------------------+-----------------+------------+
",
            1
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function provideContainerConfigBeforeClass(): array
    {
        if (null === self::$captureHandler) {
            self::$captureHandler = new class extends AbstractProcessingHandler {
                public $messages = [];

                protected function write(array $record)
                {
                    $this->messages[] = (string)$record['formatted'];
                }
            };
        }

        return [
            'ini.tests.enable_logging' => 1,
            'Tests.log.allowAllHandlers' => true,
            'log.handlers' => [self::$captureHandler],
        ];
    }

    private function getLogOutput(): string
    {
        return implode("\n", self::$captureHandler->messages);
    }

    private function getInProgressInvalidationCount(): int
    {
        return (int) Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('archive_invalidations') . ' WHERE status = 1');
    }

    private function prepareInvalidations(): void
    {
        Db::exec('TRUNCATE TABLE ' . Common::prefixTable('archive_invalidations'));

        $invalidationsToInsert = [
            // invalidations idsite 1
            [
                'idarchive' => 7, 'name' => 'done', 'idsite' => 1, 'date1' => '2025-01-01', 'date2' => '2025-01-01',
                'period' => Day::PERIOD_ID, 'ts_invalidated' => '2025-01-01 16:00:00', 'status' => ArchiveInvalidator::INVALIDATION_STATUS_IN_PROGRESS,
                'report' => null, 'ts_started' => '2025-02-05 11:30:00', 'processing_host' => 'host1', 'process_id' => 1256
            ],
            [
                'idarchive' => null, 'name' => 'done', 'idsite' => 1, 'date1' => '2024-12-30', 'date2' => '2025-01-05',
                'period' => Week::PERIOD_ID, 'ts_invalidated' => '2025-01-01 16:00:01', 'status' => ArchiveInvalidator::INVALIDATION_STATUS_QUEUED,
                'report' => null, 'ts_started' => null, 'processing_host' => null, 'process_id' => null
            ],
            [
                'idarchive' => 66, 'name' => 'donec7445c35d0f9b340f5851df27a15c5ef.Actions', 'idsite' => 1, 'date1' => '2025-01-01', 'date2' => '2025-12-31',
                'period' => Year::PERIOD_ID, 'ts_invalidated' => '2025-01-01 16:00:02', 'status' => ArchiveInvalidator::INVALIDATION_STATUS_IN_PROGRESS,
                'report' => null, 'ts_started' => '2025-02-05 16:44:22', 'processing_host' => 'anotherhost', 'process_id' => 662
            ],

            // invalidations idsite 2
            [
                'idarchive' => 7, 'name' => 'done', 'idsite' => 2, 'date1' => '2025-01-01', 'date2' => '2025-01-01',
                'period' => Day::PERIOD_ID, 'ts_invalidated' => '2025-01-01 19:15:00', 'status' => ArchiveInvalidator::INVALIDATION_STATUS_QUEUED,
                'report' => null, 'ts_started' => null, 'processing_host' => null, 'process_id' => null
            ],
            [
                'idarchive' => null, 'name' => 'done', 'idsite' => 2, 'date1' => '2024-12-30', 'date2' => '2025-01-05',
                'period' => Week::PERIOD_ID, 'ts_invalidated' => '2025-01-01 19:15:01', 'status' => ArchiveInvalidator::INVALIDATION_STATUS_IN_PROGRESS,
                'report' => null, 'ts_started' => '2025-02-05 09:11:00', 'processing_host' => 'host1', 'process_id' => 2333
            ],
            [
                'idarchive' => 66, 'name' => 'donec7445c35d0f9b340f5851df27a15c5ef', 'idsite' => 2, 'date1' => '2025-01-01', 'date2' => '2025-12-31',
                'period' => Year::PERIOD_ID, 'ts_invalidated' => '2025-01-01 19:15:02', 'status' => ArchiveInvalidator::INVALIDATION_STATUS_QUEUED,
                'report' => null, 'ts_started' => null, 'processing_host' => null, 'process_id' => null
            ],

            // invalidations idsite 3
            [
                'idarchive' => null, 'name' => 'done.VisitsSummary', 'idsite' => 3, 'date1' => '2025-02-01', 'date2' => '2025-02-28',
                'period' => Month::PERIOD_ID, 'ts_invalidated' => '2025-02-020 19:15:01', 'status' => ArchiveInvalidator::INVALIDATION_STATUS_IN_PROGRESS,
                'report' => null, 'ts_started' => '2025-02-23 18:35:00', 'processing_host' => 'random', 'process_id' => 8558
            ],
        ];

        $sql = 'INSERT INTO ' . Common::prefixTable('archive_invalidations')
            . ' (`idarchive`, `name`, `idsite`, `date1`, `date2`, `period`, `ts_invalidated`, `status`, `report`, `ts_started`, `processing_host`, `process_id`)'
            . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';


        foreach ($invalidationsToInsert as $invalidation) {
            Db::query($sql, $invalidation);
        }
    }
}
