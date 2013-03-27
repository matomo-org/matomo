<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Reads the requested DataTable from the API and prepare data for the Sparkline view.
 *
 * @package Piwik
 * @subpackage Piwik_ViewDataTable
 */
class Piwik_ViewDataTable_Sparkline extends Piwik_ViewDataTable
{
    protected function getViewDataTableId()
    {
        return 'sparkline';
    }

    /**
     * @see Piwik_ViewDataTable::main()
     * @return mixed
     */
    public function main()
    {
        if ($this->mainAlreadyExecuted) {
            return;
        }
        $this->mainAlreadyExecuted = true;

        // If period=range, we force the sparkline to draw daily data points
        $period = Piwik_Common::getRequestVar('period');
        if ($period == 'range') {
            $_GET['period'] = 'day';
        }
        $this->loadDataTableFromAPI();
        // then revert the hack for potentially subsequent getRequestVar
        $_GET['period'] = $period;

        $values = $this->getValuesFromDataTable($this->dataTable);
        $this->isDataAvailable = true;
        if (empty($values)) {
            $values = array_fill(0, 30, 0);
            $this->isDataAvailable = false;
        }

        $graph = new Piwik_Visualization_Sparkline();
        $graph->setValues($values);

        $height = Piwik_Common::getRequestVar('height', 0, 'int');
        if (!empty($height)) {
            $graph->setHeight($height);
        }

        $width = Piwik_Common::getRequestVar('width', 0, 'int');
        if (!empty($width)) {
            $graph->setWidth($width);
        }

        $graph->main();

        $this->view = $graph;
    }

    protected function getValuesFromDataTableArray($dataTableArray, $columnToPlot)
    {
        $dataTableArray->applyQueuedFilters();
        $values = array();
        foreach ($dataTableArray->getArray() as $table) {
            if ($table->getRowsCount() > 1) {
                throw new Exception("Expecting only one row per DataTable");
            }
            $value = 0;
            $onlyRow = $table->getFirstRow();
            if ($onlyRow !== false) {
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
        $columns = $this->getColumnsToDisplay();
        $columnToPlot = false;
        if (!empty($columns)) {
            $columnToPlot = $columns[0];
        }
        $values = false;
        // a Piwik_DataTable_Array is returned when using the normal code path to request data from Archives, in all core plugins
        // however plugins can also return simple datatable, hence why the sparkline can accept both data types
        if ($this->dataTable instanceof Piwik_DataTable_Array) {
            $values = $this->getValuesFromDataTableArray($dataTable, $columnToPlot);
        } elseif ($this->dataTable instanceof Piwik_DataTable) {
            $values = $this->dataTable->getColumn($columnToPlot);
        }
        return $values;
    }
}
