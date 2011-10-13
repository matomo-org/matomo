<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_ImageGraph
 */

class Piwik_ImageGraph_Controller extends Piwik_Controller
{
	public function index()
	{
		// Call metadata reports, and draw the default graph for each report.
		
	}
    public function testAllSizes()
	{
		Piwik::checkUserIsSuperUser();
		
		$view = Piwik_View::factory('index');
		$this->setGeneralVariablesView($view);
		
		$availableReports = Piwik_API_API::getInstance()->getReportMetadata($this->idSite);
		$view->availableReports = $availableReports;
		$view->graphTypes = array(
			'evolution',
			'verticalBar',
			'pie',
			'3dPie'
		);
		$view->graphSizes = array(
			array(600, 250), // standard graph size
			array(460, 150), // standard phone
			array(300, 150), // standard phone 2
			array(240, 150), // smallest mobile display
			array(800, 150), // landscape mode
			array(600, 300, $fontSize = 18, 300, 150), // iphone requires bigger font, then it will be scaled down by ios
		
		);
		echo $view->render();
	}

}