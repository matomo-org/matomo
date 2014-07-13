<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreVisualizations\Visualizations;

use Exception;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Plugin\ViewDataTable;

/**
 * Reads the requested DataTable from the API and prepare data for the Sparkline view.
 *
 */
class Sparkline extends ViewDataTable
{
    const ID = 'sparkline';

    /**
     * @see ViewDataTable::main()
     * @return mixed
     */
    protected function buildView()
    {
        // If period=range, we force the sparkline to draw daily data points
        $period = Common::getRequestVar('period');
        if ($period == 'range') {
            $_GET['period'] = 'day';
        }

        $this->loadDataTableFromAPI();

        // then revert the hack for potentially subsequent getRequestVar
        $_GET['period'] = $period;

        $values = $this->getValuesFromDataTable($this->dataTable);
        if (empty($values)) {
            $values = array_fill(0, 30, 0);
        }

        $graph = new \Piwik\Visualization\Sparkline();
        $graph->setValues($values);

        $height = Common::getRequestVar('height', 0, 'int');
        if (!empty($height)) {
            $graph->setHeight($height);
        }

        $width = Common::getRequestVar('width', 0, 'int');
        if (!empty($width)) {
            $graph->setWidth($width);
        }

        $graph->main();

        return $graph;
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

    protected function getValuesFromDataTable($dataTable)
    {
        $columns = $this->config->columns_to_display;

        $columnToPlot = false;

        if (!empty($columns)) {
            $columnToPlot = reset($columns);
            if ($columnToPlot == 'label') {
                $columnToPlot = next($columns);
            }
        }

        // a Set is returned when using the normal code path to request data from Archives, in all core plugins
        // however plugins can also return simple datatable, hence why the sparkline can accept both data types
        if ($this->dataTable instanceof DataTable\Map) {
            $values = $this->getValuesFromDataTableMap($dataTable, $columnToPlot);
        } elseif ($this->dataTable instanceof DataTable) {
            $values = $this->dataTable->getColumn($columnToPlot);
        } else {
            $values = false;
        }

        return $values;
    }
}
