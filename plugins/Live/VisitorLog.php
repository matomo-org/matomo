<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Live
 */
namespace Piwik\Plugins\Live;

use Piwik\View;
use Piwik\DataTableVisualization;

/**
 * A special DataTable visualization for the Live.getLastVisitsDetails API method.
 */
class VisitorLog extends DataTableVisualization
{
    static public $clientSideParameters = array(
        'filter_limit',
        'filter_offset',
        'filter_sort_column',
        'filter_sort_order',
    );

    /**
     * Constructor.
     */
    public function __construct($view)
    {
        $view->datatable_js_type = 'VisitorLog';
    }

    /**
     * Renders this visualization.
     *
     * @param DataTable $dataTable
     * @param array $properties View Properties.
     * @return string
     */
    public function render($dataTable, $properties, $javascriptVariablesToSet)
    {
        $view = new View("@Live/_dataTableViz_visitorLog.twig");
        $view->properties = $properties;
        $view->dataTable = $dataTable;
        $view->javascriptVariablesToSet = $javascriptVariablesToSet;
        return $view->render();
    }
}