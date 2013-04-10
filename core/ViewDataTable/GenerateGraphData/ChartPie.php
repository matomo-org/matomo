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
 * Piwik_ViewDataTable_GenerateGraphData for the pie chart, using Piwik_Visualization_Chart_Pie
 *
 * @package Piwik
 * @subpackage Piwik_ViewDataTable
 */
class Piwik_ViewDataTable_GenerateGraphData_ChartPie extends Piwik_ViewDataTable_GenerateGraphData
{
    protected $graphLimit = 6;

    protected function getViewDataTableId()
    {
        return 'generateDataChartPie';
    }

    function __construct()
    {
        $this->view = new Piwik_Visualization_Chart_Pie();
    }

    /**
     * Manipulate the configuration of the series picker since only one metric is selectable
     * for pie charts
     * @param bool $multiSelect
     */
    protected function addSeriesPickerToView($multiSelect = false)
    {
        // force $multiSelect=false
        parent::addSeriesPickerToView(false);
    }

}
