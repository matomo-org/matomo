<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\RawLogDao;
use Piwik\Db;
use Piwik\LogDeleter;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\Plugin\LogTablesProvider;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Framework\TestDataHelper\LogHelper;

class ChunkedPurgeTestRawLogDao extends RawLogDao
{
    /**
     * @var callable|null runs right after the purge's first, unlocked scan
     */
    public $afterFirstScan;

    public function __construct(int $chunkSize)
    {
        parent::__construct();
        $this->sqliteDeleteChunkSize = $chunkSize;
    }

    protected function insertActionsToKeep($maxIds, $olderThan = true, $insertIntoTempIterationStep = 100000)
    {
        parent::insertActionsToKeep($maxIds, $olderThan, $insertIntoTempIterationStep);

        if ($olderThan && $this->afterFirstScan) {
            ($this->afterFirstScan)();
        }
    }
}

/**
 * @group Core
 */
class LogDeleterTest extends IntegrationTestCase
{
    /**
     * @var LogHelper
     */
    private $logInserter;

    /**
     * @var LogDeleter
     */
    private $logDeleter;

    public function setUp(): void
    {
        parent::setUp();

        $this->logDeleter = new LogDeleter(new RawLogDao(), new LogTablesProvider());

        $this->logInserter = new LogHelper();
        $this->insertTestData();
    }

    public function testAFailedActionPurgeLeavesNothingBehindForTheNextOne(): void
    {
        if (!Fixture::isSqlite()) {
            $this->markTestSkipped('A SQLite trigger fails the purge');
        }

        $table = Common::prefixTable('log_action');
        Db::query("INSERT INTO `$table` (idaction, name, type) VALUES (99999, 'unused', 1)");
        Db::exec("CREATE TRIGGER matomo_test_failed_purge BEFORE DELETE ON `$table` BEGIN SELECT RAISE(ABORT, 'purge failed'); END");

        $dao = new RawLogDao();
        try {
            $dao->deleteUnusedLogActions();
            $this->fail('the trigger should fail the purge');
        } catch (\Exception $e) {
            $this->assertStringContainsString('purge failed', $e->getMessage());
        } finally {
            Db::exec('DROP TRIGGER matomo_test_failed_purge');
        }

        // no write lock nor temporary table is left: the next purge runs
        $dao->deleteUnusedLogActions();
        $this->assertSame(0, (int) Db::fetchOne("SELECT COUNT(*) FROM `$table` WHERE name = 'unused'"));
    }

    public function testAPurgeFailingHalfwayKeepsEveryAction(): void
    {
        if (!Fixture::isSqlite()) {
            $this->markTestSkipped('A SQLite trigger fails the purge');
        }

        $table = Common::prefixTable('log_action');
        Db::query("INSERT INTO `$table` (idaction, name, type) VALUES (99998, 'unused', 1), (99999, 'unused', 1)");
        // RAISE(FAIL) keeps what the statement already deleted: only the rollback undoes it
        Db::exec("CREATE TRIGGER matomo_test_failed_purge BEFORE DELETE ON `$table` WHEN OLD.idaction = 99999 BEGIN SELECT RAISE(FAIL, 'purge failed'); END");

        try {
            (new RawLogDao())->deleteUnusedLogActions();
            $this->fail('the trigger should fail the purge');
        } catch (\Exception $e) {
            $this->assertStringContainsString('purge failed', $e->getMessage());
        } finally {
            Db::exec('DROP TRIGGER matomo_test_failed_purge');
        }

        $this->assertSame(2, (int) Db::fetchOne("SELECT COUNT(*) FROM `$table` WHERE name = 'unused'"));
    }

    public function testAPurgeFailingInsideTheCallersLockLeavesThatLockAndItsWork(): void
    {
        if (!Fixture::isSqlite()) {
            $this->markTestSkipped('SQLite refuses the purge its own lock inside the caller\'s');
        }

        $table = Common::prefixTable('log_action');
        Db::lockTables([], [$table]);
        Db::query("INSERT INTO `$table` (idaction, name, type) VALUES (99998, 'pending', 1)");
        try {
            (new RawLogDao())->deleteUnusedLogActions();
            $this->fail('the purge should not lock inside the caller\'s lock');
        } catch (\Exception $e) {
            $this->assertStringContainsString('within a transaction', $e->getMessage());
        }

        // still the caller's: rolled back with it, not committed by the purge
        Db::abortTableLocks();
        $this->assertSame(0, (int) Db::fetchOne("SELECT COUNT(*) FROM `$table` WHERE name = 'pending'"));
    }

    public function testSqlitePurgeDeletesABacklogLargerThanItsChunksAndBudget(): void
    {
        $this->setUpChunkedPurge();
        $this->insertUnusedActions(100001, 100250);
        $this->insertActionUsedByAVisitAction(100500);

        $dao = new ChunkedPurgeTestRawLogDao(7);
        $this->assertTrue($dao->deleteUnusedLogActions());

        $this->assertSame([], $this->getActionIdsBetween(100001, 100250));
        $this->assertSame([100500], $this->getActionIdsBetween(100500, 100500));
    }

    public function testSqlitePurgeKeepsActionsReferencedDuringTheRun(): void
    {
        $this->setUpChunkedPurge();
        $this->insertUnusedActions(100001, 100020);

        $dao = new ChunkedPurgeTestRawLogDao(3);
        $dao->afterFirstScan = function () {
            // more visit actions than the budget, so the purge catches up before its first delete
            for ($i = 0; $i < 30; $i++) {
                $this->logInserter->insertVisitAction(1, ['idsite' => 1, 'idaction_url' => $i === 29 ? 100003 : 0]);
            }
            // a cart item added to the newest visit, which is rescanned rather than caught up
            $maxIdVisit = (int) Db::fetchOne('SELECT MAX(idvisit) FROM ' . Common::prefixTable('log_visit'));
            $this->logInserter->insertConversionItem($maxIdVisit, 'idorder_late', ['idsite' => 1, 'idaction_sku' => 100004, 'idaction_name' => 100004]);
        };

        $this->assertTrue($dao->deleteUnusedLogActions());

        $this->assertSame([100003, 100004], $this->getActionIdsBetween(100001, 100020));
    }

    public function testSqlitePurgeStopsWithoutDeletingWhenVisitsOutgrowItsBudget(): void
    {
        $this->setUpChunkedPurge();
        $this->insertUnusedActions(100001, 100020);

        $dao = new ChunkedPurgeTestRawLogDao(3);
        $dao->afterFirstScan = function () {
            $maxIdVisit = (int) Db::fetchOne('SELECT MAX(idvisit) FROM ' . Common::prefixTable('log_visit'));
            Db::query('INSERT INTO ' . Common::prefixTable('log_visit') . ' (idvisit, idsite) VALUES (?, 1)', [$maxIdVisit + 50]);
        };

        $this->assertFalse($dao->deleteUnusedLogActions());

        $this->assertCount(20, $this->getActionIdsBetween(100001, 100020));
        // nothing is left locked: the next run completes
        $this->assertTrue((new ChunkedPurgeTestRawLogDao(3))->deleteUnusedLogActions());
        $this->assertSame([], $this->getActionIdsBetween(100001, 100020));
    }

    private function setUpChunkedPurge(): void
    {
        if (!Fixture::isSqlite()) {
            $this->markTestSkipped('The purge runs in bounded transactions on SQLite');
        }

        $deleteLogs = Config::getInstance()->Deletelogs;
        $deleteLogs['delete_logs_unused_actions_max_rows_per_query'] = 10;
        Config::getInstance()->Deletelogs = $deleteLogs;
    }

    private function insertUnusedActions(int $from, int $to): void
    {
        $table = Common::prefixTable('log_action');
        for ($id = $from; $id <= $to; $id++) {
            Db::query("INSERT INTO `$table` (idaction, name, type) VALUES (?, 'unused', 1)", [$id]);
        }
    }

    private function insertActionUsedByAVisitAction(int $idAction): void
    {
        Db::query('INSERT INTO ' . Common::prefixTable('log_action') . " (idaction, name, type) VALUES (?, 'used', 1)", [$idAction]);
        $this->logInserter->insertVisitAction(1, ['idsite' => 1, 'idaction_url' => $idAction]);
    }

    /**
     * @return int[]
     */
    private function getActionIdsBetween(int $from, int $to): array
    {
        return array_map('intval', Db::get()->fetchCol(
            'SELECT idaction FROM ' . Common::prefixTable('log_action') . ' WHERE idaction BETWEEN ? AND ? ORDER BY idaction',
            [$from, $to]
        ));
    }

    public function testDeleteVisitsRemovesVisitsAndOtherRelatedLogs()
    {
        $this->logDeleter->deleteVisits(array(2, 3));

        $this->assertVisitExists(1);
        $this->assertVisitNotExists(2);
        $this->assertVisitNotExists(3);
        $this->assertVisitExists(4);
    }

    public function testDeleteVisitsForDeletesVisitsForSpecifiedRangeAndSitesAndInvokesCallbackAfterEveryChunkIsDeleted()
    {
        $iterationCount = 0;
        $this->logDeleter->deleteVisitsFor('2012-01-01', '2012-01-02 05:05:05', 2, $iterationStep = 1, function () use (&$iterationCount) {
            ++$iterationCount;
        });

        $this->assertEquals(2, $iterationCount);

        // visits for idSite = 1 do not get deleted
        $this->assertVisitExists(1);
        $this->assertVisitExists(2);

        // visits for idSite = 2 do get deleted
        $this->assertVisitNotExists(3);
        $this->assertVisitNotExists(4);
    }

    private function insertTestData()
    {
        // two visits for site = 1
        $this->insertVisit($idSite = 1, $dateTime = '2012-01-01 00:00:00');
        $this->insertVisit($idSite = 1, $dateTime = '2012-01-02 00:00:00');

        // two visits for site = 2
        $this->insertVisit($idSite = 2, $dateTime = '2012-01-01 00:00:00');
        $this->insertVisit($idSite = 2, $dateTime = '2012-01-02 00:00:00');
    }

    private function insertVisit($idSite, $dateTime)
    {
        $visit = $this->logInserter->insertVisit(array('idsite' => $idSite, 'visit_last_action_time' => $dateTime));

        $orderId = 'idorder_' . $visit['idvisit'];

        // insert two actions
        $this->logInserter->insertVisitAction($visit['idvisit'], array('idsite' => $idSite));
        $this->logInserter->insertVisitAction($visit['idvisit'], array('idsite' => $idSite));

        // insert two conversions
        $this->logInserter->insertConversion($visit['idvisit'], array('idsite' => $idSite, 'buster' => 1));
        $this->logInserter->insertConversion($visit['idvisit'], array('idsite' => $idSite, 'buster' => 2, 'idorder' => $orderId));

        // insert two conversion items for last conversion
        $this->logInserter->insertConversionItem($visit['idvisit'], $orderId, array('idsite' => $idSite));
        $this->logInserter->insertConversionItem($visit['idvisit'], $orderId, array('idsite' => $idSite, 'idaction_sku' => 123));
    }

    private function assertVisitExists($idVisit, $checkAggregates = true)
    {
        $this->assertEquals(1, $this->getRowCountWithIdVisit('log_visit', $idVisit));
        $this->assertEquals(2, $this->getRowCountWithIdVisit('log_link_visit_action', $idVisit));

        if ($checkAggregates) {
            $this->assertConversionsExists($idVisit);
        }
    }

    private function assertConversionsExists($idVisit, $checkAggregates = true)
    {
        $this->assertEquals(2, $this->getRowCountWithIdVisit('log_conversion', $idVisit));

        if ($checkAggregates) {
            $this->assertConversionItemsExist($idVisit);
        }
    }

    private function assertConversionItemsExist($idVisit)
    {
        $this->assertEquals(2, $this->getRowCountWithIdVisit('log_conversion_item', $idVisit));
    }

    private function assertVisitNotExists($idVisit)
    {
        $this->assertEquals(0, $this->getRowCountWithIdVisit('log_visit', $idVisit));
        $this->assertEquals(0, $this->getRowCountWithIdVisit('log_link_visit_action', $idVisit));

        $this->assertConversionsNotExists($idVisit);
    }

    private function getRowCountWithIdVisit($table, $idVisit)
    {
        return Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable($table) . " WHERE idvisit = $idVisit");
    }

    private function assertConversionsNotExists($idVisit)
    {
        $this->assertEquals(0, $this->getRowCountWithIdVisit('log_conversion', $idVisit));

        $this->assertConversionItemsNotExist($idVisit);
    }

    private function assertConversionItemsNotExist($idVisit)
    {
        $this->assertEquals(0, $this->getRowCountWithIdVisit('log_conversion_item', $idVisit));
    }
}
