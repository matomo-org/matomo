<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PagePerformance\tests\Integration;

use Piwik\Config;
use Piwik\Plugins\PagePerformance\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group PagePerformance
 * @group Plugins
 */
class CappedValuesTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2023-01-01 00:00:00');
        Fixture::createSuperUser();
        $this->trackVisits();
    }

    public function testShouldNotCapOutlinerValuesByDefault()
    {
        $result = API::getInstance()->get(1, 'day', '2024-01-16');
        $resultArray = $result->getFirstRow()->getArrayCopy();

        self::assertEquals(
            [
                'PagePerformance_network_time' => 0.04,
                'PagePerformance_network_hits' => 2.0,
                'PagePerformance_servery_time' => 0.2,
                'PagePerformance_server_hits' => 2.0,
                'PagePerformance_transfer_time' => 1.2,
                'PagePerformance_transfer_hits' => 2.0,
                'PagePerformance_domprocessing_time' => 4.0,
                'PagePerformance_domprocessing_hits' => 2.0,
                'PagePerformance_domcompletion_time' => 1.2,
                'PagePerformance_domcompletion_hits' => 2.0,
                'PagePerformance_onload_time' => 0.24,
                'PagePerformance_onload_hits' => 2.0,
                'PagePerformance_pageload_time' => 6.88,
                'PagePerformance_pageload_hits' => 2.0,
                'avg_time_network' => 0.02,
                'avg_time_server' => 0.1,
                'avg_time_transfer' => 0.6,
                'avg_time_dom_processing' => 2.0,
                'avg_time_dom_completion' => 0.6,
                'avg_time_on_load' => 0.12,
                'avg_page_load_time' => 3.44,
            ],
            $resultArray
        );
    }

    public function testShouldNotCapOutlinerValuesWhenConfigured()
    {
        Config::getInstance()->PagePerformance = [
            'time_network_cap_duration_ms' => 20,
            'time_server_cap_duration_ms' => 100,
            'time_transfer_cap_duration_ms' => 600,
            'time_dom_processing_cap_duration_ms' => 2000,
            'time_dom_completion_cap_duration_ms' => 600,
            'time_on_load_cap_duration_ms' => 120,
        ];

        $result = API::getInstance()->get(1, 'day', '2024-01-16');
        $resultArray = $result->getFirstRow()->getArrayCopy();

        self::assertEquals(
            [
                'PagePerformance_network_time' => 0.03,
                'PagePerformance_network_hits' => 2.0,
                'PagePerformance_servery_time' => 0.15,
                'PagePerformance_server_hits' => 2.0,
                'PagePerformance_transfer_time' => 0.9,
                'PagePerformance_transfer_hits' => 2.0,
                'PagePerformance_domprocessing_time' => 3.0,
                'PagePerformance_domprocessing_hits' => 2.0,
                'PagePerformance_domcompletion_time' => 0.9,
                'PagePerformance_domcompletion_hits' => 2.0,
                'PagePerformance_onload_time' => 0.18,
                'PagePerformance_onload_hits' => 2.0,
                'PagePerformance_pageload_time' => 5.16,
                'PagePerformance_pageload_hits' => 2.0,
                'avg_time_network' => 0.02,
                'avg_time_server' => 0.08,
                'avg_time_transfer' => 0.45,
                'avg_time_dom_processing' => 1.5,
                'avg_time_dom_completion' => 0.45,
                'avg_time_on_load' => 0.09,
                'avg_page_load_time' => 2.58,
            ],
            $resultArray
        );
    }

    private function trackVisits()
    {
        $tracker = Fixture::getTracker(1, '2024-01-16 16:03:04');
        $tracker->setUrl('http://example.org/test');
        Fixture::checkResponse($tracker->doTrackPageView('My Page'));
        $tracker->setPerformanceTimings(10, 50, 300, 1000, 300, 60);
        Fixture::checkResponse($tracker->doTrackEvent('cat', 'act'));

        $tracker = Fixture::getTracker(1, '2024-01-16 18:03:04');
        $tracker->setNewVisitorId();
        $tracker->setUrl('http://example.org/test');
        $tracker->setPerformanceTimings(30, 150, 900, 3000, 900, 180);
        Fixture::checkResponse($tracker->doTrackPageView('My Page'));
    }
}
