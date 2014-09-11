<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime\Reports;

use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Bar;

abstract class Base extends \Piwik\Plugin\Report
{
    protected function init()
    {
        $this->category = 'VisitsSummary_VisitsSummary';
    }

    public function getDefaultTypeViewDataTable()
    {
        return Bar::ID;
    }

    /**
     * @param ViewDataTable $view
     */
    protected function setBasicConfigViewProperties(ViewDataTable $view)
    {
        $view->requestConfig->filter_sort_column = 'label';
        $view->requestConfig->filter_sort_order = 'asc';
        $view->requestConfig->addPropertiesThatShouldBeAvailableClientSide(array('filter_sort_column'));
        $view->config->show_search = false;
        $view->config->show_limit_control = false;
        $view->config->show_exclude_low_population = false;
        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
    }
}
