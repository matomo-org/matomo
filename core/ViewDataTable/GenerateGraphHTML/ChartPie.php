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
namespace Piwik\ViewDataTable\GenerateGraphHTML;

use Piwik\ViewDataTable\GenerateGraphHTML;

/**
 * Generates HTML embed for the Pie chart
 *
 * @package Piwik
 * @subpackage ViewDataTable
 */
class ChartPie extends GenerateGraphHTML
{
    public function __construct()
    {
        parent::__construct();
        $this->viewProperties['graph_type'] = 'pie';
        $this->viewProperties['graph_limit'] = 6;
        $this->viewProperties['allow_multi_select_series_picker'] = false;
    }

    protected function getViewDataTableId()
    {
        return 'graphPie';
    }

    protected function getViewDataTableIdToLoad()
    {
        return 'generateDataChartPie';
    }
}
