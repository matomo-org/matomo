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
 * Base class for manipulating data tables.
 * It provides generic mechanisms like iteration and loading subtables.
 * 
 * The manipulators are used in Piwik_API_ResponseBuilder and are triggered by
 * API parameters. They are not filters because they don't work on the pre-
 * fetched nested data tables. Instead, they load subtables using this base
 * class. This way, they can only load the tables they really need instead
 * of using expanded=1. Another difference between manipulators and filters
 * is that filters keep the overall structure of the table intact while
 * manipulators can change the entire thing.
 * 
 * @package Piwik
 * @subpackage Piwik_API
 */
abstract class Piwik_API_DataTableManipulator
{
	protected $apiModule;
	protected $apiMethod;
	protected $request;
	
	private $apiMethodForSubtable;
	
	public function __construct($apiModule=false, $apiMethod=false, $request=array()) {
		$this->apiModule = $apiModule;
		$this->apiMethod = $apiMethod;
		$this->request = $request;
	}
	
	/**
	 * This method can be used by subclasses to iterate over data tables that might be 
	 * data table arrays. It calls back the template method self::doManipulate for each table.
	 * This way, data table arrays can be handled in a transparent fashion.
	 */
	protected function manipulate($dataTable) {
		if ($dataTable instanceof Piwik_DataTable_Array)
		{
			$newTableArray = new Piwik_DataTable_Array;
			$newTableArray->metadata = $dataTable->metadata;
			$newTableArray->setKeyName($dataTable->getKeyName());
			
			foreach ($dataTable->getArray() as $date => $subTable)
			{
				// for period=week, the label is "2011-08-15 to 2011-08-21", which is
				// an invalid date parameter => only use the first date (first 10 characters)
				$dateForApiRequest = substr($date, 0, 10);
				$subTable = $this->doManipulate($subTable, $dateForApiRequest);
				$newTableArray->addTable($subTable, $date);
			}
			
			return $newTableArray;
		}
		
		if ($dataTable instanceof Piwik_DataTable)
		{
			return $this->doManipulate($dataTable);
		}
		
		return $dataTable;
	}
	
	/** Template method called from self::manipulate */
	protected abstract function doManipulate(Piwik_DataTable $dataTable, $date=false);
	
	/**
	 * Load the subtable for a row.
	 * Returns null if none is found.
	 * @return Piwik_DataTable
	 */
	protected function loadSubtable($row, $date=false) {
		if (!($this->apiModule && $this->apiMethod && count($this->request))) {
			return null;
		}
		
		$request = $this->request;
        
		// loading subtables doesn't work if expanded=1 because when the entire table is loaded,
		// the ids of sub-datatables have a different semantic.
		if (Piwik_Common::getRequestVar('expanded', false, 'int', $this->request))
		{
			throw new Exception('Cannot load subtable if expanded=1 is set.');
		}
		
        $idSubTable = $row->getIdSubDataTable();
		if ($idSubTable === null)
		{
			return null;
		}
        
		$request['idSubtable'] = $idSubTable;
		if ($date)
		{
			$request['date'] = $date;
		}
		
		$class = 'Piwik_'.$this->apiModule.'_API';
        $method = $this->getApiMethodForSubtable();
        
        $this->manipulateSubtableRequest($request);
        $request['serialize'] = 0;
		
		$dataTable = Piwik_API_Proxy::getInstance()->call($class, $method, $request);
		$response = new Piwik_API_ResponseBuilder($format = 'original', $request);
		$dataTable = $response->getResponse($dataTable);
		if (method_exists($dataTable, 'applyQueuedFilters'))
		{
			$dataTable->applyQueuedFilters();
		}
		
		return $dataTable;
	}
	
	/**
	 * In this method, subclasses can clean up the request array for loading subtables
	 * in order to make Piwik_API_ResponseBuilder behave correctly (e.g. not trigger the
	 * manipulator again).
	 */
	protected abstract function manipulateSubtableRequest(&$request);
	
	/** Extract the API method for loading subtables from the meta data */
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
