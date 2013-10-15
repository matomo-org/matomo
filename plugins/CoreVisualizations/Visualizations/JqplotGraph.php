<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreVisualizations
 */
namespace Piwik\Plugins\CoreVisualizations\Visualizations;


use Piwik\DataTable;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;
use Piwik\View;
use Piwik\ViewDataTable\Graph;
use Piwik\Visualization\Config;
use Piwik\Visualization\Request;

/**
 * DataTable visualization that displays DataTable data in a JQPlot graph.
 * TODO: should merge all this logic w/ jqplotdatagenerator & 'Chart' visualizations.
 */
class JqplotGraph extends Graph
{
    const ID = 'jqplot_graph';
    const TEMPLATE_FILE = '@CoreVisualizations/_dataTableViz_jqplotGraph.twig';

    public static $clientSideConfigProperties = array(
        'external_series_toggle',
        'external_series_toggle_show_all'
    );

    public static $overridableProperties = array('x_axis_step_size');

    public function getDefaultConfig()
    {
        return new JqplotGraph\Config();
    }

    public function getGraphData($dataTable, $properties)
    {
        $dataGenerator = $this->makeDataGenerator($properties);
        return $dataGenerator->generate($dataTable);
    }
}

require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/JqplotGraph/Bar.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/JqplotGraph/Pie.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/JqplotGraph/Evolution.php';