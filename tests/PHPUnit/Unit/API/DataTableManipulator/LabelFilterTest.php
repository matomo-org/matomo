<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\API\DataTableManipulator;

use Piwik\API\DataTableManipulator\LabelFilter;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\CoreHome\Columns\Metrics\VisitsPercent;

class LabelFilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * LabelFilter builds a comparison label after the queued SafeDecodeLabel filter has already
     * sanitized the label column, so it has to sanitize what it writes there itself. A report
     * specific row identifier is a copy of the label taken before that filter ran, so unlike the
     * label column it is not sanitized when LabelFilter reads it.
     */
    public function testFilterSanitizesARowIdentifierBeforeUsingItAsAComparisonLabel()
    {
        $table = $this->makeTableWithComparisons('customLabel', 'the raw label <"');

        $filter = new LabelFilter(false, false, ['compare' => 1, 'labelSeries' => '0'], 'customLabel');
        $result = $filter->filter('the raw label <"', $table);

        $this->assertSame(1, $result->getRowsCount());
        $this->assertSame(
            'the raw label &lt;&quot; (Segment X)',
            $result->getFirstRow()->getColumn('label')
        );
    }

    public function testFilterLeavesAnAlreadySanitizedComparisonLabelUnchanged()
    {
        $table = $this->makeTableWithComparisons('label', 'Search &amp; Social');

        $filter = new LabelFilter(false, false, ['compare' => 1, 'labelSeries' => '0']);
        $result = $filter->filter('Search &amp; Social', $table);

        $this->assertSame(1, $result->getRowsCount());
        $this->assertSame('Search &amp; Social (Segment X)', $result->getFirstRow()->getColumn('label'));
    }

    public function testFilterKeepsTheMatchedRowWhenLabelSeriesDoesNotSelectAComparisonRow()
    {
        $table = $this->makeTableWithComparisons('customLabel', 'the raw label <"');

        $filter = new LabelFilter(false, false, ['compare' => 1, 'labelSeries' => '99'], 'customLabel');
        $result = $filter->filter('the raw label <"', $table);

        $this->assertSame(1, $result->getRowsCount());
        $this->assertSame('the sanitized label', $result->getFirstRow()->getColumn('label'));
    }

    public function testPruneLoadedSubtableKeepsOnlyTheRowTheDescentIsLookingFor()
    {
        $table = $this->makeTableWithLabels(['other', 'wanted', 'another']);
        $filter = new LabelFilter();

        $pruned = $this->pruneFor($filter, $table, 'wanted');

        $this->assertSame(1, $pruned->getRowsCount());
        $this->assertSame('wanted', $pruned->getFirstRow()->getColumn('label'));
    }

    public function testPruneLoadedSubtableKeepsTheWholeTableWhenTheLabelDoesNotMatchYet()
    {
        $table = $this->makeTableWithLabels(['other', 'another']);
        $filter = new LabelFilter();

        $pruned = $this->pruneFor($filter, $table, 'only built during post processing');

        $this->assertSame(2, $pruned->getRowsCount());
    }

    public function testPruneLoadedSubtableLeavesTheTableAloneWhenNoDescentIsInProgress()
    {
        $table = $this->makeTableWithLabels(['other', 'another']);
        $filter = new LabelFilter();

        $pruned = $this->pruneFor($filter, $table, null);

        $this->assertSame(2, $pruned->getRowsCount());
    }

    /**
     * @dataProvider getFiltersThatDependOnTheOtherRows
     */
    public function testPruneLoadedSubtableKeepsTheWholeTableForAFilterThatDependsOnTheOtherRows(array $request)
    {
        $table = $this->makeTableWithLabels(['other', 'wanted', 'another']);
        $filter = new LabelFilter();

        $pruned = $this->pruneFor($filter, $table, 'wanted', $request);

        $this->assertSame(3, $pruned->getRowsCount());
    }

    public function getFiltersThatDependOnTheOtherRows(): array
    {
        return [
            'exclude low population' => [['filter_excludelowpop' => 'nb_visits']],
            'truncate' => [['filter_truncate' => 5]],
            // zero truncates down to the summary row alone, so it drops rows like any other value
            'truncate to nothing' => [['filter_truncate' => 0]],
            'truncate to nothing as a string' => [['filter_truncate' => '0']],
            'offset' => [['filter_offset' => 1]],
            'limit' => [['filter_limit' => 10]],
            // a pattern can drop the rows kept for the whole table checks
            'pattern' => [['filter_pattern' => 'wanted']],
            // zero is a pattern too, it matches every label with a 0 in it
            'pattern of zero' => [['filter_pattern' => '0']],
            // the rows without visits are deleted, and one of them can be a row kept for the whole table checks
            'rows without visits deleted' => [['filter_add_columns_when_show_all_columns' => '1']],
            'label column hidden' => [['hideColumns' => 'nb_visits,label']],
        ];
    }

    /**
     * @dataProvider getFiltersThatDoNotDependOnTheOtherRows
     */
    public function testPruneLoadedSubtablePrunesForAFilterThatDoesNotDependOnTheOtherRows(array $request)
    {
        $table = $this->makeTableWithLabels(['other', 'wanted', 'another']);
        $filter = new LabelFilter();

        $pruned = $this->pruneFor($filter, $table, 'wanted', $request);

        $this->assertSame(1, $pruned->getRowsCount());
    }

    public function getFiltersThatDoNotDependOnTheOtherRows(): array
    {
        return [
            // the value row evolution sends, an unlimited request cannot drop the row we are after
            'unlimited' => [['filter_limit' => -1]],
            'unlimited as a string' => [['filter_limit' => '-1']],
            'no value to exclude on' => [['filter_excludelowpop' => '']],
            'a sort is per row' => [['filter_sort_column' => 'nb_visits']],
            // the value row evolution sends, which keeps the rows without visits
            'rows without visits kept' => [['filter_add_columns_when_show_all_columns' => '0']],
            'another column hidden' => [['hideColumns' => 'nb_visits']],
            // the label column is always among the columns shown
            'columns shown' => [['showColumns' => 'nb_visits']],
        ];
    }

    /**
     * @dataProvider getLabelsThatAreEqualOnceDecoded
     */
    public function testPruneLoadedSubtableKeepsTheWholeTableWhenAnotherLabelMatchesOnceDecoded(array $labels)
    {
        $table = $this->makeTableWithLabels(array_merge(['other'], $labels));
        $filter = new LabelFilter();

        $pruned = $this->pruneFor($filter, $table, 'a & b');

        $this->assertSame(3, $pruned->getRowsCount());
    }

    public function getLabelsThatAreEqualOnceDecoded(): array
    {
        return [
            'html encoded' => [['a &amp; b', 'a & b']],
            'url encoded' => [['a%20%26%20b', 'a & b']],
        ];
    }

    /**
     * @dataProvider getQueuedFiltersThatMayChangeLabels
     */
    public function testPruneLoadedSubtableKeepsTheWholeTableWhenAQueuedFilterMayChangeLabels($className, array $parameters)
    {
        $table = $this->makeTableWithLabels(['other', 'wanted', 'another']);
        $table->queueFilter($className, $parameters);
        $filter = new LabelFilter();

        $pruned = $this->pruneFor($filter, $table, 'wanted');

        $this->assertSame(3, $pruned->getRowsCount());
    }

    public function getQueuedFiltersThatMayChangeLabels(): array
    {
        $callback = function ($value) {
            return $value;
        };

        return [
            'a filter replacing column values' => ['ColumnCallbackReplace', [['label'], $callback]],
            'a filter from a plugin' => ['Piwik\Plugins\Referrers\DataTable\Filter\KeywordNotDefined', []],
            'metadata added under the label column' => ['ColumnCallbackAddMetadata', ['label', 'label', $callback]],
            'the label column deleted' => ['ColumnDelete', [['label']]],
            'the label column deleted from a list' => ['ColumnDelete', ['nb_visits,label']],
            'the label column renamed' => ['ReplaceColumnNames', [['label' => 'renamed']]],
            'another column renamed to the label column' => ['ReplaceColumnNames', [['url' => 'label']]],
            // a callable cannot be inspected, so it may do anything
            'a callable' => [function (DataTable $table) {
            }, []],
        ];
    }

    /**
     * @dataProvider getQueuedFiltersThatKeepLabels
     */
    public function testPruneLoadedSubtablePrunesWhenTheQueuedFiltersKeepLabels($className, array $parameters)
    {
        $table = $this->makeTableWithLabels(['other', 'wanted', 'another']);
        $table->queueFilter($className, $parameters);
        $filter = new LabelFilter();

        $pruned = $this->pruneFor($filter, $table, 'wanted');

        $this->assertSame(1, $pruned->getRowsCount());
    }

    public function getQueuedFiltersThatKeepLabels(): array
    {
        $callback = function ($value) {
            return $value;
        };

        return [
            'by short name' => ['ColumnDelete', [['nb_visits']]],
            'by full name' => ['Piwik\DataTable\Filter\ReplaceColumnNames', []],
            'metadata added under another name' => ['ColumnCallbackAddMetadata', ['label', 'url', $callback]],
            // the columns to keep never include the label column, but it is kept anyway
            'columns to keep' => ['ColumnDelete', [[], ['nb_visits']]],
            'columns to keep with nothing to delete' => ['ColumnDelete', [false, ['nb_visits']]],
            'another column renamed' => ['ReplaceColumnNames', [['nb_hits' => 'hits']]],
        ];
    }

    public function testPruneLoadedSubtableKeepsTheWholeTableWhenColumnsToKeepLeaveOutTheRowIdentifier()
    {
        // only the label column is kept whatever the columns to keep are, a row identifier is not
        $table = new DataTable();
        foreach (['other', 'wanted', 'another'] as $id) {
            $table->addRow(new Row([Row::COLUMNS => ['label' => 'crash', 'idlogcrash' => $id, 'nb_visits' => 1]]));
        }
        $table->queueFilter('ColumnDelete', [[], ['nb_visits']]);
        $filter = new LabelFilter(false, false, [], 'idlogcrash');

        $pruned = $this->pruneFor($filter, $table, 'wanted');

        $this->assertSame(3, $pruned->getRowsCount());
    }

    public function testPruneLoadedSubtableKeepsTheWholeTableWhenTheColumnsShownLeaveOutTheRowIdentifier()
    {
        $table = new DataTable();
        foreach (['other', 'wanted', 'another'] as $id) {
            $table->addRow(new Row([Row::COLUMNS => ['label' => 'crash', 'idlogcrash' => $id, 'nb_visits' => 1]]));
        }
        $filter = new LabelFilter(false, false, [], 'idlogcrash');

        $pruned = $this->pruneFor($filter, $table, 'wanted', ['showColumns' => 'nb_visits']);

        $this->assertSame(3, $pruned->getRowsCount());
    }

    public function testPruneLoadedSubtableKeepsTheWholeTableForAMetricThatLooksAtEveryRow()
    {
        // the share of visits is computed against the sum of visits over the table
        $table = $this->makeTableWithLabels(['other', 'wanted', 'another']);
        $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, [new VisitsPercent()]);
        $filter = new LabelFilter();

        $pruned = $this->pruneFor($filter, $table, 'wanted', [], true);

        $this->assertSame(3, $pruned->getRowsCount());
    }

    public function testPruneLoadedSubtableIgnoresAMetricThatLooksAtEveryRowWhenTheDescentGoesDeeper()
    {
        // only the subtable id of the row matters when the descent goes deeper
        $table = $this->makeTableWithLabels(['other', 'wanted', 'another']);
        $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, [new VisitsPercent()]);
        $filter = new LabelFilter();

        $pruned = $this->pruneFor($filter, $table, 'wanted');

        $this->assertSame(1, $pruned->getRowsCount());
    }

    public function testPruneLoadedSubtableKeepsARowWithAValueForEachColumnTheWantedRowLacks()
    {
        $table = $this->makeTableWithRows([
            'wanted' => ['sum_time_network' => 0],
            // a numeric string can hold no value too
            'zero as text' => ['sum_time_network' => '0.0'],
            'timed' => ['sum_time_network' => 5],
            'timed too' => ['sum_time_network' => 7],
        ]);
        $filter = new LabelFilter();

        $pruned = $this->pruneFor($filter, $table, 'wanted', [], true);

        $this->assertSame(['wanted', 'timed'], $pruned->getColumn('label'));
    }

    public function testPruneLoadedSubtableKeepsARowForEachGoalTheWantedRowLacks()
    {
        // a goal counts whatever its values are, as that is how the goal columns get added
        $table = $this->makeTableWithRows([
            'wanted' => ['goals' => ['idgoal=1' => ['nb_conversions' => 1]]],
            'same goal' => ['goals' => ['idgoal=1' => ['nb_conversions' => 3]]],
            'other goal' => ['goals' => ['idgoal=2' => ['nb_conversions' => 0]]],
        ]);
        $filter = new LabelFilter();

        $pruned = $this->pruneFor($filter, $table, 'wanted', [], true);

        $this->assertSame(['wanted', 'other goal'], $pruned->getColumn('label'));
    }

    public function testPruneLoadedSubtableKeepsOnlyTheWantedRowWhenTheDescentGoesDeeper()
    {
        $table = $this->makeTableWithRows([
            'wanted' => ['sum_time_network' => 0],
            'timed' => ['sum_time_network' => 5],
        ]);
        $filter = new LabelFilter();

        $pruned = $this->pruneFor($filter, $table, 'wanted');

        $this->assertSame(['wanted'], $pruned->getColumn('label'));
    }

    /**
     * The label the descent is after is remembered on the filter for the duration of the subtable
     * load, so a test has to set it the same way doFilterRecursiveDescend() does.
     */
    private function pruneFor(
        LabelFilter $filter,
        DataTable $table,
        ?string $labelPart,
        array $request = [],
        bool $labelPartIsLast = false
    ): DataTable {
        $class = new \ReflectionClass(LabelFilter::class);

        $property = $class->getProperty('nextLabelPart');
        $property->setAccessible(true);
        $property->setValue($filter, $labelPart);

        $property = $class->getProperty('nextLabelPartIsLast');
        $property->setAccessible(true);
        $property->setValue($filter, $labelPartIsLast);

        $method = $class->getMethod('pruneLoadedSubtable');
        $method->setAccessible(true);

        return $method->invoke($filter, $table, $request, '', '');
    }

    private function makeTableWithLabels(array $labels): DataTable
    {
        $table = new DataTable();
        foreach ($labels as $label) {
            $table->addRow(new Row([Row::COLUMNS => ['label' => $label, 'nb_visits' => 1]]));
        }

        return $table;
    }

    private function makeTableWithRows(array $columnsByLabel): DataTable
    {
        $table = new DataTable();
        foreach ($columnsByLabel as $label => $columns) {
            $table->addRow(new Row([Row::COLUMNS => ['label' => $label, 'nb_visits' => 1] + $columns]));
        }

        return $table;
    }

    /**
     * Builds a single row table whose row is identified by $labelColumn and carries one comparison
     * row. When $labelColumn is not 'label' the value is only available as row metadata, which is
     * how a report specific row identifier reaches LabelFilter.
     */
    private function makeTableWithComparisons(string $labelColumn, string $labelColumnValue): DataTable
    {
        // comparison rows only carry numeric metrics, ComparisonRowGenerator skips the label
        // column, which is why LabelFilter has to add one
        $comparisonRow = new Row([Row::COLUMNS => ['nb_visits' => 5]]);
        $comparisonRow->setMetadata('compareSeriesPretty', '(Segment X)');

        $comparisons = new DataTable();
        $comparisons->addRow($comparisonRow);

        $columns = ['label' => 'the sanitized label', 'nb_visits' => 3];
        if ($labelColumn === 'label') {
            $columns['label'] = $labelColumnValue;
        }

        $row = new Row([Row::COLUMNS => $columns]);
        if ($labelColumn !== 'label') {
            $row->setMetadata($labelColumn, $labelColumnValue);
        }
        $row->setComparisons($comparisons);

        $table = new DataTable();
        $table->addRow($row);

        return $table;
    }
}
