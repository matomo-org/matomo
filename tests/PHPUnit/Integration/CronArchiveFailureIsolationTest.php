<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Integration;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive;
use Piwik\DataAccess\ArchiveFailures;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorAPI;
use Piwik\Segment;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeLogger;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Failing archives must only hold back the archives built from them, nothing else.
 *
 * @group Archiver
 * @group CronArchive
 */
class CronArchiveFailureIsolationTest extends IntegrationTestCase
{
    private const FAIL_ARCHIVING_OPTION = 'CronArchiveFailureIsolationTest.failArchiving';

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2014-12-12 00:01:02');
        Fixture::createWebsite('2014-12-12 00:01:02');
        Rules::setBrowserTriggerArchiving(false);
    }

    public function testDaysAndWeeksAreArchivedWhenMonthsAndYearsFail(): void
    {
        $this->trackVisits(1, ['2019-11-05', '2019-12-10', '2019-12-11']);
        $this->invalidateDays(1, ['2019-11-05', '2019-12-10', '2019-12-11']);

        $this->failArchivesMatching(['* month *', '* year *']);
        $output = $this->runArchiver();

        foreach (['2019-11-05', '2019-12-10', '2019-12-11'] as $day) {
            $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'day', $day), $day);
        }
        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'week', '2019-11-04'));
        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'week', '2019-12-09'));

        // the year is built from the failed months, so it waits for them instead of failing as well
        $this->assertSame(['1 month 2019-11-01 done', '1 month 2019-12-01 done'], $this->getFailures());
        $this->assertStringNotContainsString('period=year', $output);
        $this->assertSame(['1 year 2019-01-01 done'], $this->getWaitingInvalidations(1, [4], ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD));
    }

    public function testEverythingElseIsArchivedWhenTheYearFails(): void
    {
        $this->trackVisits(1, ['2019-11-05', '2019-12-10']);
        $this->invalidateDays(1, ['2019-11-05', '2019-12-10']);

        $this->failArchivesMatching(['* year *']);
        $this->runArchiver();

        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'day', '2019-11-05'));
        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'day', '2019-12-10'));
        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'week', '2019-12-09'));
        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'month', '2019-11-01'));
        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'month', '2019-12-01'));
        $this->assertSame(['1 year 2019-01-01 done'], $this->getFailures());
    }

    public function testAllVisitsAndOtherSegmentsAreArchivedWhenASegmentFails(): void
    {
        $this->trackVisits(1, ['2019-12-10']);
        SegmentEditorAPI::getInstance()->add('failing', 'browserCode==FF', 1, true, true);
        SegmentEditorAPI::getInstance()->add('working', 'visitorType==new', 1, true, true);
        // only archive what this test invalidates, not the past reports of the new segments
        Option::delete(CronArchive\ReArchiveList::OPTION_NAME);
        $failingSegment = new Segment('browserCode==FF', [1]);
        $workingSegment = new Segment('visitorType==new', [1]);

        $this->invalidateDays(1, ['2019-12-10']);
        $this->invalidateDays(1, ['2019-12-10'], $failingSegment);
        $this->invalidateDays(1, ['2019-12-10'], $workingSegment);

        $this->failArchivesMatching(['* browserCode==FF']);
        $output = $this->runArchiver();

        foreach ([null, $workingSegment] as $segment) {
            $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'day', '2019-12-10', $segment));
            $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'week', '2019-12-09', $segment));
            $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'month', '2019-12-01', $segment));
            $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'year', '2019-01-01', $segment));
        }

        // only the failed day of the segment was attempted, its periods wait for it
        $failingDoneFlag = Rules::getDoneFlagArchiveContainsAllPlugins($failingSegment);
        $this->assertSame(['1 day 2019-12-10 ' . $failingDoneFlag], $this->getFailures());
        $this->assertSame(1, substr_count($output, '(attempt 1 of 5)'));
        $this->assertCount(4, $this->getWaitingInvalidations(1, [1, 2, 3, 4], ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD, $failingDoneFlag));
    }

    public function testOtherSitesAreArchivedWhenASiteFails(): void
    {
        $this->trackVisits(1, ['2019-12-10']);
        $this->trackVisits(2, ['2019-12-10']);
        $this->invalidateDays(1, ['2019-12-10']);
        $this->invalidateDays(2, ['2019-12-10']);

        $this->failArchivesMatching(['1 *']);
        $this->runArchiver();

        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(2, 'day', '2019-12-10'));
        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(2, 'week', '2019-12-09'));
        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(2, 'month', '2019-12-01'));
        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(2, 'year', '2019-01-01'));
        $this->assertSame(['1 day 2019-12-10 done'], $this->getFailures());
    }

    public function testManyFailingArchivesDoNotHoldBackTheOthers(): void
    {
        $days = [];
        for ($day = 1; $day <= 20; $day++) {
            $days[] = sprintf('2019-12-%02d', $day);
        }
        $this->trackVisits(1, $days);
        $this->invalidateDays(1, $days);

        // the first 9 days fail, in batches that also contain archives that work
        $this->failArchivesMatching(['* day 2019-12-0*']);
        $output = $this->runArchiver();

        foreach (array_slice($days, 9) as $day) {
            $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'day', $day), $day);
        }
        // the only week without a failed day
        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'week', '2019-12-16'));

        // every failed day was attempted exactly once, nothing built from them was attempted
        $this->assertCount(9, $this->getFailures());
        $this->assertSame(9, substr_count($output, '(attempt 1 of 5)'));
        $this->assertStringContainsString('Processed 12 archives.', $output);
        $this->assertStringNotContainsString('period=month', $output);
        $this->assertStringNotContainsString('period=year', $output);
        $this->assertStringNotContainsString('period=week&date=2019-12-09', $output);

        // the next run has nothing to do until the retry delay passed
        $output = $this->runArchiver();
        $this->assertStringContainsString('Holding back 9 archives that failed recently and 0 that were given up', $output);
        $this->assertStringNotContainsString('Starting archiving for', $output);
        $this->assertStringContainsString('Processed 0 archives.', $output);

        // once it passed, the failed days and everything built from them are archived
        $this->failArchivesMatching([]);
        Date::$now = strtotime('2020-02-03 05:05:07');
        $output = $this->runArchiver();

        $this->assertSame([], $this->getFailures());
        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'day', '2019-12-01'));
        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'week', '2019-12-09'));
        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'month', '2019-12-01'));
        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, 'year', '2019-01-01'));
        $this->assertSame([], $this->getWaitingInvalidations(1, [1, 2, 3, 4]));
    }

    /**
     * @return array<string, mixed>
     */
    public function provideContainerConfig()
    {
        Date::$now = (int) strtotime('2020-02-03 04:05:06');

        return [
            'observers.global' => \Piwik\DI::add([
                ['CoreAdminHome.archiveReports.start', \Piwik\DI::value(function ($idSite, $period, $segment) {
                    $patterns = json_decode((string) Option::get(self::FAIL_ARCHIVING_OPTION), true);
                    $job = $idSite . ' ' . $period->getLabel() . ' ' . $period->getDateStart()->toString() . ' ' . $segment->getString();

                    foreach (is_array($patterns) ? $patterns : [] as $pattern) {
                        if (is_string($pattern) && fnmatch($pattern, $job)) {
                            throw new \Exception('Simulated archiving failure');
                        }
                    }
                })],
            ]),
        ];
    }

    /**
     * @return void
     */
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }

    /**
     * @param list<string> $patterns fnmatch patterns of "idsite period date1 segment" of the jobs to fail
     */
    private function failArchivesMatching(array $patterns): void
    {
        Option::set(self::FAIL_ARCHIVING_OPTION, json_encode($patterns, JSON_THROW_ON_ERROR));
    }

    /**
     * @param list<string> $days
     */
    private function trackVisits(int $idSite, array $days): void
    {
        $tracker = Fixture::getTracker($idSite, $days[0] . ' 03:04:05');
        foreach ($days as $day) {
            $tracker->setForceVisitDateTime($day . ' 03:04:05');
            $tracker->setForceNewVisit();
            $tracker->setUrl('http://someurl.com/' . $day);
            Fixture::checkResponse($tracker->doTrackPageView('page'));
        }
    }

    /**
     * @param list<string> $days
     */
    private function invalidateDays(int $idSite, array $days, ?Segment $segment = null): void
    {
        /** @var ArchiveInvalidator $invalidator */
        $invalidator = StaticContainer::get(ArchiveInvalidator::class);
        $invalidator->markArchivesAsInvalidated([$idSite], $days, 'day', $segment);
    }

    private function runArchiver(): string
    {
        $logger = new FakeLogger();

        $archiver = new CronArchive($logger);
        $archiver->skipInvalidatingRecentDates = true;
        $archiver->init();
        $archiver->run();

        return $logger->output;
    }

    /**
     * @return string[] "idsite period date1 name" of each failure
     */
    private function getFailures(): array
    {
        $periods = array_flip(\Piwik\Piwik::$idPeriods);
        $failures = array_map(function ($failure) use ($periods) {
            return $failure['idsite'] . ' ' . $periods[(int) $failure['period']] . ' ' . $failure['date1'] . ' ' . $failure['name'];
        }, (new ArchiveFailures())->getFailures());
        sort($failures);

        return $failures;
    }

    /**
     * @param list<int> $periods
     * @param int|null $status only invalidations with this status, all not in progress if null
     * @return list<string> "idsite period date1 name" of each invalidation
     */
    private function getWaitingInvalidations(int $idSite, array $periods, ?int $status = null, string $name = 'done'): array
    {
        $labels = array_flip(\Piwik\Piwik::$idPeriods);
        $statuses = null === $status
            ? [ArchiveInvalidator::INVALIDATION_STATUS_QUEUED, ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD]
            : [$status];
        $rows = Db::fetchAll(
            'SELECT period, date1, name FROM ' . Common::prefixTable('archive_invalidations') . '
              WHERE idsite = ? AND name = ? AND status IN (' . implode(',', $statuses) . ')
                AND period IN (' . implode(',', array_map('intval', $periods)) . ')
              ORDER BY period, date1',
            [$idSite, $name]
        );

        return array_map(function ($row) use ($idSite, $labels) {
            return $idSite . ' ' . $labels[(int) $row['period']] . ' ' . $row['date1'] . ' ' . $row['name'];
        }, $rows);
    }

    private function getDoneFlagValue(int $idSite, string $period, string $date1, ?Segment $segment = null): int
    {
        $doneFlag = Rules::getDoneFlagArchiveContainsAllPlugins($segment ?? new Segment('', [$idSite]));
        $table = ArchiveTableCreator::getNumericTable(Date::factory($date1), false);

        return (int) Db::fetchOne(
            "SELECT value FROM `$table` WHERE idsite = ? AND name = ? AND period = ? AND date1 = ? ORDER BY idarchive DESC LIMIT 1",
            [$idSite, $doneFlag, \Piwik\Piwik::$idPeriods[$period], $date1]
        );
    }
}
