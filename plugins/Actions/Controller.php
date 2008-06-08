<?php

require_once "ViewDataTable.php";
class Piwik_Actions_Controller extends Piwik_Controller 
{
	
	function getDownloads($fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	$this->pluginName, 
						__FUNCTION__,
						'Actions.getDownloads',
						'getDownloadsSubDataTable' );
		
		$view->setColumnsToDisplay( array('label','nb_uniq_visitors') );
		$view->setSortedColumn( 'nb_uniq_visitors','desc' );
		$view->disableExcludeLowPopulation();
		$view->setLimit( 15 );
		$view->disableSort();
		
		return $this->renderView($view, $fetch);
	}
	function getDownloadsSubDataTable($fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	$this->pluginName, 
						__FUNCTION__,
						'Actions.getDownloads',
						'getDownloadsSubDataTable');
		
		$view->setColumnsToDisplay( array('label','nb_uniq_visitors') );
		$view->setSortedColumn( 'nb_uniq_visitors','desc' );
		$view->disableExcludeLowPopulation();
		$view->disableSearchBox();
		$view->setLimit( 15 );
		$view->disableSort();
		
		$view->main();
		return $this->renderView($view, $fetch);
	}
	
	
	function getActions($fetch = false)
	{
		$view = $this->getActionsView(	$this->pluginName, 
										__FUNCTION__,
										'Actions.getActions', 
										'getActionsSubDataTable' );
		
		return $this->renderView($view, $fetch);
	}
	function getActionsSubDataTable($fetch = false)
	{
		$view = $this->getActionsView( 	$this->pluginName, 
										__FUNCTION__,
										'Actions.getActions', 
										'getActionsSubDataTable'  );
		
		return $this->renderView($view, $fetch);
	}
	
	
	function getOutlinks($fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	$this->pluginName, 
						__FUNCTION__,
						'Actions.getOutlinks',
						'getOutlinksSubDataTable' );
		
		$view->setColumnsToDisplay( array('label','nb_uniq_visitors') );
		$view->setSortedColumn( 'nb_uniq_visitors','desc' );
		$view->disableExcludeLowPopulation();
		$view->setLimit( 15 );
		$view->disableSort();
		
		return $this->renderView($view, $fetch);
	}
	function getOutlinksSubDataTable($fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(	$this->pluginName, 
						__FUNCTION__,
						'Actions.getOutlinks',
						'getOutlinksSubDataTable');
		
		$view->setColumnsToDisplay( array('label','nb_uniq_visitors') );
		$view->setSortedColumn( 'nb_uniq_visitors','desc' );
		$view->disableExcludeLowPopulation();
		$view->disableSearchBox();
		$view->setLimit( 15 );
		$view->disableSort();
		
		return $this->renderView($view, $fetch);
	}
	
	
	function index()
	{
		$view = new Piwik_View('Actions/index.tpl');
		
		/* Actions / Downloads / Outlinks */
		$view->dataTableActions = $this->getActions( true );
		$view->dataTableDownloads = $this->getDownloads( true );
		$view->dataTableOutlinks = $this->getOutlinks( true );
		
		echo $view->render();
	}
	
	protected function getActionsView($currentControllerName,
						$currentMethod,
						$methodToCall = 'Actions.getActions', 
						$subMethod = 'getActionsSubDataTable')
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	$currentControllerName,
						$currentMethod, 
						$methodToCall, 
						$subMethod );
		$view->setTemplate('Home/templates/datatable_actions.tpl');
		
		if(Piwik_Common::getRequestVar('idSubtable', -1) != -1)
		{
			$view->setTemplate('Home/templates/datatable_actions_subdatable.tpl');
		}
		$currentlySearching = $view->setSearchRecursive();
		
		if($currentlySearching)
		{
			$view->setTemplate('Home/templates/datatable_actions_recursive.tpl');
		}
		$view->disableSort();
		
		$view->setSortedColumn( 'nb_hits', 'desc' );
		
		$view->disableOffsetInformation();
		
		$view->setColumnsToDisplay( array('label','nb_uniq_visitors','nb_hits') );
		$view->setLimit( 100 );
		// computing minimum value to exclude
		$visitsInfo = Piwik_VisitsSummary_Controller::getVisitsSummary();
		$nbActions = $visitsInfo->getColumn('nb_actions');
		$nbActionsLowPopulationThreshold = floor(0.02 * $nbActions); // 2 percent of the total number of actions
		
		// we remove 1 to make sure some actions/downloads are displayed in the case we have a very few of them
		// and each of them has 1 or 2 hits...
		$nbActionsLowPopulationThreshold = min($visitsInfo->getColumn('max_actions')-1, $nbActionsLowPopulationThreshold-1);
		
		$view->setExcludeLowPopulation( $nbActionsLowPopulationThreshold, 'nb_hits' );
		
		$view->main();
		
		// we need to rewrite the phpArray so it contains all the recursive arrays
		if($currentlySearching)
		{
			$phpArrayRecursive = $this->getArrayFromRecursiveDataTable($view->getDataTable());
//			var_dump($phpArrayRecursive);exit;
			$view->getView()->arrayDataTable = $phpArrayRecursive;
		}
//		var_dump( $view->view->arrayDataTable);exit;
		return $view;
	}
	
	protected function getArrayFromRecursiveDataTable( $dataTable, $depth = 0 )
	{
		$table = array();
		foreach($dataTable->getRows() as $row)
		{
			$phpArray = array();
			if(($idSubtable = $row->getIdSubDataTable()) !== null)
			{
				$subTable = Piwik_DataTable_Manager::getInstance()->getTable( $idSubtable );
					
				if($subTable->getRowsCount() > 0)
				{
					$phpArray = $this->getArrayFromRecursiveDataTable( $subTable, $depth + 1 );
				}
			}
			
			$label = $row->getColumn('label');
			$newRow = array(
				'level' => $depth,
				'columns' => $row->getColumns(),
				'metadata' => $row->getMetadata(),
				'idsubdatatable' => $row->getIdSubDataTable()
				);
			$table[] = $newRow;
			if(count($phpArray) > 0)
			{
				$table = array_merge( $table,  $phpArray);
			}
		}
		return $table;
	}
	
	
	
}
