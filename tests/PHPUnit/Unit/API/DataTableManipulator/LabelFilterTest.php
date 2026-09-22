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
        ];
    }

    /**
     * The label the descent is after is remembered on the filter for the duration of the subtable
     * load, so a test has to set it the same way doFilterRecursiveDescend() does.
     */
    private function pruneFor(
        LabelFilter $filter,
        DataTable $table,
        ?string $labelPart,
        array $request = []
    ): DataTable {
        $class = new \ReflectionClass(LabelFilter::class);

        $property = $class->getProperty('nextLabelPart');
        $property->setAccessible(true);
        $property->setValue($filter, $labelPart);

        $method = $class->getMethod('pruneLoadedSubtable');
        $method->setAccessible(true);

        return $method->invoke($filter, $table, $request);
    }

    private function makeTableWithLabels(array $labels): DataTable
    {
        $table = new DataTable();
        foreach ($labels as $label) {
            $table->addRow(new Row([Row::COLUMNS => ['label' => $label, 'nb_visits' => 1]]));
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
