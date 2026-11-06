<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\ArchiveFailures;
use Piwik\DataAccess\Model;
use Piwik\Date;
use Piwik\Db;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group ArchiveFailures
 */
class ArchiveFailuresTest extends IntegrationTestCase
{
    private ArchiveFailures $archiveFailures;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2015-01-01 00:00:00');
        Fixture::createWebsite('2015-01-01 00:00:00');

        Date::$now = strtotime('2020-02-03 04:05:06');
        $this->archiveFailures = new ArchiveFailures();
    }

    public function tearDown(): void
    {
        Date::$now = null;
        parent::tearDown();
    }

    public function testRecordFailureBacksOffAndGivesUp(): void
    {
        $results = [];
        for ($i = 0; $i < 5; $i++) {
            $results[] = $this->archiveFailures->recordFailure($this->getInvalidation(), 'error ' . $i);
        }

        $this->assertSame([
            ['attempts' => 1, 'maxAttempts' => 5, 'retryAfter' => '2020-02-03 05:05:06', 'givenUp' => false],
            ['attempts' => 2, 'maxAttempts' => 5, 'retryAfter' => '2020-02-03 10:05:06', 'givenUp' => false],
            ['attempts' => 3, 'maxAttempts' => 5, 'retryAfter' => '2020-02-04 04:05:06', 'givenUp' => false],
            ['attempts' => 4, 'maxAttempts' => 5, 'retryAfter' => '2020-02-04 04:05:06', 'givenUp' => false],
            ['attempts' => 5, 'maxAttempts' => 5, 'retryAfter' => '2020-02-04 04:05:06', 'givenUp' => true],
        ], $results);

        $failures = $this->archiveFailures->getFailures();
        $this->assertCount(1, $failures);
        $this->assertSame('error 4', $failures[0]['last_error']);
        $this->assertSame('', $failures[0]['report']);
        $this->assertSame(ArchiveFailures::STATE_GIVEN_UP, $failures[0]['state']);
    }

    public function testRetryDelaysAndMaxAttemptsCanBeConfiguredPerSite(): void
    {
        Config::getInstance()->General_2 = ['archive_failure_retry_delays' => '10,20', 'archive_failure_max_attempts' => 3];

        $this->assertSame(3600, ArchiveFailures::getRetryDelay(1, 1));
        $this->assertSame(86400, ArchiveFailures::getRetryDelay(1, 9));
        $this->assertSame(5, ArchiveFailures::getMaxAttempts(1));

        $this->assertSame(10, ArchiveFailures::getRetryDelay(2, 1));
        $this->assertSame(20, ArchiveFailures::getRetryDelay(2, 2));
        $this->assertSame(20, ArchiveFailures::getRetryDelay(2, 3));
        $this->assertSame(3, ArchiveFailures::getMaxAttempts(2));
    }

    /**
     * @dataProvider getInvalidRetryDelays
     */
    public function testInvalidRetryDelaysFallBackToDefaults(string $delays): void
    {
        Config::setSetting('General', 'archive_failure_retry_delays', $delays);

        $this->assertSame(3600, ArchiveFailures::getRetryDelay(1, 1));
        $this->assertSame(21600, ArchiveFailures::getRetryDelay(1, 2));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public function getInvalidRetryDelays(): iterable
    {
        yield 'units' => ['1h,6h'];
        yield 'negative' => ['60,-5'];
        yield 'zero' => ['0,60'];
        yield 'text' => ['abc'];
        yield 'empty' => [''];
    }

    public function testRetryDelaysAreCappedAndMaxAttemptsClamped(): void
    {
        Config::setSetting('General', 'archive_failure_retry_delays', ' 60 , 99999999999999 ');
        Config::setSetting('General', 'archive_failure_max_attempts', 0);

        $this->assertSame(60, ArchiveFailures::getRetryDelay(1, 1));
        $this->assertSame(31536000, ArchiveFailures::getRetryDelay(1, 2));
        $this->assertSame(1, ArchiveFailures::getMaxAttempts(1));
    }

    public function testRaisingTheMaximumAttemptsResumesRetrying(): void
    {
        Config::setSetting('General', 'archive_failure_max_attempts', 1);
        $this->archiveFailures->recordFailure($this->getInvalidation(), 'error');
        $this->assertSame(ArchiveFailures::STATE_GIVEN_UP, $this->archiveFailures->getFailures()[0]['state']);

        Config::setSetting('General', 'archive_failure_max_attempts', 2);
        $this->assertSame(ArchiveFailures::STATE_WAITING, $this->archiveFailures->getFailures()[0]['state']);

        Date::$now = strtotime('2020-02-03 05:05:07');
        $this->assertSame(ArchiveFailures::STATE_RETRY_PENDING, $this->archiveFailures->getFailures()[0]['state']);
    }

    public function testRecordFailureRemovesStackTraceAndLimitsTheErrorLength(): void
    {
        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2020-01-01', 'date2' => '2020-01-01']), "SQLSTATE[HY000]: General error #0 /core/Db.php(10): query() #1 {main}");
        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2020-01-02', 'date2' => '2020-01-02']), str_repeat('ä', 1500));
        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2020-01-03', 'date2' => '2020-01-03']), "Uncaught exception: out of memory\n#0 /core/Db.php(10): query()\n#1 {main}");

        $errors = array_column($this->archiveFailures->getFailures(), 'last_error');
        $this->assertSame('SQLSTATE[HY000]: General error', $errors[0]);
        $this->assertSame(str_repeat('ä', 1000), $errors[1]);
        $this->assertSame('Uncaught exception: out of memory', $errors[2]);
    }

    public function testFailuresAreKeptPerArchive(): void
    {
        $this->archiveFailures->recordFailure($this->getInvalidation(), 'error');
        $this->archiveFailures->recordFailure($this->getInvalidation(['report' => 'getKeywords']), 'error');
        $this->archiveFailures->recordFailure($this->getInvalidation(['period' => 2, 'date1' => '2020-01-27', 'date2' => '2020-02-02']), 'error');
        $this->archiveFailures->recordFailure($this->getInvalidation(['idsite' => 2]), 'error');

        $this->assertCount(4, $this->archiveFailures->getFailures());
        $this->assertCount(1, $this->archiveFailures->getFailures([2]));
        $this->assertSame(['waiting' => 4, 'givenUp' => 0, 'oldestFailure' => '2020-02-03 04:05:06'], $this->archiveFailures->getFailureCounts());

        $this->archiveFailures->deleteFailure($this->getInvalidation(['report' => 'getKeywords']));
        $this->assertCount(3, $this->archiveFailures->getFailures());
    }

    public function testDeleteFailuresWithoutInvalidations(): void
    {
        $withInvalidation = $this->getInvalidation(['date1' => '2020-01-01', 'date2' => '2020-01-01']);
        $this->archiveFailures->recordFailure($withInvalidation, 'error');
        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2020-01-02', 'date2' => '2020-01-02']), 'error');
        $this->archiveFailures->recordFailure($this->getInvalidation(['idsite' => 2]), 'error');

        Db::query(
            'INSERT INTO ' . Common::prefixTable('archive_invalidations') . ' (name, idsite, date1, date2, period, ts_invalidated, status) VALUES (?, ?, ?, ?, ?, ?, 1)',
            ['done', 1, '2020-01-01', '2020-01-01', 1, Date::now()->getDatetime()]
        );

        $this->assertSame(1, $this->archiveFailures->deleteFailuresWithoutInvalidations(1));

        $remaining = $this->archiveFailures->getFailures();
        $this->assertSame([['1', '2020-01-01'], ['2', '2020-02-03']], array_map(function ($failure) {
            return [(string) $failure['idsite'], $failure['date1']];
        }, $remaining));
    }

    public function testFailuresOfDeletedSitesAreIgnoredAndDeleted(): void
    {
        $this->archiveFailures->recordFailure($this->getInvalidation(), 'error');
        $this->archiveFailures->recordFailure($this->getInvalidation(['idsite' => 99]), 'error');

        $this->assertSame(['1'], array_map('strval', array_column($this->archiveFailures->getFailures(), 'idsite')));
        $this->assertSame(1, $this->archiveFailures->getFailureCounts()['waiting']);

        $this->archiveFailures->deleteFailuresForDeletedSites();

        $this->assertSame('0', (string) Db::fetchOne('SELECT COUNT(*) FROM ' . ArchiveFailures::getTable() . ' WHERE idsite = 99'));
        $this->assertCount(1, $this->archiveFailures->getFailures());
    }

    public function testGivenUpWarningCountsGivenUpArchivesOfTheInvalidatedYearsOrRange(): void
    {
        Config::setSetting('General', 'archive_failure_max_attempts', 2);
        $givenUp = [
            $this->getInvalidation(['date1' => '2019-03-05', 'date2' => '2019-03-05']),
            $this->getInvalidation(['period' => 3, 'date1' => '2019-11-01', 'date2' => '2019-11-30']),
            $this->getInvalidation(['idsite' => 2, 'date1' => '2019-06-01', 'date2' => '2019-06-01']),
            $this->getInvalidation(['date1' => '2021-01-01', 'date2' => '2021-01-01']),
        ];
        foreach ($givenUp as $invalidation) {
            $this->archiveFailures->recordFailure($invalidation, 'error');
            $this->archiveFailures->recordFailure($invalidation, 'error');
        }
        // waiting for a retry
        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2019-07-07', 'date2' => '2019-07-07']), 'error');

        // invalidating a day invalidates its year, which waits for all given up archives of that year
        $this->assertSame(
            'Warning: 3 archives between 2019-01-01 and 2019-12-31 (site IDs: 1, 2) were given up after failing repeatedly. '
            . 'core:archive does not process them, nor the periods built from them, until they are reset. '
            . 'Run ./console core:archive-failures --given-up to list them, and add --reset to retry them.',
            $this->archiveFailures->getGivenUpWarning([1, 2], [Date::factory('2019-12-24')], 'day')
        );
        $this->assertStringStartsWith(
            'Warning: 2 archives between 2019-01-01 and 2019-12-31 (site IDs: 1) ',
            (string) $this->archiveFailures->getGivenUpWarning([1], ['2019-12-24'], 'week')
        );
        $this->assertStringStartsWith(
            'Warning: 1 archives between 2019-03-01 and 2019-03-10 (site IDs: 1) ',
            (string) $this->archiveFailures->getGivenUpWarning([1, 2], ['2019-03-01,2019-03-10'], 'range')
        );
        $this->assertNull($this->archiveFailures->getGivenUpWarning([1, 2], ['2019-04-01,2019-05-10'], 'range'));
        $this->assertNull($this->archiveFailures->getGivenUpWarning([1, 2], [Date::factory('2020-02-01')], 'day'));
        $this->assertNull($this->archiveFailures->getGivenUpWarning([], [Date::factory('2019-02-01')], 'day'));
    }

    public function testFailureCountsUseTheMaximumAttemptsOfEachSite(): void
    {
        Config::getInstance()->General_2 = ['archive_failure_max_attempts' => 1];
        Date::$now = strtotime('2020-02-03 04:00:00');
        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2020-01-01', 'date2' => '2020-01-01']), 'error');
        Date::$now = strtotime('2020-02-03 04:05:06');
        $this->archiveFailures->recordFailure($this->getInvalidation(), 'error');
        $this->archiveFailures->recordFailure($this->getInvalidation(['idsite' => 2]), 'error');

        $this->assertSame(['waiting' => 2, 'givenUp' => 1, 'oldestFailure' => '2020-02-03 04:00:00'], $this->archiveFailures->getFailureCounts());
    }

    public function testStartingAnArchiveRespectsFailuresRecordedMeanwhile(): void
    {
        $ids = $this->insertInvalidations([
            'failed day' => [1, '2020-01-15', '2020-01-15'],
            'week of day' => [2, '2020-01-13', '2020-01-19'],
            'month of day' => [3, '2020-01-01', '2020-01-31'],
            'year of day' => [4, '2020-01-01', '2020-12-31'],
            'range around day' => [5, '2020-01-10', '2020-01-20'],
            'other day' => [1, '2020-01-16', '2020-01-16'],
            'range not around day' => [5, '2020-01-16', '2020-01-20'],
            'failed week straddling months' => [2, '2020-03-30', '2020-04-05'],
            'month not built from straddling week' => [3, '2020-04-01', '2020-04-30'],
            'failed week within month' => [2, '2020-06-08', '2020-06-14'],
            'month of week within month' => [3, '2020-06-01', '2020-06-30'],
            'failed day retry pending' => [1, '2021-02-02', '2021-02-02'],
            'week of day retry pending' => [2, '2021-02-01', '2021-02-07'],
        ]);
        $ids += $this->insertInvalidations(['segment archive of failed day' => [1, '2020-01-15', '2020-01-15']], 'done' . md5('segment'));

        // eg recorded by another archiver after this one loaded the invalidations to skip
        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2020-01-15', 'date2' => '2020-01-15']), 'error');
        $this->archiveFailures->recordFailure($this->getInvalidation(['period' => 2, 'date1' => '2020-03-30', 'date2' => '2020-04-05']), 'error');
        $this->archiveFailures->recordFailure($this->getInvalidation(['period' => 2, 'date1' => '2020-06-08', 'date2' => '2020-06-14']), 'error');
        Date::$now = strtotime('2020-01-01 00:00:00');
        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2021-02-02', 'date2' => '2021-02-02']), 'error');
        Date::$now = strtotime('2020-02-03 04:05:06');

        $model = new Model();
        $started = [];
        foreach ($ids as $label => $idInvalidation) {
            $invalidation = Db::fetchRow('SELECT * FROM ' . Common::prefixTable('archive_invalidations') . ' WHERE idinvalidation = ?', [$idInvalidation]);
            if ($model->startArchive($invalidation)) {
                $started[] = $label;
            }
        }

        $this->assertSame([
            'other day',
            'range not around day',
            'month not built from straddling week',
            'failed day retry pending',
            'week of day retry pending',
            'segment archive of failed day',
        ], $started);
    }

    /**
     * The SQL rule (holding a queued invalidation) and the PHP rule (releasing invalidations on hold) must agree.
     */
    public function testHoldAndReleaseAgreeOnWhatAFailureBlocks(): void
    {
        $this->recordMatrixFailures();

        $queued = $this->insertMatrixInvalidations(ArchiveInvalidator::INVALIDATION_STATUS_QUEUED);
        $heldBySql = [];
        foreach ($queued as $label => $idInvalidation) {
            if ('held' === $this->archiveFailures->holdIfBlocked($this->getInvalidationRow($idInvalidation))) {
                $heldBySql[] = $label;
            }
        }
        Db::query('DELETE FROM ' . Common::prefixTable('archive_invalidations'));

        $onHold = $this->insertMatrixInvalidations(ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD);
        $this->archiveFailures->releaseUnblockedInvalidations(1);
        $stillHeldByPhp = [];
        foreach ($onHold as $label => $idInvalidation) {
            $status = (int) Db::fetchOne('SELECT status FROM ' . Common::prefixTable('archive_invalidations') . ' WHERE idinvalidation = ?', [$idInvalidation]);
            if ($status === ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD) {
                $stillHeldByPhp[] = $label;
            }
        }

        $expected = [
            'failed day', 'week of day', 'month of day', 'year of day', 'range around day',
            'failed week straddling months',
            'failed week within month', 'month of week within month',
            'failed week straddling years',
            'plugin report day', 'plugin report month',
            'segment day', 'segment week',
        ];
        $this->assertSame($expected, $heldBySql);
        $this->assertSame($expected, $stillHeldByPhp);
    }

    public function testReleaseOnlyReleasesWhatNoOtherFailureBlocks(): void
    {
        $firstDay = $this->getInvalidation(['date1' => '2020-01-15', 'date2' => '2020-01-15']);
        $secondDay = $this->getInvalidation(['date1' => '2020-01-20', 'date2' => '2020-01-20']);
        $this->archiveFailures->recordFailure($firstDay, 'error');
        $this->archiveFailures->recordFailure($secondDay, 'error');
        $ids = $this->insertInvalidations([
            'month' => [3, '2020-01-01', '2020-01-31'],
            'week of first day' => [2, '2020-01-13', '2020-01-19'],
            'week of second day' => [2, '2020-01-20', '2020-01-26'],
        ]);
        Db::query('UPDATE ' . Common::prefixTable('archive_invalidations') . ' SET status = ?', [ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD]);

        // the first day was archived: its week can go, the month still waits for the second day
        $this->assertSame(1, $this->archiveFailures->deleteFailure($firstDay));
        $this->assertSame(1, $this->archiveFailures->releaseUnblockedInvalidations(1, $firstDay));
        $this->assertSame([
            'month' => ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD,
            'week of first day' => ArchiveInvalidator::INVALIDATION_STATUS_QUEUED,
            'week of second day' => ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD,
        ], $this->getStatuses($ids));

        $this->assertSame(1, $this->archiveFailures->deleteFailure($secondDay));
        $this->assertSame(2, $this->archiveFailures->releaseUnblockedInvalidations(1, $secondDay));
        $this->assertSame(array_fill_keys(array_keys($ids), ArchiveInvalidator::INVALIDATION_STATUS_QUEUED), $this->getStatuses($ids));

        // nothing to delete, nothing to release
        $this->assertSame(0, $this->archiveFailures->deleteFailure($secondDay));
    }

    public function testInvalidationsOnHoldWithoutFailureAreReleased(): void
    {
        $ids = $this->insertInvalidations(['day' => [1, '2020-01-15', '2020-01-15']]);
        $ids += $this->insertInvalidations(['other site' => [1, '2020-01-15', '2020-01-15']], 'done', 2);
        Db::query('UPDATE ' . Common::prefixTable('archive_invalidations') . ' SET status = ?', [ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD]);

        $this->assertSame(1, $this->archiveFailures->releaseUnblockedInvalidations(1));
        $this->assertSame(['day' => ArchiveInvalidator::INVALIDATION_STATUS_QUEUED, 'other site' => ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD], $this->getStatuses($ids));
    }

    /**
     * @dataProvider getOrdersOfHolding
     * @param list<string> $order
     * @param array<string, string> $expectedResults
     */
    public function testHoldIfBlockedKeepsOnlyTheNewestInvalidationOfAnArchive(array $order, array $expectedResults): void
    {
        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2020-01-15', 'date2' => '2020-01-15']), 'error');
        $ids = $this->insertInvalidations([
            'older' => [1, '2020-01-15', '2020-01-15'],
            'newer' => [1, '2020-01-15', '2020-01-15'],
        ]);

        $results = [];
        foreach ($order as $label) {
            $results[$label] = $this->archiveFailures->holdIfBlocked($this->getInvalidationRow($ids[$label]));
        }

        $this->assertSame($expectedResults, $results);
        $this->assertSame(['newer' => ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD], $this->getStatuses($ids));
    }

    /**
     * @return iterable<string, array{list<string>, array<string, string>}>
     */
    public function getOrdersOfHolding(): iterable
    {
        yield 'newer first, like the queue fetches them' => [['newer', 'older'], ['newer' => 'held', 'older' => 'deleted']];
        yield 'older first, eg held in an earlier run' => [['older', 'newer'], ['older' => 'held', 'newer' => 'held']];
    }

    public function testCopiesHeldAtTheSameTimeByTwoArchiversEndUpAsOne(): void
    {
        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2020-01-15', 'date2' => '2020-01-15']), 'error');
        $ids = $this->insertInvalidations([
            'older' => [1, '2020-01-15', '2020-01-15'],
            'newer' => [1, '2020-01-15', '2020-01-15'],
        ]);
        // both archivers put their copy on hold before either looks for the other one
        Db::query('UPDATE ' . Common::prefixTable('archive_invalidations') . ' SET status = ?', [ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD]);
        Db::query('UPDATE ' . Common::prefixTable('archive_invalidations') . ' SET status = ? WHERE idinvalidation = ?', [ArchiveInvalidator::INVALIDATION_STATUS_QUEUED, $ids['older']]);
        $this->archiveFailures->holdIfBlocked($this->getInvalidationRow($ids['older']));

        $this->assertSame(['newer' => ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD], $this->getStatuses($ids));
    }

    public function testDuplicateOfAnInvalidationOnHoldIsNotDeletedIfNotBlocked(): void
    {
        $ids = $this->insertInvalidations([
            'held without failure' => [1, '2020-01-15', '2020-01-15'],
            'queued' => [1, '2020-01-15', '2020-01-15'],
        ]);
        Db::query('UPDATE ' . Common::prefixTable('archive_invalidations') . ' SET status = ? WHERE idinvalidation = ?', [ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD, $ids['held without failure']]);

        $this->assertNull($this->archiveFailures->holdIfBlocked($this->getInvalidationRow($ids['queued'])));
        $this->assertSame(['held without failure' => ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD, 'queued' => ArchiveInvalidator::INVALIDATION_STATUS_QUEUED], $this->getStatuses($ids));
    }

    public function testFailedInvalidationIsPutOnHoldWithoutItsStartedState(): void
    {
        $ids = $this->insertInvalidations(['failed' => [1, '2020-01-15', '2020-01-15']]);
        Db::query(
            'UPDATE ' . Common::prefixTable('archive_invalidations') . " SET status = 1, ts_started = '2020-02-03 04:00:00', processing_host = 'host', process_id = '123'"
        );

        $inProgress = $this->getInvalidationRow($ids['failed']);
        $this->assertTrue((new Model())->holdFailedInvalidation($inProgress));

        $row = $this->getInvalidationRow($ids['failed']);
        $this->assertSame([(string) ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD, null, null, null], [(string) $row['status'], $row['ts_started'], $row['processing_host'], $row['process_id']]);

        // eg another archiver that found the same timed out job
        $this->assertFalse((new Model())->holdFailedInvalidation($inProgress));
    }

    /**
     * @dataProvider getStatusesOfTwins
     */
    public function testFailedInvalidationIsDeletedIfItsArchiveIsAlreadyWaiting(int $twinStatus): void
    {
        $ids = $this->insertInvalidations(['failed' => [1, '2020-01-15', '2020-01-15'], 'twin' => [1, '2020-01-15', '2020-01-15']]);
        Db::query('UPDATE ' . Common::prefixTable('archive_invalidations') . ' SET status = 1, ts_started = NOW() WHERE idinvalidation = ?', [$ids['failed']]);
        Db::query('UPDATE ' . Common::prefixTable('archive_invalidations') . ' SET status = ? WHERE idinvalidation = ?', [$twinStatus, $ids['twin']]);

        (new Model())->holdFailedInvalidation($this->getInvalidationRow($ids['failed']));

        $this->assertSame(['twin' => $twinStatus], $this->getStatuses($ids));
    }

    /**
     * @dataProvider getStartTimesOfTwinsInProgress
     * @param array<string, int> $expectedStatuses
     */
    public function testFailedInvalidationIsOnlyDeletedIfACopyStartedAfterItWasInvalidated(string $twinStarted, array $expectedStatuses): void
    {
        $ids = $this->insertInvalidations(['failed' => [1, '2020-01-15', '2020-01-15'], 'twin' => [1, '2020-01-15', '2020-01-15']]);
        $table = Common::prefixTable('archive_invalidations');
        Db::query("UPDATE `$table` SET status = 1, ts_invalidated = '2020-02-03 03:00:00', ts_started = '2020-02-03 03:30:00' WHERE idinvalidation = ?", [$ids['failed']]);
        Db::query("UPDATE `$table` SET status = 1, ts_started = ? WHERE idinvalidation = ?", [$twinStarted, $ids['twin']]);

        (new Model())->holdFailedInvalidation($this->getInvalidationRow($ids['failed']));

        $this->assertSame($expectedStatuses, $this->getStatuses($ids));
    }

    /**
     * @return iterable<string, array{string, array<string, int>}>
     */
    public function getStartTimesOfTwinsInProgress(): iterable
    {
        yield 'started after, it covers the failed one' => ['2020-02-03 04:00:00', ['twin' => ArchiveInvalidator::INVALIDATION_STATUS_IN_PROGRESS]];
        yield 'started before' => ['2020-02-03 02:00:00', [
            'failed' => ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD,
            'twin' => ArchiveInvalidator::INVALIDATION_STATUS_IN_PROGRESS,
        ]];
    }

    /**
     * @return iterable<string, array{int}>
     */
    public function getStatusesOfTwins(): iterable
    {
        yield 'queued' => [ArchiveInvalidator::INVALIDATION_STATUS_QUEUED];
        yield 'on hold' => [ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD];
    }

    public function testReleasingAJobKeepsTheInvalidationOnHoldOfItsArchive(): void
    {
        $ids = $this->insertInvalidations(['in progress' => [1, '2020-01-15', '2020-01-15'], 'on hold' => [1, '2020-01-15', '2020-01-15']]);
        Db::query('UPDATE ' . Common::prefixTable('archive_invalidations') . ' SET status = 1, ts_started = NOW() WHERE idinvalidation = ?', [$ids['in progress']]);
        Db::query('UPDATE ' . Common::prefixTable('archive_invalidations') . ' SET status = 2 WHERE idinvalidation = ?', [$ids['on hold']]);

        (new Model())->releaseInProgressInvalidations([$ids['in progress']]);

        $this->assertSame(['on hold' => ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD], $this->getStatuses($ids));
    }

    public function testJobResetAfterACrashIsPutOnHoldWhenItsFailureWasRecorded(): void
    {
        $ids = $this->insertInvalidations(['crashed' => [1, '2020-01-15', '2020-01-15']]);
        Db::query('UPDATE ' . Common::prefixTable('archive_invalidations') . " SET status = 1, ts_started = '2020-01-01 00:00:00'");
        // the archiver recorded the failure, then died before putting the invalidation on hold
        $this->archiveFailures->recordFailure($this->getInvalidationRow($ids['crashed']), 'error');

        (new Model())->resetFailedArchivingJobs();
        $this->assertSame(['crashed' => ArchiveInvalidator::INVALIDATION_STATUS_QUEUED], $this->getStatuses($ids));

        $this->assertSame('held', $this->archiveFailures->holdIfBlocked($this->getInvalidationRow($ids['crashed'])));
    }

    private function recordMatrixFailures(): void
    {
        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2020-01-15', 'date2' => '2020-01-15']), 'error');
        $this->archiveFailures->recordFailure($this->getInvalidation(['period' => 2, 'date1' => '2020-03-30', 'date2' => '2020-04-05']), 'error');
        $this->archiveFailures->recordFailure($this->getInvalidation(['period' => 2, 'date1' => '2020-06-08', 'date2' => '2020-06-14']), 'error');
        $this->archiveFailures->recordFailure($this->getInvalidation(['period' => 2, 'date1' => '2020-12-28', 'date2' => '2021-01-03']), 'error');
        $this->archiveFailures->recordFailure($this->getInvalidation(['name' => 'done.Goals', 'report' => 'getMetrics', 'date1' => '2020-02-10', 'date2' => '2020-02-10']), 'error');
        $this->archiveFailures->recordFailure($this->getInvalidation(['name' => 'done' . md5('segment'), 'date1' => '2020-02-11', 'date2' => '2020-02-11']), 'error');
        Date::$now = strtotime('2020-01-01 00:00:00');
        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2021-02-02', 'date2' => '2021-02-02']), 'error');
        Date::$now = strtotime('2020-02-03 04:05:06');
    }

    /**
     * @return array<string, int> label => idinvalidation
     */
    private function insertMatrixInvalidations(int $status): array
    {
        $segment = 'done' . md5('segment');
        $matrix = [
            'failed day' => [1, '2020-01-15', '2020-01-15'],
            'week of day' => [2, '2020-01-13', '2020-01-19'],
            'month of day' => [3, '2020-01-01', '2020-01-31'],
            'year of day' => [4, '2020-01-01', '2020-12-31'],
            'range around day' => [5, '2020-01-10', '2020-01-20'],
            'other day' => [1, '2020-01-16', '2020-01-16'],
            'range not around day' => [5, '2020-01-16', '2020-01-20'],
            'failed week straddling months' => [2, '2020-03-30', '2020-04-05'],
            'month before straddling week' => [3, '2020-03-01', '2020-03-31'],
            'month not built from straddling week' => [3, '2020-04-01', '2020-04-30'],
            'failed week within month' => [2, '2020-06-08', '2020-06-14'],
            'month of week within month' => [3, '2020-06-01', '2020-06-30'],
            'failed week straddling years' => [2, '2020-12-28', '2021-01-03'],
            'year after straddling week' => [4, '2021-01-01', '2021-12-31'],
            'failed day retry pending' => [1, '2021-02-02', '2021-02-02'],
            'week of day retry pending' => [2, '2021-02-01', '2021-02-07'],
            'plugin report day' => [1, '2020-02-10', '2020-02-10', 'done.Goals', 'getMetrics'],
            'plugin report month' => [3, '2020-02-01', '2020-02-29', 'done.Goals', 'getMetrics'],
            'plugin other report day' => [1, '2020-02-10', '2020-02-10', 'done.Goals', 'getOther'],
            'plugin day without report' => [1, '2020-02-10', '2020-02-10', 'done.Goals', null],
            'all plugins day of plugin failure' => [1, '2020-02-10', '2020-02-10'],
            'segment day' => [1, '2020-02-11', '2020-02-11', $segment],
            'segment week' => [2, '2020-02-10', '2020-02-16', $segment],
            'all visits day of segment failure' => [1, '2020-02-11', '2020-02-11'],
            'all visits month without failure' => [3, '2020-02-01', '2020-02-29'],
        ];

        $ids = [];
        foreach ($matrix as $label => $row) {
            Db::query(
                'INSERT INTO ' . Common::prefixTable('archive_invalidations') . ' (name, idsite, date1, date2, period, report, ts_invalidated, status) VALUES (?, 1, ?, ?, ?, ?, ?, ?)',
                [$row[3] ?? 'done', $row[1], $row[2], $row[0], $row[4] ?? null, Date::now()->getDatetime(), $status]
            );
            $ids[$label] = (int) Db::fetchOne('SELECT LAST_INSERT_ID()');
        }

        return $ids;
    }

    /**
     * @return array<array-key, string|int|float|null>
     */
    private function getInvalidationRow(int $idInvalidation): array
    {
        $row = Db::fetchRow('SELECT * FROM ' . Common::prefixTable('archive_invalidations') . ' WHERE idinvalidation = ?', [$idInvalidation]);
        $this->assertIsArray($row);

        return $row;
    }

    /**
     * @param array<string, int> $ids
     * @return array<string, int> label => status of the invalidations that still exist
     */
    private function getStatuses(array $ids): array
    {
        $statuses = [];
        foreach ($ids as $label => $idInvalidation) {
            $status = Db::fetchOne('SELECT status FROM ' . Common::prefixTable('archive_invalidations') . ' WHERE idinvalidation = ?', [$idInvalidation]);
            if (false !== $status) {
                $statuses[$label] = (int) $status;
            }
        }

        return $statuses;
    }

    public function testIterateFailuresLoadsAllFailuresInChunks(): void
    {
        Config::setSetting('General', 'archive_failure_max_attempts', 2);
        for ($day = 1; $day <= 5; $day++) {
            $invalidation = $this->getInvalidation(['date1' => "2020-01-0$day", 'date2' => "2020-01-0$day"]);
            $this->archiveFailures->recordFailure($invalidation, 'error');
            if ($day % 2 === 0) {
                $this->archiveFailures->recordFailure($invalidation, 'error');
            }
        }

        $dates = [];
        foreach ($this->archiveFailures->iterateFailures([], false, false, 2) as $failure) {
            $dates[] = $failure['date1'];
            $this->assertArrayNotHasKey('last_error', $failure);
        }
        $this->assertSame(['2020-01-01', '2020-01-02', '2020-01-03', '2020-01-04', '2020-01-05'], $dates);

        $givenUp = array_column(iterator_to_array($this->archiveFailures->iterateFailures([1], true, true, 2), false), 'date1');
        $this->assertSame(['2020-01-02', '2020-01-04'], $givenUp);
    }

    /**
     * @param array<string, array{int, string, string}> $invalidations label => [period, date1, date2]
     * @return array<string, int> label => idinvalidation
     */
    private function insertInvalidations(array $invalidations, string $name = 'done', int $idSite = 1): array
    {
        $ids = [];
        foreach ($invalidations as $label => [$period, $date1, $date2]) {
            Db::query(
                'INSERT INTO ' . Common::prefixTable('archive_invalidations') . ' (name, idsite, date1, date2, period, ts_invalidated, status) VALUES (?, ?, ?, ?, ?, ?, 0)',
                [$name, $idSite, $date1, $date2, $period, Date::now()->getDatetime()]
            );
            $ids[$label] = (int) Db::fetchOne('SELECT LAST_INSERT_ID()');
        }

        return $ids;
    }

    /**
     * @param array<string, string|int|null> $overrides
     * @return array<string, string|int|null>
     */
    private function getInvalidation(array $overrides = []): array
    {
        return array_merge([
            'idinvalidation' => 1,
            'idsite' => 1,
            'name' => 'done',
            'period' => 1,
            'date1' => '2020-02-03',
            'date2' => '2020-02-03',
            'report' => null,
        ], $overrides);
    }
}
