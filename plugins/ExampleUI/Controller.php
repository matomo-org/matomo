<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleUI;

use Piwik\Common;
use Piwik\Notification;
use Piwik\Piwik;
use Piwik\View;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;

/**
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function dataTables()
    {
        $controllerAction = $this->pluginName . '.' . __FUNCTION__;
        $apiAction = 'ExampleUI.getTemperatures';

        $view = ViewDataTableFactory::build('table', $apiAction, $controllerAction);

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

        return $view->render();
    }

    public function evolutionGraph()
    {
        $view = new View('@ExampleUI/evolutiongraph');

        $this->setPeriodVariablesView($view);
        $view->evolutionGraph = $this->getEvolutionGraph(array(), array('server1', 'server2'));

        return $view->render();
    }

    public function notifications()
    {
        $notification = new Notification('Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
        Notification\Manager::notify('ExampleUI_InfoSimple', $notification);

        $notification = new Notification('Neque porro quisquam est qui dolorem ipsum quia dolor sit amet.');
        $notification->title   = 'Warning:';
        $notification->context = Notification::CONTEXT_WARNING;
        $notification->flags   = null;
        Notification\Manager::notify('ExampleUI_warningWithClose', $notification);

        $notification = new Notification('Phasellus tincidunt arcu at justo faucibus, et lacinia est accumsan. ');
        $notification->title   = 'Well done';
        $notification->context = Notification::CONTEXT_SUCCESS;
        $notification->type    = Notification::TYPE_TOAST;
        Notification\Manager::notify('ExampleUI_successToast', $notification);

        $notification = new Notification('Phasellus tincidunt arcu at justo <a href="#">faucibus</a>, et lacinia est accumsan. ');
        $notification->raw     = true;
        $notification->context = Notification::CONTEXT_ERROR;
        Notification\Manager::notify('ExampleUI_error', $notification);

        $view = new View('@ExampleUI/notifications');
        $this->setGeneralVariablesView($view);
        return $view->render();
    }

    public function getEvolutionGraph(array $columns = array(), array $defaultColumns = array())
    {
        if (empty($columns)) {
            $columns = Common::getRequestVar('columns', false);
            if (false !== $columns) {
                $columns = Piwik::getArrayFromApiParameter($columns);
            }
        }

        $view = $this->getLastUnitGraphAcrossPlugins($this->pluginName, __FUNCTION__, $columns,
            $selectableColumns = array('server1', 'server2'), 'My documentation', 'ExampleUI.getTemperaturesEvolution');
        $view->requestConfig->filter_sort_column = 'label';
        $view->requestConfig->filter_sort_order  = 'asc';

        if (empty($view->config->columns_to_display) && !empty($defaultColumns)) {
            $view->config->columns_to_display = $defaultColumns;
        }

        return $this->renderView($view);
    }

    public function barGraph()
    {
        $view = ViewDataTableFactory::build(
            'graphVerticalBar', 'ExampleUI.getTemperatures', $controllerAction = 'ExampleUI.barGraph');

        $view->config->y_axis_unit = '°C';
        $view->config->show_footer = false;
        $view->config->translations['value'] = "Temperature";
        $view->config->selectable_columns = array("value");
        $view->config->max_graph_elements = 24;

        return $view->render();
    }

    public function pieGraph()
    {
        $view = ViewDataTableFactory::build(
            'graphPie', 'ExampleUI.getPlanetRatios', $controllerAction = 'ExampleUI.pieGraph');

        $view->config->columns_to_display = array('value');
        $view->config->translations['value'] = "times the diameter of Earth";
        $view->config->show_footer_icons = false;
        $view->config->selectable_columns = array("value");
        $view->config->max_graph_elements = 10;

        return $view->render();
    }

    public function tagClouds()
    {
        $output  = "<h2>Simple tag cloud</h2>";
        $output .= $this->echoSimpleTagClouds();

        $output .= "<br /><br /><h2>Advanced tag cloud: with logos and links</h2>
		<ul style='list-style-type:disc;margin-left:50px'>
			<li>The logo size is proportional to the value returned by the API</li>
			<li>The logo is linked to a specific URL</li>
		</ul><br /><br />";
        $output .= $this->echoAdvancedTagClouds();

        return $output;
    }

    public function echoSimpleTagClouds()
    {
        $view = ViewDataTableFactory::build(
            'cloud', 'ExampleUI.getPlanetRatios', $controllerAction = 'ExampleUI.echoSimpleTagClouds');

        $view->config->columns_to_display = array('label', 'value');
        $view->config->translations['value'] = "times the diameter of Earth";
        $view->config->show_footer = false;

        return $view->render();
    }

    public function echoAdvancedTagClouds()
    {
        $view = ViewDataTableFactory::build(
            'cloud', 'ExampleUI.getPlanetRatiosWithLogos', $controllerAction = 'ExampleUI.echoAdvancedTagClouds');

        $view->config->display_logo_instead_of_label = true;
        $view->config->columns_to_display = array('label', 'value');
        $view->config->translations['value'] = "times the diameter of Earth";

        return $view->render();
    }

    public function sparklines()
    {
        $view = new View('@ExampleUI/sparklines');
        $view->urlSparkline1 = $this->getUrlSparkline('generateSparkline', array('server' => 'server1', 'rand' => mt_rand()));
        $view->urlSparkline2 = $this->getUrlSparkline('generateSparkline', array('server' => 'server2', 'rand' => mt_rand()));

        return $view->render();
    }

    public function generateSparkline()
    {
        $view = ViewDataTableFactory::build(
            'sparkline', 'ExampleUI.getTemperaturesEvolution', $controllerAction = 'ExampleUI.generateSparkline');

        $serverRequested = Common::getRequestVar('server', false);
        if (false !== $serverRequested) {
            $view->config->columns_to_display = array($serverRequested);
        }

        return $view->render();
    }

    public function treemap()
    {
        $view = ViewDataTableFactory::build(
            'infoviz-treemap', 'ExampleUI.getTemperatures', $controllerAction = 'ExampleUI.treemap');

        $view->config->translations['value'] = "Temperature";
        $view->config->columns_to_display = array("label", "value");
        $view->config->selectable_columns = array("value");
        $view->config->show_evolution_values = 0;

        return $view->render();
    }
}
