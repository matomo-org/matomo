<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Piwik\DataTable\Row;
use Piwik\Plugins\BotTracking\Columns\Metrics\DiscrepancyScore;
use Piwik\Plugins\BotTracking\Metrics;

/**
 * @group BotTracking
 * @group DiscrepancyScore
 * @group Plugins
 */
class DiscrepancyScoreTest extends TestCase
{
    public function testRejectsUnknownVariant(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DiscrepancyScore('unknown');
    }

    /**
     * @dataProvider provideHumanFavouredCases
     */
    public function testHumanFavouredVariantFormula(int $humanPv, int $aiReq, float $expected): void
    {
        $metric = new DiscrepancyScore(DiscrepancyScore::VARIANT_HUMAN_FAVOURED);

        $row = new Row([Row::COLUMNS => [
            Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => $humanPv,
            Metrics::COLUMN_AI_CHATBOT_REQUESTS    => $aiReq,
        ]]);

        self::assertSame($expected, (float) $metric->compute($row));
    }

    /**
     * @return iterable<string, array{int, int, float}>
     */
    public static function provideHumanFavouredCases(): iterable
    {
        // Reference example from DEV-19843 — the new formula must rank Page 1 (high traffic)
        // above Page 2 (low traffic but extreme ratio).
        yield 'high-traffic page (rank 1)' => [2000, 100, self::roundedScore(2000, 100)];
        yield 'low-traffic outlier (rank 2)' => [50, 1, self::roundedScore(50, 1)];

        // Boundary cases — AI requests = 0 must not divide by zero.
        yield 'zero AI requests' => [500, 0, self::roundedScore(500, 0)];
        yield 'both zero' => [0, 0, 0.0];
    }

    /**
     * @dataProvider provideAiFavouredCases
     */
    public function testAiFavouredVariantFormula(int $humanPv, int $aiReq, float $expected): void
    {
        $metric = new DiscrepancyScore(DiscrepancyScore::VARIANT_AI_FAVOURED);

        $row = new Row([Row::COLUMNS => [
            Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => $humanPv,
            Metrics::COLUMN_AI_CHATBOT_REQUESTS    => $aiReq,
        ]]);

        self::assertSame($expected, (float) $metric->compute($row));
    }

    /**
     * @return iterable<string, array{int, int, float}>
     */
    public static function provideAiFavouredCases(): iterable
    {
        // Mirror of the human-favoured cases — same shape, sides swapped.
        yield 'high AI traffic'   => [100, 2000, self::roundedScore(2000, 100)];
        yield 'low AI traffic'    => [1, 50,     self::roundedScore(50, 1)];
        yield 'zero human visits' => [0, 500,    self::roundedScore(500, 0)];
        yield 'both zero'         => [0, 0,      0.0];
    }

    public function testHumanFavouredRanksHighTrafficPageFirst(): void
    {
        $metric = new DiscrepancyScore(DiscrepancyScore::VARIANT_HUMAN_FAVOURED);

        $highTraffic = $metric->compute(self::row(2000, 100));
        $outlier     = $metric->compute(self::row(50, 1));

        self::assertGreaterThan(
            $outlier,
            $highTraffic,
            'high-traffic human-favoured page must outrank a low-traffic outlier'
        );
    }

    public function testAiFavouredRanksHighTrafficPageFirst(): void
    {
        $metric = new DiscrepancyScore(DiscrepancyScore::VARIANT_AI_FAVOURED);

        $highTraffic = $metric->compute(self::row(100, 2000));
        $outlier     = $metric->compute(self::row(1, 50));

        self::assertGreaterThan(
            $outlier,
            $highTraffic,
            'high-traffic AI-favoured page must outrank a low-traffic outlier'
        );
    }

    /**
     * Reference implementation kept inline so the expected values aren't sourced from the class
     * under test — guards against accidental drift if the formula is edited in DiscrepancyScore.
     */
    private static function roundedScore(int $bigSide, int $smallSide): float
    {
        $score = ($bigSide / max($smallSide, 1)) * pow(log10($bigSide + 1), 2);
        return round($score, 2);
    }

    private static function row(int $humanPv, int $aiReq): Row
    {
        return new Row([Row::COLUMNS => [
            Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => $humanPv,
            Metrics::COLUMN_AI_CHATBOT_REQUESTS    => $aiReq,
        ]]);
    }
}
