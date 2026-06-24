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
use Piwik\Piwik;
use Piwik\Plugins\API\API as MetadataApi;
use Piwik\Plugins\BotTracking\API;
use Piwik\Plugins\BotTracking\BotDetector;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Action;
use Piwik\Tracker\TableLogAction;

/**
 * @group BotTracking
 * @group Plugins
 */
class RealTimeApiTest extends IntegrationTestCase
{
    /**
     * @var int
     */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        Date::$now = (int) strtotime('2026-01-01 12:00:00');
        $this->idSite = Fixture::createWebsite('2026-01-01 00:00:00');
    }

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

    public function testRealTimeApiRejectsLookbacksLongerThanTwelveHours(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('lastMinutes only accepts values between 1 and 720');

        API::getInstance()->getAIChatbotsRealTime($this->idSite, 721);
    }

    public function testRealTimeApiRejectsNonPositiveLookbacks(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('lastMinutes only accepts values between 1 and 720');

        API::getInstance()->getTopPageUrlsRealTime($this->idSite, 0);
    }

    public function testRealTimeApiReturnsDecoratedChatbotLabels(): void
    {
        $dao = new BotRequestsDao();
        $dao->insert([
            'idsite'           => $this->idSite,
            'server_time'      => '2026-01-01 11:45:00',
            'idaction_url'     => $this->createAction('example.com/realtime/a', Action::TYPE_PAGE_URL),
            'bot_name'         => 'ChatGPT-User',
            'bot_type'         => BotDetector::BOT_TYPE_AI_CHATBOT,
            'http_status_code' => 200,
        ]);

        $table = API::getInstance()->getAIChatbotsRealTime($this->idSite, '30');
        $rows = $table->getRows();

        self::assertCount(1, $rows);
        self::assertSame('ChatGPT', $rows[0]->getColumn('label'));
        self::assertSame(1, $rows[0]->getColumn(Metrics::COLUMN_CHATBOT_REQUESTS));
    }

    public function testRealTimeReportsAreHiddenFromReportMetadata(): void
    {
        $metadata = MetadataApi::getInstance()->getReportMetadata($this->idSite);
        $uniqueIds = array_column($metadata, 'uniqueId');

        self::assertNotContains('BotTracking_getAIChatbotsLast30Minutes', $uniqueIds);
        self::assertNotContains('BotTracking_getAIChatbotsLast8Hours', $uniqueIds);
        self::assertNotContains('BotTracking_getTopPageUrlsLast30Minutes', $uniqueIds);
        self::assertNotContains('BotTracking_getTopPageUrlsLast8Hours', $uniqueIds);
    }

    public function testHiddenRealTimeReportsAreAddedToGlossaryPageItems(): void
    {
        $glossaryItems = [
            'metrics' => [
                'title'   => Piwik::translate('General_Metrics'),
                'entries' => [],
            ],
            'reports' => [
                'title'   => Piwik::translate('General_Reports'),
                'entries' => [],
            ],
        ];

        Piwik::postEvent('API.addGlossaryItems', array(&$glossaryItems));
        $reportNames = array_column($glossaryItems['reports']['entries'], 'name');
        $category = Piwik::translate('General_AIAssistants');

        self::assertContains(
            sprintf('%s (%s)', Piwik::translate('BotTracking_AIChatbotsLast30MinutesTitle'), $category),
            $reportNames
        );
        self::assertContains(
            sprintf('%s (%s)', Piwik::translate('BotTracking_AIChatbotsLast8HoursTitle'), $category),
            $reportNames
        );
        self::assertContains(
            sprintf('%s (%s)', Piwik::translate('BotTracking_TopPageUrlsLast30MinutesTitle'), $category),
            $reportNames
        );
        self::assertContains(
            sprintf('%s (%s)', Piwik::translate('BotTracking_TopPageUrlsLast8HoursTitle'), $category),
            $reportNames
        );
    }

    public function testChatbotRequestsMetricAppearsInGlossaryMetrics(): void
    {
        $metrics = MetadataApi::getInstance()->getGlossaryMetrics($this->idSite);
        $metric = $this->findMetric($metrics, Metrics::COLUMN_CHATBOT_REQUESTS);

        self::assertNotNull($metric);
        self::assertSame(Piwik::translate('BotTracking_ColumnChatbotRequests'), $metric['name']);
        self::assertSame(
            Piwik::translate('BotTracking_ColumnChatbotRequestsDocumentation'),
            $metric['documentation']
        );
    }

    private function createAction(string $name, int $type): int
    {
        $actionIds = TableLogAction::loadIdsAction([[$name, $type]]);

        return (int) reset($actionIds);
    }

    /**
     * @param array<int, array<string, mixed>> $metrics
     * @return array<string, mixed>|null
     */
    private function findMetric(array $metrics, string $metricId): ?array
    {
        foreach ($metrics as $metric) {
            if (($metric['id'] ?? null) === $metricId) {
                return $metric;
            }
        }

        return null;
    }
}
