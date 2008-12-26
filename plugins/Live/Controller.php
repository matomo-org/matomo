<?php
require_once 'Live/API.php';

Piwik_AddWidget('Live', 'widget', 'Live Visitors!');

class Piwik_Live_Controller extends Piwik_Controller
{
	function widget()
	{
		echo "Live Visitors!";
	}
	
	function getLastVisits($fetch = false)
	{
		$idSite = Piwik_Common::getRequestVar('idSite', null, 'int');
		$limit = 10;
		$api = new Piwik_API_Request("method=Live.getLastVisits&idSite=$idSite&limit=$limit&format=php&serialize=0&disable_generic_filters=1");
		
		$view = new Piwik_View('Live/templates/lastVisits.tpl');
		$view->visitors = $api->process();
		$rendered = $view->render($fetch);
		
		if($fetch)
		{
			return $rendered;
		}
		echo $rendered;
	}
	
	function index()
	{
		$view = new Piwik_View('Live/templates/index.tpl');
		$this->setGeneralVariablesView($view);
		$view->visitors = $this->getLastVisits($fetch = true);
		echo $view->render();
	}
}
