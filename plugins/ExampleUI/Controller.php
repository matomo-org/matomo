<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 */

class Piwik_ExampleUI_Controller extends Piwik_Controller
{
	protected function getCustomData()
	{
	}
	
	function dataTables()
	{
		$view = Piwik_ViewDataTable::factory('table');
		$view->init( $this->pluginName,  __FUNCTION__, 'ExampleUI.getTemperatures' );
		$view->setColumnTranslation('value', "Temperature in °C");
		$view->setColumnTranslation('label', "Hour of day");
		$view->setSortedColumn('label', 'asc');
		$view->setGraphLimit( 24 );
		$view->setLimit( 24 );
		$view->disableExcludeLowPopulation();
		$view->disableShowAllColumns();
		$view->setAxisYUnit('°C'); // useful if the user requests the bar graph
		return $this->renderView($view);
	}
	
	function evolutionGraph()
	{
		echo "<h2>Evolution of server temperatures over the last few days</h2>";
		$this->echoEvolutionGraph();
	}
	
	function echoEvolutionGraph()
	{
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName,  __FUNCTION__, 'ExampleUI.getTemperaturesEvolution' );
		$view->setColumnTranslation('server1', "Temperature server piwik.org");
		$view->setColumnTranslation('server2', "Temperature server dev.piwik.org");
		$view->setAxisYUnit('°C'); // useful if the user requests the bar graph
		return $this->renderView($view);
	}
	
	function barGraph()
	{
		$view = Piwik_ViewDataTable::factory('graphVerticalBar');
		$view->init( $this->pluginName,  __FUNCTION__, 'ExampleUI.getTemperatures' );
		$view->setColumnTranslation('value', "Temperature");
		$view->setAxisYUnit('°C');
		$view->setGraphLimit( 24 );
		$view->disableFooter();
		return $this->renderView($view);
	}
	
	function pieGraph()
	{
		$view = Piwik_ViewDataTable::factory('graphPie');
		$view->init( $this->pluginName,  __FUNCTION__, 'ExampleUI.getPlanetRatios' );
		$view->setColumnsToDisplay( 'value' );
		$view->setColumnTranslation('value', "times the diameter of Earth");
		$view->setGraphLimit( 10 );
		$view->disableFooterIcons();
		return $this->renderView($view);
	}
	
	function tagClouds()
	{
		echo "<h2>Simple tag cloud</h2>";
		$this->echoSimpleTagClouds();
		
		echo "<br/><br/><h2>Advanced tag cloud: with logos and links</h2>
		<ul style='list-style-type:disc;margin-left:50px'>
			<li>The logo size is proportional to the value returned by the API</li>
			<li>The logo is linked to a specific URL</li>
		</ul><br/><br/>";
		$this->echoAdvancedTagClouds();
	}
	function echoSimpleTagClouds()
	{
		$view = Piwik_ViewDataTable::factory('cloud');
		$view->init( $this->pluginName,  __FUNCTION__, 'ExampleUI.getPlanetRatios' );
		$view->setColumnsToDisplay( array('label','value') );
		$view->setColumnTranslation('value', "times the diameter of Earth");
		$view->disableFooter();
		$this->renderView($view);
	}
	function echoAdvancedTagClouds()
	{
		$view = Piwik_ViewDataTable::factory('cloud');
		$view->init( $this->pluginName,  __FUNCTION__, 'ExampleUI.getPlanetRatiosWithLogos' );
		$view->setDisplayLogoInTagCloud(true);
		$view->disableFooterExceptExportIcons();
		$view->setColumnsToDisplay( array('label','value') );
		$view->setColumnTranslation('value', "times the diameter of Earth");
		$this->renderView($view);
	}
	
	function sparklines()
	{
		require_once PIWIK_INCLUDE_PATH . '/core/SmartyPlugins/function.sparkline.php';
		$srcSparkline1 = Piwik_Url::getCurrentQueryStringWithParametersModified(array('action'=>'generateSparkline', 'server' => 'server1', 'rand'=>rand()));
		$htmlSparkline1 = smarty_function_sparkline(array('src' => $srcSparkline1));
		echo "<div class='sparkline'>$htmlSparkline1 Evolution of temperature for server piwik.org</div>";
		
		$srcSparkline2 = Piwik_Url::getCurrentQueryStringWithParametersModified(array('action'=>'generateSparkline', 'server' => 'server2', 'rand'=>rand()));
		$htmlSparkline2 = smarty_function_sparkline(array('src' => $srcSparkline2));
		echo "<div class='sparkline'>$htmlSparkline2 Evolution of temperature for server dev.piwik.org</div>";
	}
	
	function generateSparkline()
	{
		$serverRequested = Piwik_Common::getRequestVar('server');
		$view = Piwik_ViewDataTable::factory('sparkline');
		$view->init( $this->pluginName,  __FUNCTION__, 'ExampleUI.getTemperaturesEvolution' );
		$view->setColumnsToDisplay($serverRequested);
		$this->renderView($view);
	}
	
	function misc()
	{
		echo "<h2>Evolution graph filtered to Google and Yahoo!</h2>";
		$this->echoDataTableSearchEnginesFiltered();
	}
	
	function echoDataTableSearchEnginesFiltered()
	{
		$view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, 'Referers.getSearchEngines');
		$view->setColumnsToDisplay( 'nb_visits' );
		$view->setSearchPattern('^(Google|Yahoo!)$', 'label');
		return $this->renderView($view);
	}	
}
