<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_DataTable
 */

require_once "DataTable/Renderer.php";
require_once "DataTable/Renderer/Console.php";
require_once "DataTable/Filter.php";
require_once "DataTable/Row.php";
require_once "DataTable/Manager.php";

/**
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
 * - multiple columns (a label, multiple values, ...)
 * - optional metadata
 * - optional - a sub DataTable associated to this row
 * 
 * Simple row example:
 * - columns = array(   'label' => 'Firefox', 
 * 						'visitors' => 155, 
 * 						'pages' => 214, 
 * 						'bounce_rate' => 67)
 * - metadata = array('logo' => '/img/browsers/FF.png')
 * - no sub DataTable
 * 
 * A more complex example would be a DataTable_Row that is associated to a sub DataTable.
 * For example, for the row of the search engine Google, 
 * we want to get the list of keywords associated, with their statistics.
 * - columns = array(   'label' => 'Google',
 * 						'visits' => 1550, 
 * 						'visits_length' => 514214, 
 * 						'returning_visits' => 77)
 * - metadata = array(	'logo' => '/img/search/google.png', 
 * 						'url' => 'http://google.com')
 * - DataTable = DataTable containing several DataTable_Row containing the keywords information for this search engine
 * 			Example of one DataTable_Row
 * 			- the keyword columns specific to this search engine = 
 * 					array(  'label' => 'Piwik', // the keyword 
 * 							'visitors' => 155,  // Piwik has been searched on Google by 155 visitors
 * 							'pages' => 214 // Visitors coming from Google with the kwd Piwik have seen 214 pages
 * 					)
 * 			- the keyword metadata = array() // nothing here, but we could imagining storing the URL of the search in Google for example
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
 * - add some metadata
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
 * ---- Other (ideas)
 * We can also imagine building a DataTable_Compare which would take N DataTable that have the same
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
 * 
 * @see Piwik_DataTable_Row A Piwik_DataTable is composed of Piwik_DataTable_Row
 * 
 * @package Piwik
 * @subpackage Piwik_DataTable
 * 
 */

class Piwik_DataTable
{	
	/**
	 * Array of Piwik_DataTable_Row
	 *
	 * @var array
	 */
	protected $rows = array();
	
	/**
	 * Id assigned to the DataTable, used to lookup the table using the DataTable_Manager
	 *
	 * @var int
	 */
	protected $currentId;
	
	/**
	 * Current depth level of this data table
	 * 0 is the parent data table
	 * 
	 * @var int
	 */
	protected $depthLevel = 0;
	
	/**
	 * This flag is set to false once we modify the table in a way that outdates the index 
	 * 
	 * @var bool
	 */
	protected $indexNotUpToDate = false;
	
	/**
	 * List of Piwik_DataTable_Filter queued to this table
	 *
	 * @var array
	 */
	protected $queuedFilters = array();
	
	/**
	 * We keep track of the number of rows before applying the LIMIT filter that deletes some rows
	 *
	 * @var int
	 */
	protected $rowsCountBeforeLimitFilter = 0;
	
	/**
	 * Defaults to false for performance reasons (most of the time we don't need recursive sorting so we save a looping over the dataTable)
	 *
	 * @var bool
	 */
	protected $enableRecursiveSort = false;

	/*
	 * @var Piwik_DataTable_Row
	 */
	protected $summaryRow = null;

	const ID_SUMMARY_ROW = -1; //TODO check that not possible adding a row with this ID normally
	const LABEL_SUMMARY_ROW = -1;

	/**
	 * Maximum nesting level
	 * 
	 * @var int
	 */
	const MAXIMUM_DEPTH_LEVEL_ALLOWED = 20;

	/**
	 * Builds the DataTable, registers itself to the manager
	 *
	 */
	public function __construct()
	{
		$this->currentId = Piwik_DataTable_Manager::getInstance()->addTable($this);
	}

	/**
	 * Sort the dataTable rows using the php callback function 
	 *
	 * @param string $functionCallback
	 */
	public function sort( $functionCallback )
	{
		$this->indexNotUpToDate = true;
		usort( $this->rows, $functionCallback );
		
		if($this->enableRecursiveSort === true)
		{
			foreach($this->getRows() as $row)
			{
				if(($idSubtable = $row->getIdSubDataTable()) !== null)
				{
					$table = Piwik_DataTable_Manager::getInstance()->getTable($idSubtable);
					$table->enableRecursiveSort();
					$table->sort($functionCallback);
				}
			}
		}
	}

	/**
	 * Enables the recursive sort. Means that when using $table->sort() 
	 * it will also sort all subtables using the same callback
	 * 
	 * @return void
	 */
	public function enableRecursiveSort()
	{
		$this->enableRecursiveSort = true;
	}

	/**
	 * Returns the number of rows before we applied the limit filter
	 *
	 * @return int
	 */
	public function getRowsCountBeforeLimitFilter()
	{
		$toReturn = $this->rowsCountBeforeLimitFilter;
		if($toReturn == 0)
		{
			return $this->getRowsCount();
		}
		return $toReturn;
	}

	/**
	 * Saves the current number of rows
	 * 
	 * @return void
	 *
	 */
	function setRowsCountBeforeLimitFilter()
	{
		$this->rowsCountBeforeLimitFilter = $this->getRowsCount();
	}

	/**
	 * Queue a DataTable_Filter that will be applied at the end of the process 
	 * (just before sending the datatable back to the browser (or API, etc.)
	 *
	 * @param string $className The class name of the filter, eg. Piwik_DataTable_Filter_Limit
	 * @param array $parameters The parameters to give to the filter, eg. array( $offset, $limit) for the filter Piwik_DataTable_Filter_Limit
	 */
	public function queueFilter( $className, $parameters = array() )
	{
		if(!is_array($parameters))
		{
			$parameters = array($parameters);
		}
		$this->queuedFilters[] = array('className' => $className, 'parameters' => $parameters);
	}

	/**
	 * Apply all filters that were previously queued to this table
	 * @see queueFilter()
	 * @return void
	 */
	public function applyQueuedFilters()
	{
		foreach($this->queuedFilters as $filter)
		{
			if($filter['className'] == 'Piwik_DataTable_Filter_Limit')
			{
				$this->setRowsCountBeforeLimitFilter();
			}
			
			$reflectionObj = new ReflectionClass($filter['className']);
			
			// the first parameter of a filter is the DataTable
			// we add the current datatable as the parameter
			$filter['parameters'] = array_merge(array($this), $filter['parameters']);
			
			$filter = $reflectionObj->newInstanceArgs($filter['parameters']); 
		}
		$this->queuedFilters = array();
	}

	/**
	 * Adds a new DataTable to this DataTable
	 * Go through all the rows of the new DataTable and applies the algorithm:
	 * - if a row in $table doesnt exist in $this we add the new row to $this
	 * - if a row exists in both $table and $this we sum the columns values into $this
	 * - if a row in $this doesnt exist in $table we keep the row of $this without modification
	 * 
	 * A common row to 2 DataTable is defined by the same label
	 * 	
	 * @example @see tests/modules/DataTable.test.php
	 */
	public function addDataTable( Piwik_DataTable $tableToSum )
	{
		foreach($tableToSum->getRows() as $row)
		{
			$labelToLookFor = $row->getColumn('label');
			$rowFound = $this->getRowFromLabel( $labelToLookFor );
			if($rowFound === false)
			{
				if( $labelToLookFor === self::LABEL_SUMMARY_ROW )
				{
					$this->addSummaryRow($row );
				}
				else
				{
					$this->addRow( $row );
				}
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

	/**
	 * Returns the Piwik_DataTable_Row that has a column 'label' with the value $label
	 *
	 * @param string $label Value of the column 'label' of the row to return
	 * @return Piwik_DataTable_Row|false The row if found, false otherwise
	 */
	public function getRowFromLabel( $label )
	{
		if($this->indexNotUpToDate)
		{
			$this->rebuildIndex();
		}
		
		if($label === self::LABEL_SUMMARY_ROW
			&& !is_null($this->summaryRow))
		{
			return $this->summaryRow;
		}
		
		$label = (string)$label;
		if(!isset($this->rowsIndexByLabel[$label]))
		{
			return false;
		}
		return $this->rows[$this->rowsIndexByLabel[$label]];
	}

	/**
	 * Rebuilds the index used to lookup a row by label
	 *
	 * @return void
	 */
	private function rebuildIndex()
	{
		foreach($this->rows as $id => $row)
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
	 * Returns the ith row in the array
	 *
	 * @param int $id
	 * @return Piwik_DataTable_Row or false if not found
	 */
	public function getRowFromId($id)
	{
		if(!isset($this->rows[$id]))
		{
			if($id == self::ID_SUMMARY_ROW
				&& !is_null($this->summaryRow))
			{
				return $this->summaryRow;
			}
			return false;
		}
		return $this->rows[$id];
	}

	/**
	 * Shortcut function used for performance reasons
	 * 
	 * @param Piwik_DataTable_Row $row to add at the end of the array
	 */
	public function addRow( Piwik_DataTable_Row $row )
	{
		$this->rows[] = $row;	
		$this->indexNotUpToDate = true;
	}

	/**
	 * Sets the summary row (a dataTable can have only one summary row)
	 *
	 * @param Piwik_DataTable_Row $row
	 */
	public function addSummaryRow( Piwik_DataTable_Row $row )
	{
		$this->summaryRow = $row;
	}

	/**
	 * Returns the dataTable ID
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->currentId;
	}

	/**
	 * Adds a new row from a PHP array data structure
	 * You should use loadFromArray for performance!
	 * 
	 * @param array $row, eg. array(Piwik_DataTable_Row::COLUMNS => array( 'visits' => 13, 'test' => 'toto'),)
	 */
	public function addRowFromArray( $row )
	{
		$this->loadFromArray(array($row));
	}

	/**
	 * Adds a new row a PHP array data structure
	 * You should use loadFromSimpleArray for performance!
	 * 
	 * @param array $row, eg.  array('name' => 'google analytics', 'license' => 'commercial')
	 */
	public function addRowFromSimpleArray( $row )
	{
		$this->loadFromSimpleArray(array($row));
	}

	/**
	 * Returns the array of Piwik_DataTable_Row
	 * 
	 * @return array of Piwik_DataTable_Row
	 */
	public function getRows()
	{
		if(is_null($this->summaryRow))
		{
			return $this->rows;
		}
		else
		{
			return $this->rows + array(self::ID_SUMMARY_ROW => $this->summaryRow);
		}
	}

	/**
	 * Returns the number of rows in the table
	 * 
	 * @return int
	 */
	public function getRowsCount()
	{
		$count = count($this->rows);
		if(is_null($this->summaryRow))
		{
			return $count;
		}
		else
		{
			return $count + 1;
		}
	}

	/**
	 * Returns the first row of the DataTable
	 *
	 * @return Piwik_DataTable_Row
	 */
	public function getFirstRow()
	{
		if(count($this->rows) == 0)
		{
			if(!is_null($this->summaryRow))
			{
				return $this->summaryRow;
			}
			return false;
		}
		// TODO possible improvement => reset() and value()
		$row = array_slice($this->rows, 0, 1);
		return $row[0];
	}

	/**
	 * Returns the last row of the DataTable
	 *
	 * @return Piwik_DataTable_Row
	 */
	public function getLastRow()
	{
		if(!is_null($this->summaryRow))
		{
			return $this->summaryRow;
		}
		
		if(count($this->rows) == 0)
		{
			return false;
		}
		$row = array_slice($this->rows, -1);
		return $row[0];
	}

	/**
	 * Returns the sum of the number of rows of all the subtables 
	 * 		+ the number of rows in the parent table
	 * 
	 * @return int
	 */
	public function getRowsCountRecursive()
	{
		$totalCount = 0;
		foreach($this->rows as $row)
		{
			if(($idSubTable = $row->getIdSubDataTable()) !== null)
			{
				$subTable = Piwik_DataTable_Manager::getInstance()->getTable($idSubTable);
				$count = $subTable->getRowsCountRecursive();
				$totalCount += $count;
			}
		}
		
		$totalCount += $this->getRowsCount();
		return $totalCount;
	}

	/**
	 * Delete a given column $name in all the rows
	 *
	 * @param string $name
	 */
	public function deleteColumn( $name )
	{
		foreach($this->getRows() as $row)
		{
			$row->deleteColumn($name);
		}
		if(!is_null($this->summaryRow))
		{
			$this->summaryRow->deleteColumn($name);
		}
	}

	/**
	 * Deletes the ith row 
	 *
	 * @param int $key
	 * @throws Exception if the row $id cannot be found
	 */
	public function deleteRow( $id )
	{
		if($id === self::ID_SUMMARY_ROW)
		{
			$this->summaryRow = null;
			return;
		}
		if(!isset($this->rows[$id]))
		{
			throw new Exception("Trying to delete unknown row with idkey = $id");
		}
		unset($this->rows[$id]);
	}

	/**
	 * Deletes all row from offset, offset + limit.
	 * If limit is null then limit = $table->getRowsCount()
	 *
	 * @param int $offset
	 * @param int $limit
	 */
	public function deleteRowsOffset( $offset, $limit = null )
	{
		if($limit === 0)
		{
			return;
		}

		$count = $this->getRowsCount();
		if($offset >= $count)
		{
			return;
		}

		// if we delete until the end, we delete the summary row as well
		if( is_null($limit)
			|| $limit >= $count )
		{
			$this->summaryRow = null;
		}

		if(is_null($limit))
		{
			array_splice($this->rows, $offset);
		}
		else
		{
			array_splice($this->rows, $offset, $limit);
		}
	}

	/**
	 * Deletes the rows from the list of rows ID 
	 *
	 * @param array $aKeys ID of the rows to delete
	 * @throws Exception if any of the row to delete couldn't be found
	 */
	public function deleteRows( array $aKeys )
	{
		foreach($aKeys as $key)
		{
			$this->deleteRow($key);
		}
	}

	/**
	 * Returns a simple output of the DataTable for easy visualization
	 * Example: echo $datatable;
	 *
	 * @return string
	 */
	public function __toString()
	{
		$renderer = new Piwik_DataTable_Renderer_Console($this);
		return (string)$renderer;
	}

	/**
	 * Returns true if both DataTable are exactly the same.
	 * Used in unit tests.
	 * 
	 * @param Piwik_DataTable $table1
	 * @param Piwik_DataTable $table2
	 * @return bool
	 */
	static public function isEqual(Piwik_DataTable $table1, Piwik_DataTable $table2)
	{
		$rows1 = $table1->getRows();
		$rows2 = $table2->getRows();
		
		$table1->rebuildIndex();
		$table2->rebuildIndex();
		
		$countrows1 = $table1->getRowsCount();
		$countrows2 = $table2->getRowsCount();
		
		if($countrows1 != $countrows2)
		{
			return false;
		}
		
		foreach($rows1 as $row1)
		{
			$row2 = $table2->getRowFromLabel($row1->getColumn('label'));
			if($row2 === false)
			{
				return false;
			}
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
	 * We save DataTable in serialized format in the Database.
	 * Each row of this returned PHP array will be a row in the DB table.
	 * 
	 * The keys of the array are very important as they are used to define the DataTable
	 * 
	 * IMPORTANT: The main table (level 0, parent of all tables) will always be indexed by 0
	 * 	even it was created after some other tables.
	 * 	It also means that all the parent tables (level 0) will be indexed with 0 in their respective 
	 *  serialized arrays. You should never lookup a parent table using the getTable( $id = 0) as it 
	 *  won't work.
	 * 
	 * @throws Exception if an infinite recursion is found (a table row's has a subtable that is one of its parent table)
	 * @param int If not null, defines the number of rows maximum of the serialized dataTable
	 * 	          If $addSummaryRowAfterNRows is less than the size of the table, a SummaryRow will be added at the end of the table, that
	 *            is the sum of the values of all the rows after the Nth row. All the rows after the Nth row will be deleted.
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
	 * 
	 */
	public function getSerialized( $maximumRowsInDataTable = null, $maximumRowsInSubDataTable = null )
	{
		static $depth = 0;
		
		if($depth > self::MAXIMUM_DEPTH_LEVEL_ALLOWED)
		{
			throw new Exception("Maximum recursion level of ".self::MAXIMUM_DEPTH_LEVEL_ALLOWED. " reached. You have probably set a DataTable_Row with an associated DataTable which belongs already to its parent hierarchy.");
		}
		
		if( !is_null($maximumRowsInDataTable) )
		{
			$filter = new Piwik_DataTable_Filter_AddSummaryRow($this, $maximumRowsInDataTable - 1);
		}
		
		// For each row, get the serialized row
		// If it is associated to a sub table, get the serialized table recursively ;
		// but returns all serialized tables and subtable in an array of 1 dimension!
		$aSerializedDataTable = array();
		foreach($this->rows as $row)
		{
			if(($idSubTable = $row->getIdSubDataTable()) !== null)
			{
				$subTable = Piwik_DataTable_Manager::getInstance()->getTable($idSubTable);
				$depth++;
				$aSerializedDataTable = $aSerializedDataTable + $subTable->getSerialized( $maximumRowsInSubDataTable, $maximumRowsInSubDataTable );
				$depth--;
			}
		}
		// we load the current Id of the DataTable
		$forcedId = $this->getId();
		
		// if the datatable is the parent we force the Id at 0 (this is part of the specification)
		if($depth == 0)
		{
			$forcedId = 0;
		}
		
		// we then serialize the rows and store them in the serialized dataTable
		$aSerializedDataTable[$forcedId] = serialize($this->rows + array( self::ID_SUMMARY_ROW => $this->summaryRow));
		
		return $aSerializedDataTable;
	}

	 /**
	  * Load a serialized string of a datatable.
	  * 
	  * Does not load recursively all the sub DataTable.
	  * They will be loaded only when requesting them specifically.
	  * 
	  * The function creates all the necessary DataTable_Row
	  * 
	  * @param string Serialized string of a datatable
	  * @return void
	  */
	public function loadFromSerialized( $stringSerialized )
	{
		$serialized = unserialize($stringSerialized);
		if($serialized === false)
		{
			throw new Exception("The unserialization has failed!");
		}
		$this->loadFromArray($serialized);
	}

	/**
	 * Loads the DataTable from a PHP array data structure
	 * 
	 * @param array Array with the following structure
	 * 			array(
 	 * 				// row1
	 * 				array( 
	 * 				Piwik_DataTable_Row::COLUMNS => array( col1_name => value1, col2_name => value2, ...),
	 * 				Piwik_DataTable_Row::METADATA => array( metadata1_name => value1,  ...), // see Piwik_DataTable_Row
	 * 
	 * 				),
	 * 					
	 * 				// row2
	 * 				array( ... ), 
	 * 				
	 * 			)
	 * @return void
	 */
	public function loadFromArray( $array )
	{
		foreach($array as $id => $row)
		{
			if(is_array($row))
			{
				$row = new Piwik_DataTable_Row($row);
			}
			if($id == self::ID_SUMMARY_ROW)
			{
				$this->summaryRow = $row;
			}
			else 
			{
				$this->addRow($row);
			}
		}
	}

	/**
	 * Loads the data from a simple php array.
	 * Basically maps a simple multidimensional php array to a DataTable.
	 * Not recursive (if a row contains a php array itself, it won't be loaded)
	 * 
	 * @param array Array with the simple structure:
	 * 		array(
	 * 			array( col1_name => valueA, col2_name => valueC, ...),
	 * 			array( col1_name => valueB, col2_name => valueD, ...), 
	 *		)
	 */
	public function loadFromSimpleArray( $array )
	{
		if(count($array) === 0)
		{
			return;
		}
		
		// we define an exception we may throw if at one point we notice that we cannot handle the data structure
		$e = new Exception(" Data structure returned is not convertible in the requested format.".
						" Try to call this method with the parameters '&format=original&serialize=1'".
						"; you will get the original php data structure serialized.".
						" The data structure looks like this: \n \$data = " . var_export($array, true) . "; ");
				
		
		// first pass to see if the array has the structure
		// array(col1_name => val1, col2_name => val2, etc.)
		// with val* that are never arrays (only strings/numbers/bool/etc.)
		// if we detect such a "simple" data structure we convert it to a row with the correct columns' names
		$thisIsNotThatSimple = false;
		
		foreach($array as $columnName => $columnValue )
		{
			if(is_array($columnValue) || is_object($columnValue)) 
			{
				$thisIsNotThatSimple = true;
				break;
			}
		}
		if($thisIsNotThatSimple === false)
		{
			// case when the array is indexed by the default numeric index
			if( array_keys($array) == array_keys(array_fill(0, count($array), true)) )
			{
				foreach($array as $row)
				{
					$this->addRow( new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => array($row) ) ) );					
				}
			}
			else
			{
				$this->addRow( new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => $array ) ) );
			}
			// we have converted our simple array to one single row
			// => we exit the method as the job is now finished 
			return;
		}
		
		
		foreach($array as $key => $row)
		{
			// stuff that looks like a line
			if(is_array($row))
			{
				/**
				 * We make sure we can convert this PHP array without losing information.
				 * We are able to convert only simple php array (no strings keys, no sub arrays, etc.)
				 * 
				 */
				
				// if the key is a string it means that some information was contained in this key. 
				// it cannot be lost during the conversion. Because we are not able to handle properly
				// this key, we throw an explicit exception.
				if(is_string($key))
				{
					throw $e;
				}
				// if any of the sub elements of row is an array we cannot handle this data structure...
				foreach($row as $subRow)
				{
					if(is_array($subRow))
					{
						throw $e;						
					}
				}
				$row = new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => $row ) );		
			}
			// other (string, numbers...) => we build a line from this value
			else
			{
				$row = new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => array($key => $row)) );
			}				
			$this->addRow($row);
		}
	}

	/**
	 * Rewrites the input $array 
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
	 * @param array $array See method description
	 * @param array|null $subtablePerLabel see method description
	 * 
	 * @return void
	 */
	public function loadFromArrayLabelIsKey( $array, $subtablePerLabel = null)
	{
		$cleanRow = array();
		foreach($array as $label => $row)
		{
			// we make sure that the label column is first in the list! 
			// important for the UI javascript mainly...
			// array_merge doesn't work here as it reindex the numeric value
			// see the test testMergeArray in PHP_Related.test.php
			$cleanRow[Piwik_DataTable_Row::COLUMNS] = array('label' => $label) + $row;
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

	/**
	 * At destruction we try to free memory
	 * But php doesn't give us much control on this
	 */
	public function __destruct()
	{
		unset($this->rows);
	}
	
}
