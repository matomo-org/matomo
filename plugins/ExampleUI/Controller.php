<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package ExampleUI
 */
namespace Piwik\Plugins\ExampleUI;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\View;
use Piwik\ViewDataTable;

/**
 * @package ExampleUI
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function dataTables()
    {
        $controllerAction = $this->pluginName . '.' . __FUNCTION__;
        $apiAction = 'ExampleUI.getTemperatures';

        /**
         * this is an example how you can make a custom visualization reusable.
         */
        $table = new CustomDataTable();

        echo $table->render('Temperature in °C', 'Hour of day', $apiAction, $controllerAction);
    }

    public function evolutionGraph()
    {
        $view = new View('@ExampleUI/evolutiongraph');

        $this->setPeriodVariablesView($view);
        $view->evolutionGraph = $this->getEvolutionGraph(true, array('server1', 'server2'));

        echo $view->render();
    }

    public function getEvolutionGraph($fetch = false, array $columns = array())
    {
        if (empty($columns)) {
            $columns = Common::getRequestVar('columns');
            $columns = Piwik::getArrayFromApiParameter($columns);
        }

        $view = $this->getLastUnitGraphAcrossPlugins($this->pluginName, __FUNCTION__, $columns,
            $selectableColumns = array('server1', 'server2'), 'My documentation', 'ExampleUI.getTemperaturesEvolution');
        $view->requestConfig->filter_sort_column = 'label';

        return $this->renderView($view, $fetch);
    }

    public function barGraph()
    {
        $view = ViewDataTable::factory(
            'graphVerticalBar', 'ExampleUI.getTemperatures', $controllerAction = 'ExampleUI.barGraph');

        $view->config->y_axis_unit = '°C';
        $view->config->show_footer = false;
        $view->config->translations['value'] = "Temperature";
        $view->config->selectable_columns = array("value");
        $view->config->max_graph_elements = 24;

        echo $view->render();
    }

    public function pieGraph()
    {
        $view = ViewDataTable::factory(
            'graphPie', 'ExampleUI.getPlanetRatios', $controllerAction = 'ExampleUI.pieGraph');

        $view->config->columns_to_display = array('value');
        $view->config->translations['value'] = "times the diameter of Earth";
        $view->config->show_footer_icons = false;
        $view->config->selectable_columns = array("value");
        $view->config->max_graph_elements = 10;

        echo $view->render();
    }

    public function tagClouds()
    {
        echo "<h2>Simple tag cloud</h2>";
        $this->echoSimpleTagClouds();

        echo "<br /><br /><h2>Advanced tag cloud: with logos and links</h2>
		<ul style='list-style-type:disc;margin-left:50px'>
			<li>The logo size is proportional to the value returned by the API</li>
			<li>The logo is linked to a specific URL</li>
		</ul><br /><br />";
        $this->echoAdvancedTagClouds();
    }

    public function echoSimpleTagClouds()
    {
        $view = ViewDataTable::factory(
            'cloud', 'ExampleUI.getPlanetRatios', $controllerAction = 'ExampleUI.echoSimpleTagClouds');

        $view->config->columns_to_display = array('label', 'value');
        $view->config->translations['value'] = "times the diameter of Earth";
        $view->config->show_footer = false;

        echo $view->render();
    }

    public function echoAdvancedTagClouds()
    {
        $view = ViewDataTable::factory(
            'cloud', 'ExampleUI.getPlanetRatiosWithLogos', $controllerAction = 'ExampleUI.echoAdvancedTagClouds');

        $view->config->display_logo_instead_of_label = true;
        $view->config->columns_to_display = array('label', 'value');
        $view->config->translations['value'] = "times the diameter of Earth";

        echo $view->render();
    }

    public function sparklines()
    {
        $view = new View('@ExampleUI/sparklines');
        $view->urlSparkline1 = $this->getUrlSparkline('generateSparkline', array('server' => 'server1', 'rand' => mt_rand()));
        $view->urlSparkline2 = $this->getUrlSparkline('generateSparkline', array('server' => 'server2', 'rand' => mt_rand()));

        echo $view->render();
    }

    public function generateSparkline()
    {
        $view = ViewDataTable::factory(
            'sparkline', 'ExampleUI.getTemperaturesEvolution', $controllerAction = 'ExampleUI.generateSparkline');

        $serverRequested = Common::getRequestVar('server', false);
        if (false !== $serverRequested) {
            $view->config->columns_to_display = array($serverRequested);
        }

        echo $view->render();
    }
}
