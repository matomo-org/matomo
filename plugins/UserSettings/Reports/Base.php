<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings\Reports;

use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;

abstract class Base extends \Piwik\Plugin\Report
{
    protected function init()
    {
        $this->category = 'UserSettings_VisitorSettings';
    }

    protected function getBasicUserSettingsDisplayProperties(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;

        $view->requestConfig->filter_limit = 5;

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->max_graph_elements = 5;
        }
    }
}
