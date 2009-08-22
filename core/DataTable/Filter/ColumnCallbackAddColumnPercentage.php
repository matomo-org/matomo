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
 * Add a new column to the table which is a percentage based on the value resulting 
 * from a callback function with the parameter being another column's value
 * 
 * For example in the keywords table, we can create a "nb_visits_percentage" column 
 * from the "nb_visits" column that will be nb_visits / $totalValueUsedToComputePercentage
 * You can also specify the precision of the percentage value to be displayed (defaults to 0, eg "11%")
 * 
 * Usage:
 *   $nbVisits = Piwik_VisitsSummary_API::getVisits($idSite, $period, $date);
 *   $dataTable->queueFilter('ColumnCallbackAddColumnPercentage', array('nb_visits', 'nb_visits_percentage', $nbVisits, 1));
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_ColumnCallbackAddColumnPercentage extends Piwik_DataTable_Filter
{
	private $columnValueToRead;
	private $columnNamePercentageToAdd;
	private $columnNameUsedAsDivisor;
	private $totalValueUsedAsDivisor;
	private $percentagePrecision;
	
	/**
	 * @param Piwik_DataTable $table
	 * @param string $columnValueToRead
	 * @param string $columnNamePercentageToAdd
	 * @param numeric|string $totalValueUsedToComputePercentageOrColumnName 
	 * 						if a numeric value is given, we use this value as the divisor to process the percentage. 
	 * 						if a string is given, this string is the column name's value used as the divisor.
	 * @param int $percentagePrecision precision 0 means "11", 1 means "11.2"
	 */
	public function __construct( $table, $columnValueToRead, $columnNamePercentageToAdd, $totalValueUsedToComputePercentageOrColumnName, $percentagePrecision = 0 )
	{
		parent::__construct($table);
		$this->columnValueToRead = $columnValueToRead;
		$this->columnNamePercentageToAdd = $columnNamePercentageToAdd;
		if(is_numeric($totalValueUsedToComputePercentageOrColumnName))
		{
			$this->totalValueUsedAsDivisor = $totalValueUsedToComputePercentageOrColumnName;
		}
		else
		{
			$this->columnNameUsedAsDivisor = $totalValueUsedToComputePercentageOrColumnName;
		}
		$this->percentagePrecision = $percentagePrecision;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $key => $row)
		{
			$value = $row->getColumn($this->columnValueToRead);
			if(!is_null($this->totalValueUsedAsDivisor))
			{
				$divisor = $this->totalValueUsedAsDivisor;
			}
			else
			{
				$divisor = $row->getColumn($this->columnNameUsedAsDivisor);
			}
			$percentage = Piwik::getPercentageSafe($value, $divisor, $this->percentagePrecision);
			$row->addColumn($this->columnNamePercentageToAdd, $percentage);
		}
	}
}
