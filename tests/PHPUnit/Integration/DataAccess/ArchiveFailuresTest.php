<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\ArchiveFailures;
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

    public function testRecordFailureBacksOffAndGivesUp()
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

    public function testRetryDelaysAndMaxAttemptsCanBeConfiguredPerSite()
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
    public function testInvalidRetryDelaysFallBackToDefaults(string $delays)
    {
        Config::getInstance()->General['archive_failure_retry_delays'] = $delays;

        $this->assertSame(3600, ArchiveFailures::getRetryDelay(1, 1));
        $this->assertSame(21600, ArchiveFailures::getRetryDelay(1, 2));
    }

    public function getInvalidRetryDelays(): iterable
    {
        yield 'units' => ['1h,6h'];
        yield 'negative' => ['60,-5'];
        yield 'zero' => ['0,60'];
        yield 'text' => ['abc'];
        yield 'empty' => [''];
    }

    public function testRetryDelaysAreCappedAndMaxAttemptsClamped()
    {
        Config::getInstance()->General['archive_failure_retry_delays'] = ' 60 , 99999999999999 ';
        Config::getInstance()->General['archive_failure_max_attempts'] = 0;

        $this->assertSame(60, ArchiveFailures::getRetryDelay(1, 1));
        $this->assertSame(31536000, ArchiveFailures::getRetryDelay(1, 2));
        $this->assertSame(1, ArchiveFailures::getMaxAttempts(1));
    }

    public function testRaisingTheMaximumAttemptsResumesRetrying()
    {
        Config::getInstance()->General['archive_failure_max_attempts'] = 1;
        $this->archiveFailures->recordFailure($this->getInvalidation(), 'error');
        $this->assertSame(ArchiveFailures::STATE_GIVEN_UP, $this->archiveFailures->getFailures()[0]['state']);

        Config::getInstance()->General['archive_failure_max_attempts'] = 2;
        $this->assertSame(ArchiveFailures::STATE_WAITING, $this->archiveFailures->getFailures()[0]['state']);

        Date::$now = strtotime('2020-02-03 05:05:07');
        $this->assertSame(ArchiveFailures::STATE_RETRY_PENDING, $this->archiveFailures->getFailures()[0]['state']);
    }

    public function testRecordFailureRemovesStackTraceAndLimitsTheErrorLength()
    {
        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2020-01-01', 'date2' => '2020-01-01']), "SQLSTATE[HY000]: General error #0 /core/Db.php(10): query() #1 {main}");
        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2020-01-02', 'date2' => '2020-01-02']), str_repeat('ä', 1500));
        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2020-01-03', 'date2' => '2020-01-03']), "Uncaught exception: out of memory\n#0 /core/Db.php(10): query()\n#1 {main}");

        $errors = array_column($this->archiveFailures->getFailures(), 'last_error');
        $this->assertSame('SQLSTATE[HY000]: General error', $errors[0]);
        $this->assertSame(str_repeat('ä', 1000), $errors[1]);
        $this->assertSame('Uncaught exception: out of memory', $errors[2]);
    }

    public function testFailuresAreKeptPerArchive()
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

    public function testDeleteFailuresWithoutInvalidations()
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

    public function testFailuresOfDeletedSitesAreIgnoredAndDeleted()
    {
        $this->archiveFailures->recordFailure($this->getInvalidation(), 'error');
        $this->archiveFailures->recordFailure($this->getInvalidation(['idsite' => 99]), 'error');

        $this->assertSame(['1'], array_map('strval', array_column($this->archiveFailures->getFailures(), 'idsite')));
        $this->assertSame(1, $this->archiveFailures->getFailureCounts()['waiting']);

        $this->archiveFailures->deleteFailuresForDeletedSites();

        $this->assertSame('0', (string) Db::fetchOne('SELECT COUNT(*) FROM ' . ArchiveFailures::getTable() . ' WHERE idsite = 99'));
        $this->assertCount(1, $this->archiveFailures->getFailures());
    }

    public function testFailureCountsUseTheMaximumAttemptsOfEachSite()
    {
        Config::getInstance()->General_2 = ['archive_failure_max_attempts' => 1];
        Date::$now = strtotime('2020-02-03 04:00:00');
        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2020-01-01', 'date2' => '2020-01-01']), 'error');
        Date::$now = strtotime('2020-02-03 04:05:06');
        $this->archiveFailures->recordFailure($this->getInvalidation(), 'error');
        $this->archiveFailures->recordFailure($this->getInvalidation(['idsite' => 2]), 'error');

        $this->assertSame(['waiting' => 2, 'givenUp' => 1, 'oldestFailure' => '2020-02-03 04:00:00'], $this->archiveFailures->getFailureCounts());
    }

    public function testGetBlockedInvalidationIds()
    {
        $ids = $this->insertInvalidations([
            'failed day' => [1, '2020-01-15', '2020-01-15'],
            'week of day' => [2, '2020-01-13', '2020-01-19'],
            'month of day' => [3, '2020-01-01', '2020-01-31'],
            'year of day' => [4, '2020-01-01', '2020-12-31'],
            'range around day' => [5, '2020-01-10', '2020-01-20'],
            'range not around day' => [5, '2020-01-16', '2020-01-20'],
            'other day' => [1, '2020-01-16', '2020-01-16'],
            'failed week straddling months' => [2, '2020-03-30', '2020-04-05'],
            'month not built from straddling week' => [3, '2020-04-01', '2020-04-30'],
            'failed week within month' => [2, '2020-06-08', '2020-06-14'],
            'month of week within month' => [3, '2020-06-01', '2020-06-30'],
            'failed day retry pending' => [1, '2021-02-02', '2021-02-02'],
            'week of day retry pending' => [2, '2021-02-01', '2021-02-07'],
        ]);
        $this->insertInvalidations(['segment archive of failed day' => [1, '2020-01-15', '2020-01-15']], 'done' . md5('segment'));

        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2020-01-15', 'date2' => '2020-01-15']), 'error');
        $this->archiveFailures->recordFailure($this->getInvalidation(['period' => 2, 'date1' => '2020-03-30', 'date2' => '2020-04-05']), 'error');
        Date::$now = strtotime('2020-02-03 04:10:00');
        $this->archiveFailures->recordFailure($this->getInvalidation(['period' => 2, 'date1' => '2020-06-08', 'date2' => '2020-06-14']), 'error');
        Date::$now = strtotime('2020-01-01 00:00:00');
        $this->archiveFailures->recordFailure($this->getInvalidation(['date1' => '2021-02-02', 'date2' => '2021-02-02']), 'error');
        Date::$now = strtotime('2020-02-03 04:05:06');

        $blocked = $this->archiveFailures->getBlockedInvalidationIds(1);

        $expected = array_map(function ($name) use ($ids) {
            return $ids[$name];
        }, [
            'failed day', 'week of day', 'month of day', 'year of day', 'range around day',
            'failed week straddling months', 'failed week within month', 'month of week within month',
        ]);
        $this->assertEqualsCanonicalizing($expected, $blocked['ids']);
        $this->assertSame('2020-02-03 05:05:06', $blocked['validUntil']);
        $this->assertSame(['ids' => [], 'validUntil' => null], $this->archiveFailures->getBlockedInvalidationIds(2));
    }

    public function testIterateFailuresLoadsAllFailuresInChunks()
    {
        Config::getInstance()->General['archive_failure_max_attempts'] = 2;
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

    public function testFailureStateChangesWithFailuresAndNewInvalidations()
    {
        $this->assertNull($this->archiveFailures->getFailureState(1));

        $this->archiveFailures->recordFailure($this->getInvalidation(), 'error');
        $state = $this->archiveFailures->getFailureState(1);
        $this->assertNotNull($state);

        $this->archiveFailures->recordFailure($this->getInvalidation(), 'error');
        $this->assertNotSame($state['failures'], $this->archiveFailures->getFailureState(1)['failures']);

        $ids = $this->insertInvalidations(['new invalidation' => [1, '2020-02-04', '2020-02-04']]);
        $this->assertSame($ids['new invalidation'], $this->archiveFailures->getFailureState(1)['maxIdInvalidation']);
        $this->assertNull($this->archiveFailures->getFailureState(2));
    }

    /**
     * @return array<string, int> label => idinvalidation
     */
    private function insertInvalidations(array $invalidations, string $name = 'done'): array
    {
        $ids = [];
        foreach ($invalidations as $label => [$period, $date1, $date2]) {
            Db::query(
                'INSERT INTO ' . Common::prefixTable('archive_invalidations') . ' (name, idsite, date1, date2, period, ts_invalidated, status) VALUES (?, 1, ?, ?, ?, ?, 0)',
                [$name, $date1, $date2, $period, Date::now()->getDatetime()]
            );
            $ids[$label] = (int) Db::get()->lastInsertId();
        }

        return $ids;
    }

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
