<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\DataTable\Row;
use Piwik\Plugins\BotTracking\Columns\Metrics\AIChatbotTrafficRatio;
use Piwik\Plugins\BotTracking\Metrics;

/**
 * @group BotTracking
 * @group BotTrackingUnit
 */
class AIChatbotTrafficRatioTest extends TestCase
{
    /**
     * @dataProvider getComputeTestData
     * @param int|null $requests
     * @param int|null $pageviews
     * @param float|false $expected
     */
    public function testCompute($requests, $pageviews, $expected): void
    {
        $columns = [];
        if ($requests !== null) {
            $columns[Metrics::METRIC_AI_CHATBOTS_REQUESTS] = $requests;
        }
        if ($pageviews !== null) {
            $columns['Actions_nb_pageviews'] = $pageviews;
        }

        $metric = new AIChatbotTrafficRatio();

        $this->assertSame($expected, $metric->compute(new Row([Row::COLUMNS => $columns])));
    }

    /**
     * @return iterable<string, array{int|null, int|null, float|false}>
     */
    public function getComputeTestData(): iterable
    {
        yield 'twice as many requests as page views' => [20, 10, 2.0];
        yield 'fewer requests than page views' => [1, 3, 0.3333];
        yield 'no requests' => [0, 10, 0.0];
        yield 'no page views is unavailable' => [5, 0, false];
        yield 'no data at all is unavailable' => [null, null, false];
    }
}
