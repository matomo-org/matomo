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
//		echo "AVANT LOW FILTER".$this->table;
		$filter = new Piwik_DataTable_Filter_ColumnCallback(
												$this->table, 
												$this->columnToFilter, 
												$function
											);
//		echo "APRES LOW FILTER".$this->table;
		
	}
	
	static public function excludeLowPopulation($value)
	{
		$test = self::$minimumValue;
		$return = $value >= $test;
		return $return;
	}
}
?>
