<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetTemperaturesEvolution extends Base
{
    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('ExampleUI_GetTemperaturesEvolution');
        $this->documentation = 'This is an example evolution report';
        $this->order = 111;
    }

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
                    ->setParameters(array('columns' => array('server1', 'server2')))
        );

    }

    /**
     * Here you can configure how your report should be displayed. For instance whether your report supports a search
     * etc. You can also change the default request config. For instance change how many rows are displayed by default.
     *
     * @param ViewDataTable $view
     */
    public function configureView(ViewDataTable $view)
    {
        if ($view->isViewDataTableId(Sparklines::ID)) {

            /** @var Sparklines $view */
            $view->config->addSparklineMetric(array('server1'));
            $view->config->addSparklineMetric(array('server2'));
            $view->config->addTranslations(array('server1' => 'Evolution of temperature for server piwik.org'));
            $view->config->addTranslations(array('server2' => 'Evolution of temperature for server dev.piwik.org'));
        } elseif ($view->isViewDataTableId(Evolution::ID)) {

            /** @var Evolution $view */
            $selectableColumns = array('server1', 'server2');

            $columns = Common::getRequestVar('columns', false);
            if (!empty($columns)) {
                $columns = Piwik::getArrayFromApiParameter($columns);
            }

            $columns = array_merge($columns ? $columns : array(), $selectableColumns);
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
