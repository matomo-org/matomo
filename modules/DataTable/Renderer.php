<?php

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
		$this->setPrefixRow('#');
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
				if(is_string($value)) $value = "'$value'";
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
?>
