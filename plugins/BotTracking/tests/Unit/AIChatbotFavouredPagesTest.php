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
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Plugins\BotTracking\RecordBuilders\AIChatbotFavouredPages;

/**
 * Covers the pure union logic behind the Favoured Pages records (the merge that used to live in the
 * deleted FavouredPagesMerger). The query and archiving glue are exercised by the system ApiTest.
 *
 * @group BotTracking
 * @group AIChatbotFavouredPages
 * @group Plugins
 */
class AIChatbotFavouredPagesTest extends TestCase
{
    /**
     * @param array<int, array{string, int}> $rows list of [label, value]
     */
    private static function table(string $valueColumn, array $rows): DataTable
    {
        $table = new DataTable();
        foreach ($rows as [$label, $value]) {
            $table->addRow(new Row([Row::COLUMNS => ['label' => $label, $valueColumn => $value]]));
        }
        return $table;
    }

    /**
     * @return array<string, array{int, int}> label => [human, ai]
     */
    private static function asMap(DataTable $table): array
    {
        $out = [];
        foreach ($table->getRows() as $row) {
            $out[(string) $row->getColumn('label')] = [
                (int) $row->getColumn(Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS),
                (int) $row->getColumn(Metrics::COLUMN_AI_CHATBOT_REQUESTS),
            ];
        }
        return $out;
    }

    public function testMergesOverlappingHumanOnlyAndAiOnlyUrls(): void
    {
        $human = self::table(Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS, [
            ['example.com/both', 100],
            ['example.com/human-only', 40],
        ]);
        $ai = self::table(Metrics::COLUMN_REQUESTS, [
            ['example.com/both', 7],
            ['example.com/ai-only', 12],
        ]);

        $merged = AIChatbotFavouredPages::mergeHumanAndAiTables($human, $ai);

        self::assertSame([
            'example.com/both'       => [100, 7],
            'example.com/ai-only'    => [0, 12],
            'example.com/human-only' => [40, 0],
        ], self::asMap($merged));
    }

    public function testMissingSidesDefaultToZero(): void
    {
        $merged = AIChatbotFavouredPages::mergeHumanAndAiTables(
            self::table(Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS, [['example.com/h', 5]]),
            self::table(Metrics::COLUMN_REQUESTS, [['example.com/a', 9]])
        );

        self::assertSame(['example.com/a' => [0, 9], 'example.com/h' => [5, 0]], self::asMap($merged));
    }

    public function testEmptyLabelsAreSkipped(): void
    {
        $merged = AIChatbotFavouredPages::mergeHumanAndAiTables(
            self::table(Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS, [['', 5], ['example.com/h', 3]]),
            self::table(Metrics::COLUMN_REQUESTS, [['', 9]])
        );

        self::assertSame(['example.com/h' => [3, 0]], self::asMap($merged));
    }

    public function testSummaryRowsAreExcluded(): void
    {
        $human = self::table(Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS, [['example.com/h', 3]]);
        $humanSummary = new Row([Row::COLUMNS => ['label' => 'Others', Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => 999]]);
        $humanSummary->setIsSummaryRow();
        $human->addSummaryRow($humanSummary);

        $ai = self::table(Metrics::COLUMN_REQUESTS, [['example.com/a', 4]]);
        $aiSummary = new Row([Row::COLUMNS => ['label' => 'Others', Metrics::COLUMN_REQUESTS => 888]]);
        $aiSummary->setIsSummaryRow();
        $ai->addSummaryRow($aiSummary);

        $merged = AIChatbotFavouredPages::mergeHumanAndAiTables($human, $ai);

        // The truncation "Others" rows must not leak into the union (a score over an aggregate of
        // many URLs would be meaningless).
        self::assertSame(['example.com/a' => [0, 4], 'example.com/h' => [3, 0]], self::asMap($merged));
        self::assertFalse($merged->getRowFromLabel('Others'));
    }

    public function testProducesCanonicalColumnSet(): void
    {
        $merged = AIChatbotFavouredPages::mergeHumanAndAiTables(
            self::table(Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS, [['example.com/h', 5]]),
            self::table(Metrics::COLUMN_REQUESTS, [['example.com/h', 2]])
        );

        $row = $merged->getFirstRow();
        self::assertSame(
            ['label', Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS, Metrics::COLUMN_AI_CHATBOT_REQUESTS],
            array_keys($row->getColumns())
        );
    }
}
