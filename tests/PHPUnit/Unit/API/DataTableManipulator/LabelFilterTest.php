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
