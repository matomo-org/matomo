<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreVisualizations\Visualizations;

use Exception;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Period;
use Piwik\Plugin\ViewDataTable;

/**
 * Reads the requested DataTable from the API and prepare data for the Sparkline view.
 *
 */
class Sparkline extends ViewDataTable
{
    const ID = 'sparkline';

    public function supportsComparison()
    {
        return true;
    }

    /**
     * @see ViewDataTable::main()
     * @return mixed
     */
    public function render()
    {
        // If period=range, we force the sparkline to draw daily data points
        $period = Common::getRequestVar('period');
        $date = Common::getRequestVar('date');

        if ($period == 'range') {
            $periodObj = Period\Factory::build($period, $date);
            $_GET['period'] = 'day';
            $_GET['date'] = $periodObj->getRangeString();
        }

        if ($this->isComparing()) {
            $this->transformSingleComparisonPeriods();
        }

        $this->loadDataTableFromAPI();

        // then revert the hack for potentially subsequent getRequestVar
        $_GET['period'] = $period;
        $_GET['date'] = $date;

        $columnToPlot = $this->getColumnToPlot();

        $graph = new \Piwik\Visualization\Sparkline();

        if ($this->isComparing()) {
            $otherSeries = [];

            $comparisonSeries = $this->getComparisonSeries($this->dataTable);
            foreach ($comparisonSeries as $seriesName) {
                $otherSeries[$seriesName] = [];
            }

            $this->dataTable->filter(function (DataTable $table) use ($comparisonSeries, &$otherSeries, $columnToPlot) {
                foreach ($table->getRows() as $row) {
                    $comparisons = $row->getComparisons();
                    if (empty($comparisons)) {
                        continue;
                    }

                    foreach ($comparisons->getRows() as $comparisonRow) {
                        $compareSeriesPretty = $comparisonRow->getMetadata('compareSeriesPretty');
                        $otherSeries[$compareSeriesPretty][] = $comparisonRow->getColumn($columnToPlot);
                    }
                }
            });

            foreach ($otherSeries as $seriesValues) {
                $seriesValues = $this->ensureValuesEvenIfEmpty($seriesValues);
                $graph->addSeries($seriesValues);
            }
        } else {
            $values = $this->getValuesFromDataTable($this->dataTable, $columnToPlot);
            $values = $this->ensureValuesEvenIfEmpty($values);
            $graph->addSeries($values);
        }

        $height = Common::getRequestVar('height', 0, 'int');
        if (!empty($height)) {
            $graph->setHeight($height);
        }

        $width = Common::getRequestVar('width', 0, 'int');
        if (!empty($width)) {
            $graph->setWidth($width);
        }

        $graph->main();

        return $graph->render();
    }

    /**
     * @param DataTable\Map $dataTableMap
     * @param string $columnToPlot
     *
     * @return array
     * @throws \Exception
     */
    protected function getValuesFromDataTableMap($dataTableMap, $columnToPlot)
    {
        $dataTableMap->applyQueuedFilters();

        $values = array();

        foreach ($dataTableMap->getDataTables() as $table) {

            if ($table->getRowsCount() > 1) {
                throw new Exception("Expecting only one row per DataTable");
            }

            $value   = 0;
            $onlyRow = $table->getFirstRow();

            if (false !== $onlyRow) {
                if (!empty($columnToPlot)) {
                    $value = $onlyRow->getColumn($columnToPlot);
                } // if not specified, we load by default the first column found
                // eg. case of getLastDistinctCountriesGraph
                else {
                    $columns = $onlyRow->getColumns();
                    $value = current($columns);
                }
            }

            $values[] = $value;
        }

        return $values;
    }

    private function getColumnToPlot()
    {
        $columns = $this->config->columns_to_display;

        $columnToPlot = false;

        if (!empty($columns)) {
            $columnToPlot = reset($columns);
            if ($columnToPlot == 'label') {
                $columnToPlot = next($columns);
            }
        }

        return $columnToPlot;
    }

    protected function getValuesFromDataTable($dataTable, $columnToPlot)
    {
        // a Set is returned when using the normal code path to request data from Archives, in all core plugins
        // however plugins can also return simple datatable, hence why the sparkline can accept both data types
        if ($this->dataTable instanceof DataTable\Map) {
            $values = $this->getValuesFromDataTableMap($dataTable, $columnToPlot);
        } elseif ($this->dataTable instanceof DataTable) {
            $values = $this->dataTable->getColumn($columnToPlot);
        } else {
            $values = [];
        }

        return $values;
    }

    private function ensureValuesEvenIfEmpty(array $values)
    {
        if (empty($values)) {
            return array_fill(0, 30, 0);
        }
        return $values;
    }

    private function getComparisonSeries(DataTable\DataTableInterface $dataTable)
    {
        if ($dataTable instanceof DataTable\Map) {
            $tables = $dataTable->getDataTables();
            return reset($tables)->getMetadata('comparisonSeries') ?: [];
        } else {
            return $dataTable->getMetadata('comparisonSeries') ?: [];
        }
    }

    private function transformSingleComparisonPeriods()
    {
        $comparePeriods = Common::getRequestVar('comparePeriods', $default = [], $type = 'array');
        $compareDates = Common::getRequestVar('compareDates', $default = [], $type = 'array');

        foreach ($comparePeriods as $index => $comparePeriod) {
            $compareDate = $compareDates[$index];
            if (Period::isMultiplePeriod($compareDate, $comparePeriod)) {
                continue;
            }

            $periodObj = Period\Factory::build($comparePeriod, $compareDate);
            $comparePeriods[$index] = 'day';
            $compareDates[$index] = $periodObj->getRangeString();
        }

        $this->requestConfig->request_parameters_to_modify['comparePeriods'] = $comparePeriods;
        $this->requestConfig->request_parameters_to_modify['compareDates'] = $compareDates;
    }
}
