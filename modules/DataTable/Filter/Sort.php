<?php
/**
 * Sort the DataTable based on the value of column $columnToSort ordered by $order.
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter 
 */
class Piwik_DataTable_Filter_Sort extends Piwik_DataTable_Filter
{
	protected $columnToSort;
	protected $order;
	
	public function __construct( $table, $columnToSort, $order = 'desc' )
	{
		parent::__construct($table);
		
		
		// hack... But I can't see how to do properly
		if($columnToSort == 0)
		{
			$columnToSort = 'label';
		}
		$this->columnToSort = $columnToSort;
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
	
	
	function sortString($a, $b)
	{
		return $this->sign * 
				strcasecmp($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort], 
					$b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort] 
			);
	}
	
	protected function filter()
	{
		$rows = $this->table->getRows();
		
		if(count($rows) == 0)
		{
			return;
		}
		$row = current($rows);
		$value = $row->getColumn($this->columnToSort);
		
		if($value == false)
		{
			return;
		}
		
		if( Piwik::isNumeric($value))
		{
			$methodToUse = "sort";
		}
		else
		{
			$methodToUse = "sortString";

		}
		
		$this->table->sort( array($this,$methodToUse) );
	}
}

