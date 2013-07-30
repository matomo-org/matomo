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

namespace Piwik\Visualization;

use Piwik\DataTable;
use Piwik\View;

/**
 * DataTable visualization that shows DataTable data in an HTML table.
 */
class HtmlTable
{
    /**
     * Renders this visualization.
     *
     * @param DataTable $dataTable
     * @param array $properties View Properties.
     */
    public function render(DataTable $dataTable, $properties)
    {
        $view = new View("@CoreHome/_dataTableViz_htmlTable.twig");
        $view->properties = $properties;
        $view->dataTable = $dataTable;
        return $view->render();
    }
}