<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
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
 * @group Archiver
 * @group CronArchive
 */
class CronArchiveFailedArchivingTest extends IntegrationTestCase
{
    private const FAIL_ARCHIVING_OPTION = 'CronArchiveFailedArchivingTest.failArchiving';
    private const NOW = '2020-02-03 04:05:06';

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2014-12-12 00:01:02');
        Rules::setBrowserTriggerArchiving(false);

        $tracker = Fixture::getTracker(1, '2019-12-10 03:04:05');
        $tracker->setUrl('http://someurl.com');
        Fixture::checkResponse($tracker->doTrackPageView('abcdefg'));

        $tracker->setForceVisitDateTime('2019-11-05 03:04:05');
        Fixture::checkResponse($tracker->doTrackPageView('abcdefg'));
    }

    public function testFailedArchiveIsRetriedAfterTheDelay()
    {
        $invalidations = $this->archiveAndInvalidateDay();

        Option::set(self::FAIL_ARCHIVING_OPTION, 'all');
        $output = $this->runArchiver();

        // the failed day and the periods built from it are on hold, the periods are not attempted
        $this->assertSame($this->onHold($invalidations), $this->getInvalidations());
        $this->assertSame($this->getDoneFlags(ArchiveWriter::DONE_INVALIDATED), $this->getDoneFlagValues());
        $this->assertSame([['day', '2019-12-10', '1', '2020-02-03 05:05:06', 'Simulated archiving failure']], $this->getFailures());
        $this->assertStringContainsString('(attempt 1 of 5), it will be retried after 2020-02-03 05:05:06 UTC', $output);
        $this->assertStringContainsString('Processed 0 archives.', $output);
        $this->assertStringContainsString('Archiving failed for 1 archives in this run. 1 failed archives are waiting for a retry and 0 were given up.', $output);
        $this->assertStringNotContainsString('Skipped Archiving website', $output);
        $this->assertStringNotContainsString('period=week', $output);
        $this->assertStringNotContainsString('period=month', $output);
        $this->assertStringNotContainsString('period=year', $output);

        // not retried before the delay passed, even though it would work now
        Option::delete(self::FAIL_ARCHIVING_OPTION);
        $output = $this->runArchiver();

        $this->assertSame($this->onHold($invalidations), $this->getInvalidations());
        $this->assertStringContainsString('Holding back 1 archives that failed recently and 0 that were given up', $output);
        $this->assertStringContainsString('Processed 0 archives.', $output);

        $this->setNow('2020-02-03 05:05:07');
        $output = $this->runArchiver();

        $this->assertSame([], $this->getInvalidations());
        $this->assertSame([], $this->getFailures());
        $this->assertSame($this->getDoneFlags(ArchiveWriter::DONE_OK), $this->getDoneFlagValues());
        $this->assertStringContainsString('Processed 4 archives.', $output);
        $this->assertStringContainsString(CronArchive::NO_ERROR, $output);
    }

    public function testFailedArchiveIsGivenUpAfterTheMaximumAttempts()
    {
        Config::getInstance()->General['archive_failure_max_attempts'] = 2;
        Config::getInstance()->General['archive_failure_retry_delays'] = '60';
        $invalidations = $this->archiveAndInvalidateDay();

        Option::set(self::FAIL_ARCHIVING_OPTION, 'all');
        $this->runArchiver();
        $this->setNow('2020-02-03 04:06:07');
        $output = $this->runArchiver();

        $this->assertStringContainsString('(attempt 2 of 2), giving up. Run ./console core:archive-failures', $output);
        $this->assertSame([['day', '2019-12-10', '2', '2020-02-03 04:07:07', 'Simulated archiving failure']], $this->getFailures());

        // given up archives stay queued, but are no longer retried
        Option::delete(self::FAIL_ARCHIVING_OPTION);
        $this->setNow('2020-02-10 04:05:06');
        $output = $this->runArchiver();

        $this->assertSame($this->onHold($invalidations), $this->getInvalidations());
        $this->assertSame($this->getDoneFlags(ArchiveWriter::DONE_INVALIDATED), $this->getDoneFlagValues());
        $this->assertStringContainsString('Holding back 0 archives that failed recently and 1 that were given up', $output);
        $this->assertStringContainsString('Processed 0 archives.', $output);

        // until the failure is reset
        $archiveFailures = new ArchiveFailures();
        $archiveFailures->deleteFailuresById(array_column($archiveFailures->getFailures(), 'idfailure'));
        $output = $this->runArchiver();

        $this->assertSame([], $this->getInvalidations());
        $this->assertSame($this->getDoneFlags(ArchiveWriter::DONE_OK), $this->getDoneFlagValues());
        $this->assertStringContainsString('Processed 4 archives.', $output);
    }

    public function testRetryThatFailsAgainPutsTheArchivesBackOnHold()
    {
        $invalidations = $this->archiveAndInvalidateDay();

        Option::set(self::FAIL_ARCHIVING_OPTION, 'all');
        $this->runArchiver();

        // the retry releases the day and the periods built from it, the day is archived first and fails again
        $this->setNow('2020-02-03 05:05:07');
        $output = $this->runArchiver();

        $this->assertStringContainsString('(attempt 2 of 5)', $output);
        $this->assertStringNotContainsString('period=week', $output);
        $this->assertStringNotContainsString('period=month', $output);
        $this->assertSame($this->onHold($invalidations), $this->getInvalidations());
    }

    public function testArchivesOnHoldAreRetriedAfterARunThatStoppedEarly()
    {
        $this->archiveAndInvalidateDay();

        Option::set(self::FAIL_ARCHIVING_OPTION, 'all');
        $this->runArchiver();

        // a run that only processes one archive, the failed day
        Option::delete(self::FAIL_ARCHIVING_OPTION);
        $this->setNow('2020-02-03 05:05:07');
        $output = $this->runArchiver(null, 1);
        $this->assertStringContainsString('Processed 1 archives.', $output);
        $this->assertSame([], $this->getFailures());

        // the periods built from it were released and are archived in the next run
        $output = $this->runArchiver();
        $this->assertStringContainsString('Processed 3 archives.', $output);
        $this->assertSame([], $this->getInvalidations());
        $this->assertSame($this->getDoneFlags(ArchiveWriter::DONE_OK), $this->getDoneFlagValues());
    }

    public function testInvalidatingAnArchiveOnHoldAgainAddsNoInvalidation()
    {
        $invalidations = $this->archiveAndInvalidateDay();

        Option::set(self::FAIL_ARCHIVING_OPTION, 'all');
        $this->runArchiver();

        $this->invalidateDay();
        $this->assertSame($this->onHold($invalidations), $this->getInvalidations());
    }

    public function testEmptyResponseIsRecordedAsFailure()
    {
        $invalidations = $this->archiveAndInvalidateDay();

        Option::set(self::FAIL_ARCHIVING_OPTION, 'exit');
        $output = $this->runArchiver();

        $this->assertSame($this->onHold($invalidations), $this->getInvalidations());
        $this->assertSame([['day', '2019-12-10', '1', '2020-02-03 05:05:06', 'Empty response']], $this->getFailures());
        $this->assertStringContainsString('(attempt 1 of 5), it will be retried after', $output);
    }

    public function testFailureDoesNotDeferArchivesNotBuiltFromTheFailedOne()
    {
        $this->archiveAndInvalidateDay();
        $this->invalidateDay('2019-11-05');

        Option::set(self::FAIL_ARCHIVING_OPTION, 'day 2019-12-10');
        $this->runArchiver();

        // the other day and the periods built from it are archived in the same run
        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(1, '2019-11-05'));
        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(2, '2019-11-04'));
        $this->assertSame(ArchiveWriter::DONE_OK, $this->getDoneFlagValue(3, '2019-11-01'));

        $this->assertSame($this->getDoneFlags(ArchiveWriter::DONE_INVALIDATED), $this->getDoneFlagValues());
        $this->assertSame([
            ['1', '2019-12-10', '2'],
            ['2', '2019-12-09', '2'],
            ['3', '2019-12-01', '2'],
            ['4', '2019-01-01', '2'],
        ], array_map(function ($row) {
            return [(string) $row['period'], $row['date1'], (string) $row['status']];
        }, $this->getInvalidations()));
    }

    public function testQueuedDuplicateOfFailedInvalidationIsNotRetried()
    {
        $invalidations = $this->archiveAndInvalidateDay();

        // an identical queued invalidation, which replaces the failed one when it is released
        $dayInvalidation = array_values(array_filter($invalidations, function ($row) {
            return $row['period'] == 1;
        }))[0];
        $table = Common::prefixTable('archive_invalidations');
        Db::query(
            "INSERT INTO `$table` (name, idsite, date1, date2, period, ts_invalidated, status) VALUES (?, 1, ?, ?, ?, ?, ?)",
            [$dayInvalidation['name'], $dayInvalidation['date1'], $dayInvalidation['date2'], 1, Date::now()->getDatetime(), ArchiveInvalidator::INVALIDATION_STATUS_QUEUED]
        );

        Option::set(self::FAIL_ARCHIVING_OPTION, 'all');
        $this->runArchiver(1);

        $this->assertSame([['day', '2019-12-10', '1', '2020-02-03 05:05:06', 'Simulated archiving failure']], $this->getFailures());
        $this->assertCount(4, $this->getInvalidations());
    }

    public function testFailureOfArchiveThatCanNoLongerBeProcessedIsRemoved()
    {
        $idSegment = SegmentEditorAPI::getInstance()->add('segment', 'browserCode==FF', 1, true, true);
        $segmentDoneFlag = 'done' . (new Segment('browserCode==FF', [1]))->getHash();

        $archiveFailures = new ArchiveFailures();
        $failedInvalidation = ['idsite' => 1, 'name' => $segmentDoneFlag, 'period' => 1, 'date1' => '2019-12-10', 'date2' => '2019-12-10', 'report' => null];
        for ($i = 0; $i < 5; $i++) {
            $archiveFailures->recordFailure($failedInvalidation, 'Simulated archiving failure');
        }
        $this->invalidateDay('2019-12-10', new Segment('browserCode==FF', [1]));
        $this->assertNotEmpty($this->getInvalidations($segmentDoneFlag));

        SegmentEditorAPI::getInstance()->delete($idSegment);
        $this->runArchiver();

        $this->assertSame([], $this->getFailures());
        $this->assertSame([], $this->getInvalidations($segmentDoneFlag));
    }

    public function testUpdateDuringArchivingStopsWithoutRecordingFailures()
    {
        $this->archiveAndInvalidateDay();
        $this->invalidateDay('2019-11-05');
        $invalidations = $this->getInvalidations();

        Option::set(self::FAIL_ARCHIVING_OPTION, 'update');

        $logger = new FakeLogger();
        $archiver = new CronArchive($logger);
        $archiver->skipInvalidatingRecentDates = true;
        $archiver->concurrentRequestsPerWebsite = 1;
        $archiver->disableScheduledTasks = true;

        try {
            $archiver->main();
            $this->fail('Archiving did not fail');
        } catch (\Exception $e) {
            $this->assertStringContainsString('1 total errors during this script execution', $e->getMessage());
        }

        $this->assertStringContainsString('Archiving stopped because Matomo or plugins were updated while archiving. Run ./console core:update', $logger->output);
        // the run stopped after the first job
        $this->assertSame(1, substr_count($logger->output, 'Starting archiving for'));
        $this->assertSame([], $this->getFailures());
        $this->assertSame($invalidations, $this->getInvalidations());
    }

    public function testInvalidResponseIsRecordedAsFailure()
    {
        $this->archiveAndInvalidateDay();

        Option::set(self::FAIL_ARCHIVING_OPTION, 'garbage');
        $this->runArchiver();

        $this->assertSame([['day', '2019-12-10', '1', '2020-02-03 05:05:06', 'not a JSON response']], $this->getFailures());
    }

    public function testArchivingDoesNotRunWhileAnUpdateIsPending()
    {
        $this->archiveAndInvalidateDay();
        Option::set('version_core', '6.0.0');
        Config::getInstance()->General['enable_auto_update'] = 1;
        Config::getInstance()->General['enable_internet_features'] = 1;

        try {
            $this->runArchiverMain();
            $this->fail('Archiving did not fail');
        } catch (\Exception $e) {
            $this->assertStringContainsString("Archiving won't run because Matomo or plugins were updated and the database upgrade is still pending.", $e->getMessage());
        }

        $this->assertCount(4, $this->getInvalidations());
    }

    public function testArchivingRunsWhileAnUpdateIsPendingIfAutoUpdateIsDisabled()
    {
        $this->archiveAndInvalidateDay();
        Option::set('version_core', '6.0.0');
        Config::getInstance()->General['enable_auto_update'] = 0;

        $logger = new FakeLogger();
        $archiver = new CronArchive($logger);
        $archiver->skipInvalidatingRecentDates = true;
        $archiver->disableScheduledTasks = true;

        try {
            $archiver->main();
            $this->fail('Archiving did not fail');
        } catch (\Exception $e) {
            // the sub-requests of this test still have auto update enabled, so they report the pending update
            $this->assertStringContainsString('1 total errors during this script execution', $e->getMessage());
        }

        // archiving started, like requests do while an upgrade is pending without auto update
        $this->assertStringNotContainsString("Archiving won't run", $logger->output);
        $this->assertStringContainsString('Starting archiving for', $logger->output);
        $this->assertStringContainsString('Archiving stopped because Matomo or plugins were updated while archiving', $logger->output);
    }

    public function testFailureIsRemovedWhenThereIsNothingToArchive()
    {
        // a day without visits, its failure can be retried
        $this->invalidateDay('2019-12-11');
        (new ArchiveFailures())->recordFailure(['idsite' => 1, 'name' => 'done', 'period' => 1, 'date1' => '2019-12-11', 'date2' => '2019-12-11', 'report' => null], 'Simulated archiving failure');
        $this->setNow('2020-02-03 05:05:07');

        $output = $this->runArchiver();

        $this->assertStringContainsString('Found no visits for site ID = 1, day (2019-12-11,2019-12-11)', $output);
        $this->assertSame([], $this->getFailures());
    }

    public function provideContainerConfig()
    {
        Date::$now = strtotime(self::NOW);

        return [
            'observers.global' => \Piwik\DI::add([
                ['CoreAdminHome.archiveReports.start', \Piwik\DI::value(function ($idSite, $period) {
                    $toFail = Option::get(self::FAIL_ARCHIVING_OPTION);
                    if ($toFail === 'exit') {
                        exit;
                    }
                    if ($toFail === 'garbage') {
                        echo 'not a JSON response';
                        exit;
                    }
                    if ($toFail === 'update') {
                        throw new \Exception('Piwik and/or some plugins have been upgraded to a new version.');
                    }
                    if ($toFail === 'all' || $toFail === $period->getLabel() . ' ' . $period->getDateStart()->toString()) {
                        throw new \Exception('Simulated archiving failure');
                    }
                })],
            ]),
        ];
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }

    private function onHold(array $invalidations): array
    {
        return array_map(function ($invalidation) {
            $invalidation['status'] = ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD;
            return $invalidation;
        }, $invalidations);
    }

    private function runArchiverMain(): string
    {
        $logger = new FakeLogger();

        $archiver = new CronArchive($logger);
        $archiver->skipInvalidatingRecentDates = true;
        $archiver->disableScheduledTasks = true;
        $archiver->main();

        return $logger->output;
    }

    private function setNow(string $now): void
    {
        Date::$now = strtotime($now);
    }

    private function archiveAndInvalidateDay(): array
    {
        // build the archives once, then invalidate them again
        $this->invalidateDay();
        $this->runArchiver();
        $this->assertSame($this->getDoneFlags(ArchiveWriter::DONE_OK), $this->getDoneFlagValues());

        $this->invalidateDay();
        $invalidations = $this->getInvalidations();
        $this->assertCount(4, $invalidations);

        return $invalidations;
    }

    private function invalidateDay(string $date = '2019-12-10', ?Segment $segment = null): void
    {
        /** @var ArchiveInvalidator $invalidator */
        $invalidator = StaticContainer::get(ArchiveInvalidator::class);
        $invalidator->markArchivesAsInvalidated([1], [$date], 'day', $segment);
    }

    private function runArchiver(?int $concurrentRequests = null, ?int $maxArchivesToProcess = null): string
    {
        $logger = new FakeLogger();

        $archiver = new CronArchive($logger);
        $archiver->skipInvalidatingRecentDates = true;
        if ($concurrentRequests) {
            $archiver->concurrentRequestsPerWebsite = $concurrentRequests;
        }
        if ($maxArchivesToProcess) {
            $archiver->maxArchivesToProcess = $maxArchivesToProcess;
        }
        $archiver->init();
        $archiver->run();

        return $logger->output;
    }

    private function getInvalidations(string $name = 'done'): array
    {
        $table = Common::prefixTable('archive_invalidations');
        return Db::fetchAll("SELECT idinvalidation, name, period, date1, date2, status FROM `$table` WHERE idsite = 1 AND name = ? ORDER BY idinvalidation", [$name]);
    }

    private function getFailures(): array
    {
        return array_map(function ($failure) {
            return [
                ['1' => 'day', '2' => 'week', '3' => 'month', '4' => 'year'][$failure['period']] ?? $failure['period'],
                $failure['date1'],
                (string) $failure['attempts'],
                $failure['ts_retry_after'],
                $failure['last_error'],
            ];
        }, (new ArchiveFailures())->getFailures());
    }

    private function getDoneFlags(int $value): array
    {
        return ['day' => $value, 'week' => $value, 'month' => $value, 'year' => $value];
    }

    private function getDoneFlagValues(): array
    {
        $periods = [
            'day' => [1, '2019-12-10'],
            'week' => [2, '2019-12-09'],
            'month' => [3, '2019-12-01'],
            'year' => [4, '2019-01-01'],
        ];

        $values = [];
        foreach ($periods as $label => [$period, $date1]) {
            $values[$label] = $this->getDoneFlagValue($period, $date1);
        }

        return $values;
    }

    private function getDoneFlagValue(int $period, string $date1): int
    {
        $table = ArchiveTableCreator::getNumericTable(Date::factory($date1), false);
        return (int) Db::fetchOne(
            "SELECT value FROM `$table` WHERE idsite = 1 AND name = 'done' AND period = ? AND date1 = ? ORDER BY idarchive DESC LIMIT 1",
            [$period, $date1]
        );
    }
}
