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
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugin\Visualization;

/**
 * SimpleTable Visualization.
 */
class SimpleTable extends Visualization
{
    const ID = 'simpleTable';
    const TEMPLATE_FILE     = '@ExampleVisualization/simpleTable.twig';
    const FOOTER_ICON_TITLE = 'Simple Table';
    const FOOTER_ICON       = 'plugins/ExampleVisualization/images/table.png';

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

    public function afterAllFiltersAreApplied()
    {
        // this hook is executed after the data table is loaded and after all filteres are applied.
        // format your data here that you want to pass to the view

        $this->assignTemplateVar('vizTitle', 'MyAwesomeTitle');
    }

    public function beforeRender()
    {
        // Configure how your visualization should look like, for instance you can disable search
        // By defining the config properties shortly before rendering you make sure the config properties have a certain
        // value because they could be changed by a report or by request parameters ($_GET / $_POST) before.
        // $this->config->show_search = false
    }

    public static function canDisplayViewDataTable(ViewDataTable $view)
    {
        // You usually do not need to implement this method. Here you can define whether your visualization can display
        // a specific data table or not. For instance you may only display your visualization in case a single data
        // table is requested. Example:
        // return $view->isRequestingSingleDataTable();

        return parent::canDisplayViewDataTable($view);
    }
}