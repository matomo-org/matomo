<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Date;
use Piwik\Db;
use Piwik\Log\LoggerInterface;
use Piwik\Period;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeLogger;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group ArchiveCronTest
 */
class ArchiveCronSubPeriodTest extends IntegrationTestCase
{
    /**
     * Prepares database with data to be used for each test
     *
     * Creates a fixed set of visits, one per day, for the months 2024-12 & 2025-01
     * Archives all data and ensures that done flags are available for all periods
     */
    public static function beforeTableDataCached(): void
    {
        Fixture::createSuperUser();
        Fixture::loadAllTranslations();
        $idSite = Fixture::createWebsite('2024-01-01', 0, 'My Matomo', 'https://matomo.url');
        $period = new Period\Range('day', '2024-12-01,2025-01-31');
        self::generateVisitsOnePerDay($idSite, $period);
        $output = self::runArchiving();
        self::assertStringContainsString('no error', $output);
        self::assertAllDoneFlagExists($period);
    }

    /**
     * @param Period[] $periodsToRemove
     * @param Period[] $periodsToInvalidate
     * @param Period[] $periodsExpectedToMiss
     *
     * @dataProvider getTestCases
     */
    public function testArchivingProcessesExpectedSubPeriods(array $periodsToRemove, array $periodsToInvalidate, array $periodsExpectedToMiss): void
    {
        $periodWithData = new Period\Range('day', '2024-12-01,2025-01-31');

        self::assertAllDoneFlagExists($periodWithData);

        foreach ($periodsToRemove as $period) {
            self::removeDoneFlag($period);
        }

        $invalidator = StaticContainer::get(ArchiveInvalidator::class);

        foreach ($periodsToInvalidate as $period) {
            if ($period->getId() !== Period\Range::PERIOD_ID) {
                $invalidator->markArchivesAsInvalidated([1], [$period->getDateStart()], $period->getLabel());
                self::assertDoneFlagExists($period, ArchiveWriter::DONE_INVALIDATED);
            } else {
                // directly create an invalidation for range periods
                $sql = 'INSERT INTO ' . Common::prefixTable('archive_invalidations')
                    . ' (`idarchive`, `name`, `idsite`, `date1`, `date2`, `period`, `ts_invalidated`)'
                    . ' VALUES (?, ?, ?, ?, ?, ?, ?)';

                Db::query($sql, [null, 'done', 1, $period->getDateStart()->toString(), $period->getDateEnd()->toString(), Period\Range::PERIOD_ID, 'NOW()']);
            }
        }

        $output = self::runArchiving();
        self::assertStringContainsString('no error', $output);

        self::assertAllDoneFlagExists($periodWithData, $periodsExpectedToMiss);
    }

    public function getTestCases(): \Iterator
    {
        $dayPeriod2024_12_01 = new Period\Day(Date::factory('2024-12-01'));
        $dayPeriod2024_12_12 = new Period\Day(Date::factory('2024-12-12'));
        $dayPeriod2024_12_30 = new Period\Day(Date::factory('2024-12-30'));
        $dayPeriod2025_01_03 = new Period\Day(Date::factory('2025-01-03'));
        $weekPeriod2024_12_09 = new Period\Week(Date::factory('2024-12-09'));
        $weekPeriod2024_12_16 = new Period\Week(Date::factory('2024-12-16'));
        $weekPeriod2024_12_30 = new Period\Week(Date::factory('2024-12-30'));
        $monthPeriod2024_12 = new Period\Month(Date::factory('2024-12-01'));
        $monthPeriod2025_01 = new Period\Month(Date::factory('2025-01-01'));
        $yearPeriod2024 = new Period\Year(Date::factory('2024-01-01'));
        $rangePeriod = new Period\Range('day', '2024-12-11,2025-01-12');

        yield 'missing day will not get archived automatically if not invalidated' => [
            [$dayPeriod2024_12_12],
            [],
            [$dayPeriod2024_12_12],
        ];

        yield 'missing day will get indirectly archived when week is archived' => [
            [$dayPeriod2024_12_12],
            [$weekPeriod2024_12_09],
            [],
        ];

        yield 'missing days will get indirectly archived when week crossing year and month boundaries is archived' => [
            [
                $dayPeriod2024_12_30, // 2024-12-30 is part of the invalidated week, so will get indirectly archived
                $dayPeriod2025_01_03, // 2025-01-03 is part of the invalidated week, so will get indirectly archived
            ],
            [$weekPeriod2024_12_30],
            [],
        ];

        yield 'missing day will not get indirectly archived when month is archived, but usable week exists' => [
            [
                $dayPeriod2024_12_12, // 2024-12-12 is a thursday and part of the usable week 2024-12-09,2024-12-15, so won't get archived
            ],
            [$monthPeriod2024_12],
            [$dayPeriod2024_12_12],
        ];

        yield 'missing day will get indirectly archived when month is archived, but day is outside of usable weeks' => [
            [
                $dayPeriod2024_12_01, // 2024-12-01 is a sunday, and not part of any week that can be used to process the month, so will get archived
            ],
            [$monthPeriod2024_12],
            [],
        ];

        yield 'when processing a month required periods will get indirectly archived' => [
            [
                $dayPeriod2024_12_01, // 2024-12-01 is a sunday, and not part of any week that can be used to process the month, so will get archived
                $dayPeriod2024_12_12, // 2024-12-12 is part of week 2024-12-09,2024-12-15, which is also missing, so both will get archived
                $dayPeriod2024_12_30, // 2024-12-30 is part of a week crossing year boundaries, so only the day will get archived
                $weekPeriod2024_12_09, // week archive required to process the month, so will get archived
                $weekPeriod2024_12_16, // week archive required to process the month, so will get archived
                $weekPeriod2024_12_30, // week crossing year boundaries, so can't be used to archive month and will therefor not be archived
            ],
            [$monthPeriod2024_12],
            [$weekPeriod2024_12_30],
        ];

        yield 'missing day will not get indirectly archived when year is archived, but usable month exists' => [
            [
                $dayPeriod2024_12_12, // 2024-12-12 is part of the usable week 2024-12-09,2024-12-15, so won't get archived
            ],
            [$yearPeriod2024],
            [$dayPeriod2024_12_12],
        ];

        yield 'when processing a month only missing days needed to process the month are indirectly archived' => [
            [
                $dayPeriod2024_12_01, // 2024-12-01 is a sunday, and not part of any week that can be used to process the month, so will get archived
                $dayPeriod2024_12_12, // 2024-12-12 is part of the usable week 2024-12-09,2024-12-15, so won't get archived
            ],
            [$monthPeriod2024_12],
            [$dayPeriod2024_12_12],
        ];

        yield 'when processing a year only missing periods needed to process are indirectly archived' => [
            [
                $dayPeriod2024_12_01, // 2024-12-01 is a sunday, and not part of any week that can be used to process the month, so will get archived
                $dayPeriod2024_12_12, // 2024-12-12 is part of the week 2024-12-09,2024-12-15. It will not get archived as the week is available
                $monthPeriod2024_12, // month is part of invalidated year, so will be re archived - including missing required sub periods
                $monthPeriod2025_01, // month is not part of the invalidated year, so it won't get indirectly archived
            ],
            [$yearPeriod2024],
            [$dayPeriod2024_12_12, $monthPeriod2025_01],
        ];

        yield 'when processing a range only missing periods needed to process are indirectly archived' => [
            [
                $dayPeriod2024_12_01, // 2024-12-01 is not part of the range, so will not get archived
                $dayPeriod2024_12_12, // 2024-12-12 is part of the week 2024-12-09,2024-12-15, which is not fully part of the range, so it needs to get archived
                $dayPeriod2025_01_03, // 2025-01-03 is part of the week 2024-12-30,2025-01-05. It will not get archived as the week is available
                $weekPeriod2024_12_16, // week is part of invalidated range, so will be re archived
                $monthPeriod2025_01, // month is not fully part of the invalidated range, so it won't get indirectly archived
            ],
            [$rangePeriod],
            [$dayPeriod2024_12_01, $dayPeriod2025_01_03, $monthPeriod2025_01],
        ];
    }

    private static function generateVisitsOnePerDay(int $idSite, Period $period): void
    {
        $tracker = Fixture::getTracker($idSite, $period->getDateTimeStart());

        $tracker->enableBulkTracking();

        foreach ($period->getSubperiods() as $date) {
            $tracker->setForceVisitDateTime($date->toString('Y-m-d 12:00:00'));
            $tracker->setUrl('https://matomo.url/index');
            Fixture::assertTrue($tracker->doTrackPageView('index'));
        }

        Fixture::checkBulkTrackingResponse($tracker->doBulkTrack());
    }

    private static function runArchiving(): string
    {
        $logger = new FakeLogger();
        StaticContainer::getContainer()->set(LoggerInterface::class, $logger);
        $archiver = new CronArchive($logger);
        $archiver->main();
        return $logger->output;
    }

    private static function assertAllDoneFlagExists($period, $periodsToSkip = []): void
    {
        // gather all sub periods
        $subPeriods = [];
        foreach ($period->getSubperiods() as $day) {
            $subPeriods[$day->getRangeString()] = $day;
            $week = new Period\Week($day->getDateStart());
            $subPeriods[$week->getRangeString()] = $week;
            $month = new Period\Week($day->getDateStart());
            $subPeriods[$month->getRangeString()] = $month;
            $year = new Period\Week($day->getDateStart());
            $subPeriods[$year->getRangeString()] = $year;
        }

        // check periods expected to be missing
        foreach ($periodsToSkip as $period) {
            self::assertDoneFlagNotExists($period);
            if (isset($subPeriods[$period->getRangeString()])) {
                unset($subPeriods[$period->getRangeString()]);
            }
        }

        // check all (other) sub periods have a done flag
        foreach ($subPeriods as $subPeriod) {
            self::assertDoneFlagExists($subPeriod, ArchiveWriter::DONE_OK);
        }
    }

    private static function assertDoneFlagExists(Period $period, int $expectedValue): void
    {
        $table = ArchiveTableCreator::getNumericTable($period->getDateStart());

        $value = Db::get()->fetchOne(
            'SELECT value FROM ' . $table . ' WHERE date1 = ? AND date2 = ? AND period = ? AND name = ?',
            [
                $period->getDateStart()->toString(),
                $period->getDateEnd()->toString(),
                $period->getId(),
                'done',
            ]
        );

        self::assertEquals($expectedValue, $value, 'done flag mismatch for ' . $period->getRangeString());
    }

    private static function assertDoneFlagNotExists(Period $period): void
    {
        $table = ArchiveTableCreator::getNumericTable($period->getDateStart());

        $value = Db::get()->fetchOne(
            'SELECT value FROM ' . $table . ' WHERE date1 = ? AND date2 = ? AND period = ? AND name = ?',
            [
                $period->getDateStart()->toString(),
                $period->getDateEnd()->toString(),
                $period->getId(),
                'done',
            ]
        );

        self::assertEmpty($value, 'done flag found for ' . $period->getRangeString());
    }

    private static function removeDoneFlag(Period $period): void
    {
        $table = ArchiveTableCreator::getNumericTable($period->getDateStart());

        Db::get()->query(
            'DELETE FROM ' . $table . ' WHERE date1 = ? AND date2 = ? AND period = ? AND name = ?',
            [
                $period->getDateStart()->toString(),
                $period->getDateEnd()->toString(),
                $period->getId(),
                'done',
            ]
        );

        self::assertDoneFlagNotExists($period);
    }

    public function provideContainerConfig()
    {
        return [
            // disable browser archiving
            Config::class => \Piwik\DI::decorate(function (Config $previous) {
                $previous->General['enable_browser_archiving_triggering'] = 0;
                $previous->General['browser_archiving_disabled_enforce'] = 1;
                return $previous;
            }),
        ];
    }
}
