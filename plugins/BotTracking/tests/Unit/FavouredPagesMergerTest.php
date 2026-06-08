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
use Piwik\Plugins\BotTracking\DataTable\FavouredPagesMerger;
use Piwik\Plugins\BotTracking\Metrics;

/**
 * @group BotTracking
 * @group FavouredPagesMerger
 * @group Plugins
 */
class FavouredPagesMergerTest extends TestCase
{
    /**
     * @var FavouredPagesMerger
     */
    private $merger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->merger = new FavouredPagesMerger();
    }

    private function botRow(string $label, int $requests): Row
    {
        return new Row([Row::COLUMNS => [
            'label'                  => $label,
            Metrics::COLUMN_REQUESTS => $requests,
        ]]);
    }

    private function actionsRow(string $label, int $nbVisits, ?string $url): Row
    {
        $row = new Row([Row::COLUMNS => [
            'label'     => $label,
            'nb_visits' => $nbVisits,
        ]]);
        if ($url !== null) {
            $row->setMetadata('url', $url);
        }
        return $row;
    }

    /**
     * @param Row[] $rows
     */
    private function table(array $rows): DataTable
    {
        $table = new DataTable();
        foreach ($rows as $row) {
            $table->addRow($row);
        }
        return $table;
    }

    /**
     * @return array<string, array{int, int}> label => [human, ai]
     */
    private function indexByLabel(DataTable $table): array
    {
        $out = [];
        foreach ($table->getRows() as $row) {
            $out[$row->getColumn('label')] = [
                (int) $row->getColumn(Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS),
                (int) $row->getColumn(Metrics::COLUMN_AI_CHATBOT_REQUESTS),
            ];
        }
        return $out;
    }

    public function testMatchingUrlMergesHumanAndAiIntoOneRow(): void
    {
        $bot     = $this->table([$this->botRow('example.com/article/2', 3)]);
        $actions = $this->table([$this->actionsRow('/article/2', 12, 'https://example.com/article/2')]);

        $result = $this->merger->merge($bot, $actions);

        self::assertSame(1, $result->getRowsCount(), 'matching URLs must collapse to a single row');
        self::assertSame(['example.com/article/2' => [12, 3]], $this->indexByLabel($result));
    }

    public function testWwwUrlStillMatchesTheBotLabel(): void
    {
        // Bot label is the normalized name (PageUrl::normalizeUrl strips "https://www.").
        $bot     = $this->table([$this->botRow('example.com/pricing', 5)]);
        // Actions url metadata is the reconstructed full URL including www.
        $actions = $this->table([$this->actionsRow('/pricing', 40, 'https://www.example.com/pricing')]);

        $result = $this->merger->merge($bot, $actions);

        self::assertSame(1, $result->getRowsCount(), 'www. URL must merge into the bot row, not split');
        self::assertSame(['example.com/pricing' => [40, 5]], $this->indexByLabel($result));
    }

    public function testHumanOnlyUrlIsAppendedWithZeroAiRequests(): void
    {
        $bot     = $this->table([$this->botRow('example.com/bot-only', 7)]);
        $actions = $this->table([$this->actionsRow('/human-only', 99, 'https://example.com/human-only')]);

        $result = $this->merger->merge($bot, $actions);

        self::assertSame([
            'example.com/bot-only'   => [0, 7],
            'example.com/human-only' => [99, 0],
        ], $this->indexByLabel($result));
    }

    public function testBotOnlyRowKeepsZeroHumanPageviews(): void
    {
        $bot     = $this->table([$this->botRow('example.com/bot-only', 4)]);
        $actions = $this->table([]);

        $result = $this->merger->merge($bot, $actions);

        self::assertSame(['example.com/bot-only' => [0, 4]], $this->indexByLabel($result));
    }

    public function testActionsRowWithoutUrlMetadataIsSkipped(): void
    {
        // Summary/"Others" rows and page-title rows have no url metadata.
        $bot     = $this->table([$this->botRow('example.com/x', 2)]);
        $actions = $this->table([$this->actionsRow('Others', 500, null)]);

        $result = $this->merger->merge($bot, $actions);

        self::assertSame(['example.com/x' => [0, 2]], $this->indexByLabel($result));
    }

    public function testDuplicateActionsLabelsCollapseToOneRow(): void
    {
        // http and https variants of the same page normalize to the same bot label.
        $bot     = $this->table([]);
        $actions = $this->table([
            $this->actionsRow('/dup', 10, 'http://example.com/dup'),
            $this->actionsRow('/dup', 25, 'https://example.com/dup'),
        ]);

        $result = $this->merger->merge($bot, $actions);

        self::assertSame(1, $result->getRowsCount(), 'duplicate normalized labels must not create two rows');
        // Last value wins, consistent with setColumn overwrite semantics.
        self::assertSame(['example.com/dup' => [25, 0]], $this->indexByLabel($result));
    }

    public function testCanonicalColumnOrderOnEveryRow(): void
    {
        $bot     = $this->table([$this->botRow('example.com/bot', 1)]);
        $actions = $this->table([$this->actionsRow('/human', 2, 'https://example.com/human')]);

        $result = $this->merger->merge($bot, $actions);

        $expectedOrder = ['label', Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS, Metrics::COLUMN_AI_CHATBOT_REQUESTS];
        foreach ($result->getRows() as $row) {
            self::assertSame(
                $expectedOrder,
                array_keys($row->getColumns()),
                'every merged row must use the same column order regardless of origin'
            );
        }
    }

    public function testMapIsMergedPerChildTable(): void
    {
        $botMap = new DataTable\Map();
        $botMap->addTable($this->table([$this->botRow('example.com/a', 3)]), '2025-02-02');
        $botMap->addTable($this->table([$this->botRow('example.com/b', 6)]), '2025-02-03');

        $actionsMap = new DataTable\Map();
        $actionsMap->addTable($this->table([$this->actionsRow('/a', 30, 'https://example.com/a')]), '2025-02-02');
        $actionsMap->addTable($this->table([$this->actionsRow('/b', 60, 'https://example.com/b')]), '2025-02-03');

        $result = $this->merger->merge($botMap, $actionsMap);

        self::assertInstanceOf(DataTable\Map::class, $result);
        $children = $result->getDataTables();
        self::assertSame(['example.com/a' => [30, 3]], $this->indexByLabel($children['2025-02-02']));
        self::assertSame(['example.com/b' => [60, 6]], $this->indexByLabel($children['2025-02-03']));
    }

    public function testMapWithMissingActionsChildDefaultsToBotOnly(): void
    {
        $botMap = new DataTable\Map();
        $botMap->addTable($this->table([$this->botRow('example.com/a', 3)]), '2025-02-02');

        // Actions side has no matching child key.
        $actionsMap = new DataTable\Map();

        $result = $this->merger->merge($botMap, $actionsMap);

        $children = $result->getDataTables();
        self::assertSame(['example.com/a' => [0, 3]], $this->indexByLabel($children['2025-02-02']));
    }
}
