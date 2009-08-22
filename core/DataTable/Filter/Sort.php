<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Sort the DataTable based on the value of column $columnToSort ordered by $order.
 * Possible to specify a natural sorting (see php.net/natsort for details)
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_Sort extends Piwik_DataTable_Filter
{
	protected $columnToSort;
	protected $order;
	
	public function __construct( $table, $columnToSort, $order = 'desc', $naturalSort = true, $recursiveSort = false )
	{
		parent::__construct($table);
		if(empty($columnToSort))
		{
			return;
		}
		if($recursiveSort)
		{
			$table->enableRecursiveSort();
		}
		$this->columnToSort = $columnToSort;
		$this->naturalSort = $naturalSort;
		$this->setOrder($order);
		$this->filter();
	}
	
	function setOrder($order)
	{
		if($order == 'asc')
		{
			$this->order = 'asc';
			$this->sign = 1;
		}
		else
		{
			$this->order = 'desc';
			$this->sign = -1;
		}
	}
	
	function sort($a, $b)
	{
		return 	(!isset($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
						&&  !isset($b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
					)
					? 0 
					: (
						!isset($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
						? 1
						:(
						 	!isset($b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
							? -1
							: $this->sign * ( 
								$a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort] 
									< $b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort] 
								? -1 
								: 1
							)
						)
					)
			;
	}
	
	function naturalSort($a, $b)
	{
		return !isset($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
				&& !isset($b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort] )
				? 0
				: (!isset($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
					? 1
					: (!isset($b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort] )
						? -1
						: $this->sign * strnatcasecmp( 
								$a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort], 
								$b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort]
							)
						)
					)
				;
	}
	
	function sortString($a, $b)
	{
		return !isset($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
				&& !isset($b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort] )
				? 0
				: (!isset($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
					? 1
					: (!isset($b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort] )
						? -1
						: $this->sign * 
							strcasecmp($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort], 
								$b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort] 
							)
						)
					)
				;
	}
	
	/**
	 * @param Piwik_DataTable_Row
	 */
	protected function selectColumnToSort($row)
	{
		$value = $row->getColumn($this->columnToSort);
		if($value !== false)
		{
			return $this->columnToSort;
		}
		
		// sorting by "nb_visits" but the index is Piwik_Archive::INDEX_NB_VISITS in the table
		if(isset(Piwik_Archive::$mappingFromNameToId[$this->columnToSort]))
		{
			$column = Piwik_Archive::$mappingFromNameToId[$this->columnToSort];
			$value = $row->getColumn($column);

			if($value !== false)
			{
				return $column;
			}
		}
		
		// eg. was previously sorted by revenue_per_visit, but this table 
		// doesn't have this column; defaults with nb_visits
		$column = Piwik_Archive::INDEX_NB_VISITS;
		$value = $row->getColumn($column);
		if($value !== false)
		{
			return $column;
		}
		
		// even though this column is not set properly in the table, 
		// we select it for the sort, so that the table's internal state is set properly
		return $this->columnToSort;
	}
	
	protected function filter()
	{
		if($this->table instanceof Piwik_DataTable_Simple)
		{
			return;
		}
		$rows = $this->table->getRows();
		if(count($rows) == 0)
		{
			return;
		}
		$row = current($rows);
		$this->columnToSort = $this->selectColumnToSort($row);
		
		$value = $row->getColumn($this->columnToSort);
		if( is_numeric($value))
		{
			$methodToUse = "sort";
		}
		else
		{
			if($this->naturalSort)
			{
				$methodToUse = "naturalSort";
			}
			else
			{
				$methodToUse = "sortString";
			}
		}
		$this->table->sort( array($this,$methodToUse), $this->columnToSort );
	}
}
