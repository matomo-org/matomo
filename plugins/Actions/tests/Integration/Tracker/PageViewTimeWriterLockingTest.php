<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\tests\Integration\Tracker;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\Actions\Tracker\PageViewTimeWriter;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker;
use Piwik\Tracker\Action;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * Runs the writer in a tracker transaction while a second connection plays a queued-tracking
 * worker that processes a neighbouring visit, so the writer must not lock that visit's rows.
 *
 * @group Actions
 * @group PageViewTime
 * @group Plugins
 * @group Tracker
 */
class PageViewTimeWriterLockingTest extends IntegrationTestCase
{
    private const SERVER_TIME = '2026-10-19 04:00:00';

    /** @var \mysqli|null */
    private $otherWorker;

    public function setUp(): void
    {
        parent::setUp();

        if (!extension_loaded('mysqli')) {
            $this->markTestSkipped('The second worker connects with mysqli');
        }

        Fixture::createWebsite('2026-01-01 00:00:00');
        Cache::deleteTrackerCache();
    }

    public function tearDown(): void
    {
        if ($this->otherWorker) {
            $this->otherWorker->close();
            $this->otherWorker = null;
        }
        Tracker::disconnectCachedDbConnection();

        parent::tearDown();
    }

    public function getVisitsWithoutRowToClose(): iterable
    {
        yield 'first pageview of a new visit' => [true];
        yield 'visit started before log_page_view_time existed' => [false];
    }

    /**
     * @dataProvider getVisitsWithoutRowToClose
     */
    public function testClosingThePreviousPageViewDoesNotLockThePreviousVisitRows(bool $isNewVisit)
    {
        $this->insertRow(1, 1, 'aaaaaa', 0);

        // The other worker tracks two pageviews for visit 1 in one batch. The second lands in
        // the index gap between visit 1's last row and visit 2.
        $this->startOtherWorker();
        $this->otherWorker->query($this->getInsertSql(1, 2, 'bbbbbb', 10));
        $this->otherWorker->query($this->getInsertSql(1, 3, 'cccccc', 20, $delayed = true), MYSQLI_ASYNC);

        $pageView = $this->createMock(Action::class);
        $pageView->method('getActionType')->willReturn(Action::TYPE_PAGE_URL);
        $pageView->method('getIdLinkVisitAction')->willReturn(10);
        $pageView->method('getIdActionUrl')->willReturn(1);
        $pageView->method('getIdActionName')->willReturn(2);

        $this->assertNoDeadlock(function () use ($pageView, $isNewVisit) {
            (new PageViewTimeWriter())->write($pageView, new VisitProperties(['idvisit' => 2]), $this->makeRequest('dddddd', $isNewVisit));
        });
    }

    public function testUpdatingAPageViewWithoutRowDoesNotLockThePreviousVisitRows()
    {
        $this->insertRow(1, 1, 'aaaaaa', 0);

        $this->startOtherWorker();
        $this->otherWorker->query($this->getInsertSql(1, 2, 'bbbbbb', 10));
        $this->otherWorker->query($this->getInsertSql(1, 3, 'cccccc', 20, $delayed = true), MYSQLI_ASYNC);

        // A heartbeat for a pv_id visit 2 has no row for, e.g. a visit started before the table existed.
        $this->assertNoDeadlock(function () {
            (new PageViewTimeWriter())->write(null, new VisitProperties(['idvisit' => 2]), $this->makeRequest('zzzzzz', false));
        });
    }

    private function assertNoDeadlock(callable $writeInTrackerTransaction): void
    {
        $db = Tracker::getDatabase();
        $db->query('SET SESSION innodb_lock_wait_timeout = 3');
        $transactionId = $db->beginTransaction();

        $writerError = null;
        try {
            $writeInTrackerTransaction();
        } catch (\Exception $e) {
            $writerError = $e->getMessage();
        }

        $otherWorkerError = null;
        try {
            $this->otherWorker->reap_async_query();
        } catch (\mysqli_sql_exception $e) {
            $otherWorkerError = $e->getMessage();
        }

        if ($writerError === null) {
            $db->rollBack($transactionId);
        }
        $this->otherWorker->rollback();

        $this->assertNull($writerError, 'The writer failed');
        $this->assertNull($otherWorkerError, 'The other worker failed');
    }

    private function startOtherWorker(): void
    {
        $config = Db::getDatabaseConfig();

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->otherWorker = new \mysqli($config['host'], $config['username'], $config['password'], $config['dbname'], (int) $config['port']);
        $this->otherWorker->query('SET SESSION innodb_lock_wait_timeout = 3');
        $this->otherWorker->begin_transaction();
    }

    /**
     * A delayed INSERT runs once the writer had a second to take its locks and start waiting.
     */
    private function getInsertSql(int $idVisit, int $idLinkVa, string $pvId, int $secondsAfterBase, bool $delayed = false): string
    {
        $serverTime = date('Y-m-d H:i:s', strtotime(self::SERVER_TIME) + $secondsAfterBase);
        return "INSERT INTO `{$this->table()}` (idsite, idvisit, idlink_va, idpageview, server_time, time_spent)
                SELECT 1, $idVisit, $idLinkVa, '$pvId', '$serverTime', 0 FROM DUAL" . ($delayed ? ' WHERE SLEEP(1) = 0' : '');
    }

    private function insertRow(int $idVisit, int $idLinkVa, string $pvId, int $secondsAfterBase): void
    {
        Db::query($this->getInsertSql($idVisit, $idLinkVa, $pvId, $secondsAfterBase));
    }

    private function makeRequest(string $pvId, bool $isNewVisit): Request
    {
        $request = new Request(['idsite' => 1, 'pv_id' => $pvId]);
        $request->setCurrentTimestamp(strtotime(self::SERVER_TIME) + 30);
        $request->setMetadata('CoreHome', 'isNewVisit', $isNewVisit);
        return $request;
    }

    private function table(): string
    {
        return Common::prefixTable(PageViewTimeWriter::TABLE);
    }
}
