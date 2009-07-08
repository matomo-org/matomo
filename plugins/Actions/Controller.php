<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Actions
 */

class Piwik_Actions_Controller extends Piwik_Controller 
{
	public function index()
	{
		$view = new Piwik_View('Actions/index.tpl');
		
		/* Actions, Downloads, Outlinks */
		$view->dataTableActions = $this->getActions( true );
		$view->dataTableDownloads = $this->getDownloads( true );
		$view->dataTableOutlinks = $this->getOutlinks( true );
		
		echo $view->render();
	}
	
	public function getActions($fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	$this->pluginName, 
						__FUNCTION__,
						'Actions.getActions', 
						'getActionsSubDataTable' );
		$this->configureViewActions($view);
		$view->setColumnTranslation('label', Piwik_Translate('Actions_ColumnPageName'));
		return $this->renderView($view, $fetch);
	}
	
	public function getActionsSubDataTable($fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	$this->pluginName, 
						__FUNCTION__,
						'Actions.getActions', 
						'getActionsSubDataTable'  );
		$this->configureViewActions($view);
		return $this->renderView($view, $fetch);
	}
	
	public function getDownloads($fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	$this->pluginName, 
						__FUNCTION__,
						'Actions.getDownloads',
						'getDownloadsSubDataTable' );
		
		$this->configureViewDownloads($view);
		$view->disableShowAllColumns();
		return $this->renderView($view, $fetch);
	}
	
	public function getDownloadsSubDataTable($fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	$this->pluginName, 
						__FUNCTION__,
						'Actions.getDownloads',
						'getDownloadsSubDataTable');
		$this->configureViewDownloads($view);
		$view->disableSearchBox();
		return $this->renderView($view, $fetch);
	}
	
	public function getOutlinks($fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	$this->pluginName, 
						__FUNCTION__,
						'Actions.getOutlinks',
						'getOutlinksSubDataTable' );
		$this->configureViewOutlinks($view);
		$view->disableExcludeLowPopulation();
		$view->disableShowAllColumns();
		return $this->renderView($view, $fetch);
	}
	
	public function getOutlinksSubDataTable($fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(	$this->pluginName, 
						__FUNCTION__,
						'Actions.getOutlinks',
						'getOutlinksSubDataTable');
		$this->configureViewOutlinks($view);
		$view->disableSearchBox();
		return $this->renderView($view, $fetch);
	}
	
	protected function configureViewActions($view)
	{
		$view->setTemplate('CoreHome/templates/datatable_actions.tpl');
		
		if(Piwik_Common::getRequestVar('idSubtable', -1) != -1)
		{
			$view->setTemplate('CoreHome/templates/datatable_actions_subdatable.tpl');
		}
		$currentlySearching = $view->setSearchRecursive();
		
		if($currentlySearching)
		{
			$view->setTemplate('CoreHome/templates/datatable_actions_recursive.tpl');
		}
		$view->disableSort();
		$view->disableOffsetInformation();
		$view->disableShowAllViewsIcons();
		$view->disableShowAllColumns();
		
		$view->setLimit( 100 );
		$view->setColumnsToDisplay( array('label','nb_hits','nb_visits') );
		$view->setColumnTranslation('label', Piwik_Translate('Actions_ColumnPageName'));
		$view->setColumnTranslation('nb_hits', Piwik_Translate('General_ColumnPageviews'));
		$view->setColumnTranslation('nb_visits', Piwik_Translate('General_ColumnUniquePageviews'));

		if(Piwik_Common::getRequestVar('enable_filter_excludelowpop', '0', 'string' ) != '0')
		{
			// computing minimum value to exclude
			$visitsInfo = Piwik_VisitsSummary_Controller::getVisitsSummary();
			$visitsInfo = $visitsInfo->getFirstRow();
			$nbActions = $visitsInfo->getColumn('nb_actions');
			$nbActionsLowPopulationThreshold = floor(0.02 * $nbActions); // 2 percent of the total number of actions
			// we remove 1 to make sure some actions/downloads are displayed in the case we have a very few of them
			// and each of them has 1 or 2 hits...
			$nbActionsLowPopulationThreshold = min($visitsInfo->getColumn('max_actions')-1, $nbActionsLowPopulationThreshold-1);
			
			$view->setExcludeLowPopulation( 'nb_hits', $nbActionsLowPopulationThreshold );
		}
		
		$view->main();
		
		// we need to rewrite the phpArray so it contains all the recursive arrays
		if($currentlySearching)
		{
			$phpArrayRecursive = $this->getArrayFromRecursiveDataTable($view->getDataTable());
			$view->getView()->arrayDataTable = $phpArrayRecursive;
		}
		return $view;
	}
	
	protected function configureViewDownloads($view)
	{
		$view->setColumnsToDisplay( array('label','nb_visits','nb_hits') );
		$view->setColumnTranslation('label', Piwik_Translate('Actions_ColumnDownloadURL'));
		$view->setColumnTranslation('nb_hits', Piwik_Translate('Actions_ColumnDownloads'));
		$view->setColumnTranslation('nb_visits', Piwik_Translate('Actions_ColumnUniqueDownloads'));
		$view->disableExcludeLowPopulation();
		$view->setLimit( 15 );
	}
	
	protected function configureViewOutlinks($view)
	{
		$view->setColumnsToDisplay( array('label','nb_visits','nb_hits') );
		$view->setColumnTranslation('label', Piwik_Translate('Actions_ColumnClickedURL'));
		$view->setColumnTranslation('nb_hits', Piwik_Translate('Actions_ColumnClicks'));
		$view->setColumnTranslation('nb_visits', Piwik_Translate('Actions_ColumnUniqueClicks'));
		$view->disableExcludeLowPopulation();
		$view->setLimit( 15 );
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
