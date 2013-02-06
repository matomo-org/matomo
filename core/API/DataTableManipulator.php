<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

	/**
	 * Constructor
	 *
	 * @param bool   $apiModule
	 * @param bool   $apiMethod
	 * @param array  $request
	 */
	public function __construct($apiModule=false, $apiMethod=false, $request=array()) {
		$this->apiModule = $apiModule;
		$this->apiMethod = $apiMethod;
		$this->request = $request;
	}

	/**
	 * This method can be used by subclasses to iterate over data tables that might be
	 * data table arrays. It calls back the template method self::doManipulate for each table.
	 * This way, data table arrays can be handled in a transparent fashion.
	 *
	 * @param Piwik_DataTable_Array|Piwik_DataTable  $dataTable
	 * @throws Exception
	 * @return Piwik_DataTable_Array|Piwik_DataTable
	 */
	protected function manipulate($dataTable)
	{
		if ($dataTable instanceof Piwik_DataTable_Array)
		{
			$newTableArray = new Piwik_DataTable_Array;
			$newTableArray->metadata = $dataTable->metadata;
			$newTableArray->setKeyName($dataTable->getKeyName());
			
			foreach ($dataTable->getArray() as $date => $subTable)
			{
				// for period=week, the label is "2011-08-15 to 2011-08-21", which is
				// an invalid date parameter => only use the first date
				// in other languages the whole string does not start with the date so
				// we need to preg match it.
				if (!preg_match('/[0-9]{4}(-[0-9]{2})?(-[0-9]{2})?/', $date, $match))
				{
					throw new Exception("Could not recognize date: $date");
				}
				$dateForApiRequest = $match[0];
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

	/**
	 * Template method called from self::manipulate
	 *
	 * @param Piwik_DataTable  $dataTable
	 * @param bool|string      $date
	 * @return
	 */
	protected abstract function doManipulate(Piwik_DataTable $dataTable, $date=false);

	/**
	 * Load the subtable for a row.
	 * Returns null if none is found.
	 *
	 * @param Piwik_Datatable_Row  $row
	 * @param bool|string          $date
	 * @throws Exception
	 * @return Piwik_DataTable
	 */
	protected function loadSubtable($row, $date=false) {
		if (!($this->apiModule && $this->apiMethod && count($this->request))) {
			return null;
		}
		
		$request = $this->request;
		
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
		$request['expanded'] = 0;
        
		// don't want to run recursive filters on the subtables as they are loaded,
		// otherwise the result will be empty in places (or everywhere). instead we
		// run it on the flattened table.
		unset($request['filter_pattern_recursive']);
		
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
	 *
	 * @param $request
	 * @return
	 */
	protected abstract function manipulateSubtableRequest(&$request);
	
	/**
	 * Extract the API method for loading subtables from the meta data
	 *
	 * @return string
	 */
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
