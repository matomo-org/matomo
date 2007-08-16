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
require_once "DataTable/Renderer.php";
require_once "DataTable/Filter.php";

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
	
	protected $tables = array();
	protected $count = 0;
	
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

function Piwik_DataTable_orderRowByLabel($o1,$o2)
{
	return ($o1->getColumn('label') < $o2->getColumn('label'))  ? -1 : 1;
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
	
	static public function isEqual($table1, $table2)
	{
		$rows1 = $table1->getRows();
		$rows2 = $table2->getRows();
		
		usort($rows1, 'Piwik_DataTable_orderRowByLabel');
		usort($rows2, 'Piwik_DataTable_orderRowByLabel');		
		
		$countrows1 = count($rows1);
		$countrows2 = count($rows2);
		
		if($countrows1 != $countrows2)
		{
			return false;
		}
		
		$i = 0;
		while($i < $countrows1)
		{
			if( !Piwik_DataTable_Row::isEqual($rows1[$i],$rows2[$i]) )
			{
				return false;
			}
			$i++;
		}
		
		return true;
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
			
			$this->rows[] = $row;
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
			
			$this->rows[] = new Piwik_DataTable_Row($cleanRow);
		}
	}
	/*
	public function loadFromRowLabelIsKey( $arrayOfRows )
	{
		foreach($arrayOfRows as $label => $row)
		{
			$row->addColumn('label', $label);
			$this->rows[] = $row;
		}
	}*/
	
	/**
	 * Shortcut function used for performance reasons
	 */
	public function addRow( $row )
	{
		$this->rows[] = $row;
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
}


class Piwik_DataTable_Row_ActionTableSummary extends Piwik_DataTable_Row
{
	function __construct($subTable)
	{
		$currentColumns = array();

		// go through the subTable and compute the summary
		foreach($subTable->getRows() as $row)
		{
			$columns = $row->getColumns();
			foreach($columns as $name => $value)
			{
				if($name != 'label' 
					&& ( is_int($value) || is_float($value) )
				)
				{
					if(!isset($currentColumns[$name]))
					{
						$currentColumns[$name] = $value;
					}
					else
					{
						$currentColumns[$name] += $value;
					}
				}
			}
		}
		$newRow = array();
		$newRow[Piwik_DataTable_Row::COLUMNS] = $currentColumns;
		$newRow[Piwik_DataTable_Row::DATATABLE_ASSOCIATED] = $subTable;
		
		parent::__construct($newRow);
	}
}

class Piwik_DataTable_Row
{
	// Row content
	public $c = array();
	const COLUMNS = 0;
	const DETAILS = 1;
	const DATATABLE_ASSOCIATED = 2;

	public function __construct( $row = array() )
	{
		$this->c[self::COLUMNS] = array();
		$this->c[self::DETAILS] = array();
		$this->c[self::DATATABLE_ASSOCIATED] = null;
		
		if(isset($row[self::COLUMNS]))
		{
			$this->c[self::COLUMNS] = $row[self::COLUMNS];
		}
		if(isset($row[self::DETAILS]))
		{
			$this->c[self::DETAILS] = $row[self::DETAILS];
		}
		if(isset($row[self::DATATABLE_ASSOCIATED])
			&& $row[self::DATATABLE_ASSOCIATED] instanceof Piwik_DataTable)
		{
			$this->c[self::DATATABLE_ASSOCIATED] = $row[self::DATATABLE_ASSOCIATED]->getId();
		}
	}
	// 2rows are equal is exact same columns / details
	// and if subtable is there then subtable has to be the same!
	static public function isEqual( $row1, $row2 )
	{		
		//same columns
		$cols1 = $row1->getColumns();
		$cols2 = $row2->getColumns();
		
		uksort($cols1, 'strnatcasecmp');
		uksort($cols2, 'strnatcasecmp');
		
		if($cols1 != $cols2)
		{
			return false;
		}
		
		$dets1 = $row1->getDetails();
		$dets2 = $row2->getDetails();
		
		ksort($dets1);
		ksort($dets2);
		
		// same details
		if($dets1 != $dets2)
		{
			return false;
		}
		
		// either both are null
		// or both have a value
		if( !(is_null($row1->getIdSubDataTable()) 
				&& is_null($row2->getIdSubDataTable())
			)
		)
		{
			$subtable1 = Piwik_DataTable_Manager::getInstance()->getTable($row1->getIdSubDataTable());
			$subtable2 = Piwik_DataTable_Manager::getInstance()->getTable($row2->getIdSubDataTable());
			if(!is_null($subtable1) && !is_null($subtable2))
			{
				if(!Piwik_DataTable::isEqual($subtable1, $subtable2))
				{
					return false;
				}
			}
			else
			{
				return false;
			}
			
		}
		return true;
	}
	
	public function getColumn( $name )
	{
		if(!isset($this->c[self::COLUMNS][$name]))
		{
			return false;
		}
		return $this->c[self::COLUMNS][$name];
	}
	public function getColumns()
	{
		return $this->c[self::COLUMNS];
	}
	
	public function getDetails()
	{	
		return $this->c[self::DETAILS];
	}
	
	public function getIdSubDataTable()
	{
		return $this->c[self::DATATABLE_ASSOCIATED];
	}
	public function addSubtable(Piwik_DataTable $subTable)
	{
		if(!is_null($this->c[self::DATATABLE_ASSOCIATED]))
		{
			throw new Exception("Adding a subtable to the row, but it already has a subtable associated.");
		}
		$this->c[self::DATATABLE_ASSOCIATED] = $subTable->getId();
	}
	
	public function setColumn($name, $value)
	{
		$this->c[self::COLUMNS][$name] = $value;
	}
	
	public function addColumn($name, $value)
	{
		if(isset($this->c[self::COLUMNS][$name]))
		{
			throw new Exception("Column $name already in the array!");
		}
		$this->c[self::COLUMNS][$name] = $value;
	}
	
	/**
	 * Add the given $row columns values to the existing row' columns values.
	 * It will take in consideration only the int or float values of $row.
	 * 
	 * If a given column doesn't exist in $this then it is added with the value of $row.
	 * If the column already exists in $this then we have
	 * 		this.columns[idThisCol] += $row.column[idThisCol]
	 */
	public function sumRow( $rowToSum )
	{
		foreach($rowToSum->getColumns() as $name => $value)
		{
			if(is_int($value) || is_float($value))
			{
				$current = $this->getColumn($name);
				if($current==false)
				{
					$current = 0;
				}
				$this->setColumn( $name, $current + $value);
			}
		}
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