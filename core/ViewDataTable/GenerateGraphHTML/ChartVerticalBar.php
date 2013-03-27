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
 *
 * Generates HTML embed for the vertical bar chart
 *
 * @package Piwik
 * @subpackage Piwik_ViewDataTable
 */

class Piwik_ViewDataTable_GenerateGraphHTML_ChartVerticalBar extends Piwik_ViewDataTable_GenerateGraphHTML
{

    protected $graphType = 'bar';

    protected function getViewDataTableId()
    {
        return 'graphVerticalBar';
    }

    protected function getViewDataTableIdToLoad()
    {
        return 'generateDataChartVerticalBar';
    }
}
