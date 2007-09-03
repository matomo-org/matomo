<?php
require_once "API/Request.php";
class Piwik_UserSettings_Controller extends Piwik_Controller
{	
	function index()
	{
		$view = new Piwik_View('UserSettings/templates/index.tpl');
		
		$view->dataTableResolution = $this->getResolution(true);
		
		echo $view->render();		
	}
	
	function getResolution( $fetch = false)
	{
		$request = new Piwik_API_Request('
			method=UserSettings.getResolution
			&format=php
			&serialize=0
		');
		$data = $request->process();
//		var_dump( $data );exit;
		$view = new Piwik_View('UserSettings/templates/datatable.tpl');
		$view->id 			= 'UserSettingsResolution';
		$view->dataTable 	= $data;
		
		
		$javascriptVariablesToSet = array();
		
		$genericFilters = Piwik_API_Request::getGenericFiltersInformation();
		foreach($genericFilters as $filter)
		{
			foreach($filter as $filterVariableName => $filterInfo)
			{
				// if there is a default value for this filter variable we set it 
				// so that it is propagated to the javascript
				if(isset($filterInfo[1]))
				{
					$javascriptVariablesToSet[$filterVariableName] = $filterInfo[1];
				}
			}
		}
		
		foreach($_GET as $name => $value)
		{
			$javascriptVariablesToSet[$name] = Piwik_Common::getRequestVar($name);
		}
		$javascriptVariablesToSet['action'] = substr(__METHOD__, strrpos(__METHOD__,':') + 1);
		
		$view->javascriptVariablesToSet = $javascriptVariablesToSet;
		$rendered = $view->render();
		
		if($fetch)
		{
			return $rendered;
		}
		echo $rendered;
	}
}
