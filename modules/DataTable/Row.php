<?php
/**
 * A DataTable is composed by rows.
 * A row is composed by 
 * - columns often at least a 'label' column containing the description 
 * 		of the row, and some numeric values ('nb_visits', etc.)
 * - details: other information never to be shown as "columns")
 * - idSubtable: a row can be linked to a SubTable
 * 
 * IMPORTANT: Make sure that the column named 'label' contains at least one non-numeric character.
 * Otherwise the method addDataTable() or sumRow() would fail because they would consider
 * the 'label' as being a numeric column to sum.
 * 
 * @package Piwik_DataTable
 * 
 */
class Piwik_DataTable_Row
{
	// Row content
	public $c = array();
	
	const COLUMNS = 0;
	const DETAILS = 1;
	const DATATABLE_ASSOCIATED = 3;


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
	
	/**
	 * When destroyed, a row destroys its associated subTable if there is one
	 */
	public function __destruct()
	{
		$idSubtable = $this->getIdSubDataTable();
		if(!is_null($idSubtable))
		{
			unset($idSubtable);
		}
	}

	/**
	 * Returns the given column
	 * @param string Column name
	 * @return mixed|false The column value  
	 */
	public function getColumn( $name )
	{
		if(!isset($this->c[self::COLUMNS][$name]))
		{
			return false;
		}
		return $this->c[self::COLUMNS][$name];
	}
	
	/**
	 * Returns the given detail
	 * @param string Detail name
	 * @return mixed|false The detail value  
	 */
	public function getDetail( $name )
	{
		if(!isset($this->c[self::DETAILS][$name]))
		{
			return false;
		}
		return $this->c[self::DETAILS][$name];
	}
	
	/**
	 * Returns the array of columns
	 * 
	 * @return array array( 
	 * 					'column1' 	=> VALUE,
	 * 					'label' 	=> 'www.php.net'
	 * 					'nb_visits' => 15894,
	 * 			)
	 */
	public function getColumns()
	{
		return $this->c[self::COLUMNS];
	}
	
	/**
	 * Returns the array of details
	 * 
	 * @return array array( 
	 * 					'logo' 	=> 'images/logo/www.google.png',
	 * 					'url'	=> 'www.google.com'
	 * 			)
	 */
	public function getDetails()
	{	
		return $this->c[self::DETAILS];
	}
	
	/**
	 * Returns the ID of the subDataTable. 
	 * If there is no such a table, returns null.
	 * 
	 * @return int|null
	 */
	public function getIdSubDataTable()
	{
		return $this->c[self::DATATABLE_ASSOCIATED];
	}
	
	/**
	 * Sums a DataTable to this row subDataTable.
	 * If this row doesn't have a SubDataTable yet, we create a new one.
	 * Then we add the values of the given DataTable to this row's DataTable 
	 * @see addDataTable() for the summing algorithm 
	 */
	public function sumSubtable(Piwik_DataTable $subTable)
	{
		$thisSubtableID = $this->getIdSubDataTable();
		if($thisSubtableID === null)
		{
			$thisSubTable = new Piwik_DataTable;
			$this->addSubtable($thisSubTable);
		}
		else
		{
			$thisSubTable = Piwik_DataTable_Manager::getInstance()->getTable( $thisSubtableID );
		}
		
		$thisSubTable->addDataTable($subTable);
	}

	
	protected function checkNoSubTable()
	{
		if(!is_null($this->c[self::DATATABLE_ASSOCIATED]))
		{
			throw new Exception("Adding a subtable to the row, but it already has a subtable associated.");
		}
	}
	
	
	/**
	 * Set a DataTable to be associated to this row.
	 * If the row already has a DataTable associated to it, throws an Exception.
	 * @throws Exception 
	 * 
	 */
	public function addSubtable(Piwik_DataTable $subTable)
	{
		$this->checkNoSubTable();
		$this->c[self::DATATABLE_ASSOCIATED] = $subTable->getId();
	}
	
	/**
	 * Set a DataTable to this row. If there is already 
	 * a DataTable associated, it is simply overwritten.
	 */
	public function setSubtable(Piwik_DataTable $subTable)
	{
		$this->c[self::DATATABLE_ASSOCIATED] = $subTable->getId();
	}
	
	/**
	 * Set all the columns at once.
	 * @param array array( 
	 * 					'label' 	=> 'www.php.net'
	 * 					'nb_visits' => 15894,
	 * 			)
	 */
	public function setColumns( $columns )
	{
		$this->c[self::COLUMNS] = $columns;
	}
	
	/**
	 * Set the $value value to the column named $name
	 */
	public function setColumn($name, $value)
	{
		$this->c[self::COLUMNS][$name] = $value;
	}
	
	/**
	 * Add a new column to the row. If the column already exist, throw an exception
	 * @throws Exception
	 */
	public function addColumn($name, $value)
	{
		if(isset($this->c[self::COLUMNS][$name]))
		{
			throw new Exception("Column $name already in the array!");
		}
		$this->c[self::COLUMNS][$name] = $value;
	}
	
	
	/**
	 * Add a new detail to the row. If the column already exist, throw an exception
	 * @throws Exception
	 */
	public function addDetail($name, $value)
	{
		if(isset($this->c[self::DETAILS][$name]))
		{
			throw new Exception("Detail $name already in the array!");
		}
		$this->c[self::DETAILS][$name] = $value;
	}
	
	/**
	 * Add the given $row columns values to the existing row' columns values.
	 * It will take in consideration only the int or float values of $row.
	 * 
	 * If a given column doesn't exist in $this then it is added with the value of $row.
	 * If the column already exists in $this then we have
	 * 		this.columns[idThisCol] += $row.columns[idThisCol]
	 */
	public function sumRow( $rowToSum )
	{
		foreach($rowToSum->getColumns() as $name => $value)
		{
			if($name != 'label' 
				&& Piwik::isNumeric($value))
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
	 * Helper function to test if two rows are equal.
	 * 
	 * Two rows are equal 
	 * - if they have exactly the same columns / details
	 * - if they have a subDataTable associated and that both of them are exactly the same.
	 */
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
			if(!Piwik_DataTable::isEqual($subtable1, $subtable2))
			{
				return false;
			}
		}
		return true;
	}
}

require_once "Row/DataTableSummary.php";

