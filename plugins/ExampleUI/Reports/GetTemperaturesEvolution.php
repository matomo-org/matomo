<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleUI\Reports;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

/**
 * This class defines a new report.
 *
 * See {@link https://developer.matomo.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetTemperaturesEvolution extends Base
{
    /**
     * @return void
     */
    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('ExampleUI_GetTemperaturesEvolution');
        $this->documentation = 'This is an example evolution report';
        $this->order = 111;
    }

    /**
     * @return void
     */
    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widgetsList->addWidgetConfig(
            $factory->createWidget()
                    ->setSubcategoryId('Sparklines')
                    ->forceViewDataTable(Sparklines::ID)
        );

        $widgetsList->addWidgetConfig(
            $factory->createWidget()
                    ->setName('ExampleUI_TemperaturesEvolution')
                    ->setSubcategoryId('Evolution Graph')
                    ->forceViewDataTable(Evolution::ID)
                    ->setParameters(['columns' => ['server1', 'server2']])
        );
    }

    /**
     * Here you can configure how your report should be displayed. For instance whether your report supports a search
     * etc. You can also change the default request config. For instance change how many rows are displayed by default.
     *
     * @return void
     */
    public function configureView(ViewDataTable $view)
    {
        if ($view->isViewDataTableId(Sparklines::ID)) {
            /** @var Sparklines $view */
            $view->config->addSparklineMetric(['server1']);
            $view->config->addSparklineMetric(['server2']);
            $view->config->addTranslations([
                'server1' => 'Evolution of temperature for server piwik.org',
                'server2' => 'Evolution of temperature for server dev.piwik.org',
            ]);
        } elseif ($view->isViewDataTableId(Evolution::ID)) {
            /** @var Evolution $view */
            $selectableColumns = ['server1', 'server2'];

            // the requested columns may be sent as a comma separated string or as an array
            $requestedColumns = Common::getRequestVar('columns', '');

            if (is_string($requestedColumns)) {
                $requestedColumns = Piwik::getArrayFromApiParameter($requestedColumns);
            } elseif (!is_array($requestedColumns)) {
                $requestedColumns = [];
            }

            $columns = array_merge(array_filter($requestedColumns, 'is_string'), $selectableColumns);

            $view->config->columns_to_display = $columns;
            $view->config->addTranslations(array_combine($columns, $columns));
            $view->config->selectable_columns = $selectableColumns;
            $view->requestConfig->filter_sort_column = 'label';
            $view->requestConfig->filter_sort_order  = 'asc';
            $view->config->documentation = 'My documentation';
            $view->config->show_goals = false;
        }
    }
}
