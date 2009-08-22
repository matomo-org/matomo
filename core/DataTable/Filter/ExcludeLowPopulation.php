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
 * Delete all rows that have a $columnToFilter value less than the $minimumValue 
 * 
 * For example we delete from the countries report table all countries that have less than 3 visits.
 * It is very useful to exclude noise from the reports.
 * You can obviously apply this filter on a percentaged column, eg. remove all countries with the column 'percent_visits' less than 0.05
 * 
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_ExcludeLowPopulation extends Piwik_DataTable_Filter
{
	static public $minimumValue;
	const MINIMUM_SIGNIFICANT_PERCENTAGE_THRESHOLD = 0.02;
	public function __construct( $table, $columnToFilter, $minimumValue, $minimumPercentageThreshold = false )
	{
		parent::__construct($table);
		$this->columnToFilter = $columnToFilter;
		
		if($minimumValue == 0)
		{
			if($minimumPercentageThreshold === false)
			{
				$minimumPercentageThreshold = self::MINIMUM_SIGNIFICANT_PERCENTAGE_THRESHOLD;
			}
			$allValues = $this->table->getColumn($this->columnToFilter);
			$sumValues = array_sum($allValues);
			$minimumValue = $sumValues * $minimumPercentageThreshold;
		}
		self::$minimumValue = $minimumValue;
		$this->filter();
	}
	
	function filter()
	{
		$this->table->filter('ColumnCallbackDeleteRow',
							array($this->columnToFilter, 
								array("Piwik_DataTable_Filter_ExcludeLowPopulation", "excludeLowPopulation")
							)
						);
	}
	
	static public function excludeLowPopulation($value)
	{
		return $value >= self::$minimumValue;
	}
}
