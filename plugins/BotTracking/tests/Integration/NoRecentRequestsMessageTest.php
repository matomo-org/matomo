<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\Integration;

use Piwik\Date;
use Piwik\Plugins\BotTracking\BotDetector;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Plugins\BotTracking\NoRecentRequestsMessage;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group BotTracking
 * @group BotTrackingNoRecentRequestsMessage
 * @group Plugins
 */
class NoRecentRequestsMessageTest extends IntegrationTestCase
{
    /**
     * @var int
     */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite(
            '2024-01-01 00:00:00',
            0,
            'Test Site',
            'https://example.com',
            false,
            null,
            null,
            'America/Los_Angeles'
        );
    }

    /**
     * @dataProvider getShouldShowTestData
     */
    public function testShouldShowNoRecentRequestsMessage(
        string $testUtcNow,
        string $testPeriod,
        string $testDate,
        string $requestServerTime,
        string $siteTimezone,
        bool $expectedShowShould
    ): void {
        Date::$now = strtotime($testUtcNow);
        Site::setSiteFromArray($this->idSite, ['timezone' => $siteTimezone]);

        $dao = new BotRequestsDao();
        $dao->insert([
            'idsite'      => $this->idSite,
            'server_time' => $requestServerTime,
            'bot_name'    => 'ChatGPT-User',
            'bot_type'    => BotDetector::BOT_TYPE_AI_CHATBOT,
        ]);

        $shouldShow = NoRecentRequestsMessage::shouldShow($this->idSite, $testPeriod, $testDate);

        self::assertSame($expectedShowShould, $shouldShow);
    }

    public function testShouldShowWhenNoRequestsExist(): void
    {
        Date::$now = strtotime('2024-01-01 12:00:00');
        Site::setSiteFromArray($this->idSite, ['timezone' => 'UTC']);

        $shouldShow = NoRecentRequestsMessage::shouldShow($this->idSite, 'day', '2024-01-01');

        self::assertTrue($shouldShow);
    }

    /**
     * @return iterable<string, array{string, string, string, string, string, bool}>
     */
    public function getShouldShowTestData(): iterable
    {
        yield 'today + request yesterday' => ['2024-01-01 12:00:00', 'day', '2024-01-01', '2023-12-31 12:00:00', 'UTC', false];
        yield 'today + request within last 7 days' => ['2024-01-07 12:00:00', 'day', '2024-01-08', '2024-01-01 12:00:00', 'UTC', false];
        yield 'today + request 1 week ago' => ['2024-01-08 12:00:00', 'day', '2024-01-08', '2024-01-01 12:00:00', 'UTC', true];
        yield 'today + request 1 month ago' => ['2024-01-01 12:00:00', 'day', '2024-01-01', '2023-12-01 12:00:00', 'UTC', true];

        yield 'old day + request today' => ['2024-01-01 12:00:00', 'day', '2023-01-01', '2024-01-01 00:00:00', 'UTC', false];
        yield 'old day + old request' => ['2024-01-01 12:00:00', 'day', '2023-01-01', '2023-01-01 00:00:00', 'UTC', false];

        yield 'last month within reach + request today' => ['2024-01-01 12:00:00', 'month', '2023-12-01', '2024-01-01 00:00:00', 'UTC', false];
        yield 'last month within reach + old request' => ['2024-01-01 12:00:00', 'month', '2023-12-01', '2023-01-01 00:00:00', 'UTC', true];
        yield 'last month out of reach + request today' => ['2024-01-21 12:00:00', 'month', '2023-12-01', '2024-01-21 00:00:00', 'UTC', false];
        yield 'last month out of reach + old request' => ['2024-01-21 12:00:00', 'month', '2023-12-01', '2023-01-01 00:00:00', 'UTC', false];

        yield 'range within reach + request today' => ['2024-01-01 12:00:00', 'range', '2023-12-01,2023-12-30', '2024-01-01 00:00:00', 'UTC', false];
        yield 'range within reach + old request' => ['2024-01-01 12:00:00', 'range', '2023-12-01,2023-12-30', '2023-01-01 00:00:00', 'UTC', true];
        yield 'range out of reach + request today' => ['2024-01-21 12:00:00', 'range', '2023-12-01,2023-12-30', '2024-01-21 00:00:00', 'UTC', false];
        yield 'range out of reach + old request' => ['2024-01-21 12:00:00', 'range', '2023-12-01,2023-12-30', '2023-01-01 00:00:00', 'UTC', false];

        yield 'site timezone positive UTC + recent request' => ['2023-12-31 16:00:00', 'day', '2024-01-01', '2023-12-25 16:00:00', 'UTC+8', false];
        yield 'site timezone positive UTC + old request' => ['2023-12-31 16:00:00', 'day', '2024-01-01', '2023-12-25 15:59:59', 'UTC+8', true];
        yield 'site timezone negative UTC + recent request' => ['2024-01-01 08:00:00', 'day', '2024-01-01', '2023-12-26 08:00:00', 'UTC-8', false];
        yield 'site timezone negative UTC + old request' => ['2024-01-01 08:00:00', 'day', '2024-01-01', '2023-12-26 07:59:59', 'UTC-8', true];
    }
}
