<?php

class Piwik_DataTable_Row_DataTableSummary extends Piwik_DataTable_Row
{
	function __construct($subTable)
	{
		parent::__construct();
		$this->addSubtable($subTable);
		foreach($subTable->getRows() as $row)
		{
			$this->sumRow($row);
		}
	}
}
?>
