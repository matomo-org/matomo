<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package ExampleVisualization
 */

namespace Piwik\Plugins\ExampleVisualization;

use Piwik\DataTable;
use Piwik\DataTable\DataTableInterface;
use Piwik\Plugin\Visualization;
use Piwik\Visualization\Config;
use Piwik\Visualization\Request;

/**
 * SimpleTable Visualization.
 */
class SimpleTable extends Visualization
{
    const TEMPLATE_FILE     = '@ExampleVisualization/simpleTable.twig';
    const FOOTER_ICON_TITLE = 'Simple Table';
    const FOOTER_ICON       = 'plugins/ExampleVisualization/images/table.png';

    /**
     * You do not have to implement the init method. It is just an example how to assign view variables.
     */
    public function init()
    {
        $this->assignTemplateVar('vizTitle', 'MyAwesomeTitle');
    }

    public function configureVisualization()
    {
        // Configure how your visualization should look like, for instance you can disable search
        // $this->config->show_search = false
    }

    public function beforeLoadDataTable()
    {
        // Here you can change the request that is sent to the API, for instance
        // $this->requestConfig->filter_sort_order = 'desc';
    }

    public function beforeGenericFiltersAreAppliedToLoadedDataTable()
    {
        // this hook is executed before generic filters like "filter_limit" and "filter_offset" are applied
        // Usage:
        // $this->dateTable->filter($nameOrClosure);
    }

    public function afterGenericFiltersAreAppliedToLoadedDataTable()
    {
        // this hook is executed after generic filters like "filter_limit" and "filter_offset" are applied
        // Usage:
        // $this->dateTable->filter($nameOrClosure, $parameters);
    }

    public function afterAllFilteresAreApplied()
    {
        // this hook is executed after the data table is loaded and after all filteres are applied.
        // format your data here that you want to pass to the view
        // $this->myCustomViewVariable = $dataTable->getRows();
    }
}