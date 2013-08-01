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
use Piwik\Config;
use Piwik\Common;

/**
 * DataTable visualization that shows DataTable data in an HTML table.
 */
class HtmlTable
{
    /**
     * TODO
     */
    public function getJavaScriptProperties()
    {
        return array('search_recursive');
    }

    /**
     * TODO
     * @deprecated
     */
    public function getViewDataTableId()
    {
        return 'table';
    }

    /**
     * Renders this visualization.
     *
     * @param DataTable $dataTable
     * @param array $properties View Properties.
     * @return string
     */
    public function render(DataTable $dataTable, $properties)
    {
        $view = new View("@CoreHome/_dataTableViz_htmlTable.twig");
        $view->properties = $properties;
        $view->dataTable = $dataTable;
        return $view->render();
    }

    /**
     * TODO
     */
    public function getDefaultPropertyValues()
    {
        $defaults = array(
            'enable_sort' => true,
            'disable_row_evolution' => false,
            'disable_row_actions' => false,
            'subtable_template' => "@CoreHome/_dataTable.twig",
            'datatable_js_type' => 'dataTable'
        );

        $defaultLimit = Config::getInstance()->General['datatable_default_limit'];
        if ($defaultLimit !== 0) {
            $defaults['filter_limit'] = $defaultLimit;
        }

        if (Common::getRequestVar('enable_filter_excludelowpop', false) == '1') {
            $defaults['filter_excludelowpop'] = 'nb_visits';
            $defaults['filter_excludelowpop_value'] = null;
        }

        return $defaults;
    }
}