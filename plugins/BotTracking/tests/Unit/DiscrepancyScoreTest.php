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
use Piwik\DataTable;
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
     * Reference implementation of the bounded formula, kept inline so expected values are not
     * sourced from the class under test.
     */
    private static function expected(int $strong, int $weak, int $maxStrong): float
    {
        $total = $strong + $weak;
        if ($total <= 0) {
            return 0.0;
        }
        $lean   = max(0, ($strong - $weak) / $total);
        $anchor = log10($maxStrong + 1);
        $volume = $anchor > 0 ? log10($strong + 1) / $anchor : 0.0;

        return round(100 * $lean * $volume, 1);
    }

    /**
     * @param array<int, array{int, int}> $rows list of [human, ai]
     */
    private static function table(array $rows): DataTable
    {
        $table = new DataTable();
        foreach ($rows as [$human, $ai]) {
            $table->addRow(new Row([Row::COLUMNS => [
                Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => $human,
                Metrics::COLUMN_AI_CHATBOT_REQUESTS    => $ai,
            ]]));
        }
        return $table;
    }

    private static function computeAll(string $variant, DataTable $table): array
    {
        $metric = new DiscrepancyScore($variant);
        $metric->beforeCompute(null, $table);

        $scores = [];
        foreach ($table->getRows() as $row) {
            $scores[] = (float) $metric->compute($row);
        }
        return $scores;
    }

    public function testScoresAreBoundedZeroToHundred(): void
    {
        $table  = self::table([[5000, 0], [2000, 100], [50, 1], [500, 500], [100, 2000], [1, 0]]);
        $scores = self::computeAll(DiscrepancyScore::VARIANT_HUMAN_FAVOURED, $table);

        foreach ($scores as $score) {
            self::assertGreaterThanOrEqual(0, $score);
            self::assertLessThanOrEqual(100, $score);
        }
    }

    public function testHumanFavouredFormula(): void
    {
        // maxStrong (human) = 2000.
        $table  = self::table([[2000, 100], [50, 1], [500, 500], [100, 2000], [0, 0]]);
        $scores = self::computeAll(DiscrepancyScore::VARIANT_HUMAN_FAVOURED, $table);

        self::assertSame([
            self::expected(2000, 100, 2000), // strong human, high volume
            self::expected(50, 1, 2000),     // human-leaning, low volume
            0.0,                             // balanced
            0.0,                             // opposite-leaning (clamped)
            0.0,                             // empty
        ], $scores);
    }

    public function testAiFavouredFormula(): void
    {
        // Mirror of the human-favoured case; maxStrong (ai) = 2000.
        $table  = self::table([[100, 2000], [1, 50], [500, 500], [2000, 100], [0, 0]]);
        $scores = self::computeAll(DiscrepancyScore::VARIANT_AI_FAVOURED, $table);

        self::assertSame([
            self::expected(2000, 100, 2000), // strong AI, high volume
            self::expected(50, 1, 2000),     // AI-leaning, low volume
            0.0,                             // balanced
            0.0,                             // human-leaning (clamped)
            0.0,                             // empty
        ], $scores);
    }

    public function testBalancedAndOppositeLeaningScoreZero(): void
    {
        $table  = self::table([[500, 500], [100, 2000], [0, 80]]);
        $scores = self::computeAll(DiscrepancyScore::VARIANT_HUMAN_FAVOURED, $table);

        self::assertSame([0.0, 0.0, 0.0], $scores);
    }

    public function testHighTrafficLeaningPageOutranksTinyExtremeRatio(): void
    {
        // The whole point of the volume term: 2000/100 must score above 50/1.
        $table  = self::table([[2000, 100], [50, 1]]);
        [$big, $tiny] = self::computeAll(DiscrepancyScore::VARIANT_HUMAN_FAVOURED, $table);

        self::assertGreaterThan($tiny, $big);
    }

    public function testVolumeAnchorAutoScalesWithTheTopPage(): void
    {
        // The same fully-human page (100/0) scores 100 when it is the busiest page...
        $alone = self::computeAll(DiscrepancyScore::VARIANT_HUMAN_FAVOURED, self::table([[100, 0]]));
        self::assertSame(100.0, $alone[0]);

        // ...but far less when a much bigger page sets the anchor.
        $withBigger = self::computeAll(DiscrepancyScore::VARIANT_HUMAN_FAVOURED, self::table([[100, 0], [10000, 0]]));
        self::assertSame(self::expected(100, 0, 10000), $withBigger[0]);
        self::assertLessThan(60, $withBigger[0]);
    }

    public function testComputeWithoutBeforeComputeYieldsZero(): void
    {
        // No anchor resolved (maxStrong defaults to 0) → no usable volume → score 0.
        $metric = new DiscrepancyScore(DiscrepancyScore::VARIANT_HUMAN_FAVOURED);
        $row    = new Row([Row::COLUMNS => [
            Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => 2000,
            Metrics::COLUMN_AI_CHATBOT_REQUESTS    => 100,
        ]]);

        self::assertSame(0.0, (float) $metric->compute($row));
    }
}
