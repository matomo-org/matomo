<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Sort.php 519 2008-06-09 01:59:24Z matt $
 * 
 * @package Piwik_DataTable
 */

/**
 * Sort the DataTable based on the value of column $columnToSort ordered by $order.
 * Possible to specify a natural sorting (see php.net/natsort for details)
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter 
 */
class Piwik_DataTable_Filter_Sort extends Piwik_DataTable_Filter
{
	protected $columnToSort;
	protected $order;
	
	public function __construct( $table, $columnToSort, $order = 'desc', $naturalSort = false )
	{
		parent::__construct($table);
		
		// hack... But I can't see how to do properly
		if($columnToSort == '0')
		{
			$columnToSort = 'label';
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
		return $this->sign * 
				($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort] 
					< $b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort] 
				? -1 
				: 1
			);
	}
	
	function naturalSort($a, $b)
	{
		return $this->sign * strnatcasecmp( 
				$a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort], 
				$b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort]
			);
	}
	
	
	function sortString($a, $b)
	{
		return $this->sign * 
				strcasecmp($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort], 
					$b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort] 
			);
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
		$value = $row->getColumn($this->columnToSort);
		
		if($value === false)
		{
			// we don't throw the exception because we sometimes export a DataTable without a column labelled '2'
			// and when the generic filters tries to sort by default using this column 2, this shouldnt raise an exception...
			//throw new Exception("The column to sort by '".$this->columnToSort."' is unknown in the row ". implode(array_keys($row->getColumns()), ','));
			return;
		}
		
		if( Piwik::isNumeric($value))
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
		$this->table->sort( array($this,$methodToUse) );
	}
}

