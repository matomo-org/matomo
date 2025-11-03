<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group BotTracking
 * @group Plugins
 */
class TrackerTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        Fixture::createWebsite('2014-02-04');
    }

    /**
     * @dataProvider getBotUserAgents
     */
    public function testAIAssistantIsTrackedCorrectly(string $userAgent, string $expectedAgent, string $expectedType): void
    {
        $t = Fixture::getTracker(1, '2025-02-02 12:00:00');
        $t->setUserAgent($userAgent);
        $t->setUrl('https://matomo.org/faq/123');
        $t->setCustomTrackingParameter('recMode', '1');

        Fixture::checkResponse($t->doTrackPageView(''));

        $tableName = BotRequestsDao::getPrefixedTableName();
        $sql = "SELECT * FROM `{$tableName}` WHERE idsite = ?";
        $records = Db::fetchAll($sql, [1]);

        self::assertCount(1, $records);
        self::assertEquals($expectedAgent, $records[0]['bot_name']);
        self::assertEquals($expectedType, $records[0]['bot_type']);

        $tableName = Common::prefixTable('log_visit');
        $sql = "SELECT COUNT(*) FROM `{$tableName}` WHERE idsite = ?";
        self::assertEquals(0, Db::fetchOne($sql, [1]));

        $tableName = Common::prefixTable('log_action');
        $sql = "SELECT COUNT(*) FROM `{$tableName}`";
        self::assertEquals(1, Db::fetchOne($sql));
    }

    /**
     * @return array<array{0: string, 1: string, 2: string}>
     */
    public function getBotUserAgents(): array
    {
        return [
            ['ChatGPT-User/1.0', 'ChatGPT-User', 'ai_assistant'],
            ['chatgpt-user/1.0', 'ChatGPT-User', 'ai_assistant'],
            ['CHATGPT-USER/1.0', 'ChatGPT-User', 'ai_assistant'],
            ['Mozilla/5.0 (compatible; ChatGPT-User/1.0)', 'ChatGPT-User', 'ai_assistant'],
            ['Mozilla/5.0 (compatible; ChatGPT-User/1.0; +https://openai.com)', 'ChatGPT-User', 'ai_assistant'],
            ['MistralAI-User/2.0', 'MistralAI-User', 'ai_assistant'],
            ['Gemini-Deep-Research/1.0', 'Gemini-Deep-Research', 'ai_assistant'],
            ['Claude-User/3.0', 'Claude-User', 'ai_assistant'],
            ['Perplexity-User/1.0', 'Perplexity-User', 'ai_assistant'],
            ['Devin/1.0', 'Devin', 'ai_assistant'],
        ];
    }

    public function testActionIdsAreCorrectlyReused(): void
    {
        $t = Fixture::getTracker(1, '2025-02-02 12:00:00');
        $t->setUserAgent('ChatGPT-User/1.0');
        $t->setUrl('https://matomo.org/faq/123');
        $t->setCustomTrackingParameter('recMode', '1');

        Fixture::checkResponse($t->doTrackPageView(''));

        $tableName = BotRequestsDao::getPrefixedTableName();
        $sql = "SELECT * FROM `{$tableName}` WHERE idsite = ?";
        $records = Db::fetchAll($sql, [1]);

        self::assertCount(1, $records);
        self::assertEquals('ChatGPT-User', $records[0]['bot_name']);
        self::assertEquals('ai_assistant', $records[0]['bot_type']);

        $tableName = Common::prefixTable('log_visit');
        $sql = "SELECT COUNT(*) FROM `{$tableName}` WHERE idsite = ?";
        self::assertEquals(0, Db::fetchOne($sql, [1]));

        $tableName = Common::prefixTable('log_action');
        $sql = "SELECT COUNT(*) FROM `{$tableName}`";
        self::assertEquals(1, Db::fetchOne($sql));

        // track another bot request to ensure now new action is created
        $t = Fixture::getTracker(1, '2025-02-02 12:00:00');
        $t->setUserAgent('Gemini-Deep-Research/1.0');
        $t->setUrl('https://matomo.org/faq/123');
        $t->setCustomTrackingParameter('recMode', '1');

        Fixture::checkResponse($t->doTrackPageView(''));

        $tableName = BotRequestsDao::getPrefixedTableName();
        $sql = "SELECT * FROM `{$tableName}` WHERE idsite = ?";
        $records = Db::fetchAll($sql, [1]);

        self::assertCount(2, $records);
        self::assertEquals('Gemini-Deep-Research', $records[1]['bot_name']);
        self::assertEquals('ai_assistant', $records[1]['bot_type']);

        $tableName = Common::prefixTable('log_visit');
        $sql = "SELECT COUNT(*) FROM `{$tableName}` WHERE idsite = ?";
        self::assertEquals(0, Db::fetchOne($sql, [1]));

        $tableName = Common::prefixTable('log_action');
        $sql = "SELECT COUNT(*) FROM `{$tableName}`";
        self::assertEquals(1, Db::fetchOne($sql));
    }

    public function testVisitsAndBotsShareActions(): void
    {
        // track a normal visit
        $t = Fixture::getTracker(1, '2025-02-02 12:00:00');
        $t->setUrl('https://matomo.org/faq/123');
        Fixture::checkResponse($t->doTrackPageView(''));

        $tableName = BotRequestsDao::getPrefixedTableName();
        $sql       = "SELECT * FROM `{$tableName}` WHERE idsite = ?";
        $records   = Db::fetchAll($sql, [1]);
        self::assertCount(0, $records);

        $tableName = Common::prefixTable('log_visit');
        $sql       = "SELECT COUNT(*) FROM `{$tableName}` WHERE idsite = ?";
        self::assertEquals(1, Db::fetchOne($sql, [1]));

        $tableName = Common::prefixTable('log_action');
        $sql       = "SELECT COUNT(*) FROM `{$tableName}`";
        self::assertEquals(1, Db::fetchOne($sql));

        // track another bot request to ensure now new action is created
        $t = Fixture::getTracker(1, '2025-02-02 12:00:00');
        $t->setUserAgent('Gemini-Deep-Research/1.0');
        $t->setUrl('https://matomo.org/faq/123');
        $t->setCustomTrackingParameter('recMode', '1');

        Fixture::checkResponse($t->doTrackPageView(''));

        $tableName = BotRequestsDao::getPrefixedTableName();
        $sql       = "SELECT * FROM `{$tableName}` WHERE idsite = ?";
        $records   = Db::fetchAll($sql, [1]);

        self::assertCount(1, $records);
        self::assertEquals('Gemini-Deep-Research', $records[0]['bot_name']);
        self::assertEquals('ai_assistant', $records[0]['bot_type']);

        $tableName = Common::prefixTable('log_visit');
        $sql       = "SELECT COUNT(*) FROM `{$tableName}` WHERE idsite = ?";
        self::assertEquals(1, Db::fetchOne($sql, [1]));

        $tableName = Common::prefixTable('log_action');
        $sql       = "SELECT COUNT(*) FROM `{$tableName}`";
        self::assertEquals(1, Db::fetchOne($sql));
    }
}
