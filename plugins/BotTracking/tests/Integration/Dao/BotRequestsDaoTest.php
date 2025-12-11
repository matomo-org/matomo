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
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

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

    public function testInsertCreatesRecord(): void
    {
        $data = [
            'idsite'              => $this->idSite,
            'server_time'         => '2025-10-28 12:00:00',
            'idaction_url'        => 123,
            'bot_name'            => 'ChatGPT-User',
            'bot_type'            => 'ai_assistant',
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
        self::assertEquals('ai_assistant', $record['bot_type']);
        self::assertEquals(200, $record['http_status_code']);
    }

    public function testInsertWithNullOptionalFields(): void
    {
        $data = [
            'idsite'      => $this->idSite,
            'server_time' => '2025-10-28 12:00:00',
            'bot_name'    => 'Claude-User',
            'bot_type'    => 'ai_assistant',
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

    private function insertTestRecord($serverTime, $botName, $idSite = null): int
    {
        if ($idSite === null) {
            $idSite = $this->idSite;
        }

        $data = [
            'idsite'      => $idSite,
            'server_time' => $serverTime,
            'bot_name'    => $botName,
            'bot_type'    => 'ai_assistant',
        ];

        return $this->dao->insert($data);
    }
}
