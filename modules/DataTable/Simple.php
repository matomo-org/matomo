<?php
class Piwik_DataTable_Simple extends Piwik_DataTable
{
	public function __construct()
	{
		parent::__construct();
	}
	
	function loadFromArray($array)
	{
		foreach($array as $label => $value)
		{
			$row = new Piwik_DataTable_Row;
			$row->addColumn('label', $label);
			$row->addColumn('value', $value);
			$this->addRow($row);
		}
	}
}
?>
