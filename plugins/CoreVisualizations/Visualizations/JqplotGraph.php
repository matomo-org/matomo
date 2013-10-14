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

    /**
     * The name of the JavaScript class to use as this graph's external series toggle. The class
     * must be a subclass of JQPlotExternalSeriesToggle.
     *
     * @see self::EXTERNAL_SERIES_TOGGLE_SHOW_ALL
     *
     * Default value: false
     */
    const EXTERNAL_SERIES_TOGGLE = 'external_series_toggle';

    /**
     * Whether the graph should show all loaded series upon initial display.
     *
     * @see self::EXTERNAL_SERIES_TOGGLE
     *
     * Default value: false
     */
    const EXTERNAL_SERIES_TOGGLE_SHOW_ALL = 'external_series_toggle_show_all';

    /**
     * The number of x-axis ticks for each x-axis label.
     *
     * Default: 2
     */
    const X_AXIS_STEP_SIZE = 'x_axis_step_size';

    public static $clientSideConfigProperties = array(
        'external_series_toggle',
        'external_series_toggle_show_all'
    );

    public static $overridableProperties = array('x_axis_step_size');

    /**
     * Returns an array mapping property names with default values for this visualization.
     *
     * @return array
     */
    public static function getDefaultPropertyValues()
    {
        $result = parent::getDefaultPropertyValues();
        return array_merge_recursive($result, array(
                                                   'show_offset_information'     => false,
                                                   'show_pagination_control'     => false,
                                                   'show_exclude_low_population' => false,
                                                   'show_search'                 => false,
                                                   'show_export_as_image_icon'   => true,
                                                   'y_axis_unit'                 => '',
                                                   'visualization_properties'    => array(
                                                       'jqplot_graph' => array(
                                                           'external_series_toggle'          => false,
                                                           'external_series_toggle_show_all' => false,
                                                           'x_axis_step_size'                => 2
                                                       )
                                                   )
                                              ));
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