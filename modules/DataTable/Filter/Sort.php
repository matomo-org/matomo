<?php

class Piwik_DataTable_Filter_Sort extends Piwik_DataTable_Filter
{
	private $columnToSort;
	private $order;
	
	public function __construct( $table, $columnToSort, $order = 'desc' )
	{
		parent::__construct($table);
		$this->columnToSort = $columnToSort;
		$this->setOrder($order);
		$this->filter();
	}
	
	function setOrder($order)
	{
		if($order == 'asc')
		{
			$this->order = 'asc';
		}
		else
		{
			$this->order = 'desc';
		}
	}
	
	function sort($a, $b)
	{
		$va = $a->getColumn($this->columnToSort);
		$vb = $b->getColumn($this->columnToSort);
		
		if(is_string($va))
		{	
			$result = strcasecmp($va,$vb);
		}
		else
		{
			$result = $va < $vb ? -1 : 1;
		}
		
		if($this->order == 'asc')
		{
			return $result;
		}
		return -$result;
	}
	
	protected function filter()
	{
		$this->table->sort( array($this,"sort") );
	}
}
?>
