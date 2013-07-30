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
 * DataTable visualization that shows DataTable data in an HTML table.
 */
class Piwik_Visualization_HtmlTable
{
    /**
     * Renders this visualization.
     * 
     * @param Piwik_DataTable $dataTable
     * @param array $properties View Properties.
     */
    public function render($dataTable, $properties)
    {
        $view = new Piwik_View("@CoreHome/_dataTableViz_htmlTable.twig");
        $view->properties = $properties;
        $view->dataTable = $dataTable;
        return $view->render();
    }
}