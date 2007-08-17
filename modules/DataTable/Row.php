<?php
/**
 * IMPORTANT: A column named 'label' must not be composed only of the characters [0-9.]
 * Otherwise the methods to addDataTable, sumRow, etc. would fail because they would consider
 * the label as being a column to sum
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
		$this->checkNoSubTable();
		$this->c[self::DATATABLE_ASSOCIATED] = $subTable->getId();
	}
	
	protected function checkNoSubTable()
	{
		if(!is_null($this->c[self::DATATABLE_ASSOCIATED]))
		{
			throw new Exception("Adding a subtable to the row, but it already has a subtable associated.");
		}
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
	 * 		this.columns[idThisCol] += $row.columns[idThisCol]
	 */
	public function sumRow( $rowToSum )
	{
//		if( $rowToSum->getIdSubDataTable() != null xor $this->getIdSubDataTable() != null )
//		{
//			throw new Exception("Only one of either \$this or \$rowToSum 
//									has a subTable associated. Not expected.");
//		}
//		
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
	 *  2rows are equal is exact same columns / details
	 * and if subtable is there then subtable has to be the same!
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
}

require_once "Row/DataTableSummary.php";
?>
