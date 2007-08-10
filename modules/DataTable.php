<?php
/**
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
 * 
 */

class Piwik_DataTable_Manager
{
	static private $instance = null;
	protected function __construct()
	{}
	
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	static private $id = 0;
	static private $tables = array();
	
	function addTable( $table )
	{
		$this->tables[] = $table;
		return count($this->tables);
	}
	
	function getTable( $idTable )
	{
		// the array tables is indexed at 0 
		// but the index is computed as the count() of the array after inserting the table
		$idTable -= 1;
		
		if(isset($this->tables[$idTable]))
		{
			return $this->tables[$idTable];
		}
		
		return null;
	} 
}

class Piwik_DataTable
{	
	protected $rows = array();
	protected $currentId;
	protected $depthLevel = 0;
	
	const MAXIMUM_DEPTH_LEVEL_ALLOWED = 20;
	
	public function __construct()
	{
		$this->currentId = Piwik_DataTable_Manager::getInstance()->addTable($this);
//		self::$idSubtableAssociated[$this->currentId] = true;
	}
	
	public function getId()
	{
		return $this->currentId;
	}
	
	/**
	 * The serialization returns a one dimension array containing all the 
	 * serialized DataTable contained in this DataTable.
	 * 
	 * The keys of the array are very important as they are used to define the DataTable
	 * For the example the key 3 is used in the array corresponding to the key 2 
	 * because the key 3 is the array which is a child of the array corresponding to the key 2
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
		$aSerializedDataTable[$this->getId()] = serialize($this->rows);
		
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
			
			$this->rows[] = $row;
		}
	}
	
	/**
	 * You should use loadFromArray for performance!
	 */
	public function addRow( $row )
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
	
	public function deleteRowsOffset( $offset, $limit )
	{
		array_splice($this->rows, $offset, $limit);
	}
	
	public function deleteRows( array $aKeys )
	{
		foreach($aKeys as $key)
		{
			$this->deleteRow($key);
		}
	}
}

class Piwik_DataTable_Renderer
{
	protected $table;
	function __construct($table)
	{
		if(!($table instanceof Piwik_DataTable))
		{
			throw new Exception("The renderer accepts only a Piwik_DataTable object.");
		}
		$this->table = $table;
	}
	
	public function __toString()
	{
		return $this->render();
	}
}

class Piwik_DataTable_Renderer_Console extends Piwik_DataTable_Renderer
{
	protected $prefixRows;
	function __construct($table)
	{
		parent::__construct($table);
	}
	
	function render()
	{
		return $this->renderTable($this->table);
	}
	
	function setPrefixRow($str)
	{
		$this->prefixRows = $str;
	}
	
	function renderTable($table)
	{
		static $depth=0;
		$output = '';
		$i = 1;
		foreach($table->getRows() as $row)
		{
			$columns=array();
			foreach($row->getColumns() as $column => $value)
			{
				$columns[] = "'$column' => $value";
			}
			$columns = implode(", ", $columns);
			$details=array();
			foreach($row->getDetails() as $detail => $value)
			{
				$details[] = "'$detail' => $value";
			}
			$details = implode(", ", $details);
			$output.= str_repeat($this->prefixRows, $depth) . "- $i [".$columns."] [".$details."] [idsubtable = ".$row->getIdSubDataTable()."]<br>\n";
			
			if($row->getIdSubDataTable() !== null)
			{
				$depth++;
				$output.= $this->renderTable( Piwik_DataTable_Manager::getInstance()->getTable($row->getIdSubDataTable()));
				$depth--;
			}
			$i++;
		}
		
		return $output;
		
	}	
}

class Piwik_DataTable_Row
{
	public $content = array();
	const COLUMNS = 0;
	const DETAILS = 1;
	const DATATABLE_ASSOCIATED = 2;

	public function __construct( $row )
	{
		$this->loadFromArray($row);
	}
	public function getColumn( $name )
	{
		if(!isset($this->content[self::COLUMNS][$name]))
		{
			return false;
		}
		return $this->content[self::COLUMNS][$name];
	}
	public function getColumns()
	{
		return $this->content[self::COLUMNS];
	}
	
	public function getDetails()
	{	
		return $this->content[self::DETAILS];
	}
	
	public function getIdSubDataTable()
	{
		return $this->content[self::DATATABLE_ASSOCIATED];
	}
	/**
	 * Very efficient load of the Row structure from a well structured php array
	 * 
	 * @param array The row array has the structure
	 * 					array( 
	 * 						DataTable_Row::COLUMNS => array( 
	 * 										0 => 1554,
	 * 										1 => 42,
	 * 										2 => 657,
	 * 										3 => 155744,	
	 * 									),
	 * 						DataTable_Row::DETAILS => array(
	 * 										'logo' => 'test.png'
	 * 									),
	 * 						DataTable_Row::DATATABLE_ASSOCIATED => #DataTable object // numeric idDataTable
	 * 					)
	 */
	public function loadFromArray( $array )
	{
		$this->content[self::COLUMNS] = array();
		$this->content[self::DETAILS] = array();
		$this->content[self::DATATABLE_ASSOCIATED] = null;
		
		if(isset($array[self::COLUMNS]))
		{
			$this->content[self::COLUMNS] = $array[self::COLUMNS];
		}
		if(isset($array[self::DETAILS]))
		{
			$this->content[self::DETAILS] = $array[self::DETAILS];
		}
		if(isset($array[self::DATATABLE_ASSOCIATED]))
		{
			$this->content[self::DATATABLE_ASSOCIATED] = $array[self::DATATABLE_ASSOCIATED]->getId();
		}
	}
	
}

abstract class Piwik_DataTable_Filter
{
	protected $table;
	
	public function __construct($table)
	{
		if(!($table instanceof Piwik_DataTable))
		{
			throw new Exception("The filter accepts only a Piwik_DataTable object.");
		}
		$this->table = $table;
	}
	
	abstract protected function filter();
}


class Piwik_DataTable_Filter_Pattern extends Piwik_DataTable_Filter
{
	private $columnToFilter;
	private $patternToSearch;
	
	public function __construct( $table, $columnToFilter, $patternToSearch )
	{
		parent::__construct($table);
		$this->patternToSearch = $patternToSearch;
		$this->columnToFilter = $columnToFilter;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $key => $row)
		{
			if( !ereg($this->patternToSearch, $row->getColumn($this->columnToFilter)))
			{
				$this->table->deleteRow($key);
			}
		}
	}
}

class Piwik_DataTable_Filter_Limit extends Piwik_DataTable_Filter
{	
	public function __construct( $table, $offset, $limit )
	{
		parent::__construct($table);
		$this->offset = $offset;
		$this->limit = $limit;
		$this->filter();
	}
	
	protected function filter()
	{
		$table = $this->table;
		
		$rowsCount = $table->getRowsCount();
		
		// we have to delete
		// - from 0 to offset
		// - from limit to the end
		$table->deleteRowsOffset( 0, $this->offset );
		$table->deleteRowsOffset( $this->offset + $this->limit, $rowsCount );
	}
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
?>