<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\Integration\Dao;

use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\BotTracking\BotDetector;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Action;
use Piwik\Tracker\TableLogAction;

/**
 * @group BotTracking
 * @group BotRequestsDao
 * @group Plugins
 */
class BotRequestsDaoTest extends IntegrationTestCase
{
    /**
     * @var BotRequestsDao
     */
    private $dao;

    /**
     * @var int
     */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2025-01-01 00:00:00');
        $this->dao    = new BotRequestsDao();
    }

    public function testGetTableName(): void
    {
        $tableName = BotRequestsDao::getTableName();
        self::assertEquals('log_bot_request', $tableName);

        $tableName = BotRequestsDao::getPrefixedTableName();
        self::assertStringContainsString('log_bot_request', $tableName);
    }

    public function testCreateTableAddsRealTimeLookupIndex(): void
    {
        $this->dao->dropTable();
        $this->dao->createTable();

        self::assertSame(
            ['idsite', 'bot_type', 'server_time'],
            $this->getIndexColumns(BotRequestsDao::INDEX_IDSITE_BOT_TYPE_SERVER_TIME)
        );
    }

    public function testInsertCreatesRecord(): void
    {
        $data = [
            'idsite'              => $this->idSite,
            'server_time'         => '2025-10-28 12:00:00',
            'idaction_url'        => 123,
            'bot_name'            => 'ChatGPT-User',
            'bot_type'            => BotDetector::BOT_TYPE_AI_CHATBOT,
            'http_status_code'    => 200,
            'response_size_bytes' => 2048,
            'response_time_ms'    => 125,
            'source'              => 'cloudflare',
        ];

        $idRequest = $this->dao->insert($data);

        self::assertGreaterThan(0, $idRequest);

        $tableName = BotRequestsDao::getPrefixedTableName();
        $sql       = "SELECT * FROM `{$tableName}` WHERE idrequest = ?";
        $record    = Db::fetchRow($sql, [$idRequest]);

        self::assertNotEmpty($record);
        self::assertEquals($this->idSite, $record['idsite']);
        self::assertEquals('ChatGPT-User', $record['bot_name']);
        self::assertEquals(BotDetector::BOT_TYPE_AI_CHATBOT, $record['bot_type']);
        self::assertEquals(200, $record['http_status_code']);
    }

    public function testInsertWithNullOptionalFields(): void
    {
        $data = [
            'idsite'      => $this->idSite,
            'server_time' => '2025-10-28 12:00:00',
            'bot_name'    => 'Claude-User',
            'bot_type'    => BotDetector::BOT_TYPE_AI_CHATBOT,
        ];

        $idRequest = $this->dao->insert($data);

        self::assertGreaterThan(0, $idRequest);

        $tableName = BotRequestsDao::getPrefixedTableName();
        $sql       = "SELECT * FROM `{$tableName}` WHERE idrequest = ?";
        $record    = Db::fetchRow($sql, [$idRequest]);

        self::assertNull($record['idaction_url']);
        self::assertNull($record['http_status_code']);
        self::assertNull($record['response_size_bytes']);
        self::assertNull($record['response_time_ms']);
        self::assertNull($record['source']);
    }

    public function testDeleteOldRecordsDeletesRecordsBeforeDate(): void
    {
        $this->insertTestRecord('2025-10-20 10:00:00', 'Old-Bot-1');
        $this->insertTestRecord('2025-10-25 10:00:00', 'Old-Bot-2');
        $this->insertTestRecord('2025-10-28 10:00:00', 'Recent-Bot');

        $deleteBeforeDate = Date::factory('2025-10-27 00:00:00');
        $deletedCount     = $this->dao->deleteOldRecords($deleteBeforeDate);

        self::assertEquals(2, $deletedCount);

        $tableName      = BotRequestsDao::getPrefixedTableName();
        $sql            = "SELECT COUNT(*) as count FROM `{$tableName}` WHERE idsite = ?";
        $remainingCount = Db::fetchOne($sql, [$this->idSite]);

        self::assertEquals(1, $remainingCount);
    }

    public function testGetAIChatbotsRealTimeAggregatesRecentAIChatbotRows(): void
    {
        $pageA = $this->createAction('example.com/realtime/a', Action::TYPE_PAGE_URL);
        $pageB = $this->createAction('example.com/realtime/b', Action::TYPE_PAGE_URL);
        $pageC = $this->createAction('example.com/realtime/c', Action::TYPE_PAGE_URL);
        $document = $this->createAction('example.com/realtime/doc.pdf', Action::TYPE_DOWNLOAD);

        $this->insertTestRecord('2026-01-01 10:00:00', 'ChatGPT-User', null, $pageA, 200);
        $this->insertTestRecord('2026-01-01 10:10:00', 'ChatGPT-User', null, $pageA, 404);
        $this->insertTestRecord('2026-01-01 10:20:00', 'ChatGPT-User', null, $pageB, 500);
        $this->insertTestRecord('2026-01-01 10:30:00', 'ChatGPT-User', null, $document, 404);
        $this->insertTestRecord('2026-01-01 10:40:00', 'Claude-User', null, $pageC, 200);
        $this->insertTestRecord('2026-01-01 10:50:00', 'Claude-User', null, $document, 503);

        $this->insertTestRecord('2026-01-01 09:59:59', 'OutOfWindow-Bot', null, $pageA, 200);
        $this->insertTestRecord('2026-01-01 10:00:00', 'Regular-Bot', null, $pageA, 200, 'crawler');
        $this->insertTestRecord('2026-01-01 10:00:00', 'OtherSite-Bot', $this->idSite + 1, $pageA, 200);

        $table = $this->dao->getAIChatbotsRealTime([$this->idSite], '2026-01-01 10:00:00', '2026-01-01 12:00:00');
        $rows = $this->indexRowsByLabel($table);

        self::assertCount(2, $rows);

        self::assertSame(4, $rows['ChatGPT-User'][Metrics::COLUMN_REQUESTS]);
        self::assertSame(2, $rows['ChatGPT-User'][Metrics::METRIC_AI_CHATBOTS_UNIQUE_PAGE_URLS]);
        self::assertSame(2, $rows['ChatGPT-User'][Metrics::METRIC_AI_CHATBOTS_NOT_FOUND_REQUESTS]);
        self::assertSame(1, $rows['ChatGPT-User'][Metrics::METRIC_AI_CHATBOTS_SERVER_ERROR_REQUESTS]);

        self::assertSame(2, $rows['Claude-User'][Metrics::COLUMN_REQUESTS]);
        self::assertSame(1, $rows['Claude-User'][Metrics::METRIC_AI_CHATBOTS_UNIQUE_PAGE_URLS]);
        self::assertSame(0, $rows['Claude-User'][Metrics::METRIC_AI_CHATBOTS_NOT_FOUND_REQUESTS]);
        self::assertSame(1, $rows['Claude-User'][Metrics::METRIC_AI_CHATBOTS_SERVER_ERROR_REQUESTS]);
    }

    public function testGetTopPageUrlsRealTimeReturnsOnlyFlatPageUrls(): void
    {
        $pageA = $this->createAction('example.com/realtime/a', Action::TYPE_PAGE_URL);
        $pageB = $this->createAction('example.com/realtime/b', Action::TYPE_PAGE_URL);
        $pageC = $this->createAction('example.com/realtime/c', Action::TYPE_PAGE_URL);
        $document = $this->createAction('example.com/realtime/doc.pdf', Action::TYPE_DOWNLOAD);

        $this->insertTestRecord('2026-01-01 10:00:00', 'ChatGPT-User', null, $pageA, 200);
        $this->insertTestRecord('2026-01-01 10:10:00', 'ChatGPT-User', null, $pageA, 404);
        $this->insertTestRecord('2026-01-01 10:20:00', 'ChatGPT-User', null, $pageB, 500);
        $this->insertTestRecord('2026-01-01 10:30:00', 'Claude-User', null, $document, 404);
        $this->insertTestRecord('2026-01-01 10:40:00', 'Claude-User', null, $pageC, 200);
        $this->insertTestRecord('2026-01-01 09:59:59', 'OutOfWindow-Bot', null, $pageA, 200);
        $this->insertTestRecord('2026-01-01 10:00:00', 'Regular-Bot', null, $pageA, 200, 'crawler');

        $table = $this->dao->getTopPageUrlsRealTime([$this->idSite], '2026-01-01 10:00:00', '2026-01-01 12:00:00');
        $rows = $table->getRows();

        self::assertCount(3, $rows);
        self::assertSame('example.com/realtime/a', $rows[0]->getColumn('label'));
        self::assertSame(2, $rows[0]->getColumn(Metrics::COLUMN_REQUESTS));
        self::assertSame('example.com/realtime/b', $rows[1]->getColumn('label'));
        self::assertSame(1, $rows[1]->getColumn(Metrics::COLUMN_REQUESTS));
        self::assertSame('example.com/realtime/c', $rows[2]->getColumn('label'));
        self::assertSame(1, $rows[2]->getColumn(Metrics::COLUMN_REQUESTS));
    }

    private function insertTestRecord(
        $serverTime,
        $botName,
        $idSite = null,
        ?int $idActionUrl = null,
        ?int $httpStatusCode = null,
        string $botType = BotDetector::BOT_TYPE_AI_CHATBOT
    ): int {
        if ($idSite === null) {
            $idSite = $this->idSite;
        }

        $data = [
            'idsite'           => $idSite,
            'server_time'      => $serverTime,
            'bot_name'         => $botName,
            'bot_type'         => $botType,
            'idaction_url'     => $idActionUrl,
            'http_status_code' => $httpStatusCode,
        ];

        return $this->dao->insert($data);
    }

    private function createAction(string $name, int $type): int
    {
        $actionIds = TableLogAction::loadIdsAction([[$name, $type]]);

        return (int) reset($actionIds);
    }

    /**
     * @return array<string, array<string, int|string|null>>
     */
    private function indexRowsByLabel(\Piwik\DataTable $table): array
    {
        $rows = [];
        foreach ($table->getRows() as $row) {
            $rows[(string) $row->getColumn('label')] = $row->getColumns();
        }

        return $rows;
    }

    /**
     * @return string[]
     */
    private function getIndexColumns(string $indexName): array
    {
        $tableName = BotRequestsDao::getPrefixedTableName();
        $rows      = Db::fetchAll("SHOW INDEX FROM `{$tableName}`");
        $rows      = array_filter($rows, function (array $row) use ($indexName): bool {
            return $row['Key_name'] === $indexName;
        });
        usort($rows, function (array $left, array $right): int {
            return (int) $left['Seq_in_index'] <=> (int) $right['Seq_in_index'];
        });

        return array_column($rows, 'Column_name');
    }
}
