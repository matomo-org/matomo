<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\Integration;

use Piwik\API\Request;
use Piwik\Cache;
use Piwik\DataTable;
use Piwik\Plugin\Manager;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group BotTracking
 * @group BotTrackingAIChatbotTrafficRatioAvailability
 * @group Plugins
 */
class AIChatbotTrafficRatioAvailabilityTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2025-01-01 00:00:00');
    }

    public function tearDown(): void
    {
        if (!Manager::getInstance()->isPluginActivated('Actions')) {
            Manager::getInstance()->activatePlugin('Actions');
            Cache::flushAll();
        }

        parent::tearDown();
    }

    public function testTheTrafficRatioIsOfferedWhileActionsIsActivated(): void
    {
        $this->assertContains(Metrics::METRIC_AI_CHATBOTS_TRAFFIC_RATIO, Metrics::getSparklineMetricOrder());
        $this->assertArrayHasKey(Metrics::METRIC_AI_CHATBOTS_TRAFFIC_RATIO, $this->getReportProcessedMetrics());
    }

    public function testGetLeavesOutTheTrafficRatioWhileActionsIsDeactivated(): void
    {
        Manager::getInstance()->deactivatePlugin('Actions');
        Cache::flushAll();

        $this->assertNotContains(Metrics::METRIC_AI_CHATBOTS_TRAFFIC_RATIO, Metrics::getSparklineMetricOrder());
        $this->assertArrayNotHasKey(Metrics::METRIC_AI_CHATBOTS_TRAFFIC_RATIO, $this->getReportProcessedMetrics());

        foreach (['BotTracking.get', 'API.get'] as $method) {
            $columns = $this->getFirstRowColumns($method);
            $this->assertArrayHasKey(Metrics::METRIC_AI_CHATBOTS_REQUESTS, $columns, $method);
            $this->assertArrayNotHasKey(Metrics::METRIC_AI_CHATBOTS_TRAFFIC_RATIO, $columns, $method);
        }
    }

    /**
     * @return array<string, string>
     */
    private function getReportProcessedMetrics(): array
    {
        return ReportsProvider::factory('BotTracking', 'get')->getProcessedMetrics();
    }

    /**
     * @return array<string, mixed>
     */
    private function getFirstRowColumns(string $method): array
    {
        /** @var DataTable $table */
        $table = Request::processRequest($method, [
            'idSite' => 1,
            'period' => 'day',
            'date'   => '2025-01-15',
            'format' => 'original',
        ]);

        return $table->getFirstRow()->getColumns();
    }
}
