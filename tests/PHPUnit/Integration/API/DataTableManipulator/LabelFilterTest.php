<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\API\DataTableManipulator;

use Piwik\API\Request;
use Piwik\Archive\DataTableFactory;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Plugins\CoreHome\Columns\Metrics\VisitsPercent;
use Piwik\Plugins\Referrers\API as ReferrersAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Tests the label search through the real request pipeline, so pruning runs together with the
 * generic filters. The report comes from an intercept, not an archive, since only the subtable's
 * shape matters.
 *
 * @group LabelFilterTest
 * @group Core
 */
class LabelFilterTest extends IntegrationTestCase
{
    private const DATE = '2020-04-07';
    private const SUBTABLE_ID = 42;

    /**
     * Hits on the two siblings of the row we want, which has 1 hit. They push the low-population
     * threshold above 1 and give a truncate something to drop.
     */
    private const SIBLING_HITS = [60, 30];

    /**
     * Which subtable the intercept serves. Most tests use the plain one.
     */
    private static string $subtableShape = 'siblings';

    /**
     * The label of the root table's only row. A pattern has to match it too.
     */
    private static string $rootLabel = 'dir';

    public function setUp(): void
    {
        parent::setUp();

        self::$subtableShape = 'siblings';
        self::$rootLabel = 'dir';

        // the search looks up the report to load subtables, and that needs a site
        Fixture::createWebsite('2018-05-05 09:00:00');
    }

    public function testRecursiveLabelReturnsTheMatchingRow(): void
    {
        $this->assertSame(['/target'], $this->getLabelsFromPageUrls());
    }

    public function testRecursiveLabelReturnsNothingWhenTheLabelDoesNotExist(): void
    {
        $this->assertSame([], $this->getLabelsFromPageUrls(['label' => 'dir>' . urlencode('/nonExistent')]));
    }

    public function testRecursiveLabelJudgesExcludeLowPopulationAgainstTheWholeSubtable(): void
    {
        // 1 hit out of 91 is below the low-population threshold, so the row is removed
        $this->assertSame([], $this->getLabelsFromPageUrls(['filter_excludelowpop' => 'nb_hits']));
    }

    public function testRecursiveLabelKeepsTheRowForAnExplicitExcludeLowPopulationThreshold(): void
    {
        // a fixed threshold is checked per row, so the siblings don't matter
        $this->assertSame(['/target'], $this->getLabelsFromPageUrls([
            'filter_excludelowpop' => 'nb_hits',
            'filter_excludelowpop_value' => 1,
        ]));
    }

    public function testRecursiveLabelRespectsATruncateOnTheSubtable(): void
    {
        // the row we want has the fewest hits, so truncating to 1 row removes it
        $this->assertSame([], $this->getLabelsFromPageUrls(['filter_truncate' => 1]));
    }

    public function testRecursiveLabelRespectsATruncateOfZeroOnTheSubtable(): void
    {
        // a truncate of 0 removes every row but the summary row
        $this->assertSame([], $this->getLabelsFromPageUrls(['filter_truncate' => 0]));
    }

    public function testRecursiveLabelRespectsAnOffsetOnTheSubtable(): void
    {
        // the row we want is last, so an offset of 2 keeps it only if its siblings are still
        // there. No limit, so the offset is the only reason not to prune
        $this->assertSame(['/target'], $this->getLabelsFromPageUrls(['filter_offset' => 2]));
    }

    public function testRecursiveLabelFindsARowLabelledLikeTheSummaryRow(): void
    {
        // a real row can be labelled '-1', like the summary row before ReplaceSummaryRowLabel runs
        self::$subtableShape = 'summaryRow';

        $this->assertSame(['-1'], $this->getLabelsFromPageUrls(['label' => 'dir>-1']));
    }

    public function testRecursiveLabelSortsTheSubtableByTheReportDefaultBeforeAnOffset(): void
    {
        // the search sorts subtables by the report's default column, not the requested one.
        // Sorted by label, the row we want would come first and the offset would remove it
        $this->assertSame(['/target'], $this->getLabelsFromPageUrls([
            'filter_offset' => 2,
            'filter_sort_column' => 'label',
            'filter_sort_order' => 'desc',
        ]));
    }

    public function testRecursiveLabelKeepsColumnsOnlyASiblingHasAValueFor(): void
    {
        // the page performance columns are dropped if no row has a timing sum, and here only a
        // sibling has one
        self::$subtableShape = 'siblingWithTiming';

        $row = $this->getRowFromPageUrls();

        $this->assertSame(1, $row['nb_hits_with_time_network'] ?? null);
        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning(), $row);
    }

    public function testRecursiveLabelPicksTheSameRowWhenTwoLabelsAreEqualOnceDecoded(): void
    {
        // both labels become "/a &amp; b" after decoding. The search then picks the last one after
        // sorting, which has fewer hits
        self::$subtableShape = 'labelsEqualOnceDecoded';

        $params = ['label' => 'dir>' . urlencode('/a & b')];
        $row = $this->getRowFromPageUrls($params);

        $this->assertSame(5, $row['nb_hits']);
        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning($params), $row);
    }

    public function testRecursiveLabelKeepsColumnsWhenAPatternRemovesTheSiblingThatHasThem(): void
    {
        // the pattern removes the first sibling with a timing sum but keeps the second, so the
        // timing columns stay
        self::$subtableShape = 'siblingsWithTiming';

        $params = ['filter_pattern' => '[^0]$'];
        $row = $this->getRowFromPageUrls($params);

        $this->assertSame(1, $row['nb_hits_with_time_network'] ?? null);
        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning($params), $row);
    }

    public function testRecursiveLabelKeepsTheOrderOfTheGoalColumns(): void
    {
        // goal columns follow the order goals first appear in the subtable, not the order in the
        // row we want
        self::$subtableShape = 'goals';

        $params = ['filter_update_columns_when_show_all_goals' => 1, 'idGoal' => 0];
        $row = $this->getRowFromPageUrls($params);

        $this->assertSame(array_keys($this->getRowFromPageUrlsWithoutPruning($params)), array_keys($row));
    }

    public function testRecursiveLabelKeepsColumnsWhenAPatternOfZeroRemovesTheSiblingThatHasThem(): void
    {
        // "0" is a pattern too. It removes the first sibling with a timing sum but keeps the
        // second, so the timing columns stay
        self::$subtableShape = 'siblingsWithTimingForAPatternOfZero';
        self::$rootLabel = 'dir0';

        $params = ['label' => 'dir0>' . urlencode('/target0'), 'filter_pattern' => '0'];
        $row = $this->getRowFromPageUrls($params);

        $this->assertArrayHasKey('avg_time_network', $row);
        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning($params), $row);
    }

    public function testRecursiveLabelPicksTheSameRowWhenAQueuedFilterRenamesALabel(): void
    {
        // Referrers renames an empty keyword to "Keyword not defined", which can also be a real
        // keyword
        self::$subtableShape = 'renamedLabel';

        $params = ['label' => 'dir>' . urlencode(ReferrersAPI::getKeywordNotDefinedString())];
        $row = $this->getRowFromPageUrls($params);

        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning($params), $row);
    }

    public function testRecursiveLabelComputesAMetricThatLooksAtEveryRowOnTheWholeSubtable(): void
    {
        // the share of visits is 1 out of 91 visits in the whole subtable
        self::$subtableShape = 'visitsPercent';

        $row = $this->getRowFromPageUrls();

        $this->assertEquals(0.01, $row['nb_visits_percentage']);
        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning(), $row);
    }

    public function testRecursiveLabelPicksTheSameRowWhenAQueuedFilterDeletesTheLabelColumn(): void
    {
        // without the label column, rows are found by their label metadata, which here matches
        // another row
        self::$subtableShape = 'deletedLabelColumn';

        $row = $this->getRowFromPageUrls();

        $this->assertSame(5, $row['nb_visits']);
        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning(), $row);
    }

    public function testRecursiveLabelPicksTheSameRowWhenTheRequestHidesTheLabelColumn(): void
    {
        // hideColumns removes the label column before the search, like the test above
        self::$subtableShape = 'conflictingLabelMetadata';

        $row = $this->getRowFromPageUrls(['hideColumns' => 'label']);

        $this->assertSame(5, $row['nb_visits']);
        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning(['hideColumns' => 'label']), $row);
    }

    public function testRecursiveLabelKeepsTheGoalColumnsWhenTheRowsWithoutVisitsAreDeleted(): void
    {
        // the first row with the goal has no visits, so it is removed before the goal columns are
        // added. Only a later row still has the goal
        self::$subtableShape = 'conversionOnlyGoal';

        $params = [
            'filter_add_columns_when_show_all_columns' => 1,
            'filter_update_columns_when_show_all_goals' => 1,
            'idGoal' => 0,
        ];
        $row = $this->getRowFromPageUrls($params);

        $this->assertArrayHasKey('goal_3_nb_conversions', $row);
        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning($params), $row);
    }

    public function testRecursiveLabelKeepsTheTimingColumnsWhenAPrunedRowSortsFirst(): void
    {
        // the most viewed row sorts first and has an average, so the timing columns stay. It adds
        // no new column, so pruning would drop it
        self::$subtableShape = 'popularRowWithAverage';

        $row = $this->getRowFromPageUrls();

        $this->assertSame(1, $row['nb_hits_with_time_network'] ?? null);
        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning(), $row);
    }

    public function testRecursiveLabelPicksTheSameGenerationTimeColumnWhenAPrunedRowSortsFirst(): void
    {
        // the most viewed row uses column names, the others use ids, and the top row decides
        // which one the metric reads
        self::$subtableShape = 'popularRowWithNamedColumns';

        $row = $this->getRowFromPageUrls();

        $this->assertArrayNotHasKey('avg_time_generation', $row);
        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning(), $row);
    }

    private function getRowFromPageUrls(array $params = []): array
    {
        $table = Request::processRequest('Actions.getPageUrls', array_merge([
            'idSite' => 1,
            'period' => 'day',
            'date' => self::DATE,
            'label' => 'dir>' . urlencode('/target'),
            'filter_limit' => -1,
            'format' => 'original',
        ], $params));

        $this->assertSame(1, $table->getRowsCount());

        return $table->getFirstRow()->getColumns();
    }

    /**
     * A limit bigger than the subtable keeps every row, but it turns pruning off. So this returns
     * the result without pruning.
     */
    private function getRowFromPageUrlsWithoutPruning(array $params = []): array
    {
        return $this->getRowFromPageUrls(array_merge($params, ['filter_limit' => 1000]));
    }

    private function getLabelsFromPageUrls(array $params = []): array
    {
        $table = Request::processRequest('Actions.getPageUrls', array_merge([
            'idSite' => 1,
            'period' => 'day',
            'date' => self::DATE,
            'label' => 'dir>' . urlencode('/target'),
            'filter_limit' => -1,
            'format' => 'original',
        ], $params));

        return $table->getColumn('label');
    }

    private static function makeRootTable(): DataTable
    {
        $hits = array_sum(self::SIBLING_HITS) + 1;

        $row = new Row([Row::COLUMNS => ['label' => self::$rootLabel, 'nb_visits' => $hits, 'nb_hits' => $hits]]);
        $row->setNonLoadedSubtableId(self::SUBTABLE_ID);
        // hideColumns removes the label column on the root table too
        $row->setMetadata('label', self::$rootLabel);

        $table = new DataTable();
        $table->addRow($row);
        $table->setMetadata(
            DataTableFactory::TABLE_METADATA_PERIOD_INDEX,
            PeriodFactory::build('day', self::DATE)
        );

        return $table;
    }

    private static function makeSubtable(): DataTable
    {
        if (self::$subtableShape === 'summaryRow') {
            return self::makeSubtableWithASummaryRow();
        }

        $rows = [];

        foreach (self::SIBLING_HITS as $index => $hits) {
            $rows[] = ['label' => '/sibling' . $index, 'nb_visits' => $hits, 'nb_hits' => $hits];
        }

        $rows[] = ['label' => '/target', 'nb_visits' => 1, 'nb_hits' => 1];

        if (self::$subtableShape === 'siblingWithTiming') {
            $rows[0] += [
                'sum_time_network' => 120,
                'nb_hits_with_time_network' => 60,
                'min_time_network' => 1,
                'max_time_network' => 3,
            ];
            // a hit timed at less than a millisecond
            $rows[2] += [
                'sum_time_network' => 0,
                'nb_hits_with_time_network' => 1,
                'min_time_network' => 0,
                'max_time_network' => 0,
            ];
        }

        if (self::$subtableShape === 'siblingsWithTiming') {
            foreach ([0, 1] as $index) {
                $rows[$index] += [
                    'sum_time_network' => 120,
                    'nb_hits_with_time_network' => 60,
                    'min_time_network' => 1,
                    'max_time_network' => 3,
                ];
            }
            $rows[2] += [
                'sum_time_network' => 0,
                'nb_hits_with_time_network' => 1,
                'min_time_network' => 0,
                'max_time_network' => 0,
            ];
        }

        if (self::$subtableShape === 'siblingsWithTimingForAPatternOfZero') {
            $timing = [
                'sum_time_network' => 120,
                'nb_hits_with_time_network' => 60,
                'min_time_network' => 1,
                'max_time_network' => 3,
            ];
            // "/sibling1" is the first row with a timing sum and has no 0 in its label
            $rows[1] += $timing;
            $rows[] = ['label' => '/timed0', 'nb_visits' => 5, 'nb_hits' => 5] + $timing;
            $rows[] = ['label' => '/target0', 'nb_visits' => 1, 'nb_hits' => 1];
        }

        if (self::$subtableShape === 'popularRowWithAverage') {
            $rows[1]['avg_time_network'] = 0.2;
            $rows[2] += ['nb_hits_with_time_network' => 1, 'min_time_network' => 0];
            $rows[] = ['label' => '/popular', 'nb_visits' => 100, 'nb_hits' => 100, 'avg_time_network' => 0.3];
        }

        if (self::$subtableShape === 'popularRowWithNamedColumns') {
            $rows[0] += [Metrics::INDEX_PAGE_SUM_TIME_GENERATION => 5, Metrics::INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION => 1];
            $rows[2] += [Metrics::INDEX_PAGE_SUM_TIME_GENERATION => 3, Metrics::INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION => 1];
            $rows[] = [
                'label' => '/popular',
                'nb_visits' => 100,
                'nb_hits' => 100,
                'sum_time_generation' => 0,
                'nb_hits_with_time_generation' => 0,
            ];
        }

        if (self::$subtableShape === 'renamedLabel') {
            $rows[] = ['label' => '', 'nb_visits' => 12, 'nb_hits' => 12];
            $rows[] = ['label' => ReferrersAPI::getKeywordNotDefinedString(), 'nb_visits' => 19, 'nb_hits' => 19];
        }

        if (self::$subtableShape === 'goals') {
            $rows[0]['goals'] = ['idgoal=2' => [Metrics::INDEX_GOAL_NB_CONVERSIONS => 1]];
            $rows[1]['goals'] = ['idgoal=1' => [Metrics::INDEX_GOAL_NB_CONVERSIONS => 1]];
            $rows[2]['goals'] = [
                'idgoal=1' => [Metrics::INDEX_GOAL_NB_CONVERSIONS => 1],
                'idgoal=2' => [Metrics::INDEX_GOAL_NB_CONVERSIONS => 1],
            ];
        }

        if (self::$subtableShape === 'conversionOnlyGoal') {
            // the first sibling already has every column, so the second is kept only for the goal
            $rows[1]['nb_visits'] = 0;
            $rows[1]['nb_hits'] = 0;
            $rows[1]['goals'] = ['idgoal=3' => [Metrics::INDEX_GOAL_NB_CONVERSIONS => 1]];
            $rows[] = [
                'label' => '/converted',
                'nb_visits' => 5,
                'nb_hits' => 5,
                'goals' => ['idgoal=3' => [Metrics::INDEX_GOAL_NB_CONVERSIONS => 2]],
            ];
        }

        if (in_array(self::$subtableShape, ['deletedLabelColumn', 'conflictingLabelMetadata'], true)) {
            $rows[] = ['label' => '/renamed', 'nb_visits' => 5, 'nb_hits' => 5];
        }

        if (self::$subtableShape === 'labelsEqualOnceDecoded') {
            $rows[] = ['label' => '/a &amp; b', 'nb_visits' => 20, 'nb_hits' => 20];
            $rows[] = ['label' => '/a & b', 'nb_visits' => 5, 'nb_hits' => 5];
        }

        // addRowsFromSimpleArray() can't hold the goals array
        $table = new DataTable();
        foreach ($rows as $columns) {
            $table->addRow(new Row([Row::COLUMNS => $columns]));
        }

        if (in_array(self::$subtableShape, ['deletedLabelColumn', 'conflictingLabelMetadata'], true)) {
            $table->getRowFromLabel('/target')->setMetadata('label', '/elsewhere');
            $table->getRowFromLabel('/renamed')->setMetadata('label', '/target');
        }

        if (self::$subtableShape === 'deletedLabelColumn') {
            $table->queueFilter('ColumnDelete', [['label']]);
        }

        if (self::$subtableShape === 'renamedLabel') {
            $table->queueFilter('Piwik\Plugins\Referrers\DataTable\Filter\KeywordNotDefined');
        }

        if (self::$subtableShape === 'visitsPercent') {
            $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, [new VisitsPercent()]);
        }

        return $table;
    }

    /**
     * A truncated subtable as an archive stores it: the summary row still has its raw label, and
     * the rename is queued.
     */
    private static function makeSubtableWithASummaryRow(): DataTable
    {
        $table = new DataTable();
        $table->addRowsFromSimpleArray([
            ['label' => '/sibling', 'nb_visits' => 60, 'nb_hits' => 60],
            ['label' => '-1', 'nb_visits' => 8, 'nb_hits' => 8],
        ]);
        $table->addSummaryRow(new Row([Row::COLUMNS => [
            'label' => DataTable::LABEL_SUMMARY_ROW,
            'nb_visits' => 30,
            'nb_hits' => 30,
        ]]));
        $table->queueFilter('ReplaceSummaryRowLabel');

        return $table;
    }

    public static function provideContainerConfigBeforeClass(): array
    {
        return [
            'observers.global' => \DI\add([
                ['API.Request.intercept', \DI\value(function (
                    &$returnedValue,
                    $finalParameters,
                    $pluginName,
                    $methodName,
                    $parametersRequest
                ) {
                    if ($pluginName !== 'Actions' || $methodName !== 'getPageUrls') {
                        return;
                    }

                    $returnedValue = empty($parametersRequest['idSubtable'])
                        ? self::makeRootTable()
                        : self::makeSubtable();
                })],
            ]),
        ];
    }
}
