<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Actions
 */

/**
 * Actions API
 *
 * @package Piwik_Actions
 */
class Piwik_Actions_API
{
	static private $instance = null;
	
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new self;
		}
		return self::$instance;
	}
	

	/**
	 * Backward compatibility. Fallsback to getPageTitles() instead.
	 * @deprecated Deprecated since Piwik 0.5
	 */
	public function getActions( $idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false )
	{
	    return $this->getPageTitles( $idSite, $period, $date, $segment, $expanded, $idSubtable );
	}
	
	public function getPageUrls( $idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false )
	{
		$dataTable = Piwik_Archive::getDataTableFromArchive('Actions_actions_url', $idSite, $period, $date, $segment, $expanded, $idSubtable );
		$this->filterPageDatatable($dataTable);
		$this->filterActionsDataTable($dataTable, $expanded);
		return $dataTable;
	}
	
	public function getPageUrl( $pageUrl, $idSite, $period, $date, $segment = false)
	{
		$callBackParameters = array('Actions_actions_url', $idSite, $period, $date, $segment, $expanded = false, $idSubtable = false );
		$dataTable = $this->getFilterPageDatatableSearch($callBackParameters, $pageUrl, Piwik_Tracker_Action::TYPE_ACTION_URL);
		$this->filterPageDatatable($dataTable);
		$this->filterActionsDataTable($dataTable);
		return $dataTable;
	}
	
	public function getPageTitles( $idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false)
	{
		$dataTable = Piwik_Archive::getDataTableFromArchive('Actions_actions', $idSite, $period, $date, $segment, $expanded, $idSubtable);
		$this->filterPageDatatable($dataTable);
		$this->filterActionsDataTable($dataTable, $expanded);
		return $dataTable;
	}
	
	public function getPageTitle( $pageName, $idSite, $period, $date, $segment = false)
	{
		$callBackParameters = array('Actions_actions', $idSite, $period, $date, $segment, $expanded = false, $idSubtable = false );
		$dataTable = $this->getFilterPageDatatableSearch($callBackParameters, $pageName, Piwik_Tracker_Action::TYPE_ACTION_NAME);
		$this->filterActionsDataTable($dataTable);
		$this->filterPageDatatable($dataTable);
		return $dataTable;
	}
	
	public function getDownloads( $idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false )
	{
		$dataTable = Piwik_Archive::getDataTableFromArchive('Actions_downloads', $idSite, $period, $date, $segment, $expanded, $idSubtable );
		$this->filterActionsDataTable($dataTable, $expanded);
		return $dataTable;
	}

	public function getDownload( $downloadUrl, $idSite, $period, $date, $segment = false)
	{
		$callBackParameters = array('Actions_downloads', $idSite, $period, $date, $segment, $expanded = false, $idSubtable = false );
		$dataTable = $this->getFilterPageDatatableSearch($callBackParameters, $downloadUrl, Piwik_Tracker_Action::TYPE_DOWNLOAD);
		$this->filterActionsDataTable($dataTable);
		return $dataTable;
	}
	
	public function getOutlinks( $idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false )
	{
		$dataTable = Piwik_Archive::getDataTableFromArchive('Actions_outlink', $idSite, $period, $date, $segment, $expanded, $idSubtable );
		$this->filterActionsDataTable($dataTable, $expanded);
		return $dataTable;
	}

	public function getOutlink( $outlinkUrl, $idSite, $period, $date, $segment = false)
	{
		$callBackParameters = array('Actions_outlink', $idSite, $period, $date, $segment, $expanded = false, $idSubtable = false );
		$dataTable = $this->getFilterPageDatatableSearch($callBackParameters, $outlinkUrl, Piwik_Tracker_Action::TYPE_OUTLINK);
		$this->filterActionsDataTable($dataTable);
		return $dataTable;
	}
	
	/**
	 * Will search in the DataTable for a Label matching the searched string
	 * and return only the matching row, or an empty datatable
	 */
	protected function getFilterPageDatatableSearch( $callBackParameters, $search, $actionType, $table = false, $searchTree = false, $searchCurrentLevel = 0 )
	{
		if($table === false)
		{
			$table = call_user_func_array(array('Piwik_Archive', 'getDataTableFromArchive'), $callBackParameters);
		}
		if($searchTree === false)
		{
    		if($actionType == Piwik_Tracker_Action::TYPE_ACTION_NAME)
    		{
    			$searchedString = Piwik_Common::unsanitizeInputValue($search);
    		}
    		else
    		{
    			$searchedString = Piwik_Tracker_Action::excludeQueryParametersFromUrl($search, $idSite = $callBackParameters[1]);
    		}
			$searchTree = Piwik_Actions::getActionExplodedNames($searchedString, $actionType);
		}
		$rows = $table->getRows();
		$labelSearch = $searchTree[$searchCurrentLevel];
		$isEndSearch = ((count($searchTree)-1) == $searchCurrentLevel);
		foreach($rows as $key => $row)
		{
			$found = false;
			// Found a match at this level
			$label = $row->getColumn('label');
			if($label === $labelSearch)
			{
				// Is this the end of the search tree? then we found the requested row
				if($isEndSearch)
				{
//					var_dump($label); var_dump($labelSearch); exit;
					$table = new Piwik_DataTable();
					$table->addRow($row);
					return $table;
				}
				
				// If we still need to search deeper, call search 
				$idSubTable = $row->getIdSubDataTable();
				// Update the idSubtable in the callback parameter list, to fetch this subtable from the archive
				$callBackParameters[6] = $idSubTable;
				$subTable = call_user_func_array(array('Piwik_Archive', 'getDataTableFromArchive'), $callBackParameters);
				$found = $this->getFilterPageDatatableSearch($callBackParameters, $search, $actionType, $subTable, $searchTree, $searchCurrentLevel+1);
				if($found)
				{
					return $found;
				}
			}
			if(!$found)
			{
				$table->deleteRow($key);
			}
		}
		// Case the DataTable was searched but nothing was found, @see getFilterPageDatatableSearch()
		if($searchCurrentLevel == 0)
		{
			return new Piwik_DataTable;
		}
		return false;
	}
	
	/**
	 * Common filters for Page URLs and Page Titles
	 */
	protected function filterPageDatatable($dataTable)
	{
		// Average time on page = total time on page / number visits on that page
		$dataTable->queueFilter('ColumnCallbackAddColumnQuotient', array('avg_time_on_page', 'sum_time_spent', 'nb_visits', 0));
		
		// Bounce rate = single page visits on this page / visits started on this page
		$dataTable->queueFilter('ColumnCallbackAddColumnPercentage', array('bounce_rate', 'entry_bounce_count', 'entry_nb_visits', 0));
		
		// % Exit = Number of visits that finished on this page / visits on this page
		$dataTable->queueFilter('ColumnCallbackAddColumnPercentage', array('exit_rate', 'exit_nb_visits', 'nb_visits', 0));
	}
	
	/**
	 * Common filters for all Actions API getters
	 */
	protected function filterActionsDataTable($dataTable, $expanded = false)
	{
		// Must be applied before Sort in this case, since the DataTable can contain both int and strings indexes 
		// (in the transition period between pre 1.2 and post 1.2 datatable structure)
		$dataTable->filter('ReplaceColumnNames', array($recursive = true));
		$dataTable->filter('Sort', array('nb_visits', 'desc', $naturalSort = false, $expanded));
		
		$dataTable->queueFilter('ReplaceSummaryRowLabel');
	}
}

