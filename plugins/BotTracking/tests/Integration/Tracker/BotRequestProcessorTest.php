<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\Integration\Tracker;

use Piwik\Common;
use Piwik\Db;
use Piwik\Log\NullLogger;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Plugins\BotTracking\Tracker\BotRequestProcessor;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;

/**
 * @group BotTracking
 * @group BotRequestProcessor
 * @group Plugins
 */
class BotRequestProcessorTest extends IntegrationTestCase
{
    /**
     * @var BotRequestProcessor
     */
    private $requestProcessor;

    /**
     * @var int
     */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2025-01-01 00:00:00');

        $this->requestProcessor = new BotRequestProcessor(new BotRequestsDao(), new NullLogger());
    }

    public function testProcessRequestParamsDetectsBotRequest(): void
    {
        $request = $this->makeRequest([
            'idsite' => $this->idSite,
            'url' => 'https://example.com/test',
            'ua' => 'ChatGPT-User/1.0',
        ]);

        $result = $this->requestProcessor->handleRequest($request);

        self::assertTrue($result);
    }

    public function testProcessRequestParamsDoesNotDetectNonBotRequest(): void
    {
        $request = $this->makeRequest([
            'idsite' => $this->idSite,
            'url' => 'https://example.com/test',
            'ua' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);

        $result = $this->requestProcessor->handleRequest($request);

        self::assertFalse($result);

        $tableName = BotRequestsDao::getPrefixedTableName();
        $sql = "SELECT COUNT(*) as count FROM `{$tableName}` WHERE idsite = ?";
        $count = Db::fetchOne($sql, [$this->idSite]);

        self::assertEquals(0, $count);
    }

    public function testRecordLogsHandlesOptionalParameters(): void
    {
        $request = $this->makeRequest([
            'idsite' => $this->idSite,
            'url' => 'https://example.com/test',
            'ua' => 'Perplexity-User/1.0',
            // No optional parameters
        ]);

        $this->requestProcessor->handleRequest($request);

        // Verify data was stored with NULL optional fields
        $tableName = BotRequestsDao::getPrefixedTableName();
        $sql = "SELECT * FROM `{$tableName}` WHERE idsite = ?";
        $records = Db::fetchAll($sql, [$this->idSite]);

        $this->assertCount(1, $records);
        $record = $records[0];

        self::assertNull($record['http_status_code']);
        self::assertNull($record['response_size_bytes']);
        self::assertNull($record['response_time_ms']);
        self::assertNull($record['source']);
    }

    public function testRecordLogsHandlesDownloadParameter(): void
    {
        $request = $this->makeRequest([
            'idsite' => $this->idSite,
            'url' => 'https://example.com/page',
            'download' => 'https://example.com/document.pdf',
            'ua' => 'Claude-User/2.0',
        ]);

        $this->requestProcessor->handleRequest($request);

        $tableName = BotRequestsDao::getPrefixedTableName();
        $sql = "SELECT * FROM `{$tableName}` WHERE idsite = ?";
        $records = Db::fetchAll($sql, [$this->idSite]);

        self::assertCount(1, $records);
        self::assertNotNull($records[0]['idaction_url']);

        $tableName = Common::prefixTable('log_action');
        $action = Db::fetchAll("SELECT * FROM `{$tableName}` WHERE idaction = ?", [$records[0]['idaction_url']]);
        self::assertCount(1, $action);
        self::assertEquals('example.com/document.pdf', $action[0]['name']);
        self::assertEquals(Action::TYPE_DOWNLOAD, $action[0]['type']);
    }

    /**
     * @dataProvider getBotUserAgents
     */
    public function testMultipleBotTypesAreDetected(string $userAgent, string $expectedBotName): void
    {
        $request = $this->makeRequest([
            'idsite' => $this->idSite,
            'url' => 'https://example.com/test',
            'ua' => $userAgent,
        ]);

        $this->requestProcessor->handleRequest($request);

        $tableName = BotRequestsDao::getPrefixedTableName();
        $sql = "SELECT bot_name FROM `{$tableName}` WHERE idsite = ? ORDER BY idrequest DESC LIMIT 1";
        $botName = Db::fetchOne($sql, [$this->idSite]);

        self::assertEquals($expectedBotName, $botName);
    }

    /**
     * @return array<array{0: string, 1: string}>
     */
    public function getBotUserAgents(): array
    {
        return [
            ['ChatGPT-User/1.0', 'ChatGPT-User'],
            ['MistralAI-User/2.0', 'MistralAI-User'],
            ['Gemini-Deep-Research/1.0', 'Gemini-Deep-Research'],
            ['Claude-User/3.0', 'Claude-User'],
            ['Perplexity-User/1.0', 'Perplexity-User'],
            ['Devin/1.0', 'Devin'],
        ];
    }

    private function makeRequest(array $params): Request
    {
        $params = array_merge([
            'rec' => 1,
        ], $params);

        return new Request($params);
    }
}
