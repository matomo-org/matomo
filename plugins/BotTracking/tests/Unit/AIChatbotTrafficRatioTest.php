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
use Piwik\Metrics\Formatter;
use Piwik\NumberFormatter;
use Piwik\Plugins\BotTracking\Columns\Metrics\AIChatbotTrafficRatio;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Tests\Framework\Fixture;

/**
 * @group BotTracking
 * @group BotTrackingUnit
 */
class AIChatbotTrafficRatioTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Fixture::loadAllTranslations();
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();
        NumberFormatter::getInstance()->clearCache();
        parent::tearDown();
    }

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

    /**
     * @dataProvider getFormatTestData
     * @param int|float $value
     */
    public function testFormat($value, string $expected): void
    {
        $metric = new AIChatbotTrafficRatio();

        $this->assertSame($expected, $metric->format($value, new Formatter()));
    }

    /**
     * @return iterable<string, array{int|float, string}>
     */
    public function getFormatTestData(): iterable
    {
        yield 'one decimal from 1 upwards' => [2, '2.0×'];
        yield 'one decimal for a multiplier' => [8.04, '8.0×'];
        yield 'two decimals below 1' => [0.04, '0.04×'];
        yield 'no trailing zero below 1' => [0.5, '0.5×'];
        yield 'some traffic never reads as none' => [0.0049, '<0.01×'];
        yield 'smallest value shown as is' => [0.005, '0.01×'];
        yield 'no requests' => [0, '0.0×'];
    }
}
