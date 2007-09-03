<?php
/**
 * 
 * 
 * Initial Specification 
 * ---------------------------------------------------------
 * CAREFUL: It may be outdated as I have not reviewed it yet
 * ---------------------------------------------------------
 * 
 * ---- DataTable
 * A DataTable is a data structure used to store complex tables of data.
 * 
 * A DataTable is composed of multiple DataTable_Row.
 * A DataTable can be applied one or several DataTable_Filter.
 * A DataTable can be given to a DataTable_Renderer that would export the data under a given format (XML, HTML, etc.).
 * 
 * A DataTable has the following features:
 * - serializable to be stored in the DB
 * - loadable from the serialized version
 * - efficient way of loading data from an external source (from a PHP array structure)
 * - very simple interface to get data from the table
 * 
 * ---- DataTable_Row
 * A DataTableRow in the table is defined by
 * - multiple column (a label, multiple values, ...)
 * - details
 * - [a sub DataTable associated to this row]
 * 
 * Simple row example:
 * - columns = array(   'label' => 'Firefox', 
 * 						'visitors' => 155, 
 * 						'pages' => 214, 
 * 						'bounce_rate' => 67)
 * - details = array('logo' => '/img/browsers/FF.png')
 * - no sub DataTable
 * 
 * A more complex example would be a DataTable_Row that is associated to a sub DataTable.
 * For example, for the row of the search engine Google, 
 * we want to get the list of keywords associated, with their statistics.
 * - columns = array(   'label' => 'Google',
 * 						'visits' => 1550, 
 * 						'visits_length' => 514214, 
 * 						'returning_visits' => 77)
 * - details = array(	'logo' => '/img/search/google.png', 
 * 						'url' => 'http://google.com')
 * - DataTable = DataTable containing several DataTable_Row containing the keywords information for this search engine
 * 			Example of one DataTable_Row
 * 			- the keyword columns specific to this search engine = 
 * 					array(  'label' => 'Piwik', // the keyword 
 * 							'visitors' => 155,  // Piwik has been searched on Google by 155 visitors
 * 							'pages' => 214 // Visitors coming from Google with the kwd Piwik have seen 214 pages
 * 					)
 * 			- the keyword details = array() // nothing here, but we could imagining storing the URL of the search in Google for example
 * 			- no subTable
 *  
 * 
 * ---- DataTable_Filter
 * A DataTable_Filter is a applied to a DataTable and so 
 * can filter information in the multiple DataTable_Row.
 * 
 * For example a DataTable_Filter can:
 * - remove rows from the table, 
 * 		for example the rows' labels that do not match a given searched pattern
 * 		for example the rows' values that are less than a given percentage (low population)
 * - return a subset of the DataTable 
 * 		for example a function that apply a limit: $offset, $limit
 * - add / remove columns
 * 		for example adding a column that gives the percentage of a given value
 * - add some details
 * 		for example the 'logo' path if the filter detects the logo
 * - edit the value, the label
 * - change the rows order
 * 		for example if we want to sort by Label alphabetical order, or by any column value
 * 
 * When several DataTable_Filter are to be applied to a DataTable they are applied sequentially.
 * A DataTable_Filter is assigned a priority. 
 * For example, filters that 
 * 	- sort rows should be applied with the highest priority
 * 	- remove rows should be applied with a high priority as they prune the data and improve performance.
 * 	
 * ---- Code example
 * 
 * $table = new DataTable;
 * $table->loadFromArray( array(...) );
 * 
 * # sort the table by visits asc
 * $filter = new DataTable_Filter_Sort( $table, 'visits', 'asc');
 * $tableFiltered = $filter->getTableFiltered();
 * 
 * # add a filter to select only the website with a label matching '*.com' (regular expression)
 * $filter = new DataTable_Filter_Pattern( $table, 'label', '*(.com)');
 * $tableFiltered = $filter->getTableFiltered();
 * 
 * # keep the 20 elements from offset 15
 * $filter = new DataTable_Filter_Limit( $tableFiltered, 15, 20);
 * $tableFiltered = $filter->getTableFiltered();
 * 
 * # add a column computing the percentage of visits
 * # params = table, column containing the value, new column name to add, number of total visits to use to compute the %
 * $filter = new DataTable_Filter_AddColumnPercentage( $tableFiltered, 'visits', 'visits_percentage', 2042);
 * $tableFiltered = $filter->getTableFiltered();
 * 
 * # we get the table as XML
 * $xmlOutput = new DataTable_Exporter_Xml( $table );
 * $xmlOutput->setHeader( ... );
 * $xmlOutput->setColumnsToExport( array('visits', 'visits_percent', 'label') );
 * $XMLstring = $xmlOutput->getOutput();
 * 
 * 
 * @package Piwik
 * @subpackage Piwik_DataTable
 * 
 */
require_once "DataTable/Renderer.php";
require_once "DataTable/Renderer/Console.php";
require_once "DataTable/Filter.php";
require_once "DataTable/Row.php";
require_once "DataTable/Manager.php";

class Piwik_DataTable
{	
	protected $rows = array();
	protected $currentId;
	protected $depthLevel = 0;
	protected $indexNotUpToDate = false;
	protected $queuedFilters = array();
	
	const MAXIMUM_DEPTH_LEVEL_ALLOWED = 20;
	
	public function __construct()
	{
		$this->currentId = Piwik_DataTable_Manager::getInstance()->addTable($this);
	}
	
	public function sort( $functionCallback )
	{
		$this->indexNotUpToDate = true;
		usort(&$this->rows, $functionCallback);
	}
	
	public function queueFilter( $className, $parameters = array() )
	{
		if(!is_array($parameters))
		{
			$parameters = array($parameters);
		}
		$this->queuedFilters[] = array('className' => $className, 'parameters' => $parameters);
	}
	public function applyQueuedFilters()
	{
		foreach($this->queuedFilters as $filter)
		{
			$reflectionObj = new ReflectionClass($filter['className']);
			
			// the first parameter of a filter is the DataTable
			// we add the current datatable as the parameter
			$filter['parameters'] = array_merge(array($this), $filter['parameters']);
			
			$filter = $reflectionObj->newInstanceArgs($filter['parameters']); 
		}
		$this->queuedFilters = array();
	}
	
	public function rebuildIndex()
	{
		foreach($this->getRows() as $id => $row)
		{
			$label = $row->getColumn('label');
		
			if($label !== false)
			{
				$this->rowsIndexByLabel[$label] = $id;
			}
		}
		
		$this->indexNotUpToDate = false;
	}
	
	
	/**
	 * Add a new DataTable to this DataTable
	 * Go through all the rows of the new DataTable and applies the algorithm:
	 * - if a row in $table doesnt exist in $this we add the row to $this
	 * - if a row exists in both $table and $this we add the columns values into $this
	 * 
	 * A common row to 2 DataTable is defined by the same label
	 * 
	 * Details: 
	 * - if a row in $this doesnt exist in $table we simply keep the row of $this without modification
	 * 	
	 * @example @see tests/modules/DataTable.test.php
	 */
	public function addDataTable( Piwik_DataTable $tableToSum )
	{
		foreach($tableToSum->getRows() as $row)
		{
			$labelToLookFor = $row->getColumn('label');
			$rowFound = $this->getRowFromLabel( $labelToLookFor );
			
			// the row with this label already exists
			if($rowFound === false)
			{
				$this->addRow( $row );
			}
			else
			{
				$rowFound->sumRow( $row );

				// if the row to add has a subtable whereas the current row doesn't
				// we simply add it (cloning the subtable)
				// if the row has the subtable already 
				// then we have to recursively sum the subtables
				if(($idSubTable = $row->getIdSubDataTable()) !== null)
				{
					$rowFound->sumSubtable( Piwik_DataTable_Manager::getInstance()->getTable($idSubTable) );
				}
			}
		}
	}
	
	public function getRowFromLabel( $label )
	{
		if($this->indexNotUpToDate)
		{
			throw new Exception("TODO need to rebuild the index of the DataTable (some rows have been moved)!");
		}
		
		$label = (string)$label;
		if(!isset($this->rowsIndexByLabel[$label]))
		{
			return false;
		}
		return $this->rows[$this->rowsIndexByLabel[$label]];
	}

	public function __destruct()
	{
		unset($this->rows);
	}

	/**
	 * Shortcut function used for performance reasons
	 */
	public function addRow( $row )
	{
		$this->rows[] = $row;
		
		$label = $row->getColumn('label');
		
		if($label !== false)
		{
			if(isset($this->rowsIndexByLabel[$label]))
			{
				throw new Exception("The row with the label $label already exists in this DataTable");
			}
			$this->rowsIndexByLabel[$label] = count($this->rows) - 1;
		}
	}
	
	public function getId()
	{
		return $this->currentId;
	}
	
	
	/**
	 * You should use loadFromArray for performance!
	 */
	public function addRowFromArray( $row )
	{
		$this->loadFromArray(array($row));
	}
	
	/**
	 * Returns the array of Piwik_DataTable_Row
	 */
	public function getRows()
	{
		return $this->rows;
	}
	/**
	 * Returns the number of rows 
	 */
	public function getRowsCount()
	{
		return count($this->rows);
	}
	
	public function deleteRow( $key )
	{
		if(!isset($this->rows[$key]))
		{
			throw new Exception("Trying to delete unknown row with idkey = $key");
		}
		unset($this->rows[$key]);
	}
	
	public function deleteRowsOffset( $offset, $limit = null )
	{
		if(is_null($limit))
		{
			$limit = count($this->rows);
		}
		array_splice($this->rows, $offset, $limit);
	}
	
	public function deleteRows( array $aKeys )
	{
		foreach($aKeys as $key)
		{
			$this->deleteRow($key);
		}
	}
	
	public function __toString()
	{
		$renderer = new Piwik_DataTable_Renderer_Console($this);
		return (string)$renderer;
	}
	
	
	static public function isEqual($table1, $table2)
	{
		$rows1 = $table1->getRows();
		$rows2 = $table2->getRows();
		
		$table1->rebuildIndex();
		$table2->rebuildIndex();
		
		$countrows1 = count($rows1);
		$countrows2 = count($rows2);
		
		if($countrows1 != $countrows2)
		{
			return false;
		}
		
		foreach($rows1 as $row1)
		{
			$row2 = $table2->getRowFromLabel($row1->getColumn('label'));	
			if( !Piwik_DataTable_Row::isEqual($row1,$row2) )
			{
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * The serialization returns a one dimension array containing all the 
	 * serialized DataTable contained in this DataTable.
	 * 
	 * The keys of the array are very important as they are used to define the DataTable
	 * For the example the key 3 is used in the array corresponding to the key 2 
	 * because the key 3 is the array which is a child of the array corresponding to the key 2
	 * 
	 * IMPORTANT: The main table (level 0, parent of all tables) will always be indexed by 0
	 * 	even it was created after some other tables.
	 * 	It also means that all the parent tables (level 0) will be indexed with 0 in their respective 
	 *  serialized arrays. You should never lookup a parent table using the getTable( $id = 0) as it 
	 *  won't work.
	 * 
	 * @return array Serialized arrays	
	 * 			array( 	// Datatable level0
	 * 					0 => 'eghuighahgaueytae78yaet7yaetae', 
	 * 
	 * 					// first Datatable level1
	 * 					1 => 'gaegae gh gwrh guiwh uigwhuige',
	 * 					
	 * 					//second Datatable level1 
	 * 					2 => 'gqegJHUIGHEQjkgneqjgnqeugUGEQHGUHQE',  
	 * 					
	 * 					//first Datatable level3 (child of second Datatable level1 for example)
 	 *					3 => 'eghuighahgaueytae78yaet7yaetaeGRQWUBGUIQGH&QE',
	 * 					);
	 */
	public function getSerialized()
	{
		static $depth = 0;
		
		if($depth > self::MAXIMUM_DEPTH_LEVEL_ALLOWED)
		{
			throw new Exception("Maximum recursion level of ".self::MAXIMUM_DEPTH_LEVEL_ALLOWED. " reached. You have probably set a DataTable_Row with an associated DataTable which belongs already to its parent hierarchy.");
		}
		// for each row, get the serialized row
		// if it is associated to a sub table, get the serialized table recursively
		// but returns all serialized tables and subtable in an array of 1 dimension!
		
		$aSerializedDataTable = array();
		foreach($this->rows as $row)
		{
			if(($idSubTable = $row->getIdSubDataTable()) !== null)
			{
				$subTable = Piwik_DataTable_Manager::getInstance()->getTable($idSubTable);
				$depth++;
				$serialized = $subTable->getSerialized();
				$depth--;
				
				$aSerializedDataTable = $aSerializedDataTable + $serialized;
			}
		}
		//TODO COmment
		$forcedId = $this->getId();
		if($depth==0)
		{
			$forcedId = 0;
		}	
		$aSerializedDataTable[$forcedId] = serialize($this->rows);
		
		return $aSerializedDataTable;
	}
	 
	 /**
	  * Load a serialized string.
	  * 
	  * Does not load recursively all the sub DataTable.
	  * They will be loaded only when requesting them specifically.
	  * 
	  * The function creates the DataTable_Row
	  * 
	  */
	public function loadFromSerialized( $stringSerialized )
	{
		$serialized = unserialize($stringSerialized);
		if($serialized===false)
		{
			throw new Exception("The unserialization has failed!");
		}
		$this->loadFromArray($serialized);
	}
		 
	/**
	 * Load the data from a PHP array 
	 * 
	 * @param array Array with the following structure
	 * 				array(
	 * 					array(...), // row1
	 * 					array(...), // row2
	 * 						)
	 * 				)
	 * 
	 * @see DataTable_Row::loadFromArray for the row structures
	 */
	public function loadFromArray( $array )
	{
		foreach($array as $row)
		{
			if(is_array($row))
			{
				$row = new Piwik_DataTable_Row($row);
			}
			
			$this->addRow($row);
		}
	}
	
	/**
	 * Rewrite the input $array 
	 * array (
	 * 	 LABEL => array(col1 => X, col2 => Y),
	 * 	 LABEL2 => array(col1 => X, col2 => Y),
	 * )
	 * 
	 * to the structure 
	 * array (
	 * 	 array( Piwik_DataTable_Row::COLUMNS => array('label' => LABEL, col1 => X, col2 => Y)),
	 * 	 array( Piwik_DataTable_Row::COLUMNS => array('label' => LABEL2, col1 => X, col2 => Y)),
	 * )
	 * 
	 * The optional parameter $subtablePerLabel is an array of subTable associated to the rows of the $array
	 * For example if $subtablePerLabel is given
	 * array(
	 * 		LABEL => #Piwik_DataTable_ForLABEL,
	 * 		LABEL2 => #Piwik_DataTable_ForLABEL2,
	 * )
	 * 
	 * the $array would become 
	 * array (
	 * 	 array( 	Piwik_DataTable_Row::COLUMNS => array('label' => LABEL, col1 => X, col2 => Y),
	 * 				Piwik_DataTable_Row::DATATABLE_ASSOCIATED => #ID DataTable For LABEL
	 * 		),
	 * 	 array( 	Piwik_DataTable_Row::COLUMNS => array('label' => LABEL2, col1 => X, col2 => Y)
	 * 				Piwik_DataTable_Row::DATATABLE_ASSOCIATED => #ID2 DataTable For LABEL2
	 * 		),
	 * )
	 * 
	 */
	public function loadFromArrayLabelIsKey( $array, $subtablePerLabel = null)
	{
		$cleanRow = array();
		foreach($array as $label => $row)
		{
			$cleanRow[Piwik_DataTable_Row::COLUMNS] = $row;
			$cleanRow[Piwik_DataTable_Row::COLUMNS]['label'] = $label;
			if(!is_null($subtablePerLabel)
				// some rows of this table don't have subtables 
				// (for examplecase of the campaign without keywords )
				&& isset($subtablePerLabel[$label]) 
			)
			{
				$cleanRow[Piwik_DataTable_Row::DATATABLE_ASSOCIATED] = $subtablePerLabel[$label];
			}
			
			$this->addRow( new Piwik_DataTable_Row($cleanRow) );
		}
	}
}


function Piwik_DataTable_orderRowByLabel($o1,$o2)
{
	return strcmp($o1->getColumn('label'), $o2->getColumn('label'));
}

/**
 * ---- Other
 * We can also imagine building a DataTable_Compare which would take 2 DataTable that have the same
 * structure and would compare them, by computing the percentages of differences, etc.
 * 
 * For example 
 * DataTable1 = [ keyword1, 1550 visits]
 * 				[ keyword2, 154 visits ]
 * DataTable2 = [ keyword1, 1004 visits ]
 * 				[ keyword3, 659 visits ]
 * DataTable_Compare = result of comparison of table1 with table2
 * 						[ keyword1, +154% ]
 * 						[ keyword2, +1000% ]
 * 						[ keyword3, -430% ]
 */
