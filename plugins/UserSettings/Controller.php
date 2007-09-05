<?php
require_once "API/Request.php";
class Piwik_UserSettings_Controller extends Piwik_Controller
{	
	function index()
	{
		$view = new Piwik_View('UserSettings/templates/index.tpl');
		
		$view->dataTableResolution = $this->getResolution(true);
		$view->dataTableSearchEngines = $this->getSearchEngines(true);
		$view->dataTableKeywords = $this->getKeywords(true);
		$view->dataTableBrowser = $this->getBrowser(true);
		
		echo $view->render();		
	}
	
	function getSearchEnginesFromKeywordId( $fetch = false )
	{
		$view = $this->getTable(	'getSearchEnginesFromKeywordId', 
									'Referers.getSearchEnginesFromKeywordId', 
									'ReferersKeywordsSe'
								);
		
		return $this->renderView($view, $fetch);
	}
	
	
	function getKeywords( $fetch = false)
	{
		$view = $this->getTable(	'getKeywords', 
									'Referers.getKeywords', 
									'ReferersKeywords'
								);
		return $this->renderView($view, $fetch);
	}
	function getKeywordsFromSearchEngineId( $fetch = false )
	{
		$view = $this->getTable(	'getKeywordsFromSearchEngineId', 
									'Referers.getKeywordsFromSearchEngineId', 
									'ReferersSeKeywords'
								);
		//TODO setup a method for this
		$view->dataTableColumns = array(
					array('id' => 0, 'name' => 'label'),
					array('id' => Piwik_Archive::INDEX_NB_VISITS, 'name' => 'nb_visits'),
				);
		
		return $this->renderView($view, $fetch);
	}
	
	
	function getSearchEngines( $fetch = false)
	{
		$view = $this->getTable(	'getSearchEngines', 
									'Referers.getSearchEngines', 
									'ReferersSe'
								);
		//TODO setup a method for this
		$view->dataTableColumns = array(
					array('id' => 0, 'name' => 'label'),
					array('id' => Piwik_Archive::INDEX_NB_VISITS, 'name' => 'nb_visits'),
				);
		return $this->renderView($view, $fetch);
	}
	
	
	function getResolution( $fetch = false)
	{
		$view = $this->getTable(	'getResolution', 
									'UserSettings.getResolution', 
									'UserSettingsResolution'
								);
		//TODO setup a method for this
		$view->dataTableColumns = array(
					array('id' => 0, 'name' => 'label'),
					array('id' => Piwik_Archive::INDEX_NB_VISITS, 'name' => 'nb_visits'),
				);
		return $this->renderView($view, $fetch);
	}
	
	function getBrowser( $fetch = false)
	{
		$view = $this->getTable(	'getBrowser', 
									'UserSettings.getBrowser', 
									'UserSettingsBrowser'
								);
		return $this->renderView($view, $fetch);
	}
	
	protected $dataTableTemplate = 'UserSettings/templates/datatable.tpl';
	
	
	protected function getTable( $currentControllerAction, $moduleNameAndMethod, $uniqIdTable )
	{
		$requestString = 'method='.$moduleNameAndMethod.'
			&format=original';
			
		$idSubtable = Piwik_Common::getRequestVar('idSubtable', false,'int');
		if( $idSubtable != false)
		{
			$requestString .= '&idSubtable='.$idSubtable;
			
			$uniqIdTable = 'subDataTable_' . $idSubtable;
		}
		$request = new Piwik_API_Request($requestString);
		
		$dataTable = $request->process();
//		echo $dataTable; exit;
		$filter = new Piwik_DataTable_Filter_ColumnCallbackReplace(
									$dataTable, 
									'label', 
									'urldecode'
								);
		
		$renderer = Piwik_DataTable_Renderer::factory('php');
		$renderer->setTable($dataTable);
		$renderer->setSerialize( false );
		$phpArray = $renderer->render();
				
//		var_dump( $data );exit;
		$view = new Piwik_View($this->dataTableTemplate);
		$view->id 			= $uniqIdTable;
		$view->dataTable 	= $phpArray;
		
//		$i=0;while($i<1500000){ $j=$i*$i;$i++;}
		
		$dataTableColumns = array();
		
		if(count($phpArray) > 0)
		{
			// build column information
			$id = 0;
			foreach($phpArray[0]['columns'] as $columnName => $row)
			{
				$dataTableColumns[]	= array('id' => $id, 'name' => $columnName);
				$id++;
			}
		}
		$view->dataTableColumns = $dataTableColumns;
		
		
		// build javascript variables to set
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
		
		//TODO check security of printing javascript variables; inject some JS code here??
		foreach($_GET as $name => $value)
		{
			try{
				$requestValue = Piwik_Common::getRequestVar($name);
			}
			catch(Exception $e) {
				$requestValue = '';
			}
			$javascriptVariablesToSet[$name] = $requestValue;
		}
		
		$javascriptVariablesToSet['action'] = $currentControllerAction;
		
		// mapping between the current action call and the API method to call when a SubDataTable is requested 
		// for a row returned by this action method
		$mapping = array(
			'getKeywords' => 'getSearchEnginesFromKeywordId',
			'getSearchEngines' => 'getKeywordsFromSearchEngineId',
		);
		if(isset($mapping[$currentControllerAction]))
		{
			$javascriptVariablesToSet['actionToLoadTheSubTable'] = $mapping[$currentControllerAction];
		}
		
		$javascriptVariablesToSet['totalRows'] = $dataTable->getRowsCountBeforeLimitFilter();
		$view->javascriptVariablesToSet = $javascriptVariablesToSet;
		
		return $view;
	}
	
	
	protected function renderView($view, $fetch)
	{
		$rendered = $view->render();
		if($fetch)
		{
			return $rendered;
		}
		echo $rendered;
		return;
	}
	
}
