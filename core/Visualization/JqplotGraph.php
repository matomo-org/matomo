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
 * DataTable visualization that displays DataTable data in a JQPlot graph.
 */
class Piwik_Visualization_JqplotGraph
{
    /**
     * Renders this visualization.
     * 
     * @param Piwik_DataTable $dataTable
     * @param array $properties View Properties.
     */
    public function render($dataTable, $properties)
    {
        $view = new Piwik_View("@CoreHome/_dataTableViz_jqplotGraph.twig");
        $view->properties = $properties;
        $view->dataTable = $dataTable;
        $view->data = $this->getGraphData($dataTable, $properties);
        return $view->render();
    }

    /**
     * Generats JQPlot graph data for a DataTable.
     */
    private function getGraphData($dataTable, $properties)
    {
        $properties = array_merge($properties, $properties['request_parameters_to_modify']);
        $dataGenerator = Piwik_JqplotDataGenerator::factory($properties['graph_type'], $properties);
        
        $jsonData = $dataGenerator->generate($dataTable);
        return str_replace(array("\r", "\n"), '', $jsonData);
    }
}