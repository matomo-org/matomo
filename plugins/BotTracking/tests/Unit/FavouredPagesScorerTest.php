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
use Piwik\Plugins\BotTracking\DataTable\FavouredPagesScorer;
use Piwik\Plugins\BotTracking\Metrics;

/**
 * @group BotTracking
 * @group FavouredPagesScorer
 * @group Plugins
 */
class FavouredPagesScorerTest extends TestCase
{
    public function testRejectsUnknownVariant(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new FavouredPagesScorer('unknown');
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

    public function testScoreAtBoundaryInputs(): void
    {
        // Exact values at edge inputs (pins the formula, not just the [0,100] bound): a fully
        // one-sided page that is itself the anchor (→100), partial leans, balanced (→0), and a
        // near-zero-volume page (strong=1 against a far larger anchor).
        $cases = [[5000, 0, 5000], [2000, 100, 2000], [50, 1, 2000], [500, 500, 5000], [1, 0, 10000]];
        foreach ($cases as [$strong, $weak, $max]) {
            self::assertSame(
                self::expected($strong, $weak, $max),
                FavouredPagesScorer::score($strong, $weak, $max),
                "score($strong, $weak, $max)"
            );
        }

        // Spot-check the two most informative edges against hand-computed literals so the test is not
        // purely self-referential: the anchor page scores exactly 100, and strong=1/anchor=10000 is tiny.
        self::assertSame(100.0, FavouredPagesScorer::score(5000, 0, 5000));
        self::assertSame(7.5, FavouredPagesScorer::score(1, 0, 10000));
    }

    public function testBalancedAndOppositeLeaningScoreZero(): void
    {
        self::assertSame(0.0, FavouredPagesScorer::score(500, 500, 5000)); // balanced
        self::assertSame(0.0, FavouredPagesScorer::score(100, 2000, 5000)); // opposite-leaning
        self::assertSame(0.0, FavouredPagesScorer::score(0, 0, 5000));      // empty
    }

    public function testHighTrafficLeaningPageOutranksTinyExtremeRatio(): void
    {
        // The whole point of the volume term: 2000/100 must score above 50/1 (same maxStrong).
        $big  = FavouredPagesScorer::score(2000, 100, 2000);
        $tiny = FavouredPagesScorer::score(50, 1, 2000);

        self::assertGreaterThan($tiny, $big);
    }

    public function testVolumeAnchorScalesWithTopPage(): void
    {
        // The same fully-one-sided page scores 100 when it is the busiest page...
        self::assertSame(100.0, FavouredPagesScorer::score(100, 0, 100));
        // ...and far less when a much bigger page sets the anchor.
        self::assertSame(self::expected(100, 0, 10000), FavouredPagesScorer::score(100, 0, 10000));
        self::assertLessThan(60, FavouredPagesScorer::score(100, 0, 10000));
    }

    /**
     * @param array<int, array{int, int}> $rows list of [human, ai]
     */
    private static function table(array $rows): DataTable
    {
        $table = new DataTable();
        foreach ($rows as [$human, $ai]) {
            $table->addRow(new Row([Row::COLUMNS => [
                'label'                                => 'example.com/' . $human . '-' . $ai,
                Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => $human,
                Metrics::COLUMN_AI_CHATBOT_REQUESTS    => $ai,
            ]]));
        }
        return $table;
    }

    private static function scores(DataTable $table): array
    {
        $out = [];
        foreach ($table->getRows() as $row) {
            $out[] = (float) $row->getColumn(Metrics::COLUMN_DISCREPANCY_SCORE);
        }
        return $out;
    }

    public function testAddScoresHumanFavouredUsesHumanAsStrongSide(): void
    {
        // maxStrong (human) = 2000.
        $table = self::table([[2000, 100], [50, 1], [500, 500], [100, 2000]]);
        (new FavouredPagesScorer(DiscrepancyScore::VARIANT_HUMAN_FAVOURED))->addScores($table);

        self::assertSame([
            self::expected(2000, 100, 2000),
            self::expected(50, 1, 2000),
            0.0, // balanced
            0.0, // leans AI → clamped
        ], self::scores($table));
    }

    public function testAddScoresAiFavouredUsesAiAsStrongSide(): void
    {
        // maxStrong (ai) = 2000.
        $table = self::table([[100, 2000], [1, 50], [500, 500], [2000, 100]]);
        (new FavouredPagesScorer(DiscrepancyScore::VARIANT_AI_FAVOURED))->addScores($table);

        self::assertSame([
            self::expected(2000, 100, 2000),
            self::expected(50, 1, 2000),
            0.0, // balanced
            0.0, // leans human → clamped
        ], self::scores($table));
    }

    public function testAddScoresMarksScoreToSkipInSummaryRows(): void
    {
        $table = self::table([[2000, 100], [50, 1]]);
        (new FavouredPagesScorer(DiscrepancyScore::VARIANT_HUMAN_FAVOURED))->addScores($table);

        // The per-page 0-100 index must not be summed into any summary row (the Truncate "Others"
        // row during archiving, or the report totals row): the score column is marked 'skip'.
        $ops = $table->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME);
        self::assertSame('skip', $ops[Metrics::COLUMN_DISCREPANCY_SCORE] ?? null);
    }

    public function testAddScoresRecursesIntoMap(): void
    {
        $map = new DataTable\Map();
        $map->addTable(self::table([[100, 0]]), '2025-02-02');               // alone → maxStrong 100 → 100
        $map->addTable(self::table([[100, 0], [10000, 0]]), '2025-02-03');   // bigger page → lower score

        (new FavouredPagesScorer(DiscrepancyScore::VARIANT_HUMAN_FAVOURED))->addScores($map);

        $children = $map->getDataTables();
        self::assertSame([100.0], self::scores($children['2025-02-02']));
        self::assertSame(
            [self::expected(100, 0, 10000), self::expected(10000, 0, 10000)],
            self::scores($children['2025-02-03'])
        );
    }

    public function testSummaryRowExcludedFromAnchorAndLeftUnscored(): void
    {
        // One real fully-human page (100, 0): on its own it anchors maxStrong at 100 → score 100.
        $table = self::table([[100, 0]]);

        // An "Others" summary row aggregating a far larger human tail must NOT become the anchor.
        $others = new Row([Row::COLUMNS => [
            'label'                                => 'Others',
            Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => 100000,
            Metrics::COLUMN_AI_CHATBOT_REQUESTS    => 0,
        ]]);
        $others->setIsSummaryRow();
        $table->addSummaryRow($others);

        (new FavouredPagesScorer(DiscrepancyScore::VARIANT_HUMAN_FAVOURED))->addScores($table);

        // The real page still scores 100 (maxStrong = 100, not 100000): the summary aggregate did not
        // hijack the volume anchor.
        self::assertSame(100.0, (float) $table->getRowFromLabel('example.com/100-0')->getColumn(Metrics::COLUMN_DISCREPANCY_SCORE));
        // The summary row itself is left unscored.
        self::assertFalse($table->getSummaryRow()->getColumn(Metrics::COLUMN_DISCREPANCY_SCORE));
    }
}
