<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: GroupBy.php $
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * DataTable filter that will group DataTable rows together based on the results
 * of a reduce function. Rows with the same reduce result will be summed and merged.
 * 
 * NOTE: This filter should never be queued, it must be applied directly on a DataTable.
 * 
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_GroupBy extends Piwik_DataTable_Filter
{
	/**
	 * The name of the columns to reduce.
	 * @var string
	 */
	private $groupByColumn;
	
	/**
	 * A callback that modifies the $groupByColumn of each row in some way. Rows with
	 * the same reduction result will be added together.
	 */
	private $reduceFunction;
	
	/**
	 * Extra parameters to pass to the reduce function.
	 */
	private $parameters;
	
	/**
	 * Constructor.
	 * 
	 * @param Piwik_DataTable  $table           The DataTable to filter.
	 * @param string           $groupByColumn   The column name to reduce.
	 * @param mixed            $reduceFunction  The reduce function. This must alter the $groupByColumn in some way.
	 * @param array            $parameters      Extra parameters to supply to the reduce function.
	 */
	public function __construct( $table, $groupByColumn, $reduceFunction, $parameters = array() )
	{
		parent::__construct($table);
		
		$this->groupByColumn = $groupByColumn;
		$this->reduceFunction = $reduceFunction;
		$this->parameters = $parameters;
	}
	
	/**
	 * Applies the reduce function to each row and merges rows w/ the same reduce result.
	 *
	 * @param Piwik_DataTable  $table
	 */
	public function filter( $table )
	{
		$groupByRows = array();
		$nonGroupByRowIds = array();
		
		foreach ($table->getRows() as $rowId => $row)
		{
			// skip the summary row
			if ($rowId == Piwik_DataTable::ID_SUMMARY_ROW)
			{
				continue;
			}
			
			// reduce the group by column of this row
			$groupByColumnValue = $row->getColumn($this->groupByColumn);
			$parameters = array_merge(array($groupByColumnValue), $this->parameters);
			$groupByValue = call_user_func_array($this->reduceFunction, $parameters);
			
			if (!isset($groupByRows[$groupByValue]))
			{
				// if we haven't encountered this group by value before, we mark this row as a
				// row to keep, and change the group by column to the reduced value.
				$groupByRows[$groupByValue] = $row;
				$row->setColumn($this->groupByColumn, $groupByValue);
			}
			else
			{
				// if we have already encountered this group by value, we add this row to the
				// row that will be kept, and mark this one for deletion
				$groupByRows[$groupByValue]->sumRow($row);
				$nonGroupByRowIds[] = $rowId;
			}
		}
		
		// delete the unneeded rows.
		$table->deleteRows($nonGroupByRowIds);
	}
}
