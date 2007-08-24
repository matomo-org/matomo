<?php
class Piwik_DataTable_Filter_ExcludeLowPopulation extends Piwik_DataTable_Filter
{
	static public $minimumValue;
	public function __construct( $table, $columnToFilter, $minimumValue )
	{
		$this->columnToFilter = $columnToFilter;
		self::$minimumValue = $minimumValue;
		parent::__construct($table);
		$this->filter();
	}
	
	function filter()
	{
		$function = array("Piwik_DataTable_Filter_ExcludeLowPopulation","excludeLowPopulation");		

		$filter = new Piwik_DataTable_Filter_ColumnCallback(
												$this->table, 
												$this->columnToFilter, 
												$function
											);
		
	}
	
	static public function excludeLowPopulation($value)
	{
		return $value >= self::$minimumValue;
	}
}
?>
