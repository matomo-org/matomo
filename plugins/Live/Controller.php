<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Live
 */

require_once 'Live/API.php';


class Piwik_Live_Controller extends Piwik_Controller
{
	function widget()
	{
		$view = new Piwik_View('Live/templates/index.tpl');		
		$this->setGeneralVariablesView($view);
		$view->visitors = $this->getLastVisits($fetch = true);
		echo $view->render();
	}
	
	function getLastVisits($fetch = false)
	{
		$idSite = Piwik_Common::getRequestVar('idSite', null, 'int');
		$minIdVisit = Piwik_Common::getRequestVar('minIdVisit', 0, 'int');
		$limit = 10;
		$api = new Piwik_API_Request("method=Live.getLastVisits&idSite=$idSite&limit=$limit&minIdVisit=$minIdVisit&format=php&serialize=0&disable_generic_filters=1");
		
		$view = new Piwik_View('Live/templates/lastVisits.tpl');
		$visitors = $api->process();
		if($minIdVisit == 0)
		{
			$visitors = array_slice($visitors, 3);
		}
		$view->visitors = $visitors;
		$rendered = $view->render($fetch);
		
		if($fetch)
		{
			return $rendered;
		}
		echo $rendered;
	}
	
	function index()
	{
		$view = new Piwik_View('Live/templates/structure.tpl');
		$this->setGeneralVariablesView($view);
		$view->visitors = $this->getLastVisits($fetch = true);
		echo $view->render();
	}
}
