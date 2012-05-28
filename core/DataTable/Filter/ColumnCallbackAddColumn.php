<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: ColumnCallbackAddColumn.php $
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Adds a new column to every row of a DataTable based on the result of callback.
 * 
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_ColumnCallbackAddColumn extends Piwik_DataTable_Filter
{
	/**
	 * The names of the columns to pass to the callback.
	 */
	private $columns;
	
	/**
	 * The name of the column to add.
	 */
	private $columnToAdd;
	
	/**
	 * The callback to apply to each row of the DataTable. The result is added as
	 * the value of a new column.
	 */
	private $functionToApply;
	
	/**
	 * Extra parameters to pass to the callback.
	 */
	private $functionParameters;
	
	/**
	 * Constructor.
	 * 
	 * @param Piwik_DataTable  $table               The DataTable that will be filtered.
	 * @param array|string     $columns             The names of the columns to pass to the callback.
	 * @param string           $columnToAdd         The name of the column to add.
	 * @param mixed            $functionToApply     The callback to apply to each row of a DataTable.
	 * @param array            $functionParameters  Extra parameters to pass to $functionToApply.
	 */
	public function __construct( $table, $columns, $columnToAdd, $functionToApply, $functionParameters = array() )
	{
		parent::__construct($table);
		
		if (!is_array($columns))
		{
			$columns = array($columns);
		}
		
		$this->columns = $columns;
		$this->columnToAdd = $columnToAdd;
		$this->functionToApply = $functionToApply;
		$this->functionParameters = $functionParameters;
	}
	
	/**
	 * Executes a callback on every row of the supplied table and adds the result of
	 * the callback as a new column to each row.
	 * 
	 * @param Piwik_DataTable  $table  The table to filter.
	 */
	public function filter( $table )
	{
		foreach ($table->getRows() as $row)
		{
			$columnValues = array();
			foreach ($this->columns as $column)
			{
				$columnValues[] = $row->getColumn($column);
			}
			
			$parameters = array_merge($columnValues, $this->functionParameters);
			$value = call_user_func_array($this->functionToApply, $parameters);
			
			$row->setColumn($this->columnToAdd, $value);
			
			$this->filterSubTable($row);
		}
	}
}
