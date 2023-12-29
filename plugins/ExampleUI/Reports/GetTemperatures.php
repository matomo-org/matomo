<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleUI\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Bar;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetTemperatures extends Base
{
    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('ExampleUI_GetTemperaturesDataTable');
        $this->documentation = 'This is an example documentation of a report.';
        $this->subcategoryId = 'ExampleUI_GetTemperaturesDataTable';
        $this->order = 110;
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        // this will render the default view, in this case an Html Table
        $widgetsList->addWidgetConfig($factory->createWidget());

        $widgetsList->addWidgetConfig(
            $factory->createWidget()
                    ->forceViewDataTable(Bar::ID)
                    ->setSubcategoryId('Bar graph')
        );

        if (PluginManager::getInstance()->isPluginActivated('TreemapVisualization')) {
            $widgetsList->addWidgetConfig(
                $factory->createWidget()
                        ->setName('Treemap example')
                        ->setSubcategoryId('Treemap')
                        ->forceViewDataTable('infoviz-treemap')
            );
        }
    }

    public function configureView(ViewDataTable $view)
    {
        if ($view->isViewDataTableId(Bar::ID)) {

            $view->config->y_axis_unit = '°C';
            $view->config->show_footer = false;
            $view->config->translations['value'] = "Temperature";
            $view->config->selectable_columns = array("value");
            $view->config->max_graph_elements = 24;
        } elseif ($view->isViewDataTableId('infoviz-treemap')) {

            $view->config->translations['value'] = "Temperature";
            $view->config->columns_to_display = array("label", "value");
            $view->config->selectable_columns = array("value");
            $view->config->show_evolution_values = 0;
        } else {
            // for default view datatable, eg HtmlTable

            $view->config->translations['value'] = 'Temperature in °C';
            $view->config->translations['label'] = 'Hour of day';
            $view->requestConfig->filter_sort_column = 'label';
            $view->requestConfig->filter_sort_order = 'asc';
            $view->requestConfig->filter_limit = 24;
            $view->config->columns_to_display  = array('label', 'value');
            $view->config->y_axis_unit = '°C'; // useful if the user requests the bar graph
            $view->config->show_exclude_low_population = false;
            $view->config->show_table_all_columns = false;
            $view->config->disable_row_evolution  = true;
            $view->config->max_graph_elements = 24;
            $view->config->metrics_documentation = array('value' => 'Documentation for temperature metric');
        }
    }
}
