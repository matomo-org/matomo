<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\API\DataTableManipulator;

use Piwik\API\DataTableManipulator\SubtablePruner;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\CoreHome\Columns\Metrics\VisitsPercent;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeNetwork;

class SubtablePrunerTest extends \PHPUnit\Framework\TestCase
{
    public function testPruneKeepsOnlyTheRowTheSearchIsLookingFor(): void
    {
        $table = $this->makeTableWithLabels(['other', 'wanted', 'another']);

        $pruned = $this->prune($table, ['wanted']);

        $this->assertSame(1, $pruned->getRowsCount());
        $this->assertSame('wanted', $pruned->getFirstRow()->getColumn('label'));
    }

    public function testPruneKeepsTheWholeTableWhenTheLabelDoesNotMatchYet(): void
    {
        $table = $this->makeTableWithLabels(['other', 'another']);

        $pruned = $this->prune($table, ['only built during post processing']);

        $this->assertSame(2, $pruned->getRowsCount());
    }

    /**
     * @dataProvider getFiltersThatDependOnTheOtherRows
     */
    public function testPruneKeepsTheWholeTableForAFilterThatDependsOnTheOtherRows(array $request): void
    {
        $table = $this->makeTableWithLabels(['other', 'wanted', 'another']);

        $pruned = $this->prune($table, ['wanted'], $request);

        $this->assertSame(3, $pruned->getRowsCount());
    }

    public function getFiltersThatDependOnTheOtherRows(): array
    {
        return [
            'exclude low population' => [['filter_excludelowpop' => 'nb_visits']],
            'truncate' => [['filter_truncate' => 5]],
            // a truncate of 0 still removes every row but the summary row
            'truncate to nothing' => [['filter_truncate' => 0]],
            'truncate to nothing as a string' => [['filter_truncate' => '0']],
            'offset' => [['filter_offset' => 1]],
            'limit' => [['filter_limit' => 10]],
            // a pattern can remove the rows kept for the checks
            'pattern' => [['filter_pattern' => 'wanted']],
            // "0" is a pattern too
            'pattern of zero' => [['filter_pattern' => '0']],
            // removes rows without visits, which can be rows kept for the checks
            'rows without visits deleted' => [['filter_add_columns_when_show_all_columns' => '1']],
            'label column hidden' => [['hideColumns' => 'nb_visits,label']],
        ];
    }

    /**
     * @dataProvider getFiltersThatDoNotDependOnTheOtherRows
     */
    public function testPrunePrunesForAFilterThatDoesNotDependOnTheOtherRows(array $request): void
    {
        $table = $this->makeTableWithLabels(['other', 'wanted', 'another']);

        $pruned = $this->prune($table, ['wanted'], $request);

        $this->assertSame(1, $pruned->getRowsCount());
    }

    public function getFiltersThatDoNotDependOnTheOtherRows(): array
    {
        return [
            // what row evolution sends: no limit
            'unlimited' => [['filter_limit' => -1]],
            'unlimited as a string' => [['filter_limit' => '-1']],
            'no value to exclude on' => [['filter_excludelowpop' => '']],
            'a sort is per row' => [['filter_sort_column' => 'nb_visits']],
            // what row evolution sends: keep rows without visits
            'rows without visits kept' => [['filter_add_columns_when_show_all_columns' => '0']],
            'another column hidden' => [['hideColumns' => 'nb_visits']],
            // the label column is always shown
            'columns shown' => [['showColumns' => 'nb_visits']],
        ];
    }

    /**
     * @dataProvider getLabelsThatAreEqualOnceDecoded
     */
    public function testPruneKeepsTheWholeTableWhenAnotherLabelMatchesOnceDecoded(array $labels): void
    {
        $table = $this->makeTableWithLabels(array_merge(['other'], $labels));

        // what LabelFilter tries for 'a & b'
        $pruned = $this->prune($table, ['a &amp; b', 'a & b']);

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
    public function testPruneKeepsTheWholeTableWhenAQueuedFilterMayChangeLabels(string|\Closure $className, array $parameters): void
    {
        $table = $this->makeTableWithLabels(['other', 'wanted', 'another']);
        $table->queueFilter($className, $parameters);

        $pruned = $this->prune($table, ['wanted']);

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
            // we can't tell what a callable does
            'a callable' => [function (DataTable $table) {
            }, []],
        ];
    }

    /**
     * @dataProvider getQueuedFiltersThatKeepLabels
     */
    public function testPrunePrunesWhenTheQueuedFiltersKeepLabels(string $className, array $parameters): void
    {
        $table = $this->makeTableWithLabels(['other', 'wanted', 'another']);
        $table->queueFilter($className, $parameters);

        $pruned = $this->prune($table, ['wanted']);

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
            // ColumnDelete always keeps the label column
            'columns to keep' => ['ColumnDelete', [[], ['nb_visits']]],
            'columns to keep with nothing to delete' => ['ColumnDelete', [false, ['nb_visits']]],
            'another column renamed' => ['ReplaceColumnNames', [['nb_hits' => 'hits']]],
        ];
    }

    public function testPruneKeepsTheWholeTableWhenColumnsToKeepLeaveOutTheRowIdentifier(): void
    {
        // ColumnDelete always keeps "label", but not another row identifier
        $table = $this->makeTableWithCrashIds();
        $table->queueFilter('ColumnDelete', [[], ['nb_visits']]);

        $pruned = $this->prune($table, ['wanted'], [], false, 'idlogcrash');

        $this->assertSame(3, $pruned->getRowsCount());
    }

    public function testPruneKeepsTheWholeTableWhenTheColumnsShownLeaveOutTheRowIdentifier(): void
    {
        $table = $this->makeTableWithCrashIds();

        $pruned = $this->prune($table, ['wanted'], ['showColumns' => 'nb_visits'], false, 'idlogcrash');

        $this->assertSame(3, $pruned->getRowsCount());
    }

    public function testPruneKeepsTheWholeTableForAMetricThatLooksAtEveryRow(): void
    {
        // the share of visits needs the visits of every row
        $table = $this->makeTableWithLabels(['other', 'wanted', 'another']);
        $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, [new VisitsPercent()]);

        $pruned = $this->prune($table, ['wanted'], [], true);

        $this->assertSame(3, $pruned->getRowsCount());
    }

    /**
     * @dataProvider getRowsDisagreeingOnTheAverageColumns
     */
    public function testPruneKeepsTheWholeTableWhenTheRowsDisagreeOnTheAverageColumns(array $otherColumns): void
    {
        // the average time metrics read the top row, and pruning can change it
        $table = $this->makeTableWithRows([
            'other' => $otherColumns,
            'wanted' => ['sum_time_network' => 2],
            'another' => ['sum_time_network' => 5],
        ]);
        $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, [new AverageTimeNetwork()]);

        $pruned = $this->prune($table, ['wanted'], [], true);

        $this->assertSame(3, $pruned->getRowsCount());
    }

    public function getRowsDisagreeingOnTheAverageColumns(): iterable
    {
        yield 'no sum column' => [[]];
        yield 'an average column' => [['sum_time_network' => 1, 'avg_time_network' => 0.5]];
    }

    public function testPrunePrunesWhenTheRowsAgreeOnTheAverageColumns(): void
    {
        $table = $this->makeTableWithRows([
            'other' => ['sum_time_network' => 1],
            'wanted' => ['sum_time_network' => 2],
            'another' => ['sum_time_network' => 5],
        ]);
        $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, [new AverageTimeNetwork()]);

        $pruned = $this->prune($table, ['wanted'], [], true);

        $this->assertSame(['other', 'wanted'], $pruned->getColumn('label'));
    }

    public function testPruneIgnoresAMetricThatLooksAtEveryRowWhenTheSearchGoesDeeper(): void
    {
        // when we go deeper only the row's subtable id matters
        $table = $this->makeTableWithLabels(['other', 'wanted', 'another']);
        $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, [new VisitsPercent()]);

        $pruned = $this->prune($table, ['wanted']);

        $this->assertSame(1, $pruned->getRowsCount());
    }

    public function testPruneKeepsARowWithAValueForEachColumnTheWantedRowLacks(): void
    {
        $table = $this->makeTableWithRows([
            'wanted' => ['sum_time_network' => 0],
            // a numeric string can be empty too
            'zero as text' => ['sum_time_network' => '0.0'],
            'timed' => ['sum_time_network' => 5],
            'timed too' => ['sum_time_network' => 7],
        ]);

        $pruned = $this->prune($table, ['wanted'], [], true);

        $this->assertSame(['wanted', 'timed'], $pruned->getColumn('label'));
    }

    public function testPruneKeepsARowForEachGoalTheWantedRowLacks(): void
    {
        // a goal counts even with zero conversions, since it still gets its columns
        $table = $this->makeTableWithRows([
            'wanted' => ['goals' => ['idgoal=1' => ['nb_conversions' => 1]]],
            'same goal' => ['goals' => ['idgoal=1' => ['nb_conversions' => 3]]],
            'other goal' => ['goals' => ['idgoal=2' => ['nb_conversions' => 0]]],
        ]);

        $pruned = $this->prune($table, ['wanted'], [], true);

        $this->assertSame(['wanted', 'other goal'], $pruned->getColumn('label'));
    }

    public function testPruneKeepsOnlyTheWantedRowWhenTheSearchGoesDeeper(): void
    {
        $table = $this->makeTableWithRows([
            'wanted' => ['sum_time_network' => 0],
            'timed' => ['sum_time_network' => 5],
        ]);

        $pruned = $this->prune($table, ['wanted']);

        $this->assertSame(['wanted'], $pruned->getColumn('label'));
    }

    private function prune(
        DataTable $table,
        array $labelVariations,
        array $request = [],
        bool $isLastLabelPart = false,
        string $labelColumn = 'label'
    ): DataTable {
        return (new SubtablePruner($labelColumn))->prune($table, $request, '', '', $labelVariations, $isLastLabelPart);
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

    private function makeTableWithCrashIds(): DataTable
    {
        $table = new DataTable();
        foreach (['other', 'wanted', 'another'] as $id) {
            $table->addRow(new Row([Row::COLUMNS => ['label' => 'crash', 'idlogcrash' => $id, 'nb_visits' => 1]]));
        }

        return $table;
    }
}
