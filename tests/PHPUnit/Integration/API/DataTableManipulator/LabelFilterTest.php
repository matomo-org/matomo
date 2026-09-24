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
 * Covers the recursive label descent through the real request pipeline, so that the pruning it does
 * on a loaded subtable is exercised together with the generic filters that run on that subtable.
 *
 * The report is served from an intercept rather than from an archive, because what matters here is
 * the shape of the subtable, not where it came from.
 *
 * @group LabelFilterTest
 * @group Core
 */
class LabelFilterTest extends IntegrationTestCase
{
    private const DATE = '2020-04-07';
    private const SUBTABLE_ID = 42;

    /**
     * Hits on the two siblings of the row the label asks for, which has a single hit. The siblings
     * alone put the column sum well above the threshold ExcludeLowPopulation derives from it, and
     * they give the table enough rows for a truncate to have something to drop.
     */
    private const SIBLING_HITS = [60, 30];

    /**
     * Which subtable the intercept below serves. Most tests want the plain one.
     */
    private static string $subtableShape = 'siblings';

    /**
     * The label of the one row of the root table, which a pattern has to match too.
     */
    private static string $rootLabel = 'dir';

    public function setUp(): void
    {
        parent::setUp();

        self::$subtableShape = 'siblings';
        self::$rootLabel = 'dir';

        // the descent resolves the method to load subtables with from the report metadata, which
        // needs a site to look the report up for
        Fixture::createWebsite('2018-05-05 09:00:00');
    }

    public function testRecursiveLabelReturnsTheMatchingRow()
    {
        $this->assertSame(['/target'], $this->getLabelsFromPageUrls());
    }

    public function testRecursiveLabelReturnsNothingWhenTheLabelDoesNotExist()
    {
        $this->assertSame([], $this->getLabelsFromPageUrls(['label' => 'dir>' . urlencode('/nonExistent')]));
    }

    public function testRecursiveLabelJudgesExcludeLowPopulationAgainstTheWholeSubtable()
    {
        // one hit out of 91 is below the threshold ExcludeLowPopulation derives from the sum of the
        // column over the whole subtable, so the row has to go even though the label asks for it
        $this->assertSame([], $this->getLabelsFromPageUrls(['filter_excludelowpop' => 'nb_hits']));
    }

    public function testRecursiveLabelKeepsTheRowForAnExplicitExcludeLowPopulationThreshold()
    {
        // an explicit threshold is decided per row, so the siblings make no difference to it
        $this->assertSame(['/target'], $this->getLabelsFromPageUrls([
            'filter_excludelowpop' => 'nb_hits',
            'filter_excludelowpop_value' => 1,
        ]));
    }

    public function testRecursiveLabelRespectsATruncateOnTheSubtable()
    {
        // the row the label asks for is the one with the fewest hits, so truncating to one row
        // leaves it out
        $this->assertSame([], $this->getLabelsFromPageUrls(['filter_truncate' => 1]));
    }

    public function testRecursiveLabelRespectsATruncateOfZeroOnTheSubtable()
    {
        // zero is a truncate like any other, so everything but the summary row goes
        $this->assertSame([], $this->getLabelsFromPageUrls(['filter_truncate' => 0]));
    }

    public function testRecursiveLabelRespectsAnOffsetOnTheSubtable()
    {
        // the row the label asks for is the last one, so it only survives an offset of two while
        // its siblings are still in front of it. the limit stays unlimited so that the offset is
        // the only reason the prune has to stay out of the way
        $this->assertSame(['/target'], $this->getLabelsFromPageUrls(['filter_offset' => 2]));
    }

    public function testRecursiveLabelFindsARowLabelledLikeTheSummaryRow()
    {
        // a row can be labelled '-1', which is also the label a summary row carries until
        // ReplaceSummaryRowLabel renames it during post processing
        self::$subtableShape = 'summaryRow';

        $this->assertSame(['-1'], $this->getLabelsFromPageUrls(['label' => 'dir>-1']));
    }

    public function testRecursiveLabelSortsTheSubtableByTheReportDefaultBeforeAnOffset()
    {
        // the descent has always sorted the subtable by the report's default column, whatever sort
        // the request asks for. sorted by label the row we are after would come first and the
        // offset would drop it
        $this->assertSame(['/target'], $this->getLabelsFromPageUrls([
            'filter_offset' => 2,
            'filter_sort_column' => 'label',
            'filter_sort_order' => 'desc',
        ]));
    }

    public function testRecursiveLabelKeepsColumnsOnlyASiblingHasAValueFor()
    {
        // the page performance metrics drop their columns when no row in the subtable has a timing
        // sum, and here only a sibling of the row we are after has one
        self::$subtableShape = 'siblingWithTiming';

        $row = $this->getRowFromPageUrls();

        $this->assertSame(1, $row['nb_hits_with_time_network'] ?? null);
        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning(), $row);
    }

    public function testRecursiveLabelPicksTheSameRowWhenTwoLabelsAreEqualOnceDecoded()
    {
        // both labels end up as "/a &amp; b" once post-processing decodes them, and the search then
        // picks the one the sort puts last, which is the one with fewer hits
        self::$subtableShape = 'labelsEqualOnceDecoded';

        $params = ['label' => 'dir>' . urlencode('/a & b')];
        $row = $this->getRowFromPageUrls($params);

        $this->assertSame(5, $row['nb_hits']);
        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning($params), $row);
    }

    public function testRecursiveLabelKeepsColumnsWhenAPatternRemovesTheSiblingThatHasThem()
    {
        // the pattern drops the first sibling with a timing sum but keeps the second one, so the
        // page performance metrics still see a timing sum on the whole subtable
        self::$subtableShape = 'siblingsWithTiming';

        $params = ['filter_pattern' => '[^0]$'];
        $row = $this->getRowFromPageUrls($params);

        $this->assertSame(1, $row['nb_hits_with_time_network'] ?? null);
        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning($params), $row);
    }

    public function testRecursiveLabelKeepsTheOrderOfTheGoalColumns()
    {
        // goal columns are added in the order the rows of the subtable first show each goal, which
        // is not the order the row we are after lists them in
        self::$subtableShape = 'goals';

        $params = ['filter_update_columns_when_show_all_goals' => 1, 'idGoal' => 0];
        $row = $this->getRowFromPageUrls($params);

        $this->assertSame(array_keys($this->getRowFromPageUrlsWithoutPruning($params)), array_keys($row));
    }

    public function testRecursiveLabelKeepsColumnsWhenAPatternOfZeroRemovesTheSiblingThatHasThem()
    {
        // "0" is a pattern like any other. It drops the first sibling with a timing sum but keeps
        // the second one, so the page performance metrics still see a timing sum on the subtable
        self::$subtableShape = 'siblingsWithTimingForAPatternOfZero';
        self::$rootLabel = 'dir0';

        $params = ['label' => 'dir0>' . urlencode('/target0'), 'filter_pattern' => '0'];
        $row = $this->getRowFromPageUrls($params);

        $this->assertArrayHasKey('avg_time_network', $row);
        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning($params), $row);
    }

    public function testRecursiveLabelPicksTheSameRowWhenAQueuedFilterRenamesALabel()
    {
        // Referrers renames the empty keyword to "Keyword not defined" after the subtable is
        // loaded, and a real keyword can already read like that
        self::$subtableShape = 'renamedLabel';

        $params = ['label' => 'dir>' . urlencode(ReferrersAPI::getKeywordNotDefinedString())];
        $row = $this->getRowFromPageUrls($params);

        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning($params), $row);
    }

    public function testRecursiveLabelComputesAMetricThatLooksAtEveryRowOnTheWholeSubtable()
    {
        // the share of visits is one visit out of 91 on the whole subtable
        self::$subtableShape = 'visitsPercent';

        $row = $this->getRowFromPageUrls();

        $this->assertEquals(0.01, $row['nb_visits_percentage']);
        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning(), $row);
    }

    public function testRecursiveLabelPicksTheSameRowWhenAQueuedFilterDeletesTheLabelColumn()
    {
        // with the label column gone the rows are identified by their label metadata, which is
        // another row's label column
        self::$subtableShape = 'deletedLabelColumn';

        $row = $this->getRowFromPageUrls();

        $this->assertSame(5, $row['nb_visits']);
        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning(), $row);
    }

    public function testRecursiveLabelPicksTheSameRowWhenTheRequestHidesTheLabelColumn()
    {
        // hideColumns deletes the label column before the search, see the test above
        self::$subtableShape = 'conflictingLabelMetadata';

        $row = $this->getRowFromPageUrls(['hideColumns' => 'label']);

        $this->assertSame(5, $row['nb_visits']);
        $this->assertEquals($this->getRowFromPageUrlsWithoutPruning(['hideColumns' => 'label']), $row);
    }

    public function testRecursiveLabelKeepsTheGoalColumnsWhenTheRowsWithoutVisitsAreDeleted()
    {
        // the first row showing the goal has a conversion but no visit, so it is deleted before
        // the goal columns are added, and only a later row still shows the goal
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
     * A limit the subtable does not reach keeps every row, but it is one of the filters the prune
     * steps aside for, so the subtable is post-processed whole, as it was before the prune existed.
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
        // hideColumns deletes the label column on the root table too
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
            // the first sibling already has a value for every column, so the second one is the
            // only row kept for the goal
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

        // a simple array cannot hold the goals column, which is an array itself
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
     * A truncated subtable, as an archive would produce it: a summary row still carrying the raw
     * label the archive stored, and the rename queued for post processing.
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

    public static function provideContainerConfigBeforeClass()
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
