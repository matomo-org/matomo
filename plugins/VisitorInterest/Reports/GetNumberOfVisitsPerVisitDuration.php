<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitorInterest\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Cloud;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;
use Piwik\Plugins\VisitorInterest\Columns\VisitDuration;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

class GetNumberOfVisitsPerVisitDuration extends Base
{
    protected $defaultSortColumn = '';

    protected function init()
    {
        parent::init();
        $this->dimension     = new VisitDuration();
        $this->name          = Piwik::translate('VisitorInterest_WidgetLengths');
        $this->documentation = Piwik::translate('VisitorInterest_WidgetLengthsDocumentation')
                             . '<br />' . Piwik::translate('General_ChangeTagCloudView');
        $this->metrics       = array('nb_visits');
        $this->processedMetrics  = false;
        $this->constantRowsCount = true;
        $this->order = 15;
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widgetsList->addWidgetConfig(
            $factory->createWidget()->setName('VisitorInterest_VisitsPerDuration')
        );
    }

    public function getDefaultTypeViewDataTable()
    {
        return Cloud::ID;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->requestConfig->filter_sort_column = 'label';
        $view->requestConfig->filter_sort_order  = 'asc';

        $view->config->enable_sort = false;
        $view->config->show_exclude_low_population = false;
        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
        $view->config->show_search             = false;
        $view->config->show_table_all_columns  = false;
        $view->config->columns_to_display      = array('label', 'nb_visits');

        if (!$view->isViewDataTableId(Evolution::ID)) {
            $view->config->show_limit_control = false;
        }

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->show_series_picker = false;
            $view->config->selectable_columns = array();
            $view->config->max_graph_elements = 10;
        }
    }
}
