<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * This class is responsible for handling the label parameter that can be
 * added to every API call. If the parameter is set, only the row with the matching
 * label is returned.
 * 
 * Some reports use recursive labels (e.g. action reports). Use ->>- to join them.
 * 
 * This filter does not work when expanded=1 is set because it is designed to load
 * only the subtables on the path, not all existing subtables (which would happen with
 * expanded=1). Also, the aim of this filter is to return only the row matching the
 * label. With expanded=1, the subtables of the matching row would be returned as well.
 * 
 * @package Piwik
 * @subpackage Piwik_API
 */
class Piwik_API_DataTableLabelFilter
{
    
    /** The separator to be used for specifying recursive labels */
    const RECURSIVE_LABEL_SEPARATOR = '->>-';
	
	private $apiModule;
	private $apiMethod;
	private $apiMethodForSubtable;
	private $request;
	
	/**
	 * Filter a data table by label.
	 * The filtered table is returned, which might be a new instance.
	 * 
	 * $apiModule, $apiMethod and $request are needed load sub-datatables
     * for the recursive search. If the label is not recursive, these parameters
     * are not needed.
	 * 
	 * @param $label the label to search for
	 * @param $dataTable the data table to be filtered
	 * @param $apiModule the API module that generated the data table (optional)
	 * @param $apiMethod the API method that generated the data table (optional)
	 * @param $request the request parameters used to generate the data table (optional)
	 */
	public function filter($label, $dataTable, $apiModule=false, $apiMethod=false,
			$request=array())
	{
		// make sure we have the right classes
		if (!($dataTable instanceof Piwik_DataTable)
				&& !($dataTable instanceof Piwik_DataTable_Array))
		{
			return $dataTable;
		}
		
		// check whether the label is recursive
		if ($apiModule && $apiMethod && count($request))
		{
			$this->apiModule = $apiModule;
			$this->apiMethod = $apiMethod;
			$this->request = $request;
			
			$label = explode(self::RECURSIVE_LABEL_SEPARATOR, $label);
			if (count($label) > 1)
			{
				// do a recursive search
				return $this->filterRecursive($label, $dataTable);
			}
			$label = $label[0];
		}
		
		// do a non-recursive search
		return $dataTable->getFilteredTableFromLabel($label);
	}
	
	/**
	 * This method searches for a recursive label.
	 * The label parts are used to descend recursively until a complete match is found. 
	 * 
	 * The method will return a table containing only the matching row 
	 * or an empty data table. 
	 */
	private function filterRecursive($labelParts, $dataTable)
	{
		if ($dataTable instanceof Piwik_DataTable_Array)
		{
			// search an array of tables, e.g. when using date=last30
			// note that if the root is an array, we filter all children
			// if an array occurs inside the nested table, we only look for the first match (see below)
			$newTableArray = new Piwik_DataTable_Array;
			$newTableArray->metadata = $dataTable->metadata;
			
			foreach ($dataTable->getArray() as $date => $subTable)
			{
				// for period=week, the label is "2011-08-15 to 2011-08-21", which is
				// an invalid date parameter => only use the first date (first 10 characters)
				$dateForApiRequest = substr($date, 0, 10);
				$subTable = $this->doFilterRecursive($labelParts, $subTable, $dateForApiRequest);
				$newTableArray->addTable($subTable, $date);
			}
			
			return $newTableArray;
		}
		
		return $this->doFilterRecursive($labelParts, $dataTable);
	}
	
	/**
	 * Filter the data table
	 * It will have zero or one rows afterwards (depending on whether the label was found)
	 */
	protected function doFilterRecursive($labelParts, $dataTable, $date=false)
	{
		$row = $this->doFilterRecursiveDescend($labelParts, $dataTable, $date);
		$newDataTable = $dataTable->getEmptyClone();
		if ($row !== false)
		{
			$newDataTable->addRow($row);
		}
		return $newDataTable;
	}
	
	/**
	 * Method for the recursive descend
	 * @return Piwik_DataTable_Row | false
	 */
	protected function doFilterRecursiveDescend($labelParts, $dataTable, $date=false)
	{
		if ($dataTable instanceof Piwik_DataTable)
		{
			// search for the first part of the tree search
            $labelPart = array_shift($labelParts);
            $row = $dataTable->getRowFromLabel($labelPart);
			if ($row === false)
			{
				$labelPart = htmlentities($labelPart);
				$row = $dataTable->getRowFromLabel($labelPart);
			}
			if ($row === false)
			{
				// not found
				return false;
			}
			
			// end of tree search reached
			if (count($labelParts) == 0)
			{
				return $row;
			}
			
			// match found on this level and more levels remaining: go deeper
			$request = $this->request;
            
            // this is why the filter does not work with expanded=1:
            // if the entire table is loaded, the id of sub-datatable has a different semantic.
            $idSubTable = $row->getIdSubDataTable();
            
			$request['idSubtable'] = $idSubTable;
			if ($date)
			{
				$request['date'] = $date;
			}
			
			$class = 'Piwik_'.$this->apiModule.'_API';
            $method = $this->getApiMethodForSubtable();
            
			$dataTable = Piwik_API_Proxy::getInstance()->call($class, $method, $request);
            $dataTable->applyQueuedFilters();
            
			return $this->doFilterRecursiveDescend($labelParts, $dataTable, $date);
		}

		throw new Exception("Using the label filter is not supported for DataTable ".get_class($dataTable));
	}
	
	private function getApiMethodForSubtable()
	{
		if (!$this->apiMethodForSubtable)
		{
			$meta = Piwik_API_API::getInstance()->getMetadata('all', $this->apiModule, $this->apiMethod);
			if (isset($meta[0]['actionToLoadSubTables']))
			{
				$this->apiMethodForSubtable = $meta[0]['actionToLoadSubTables'];
			}
			else
			{
				$this->apiMethodForSubtable = $this->apiMethod;
			}
		}
		
		return $this->apiMethodForSubtable; 
	}
	
}
