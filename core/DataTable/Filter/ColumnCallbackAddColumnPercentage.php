<?php
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
 *   $dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackAddColumnPercentage', array('nb_visits', 'nb_visits_percentage', $nbVisits, 1));
 *
 */
class Piwik_DataTable_Filter_ColumnCallbackAddColumnPercentage extends Piwik_DataTable_Filter
{
	private $columnValueToRead;
	private $columnNamePercentageToAdd;
	private $totalValueUsedToComputePercentage;
	private $percentagePrecision;
	
	/**
	 * @param Piwik_DataTable $table
	 * @param string $columnValueToRead
	 * @param string $columnNamePercentageToAdd
	 * @param double $totalValueUsedToComputePercentage
	 * @param int $percentagePrecision precision 0 means "11", 1 means "11.2"
	 */
	public function __construct( $table, $columnValueToRead, $columnNamePercentageToAdd, $totalValueUsedToComputePercentage, $percentagePrecision = 0 )
	{
		parent::__construct($table);
		$this->columnValueToRead = $columnValueToRead;
		$this->columnNamePercentageToAdd = $columnNamePercentageToAdd;
		$this->totalValueUsedToComputePercentage = $totalValueUsedToComputePercentage;
		$this->percentagePrecision = $percentagePrecision;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $key => $row)
		{
			$value = $row->getColumn($this->columnValueToRead);
			$percentage = round( 100 * $value / $this->totalValueUsedToComputePercentage, $this->percentagePrecision);
			$row->addColumn($this->columnNamePercentageToAdd, $percentage);
		}
	}
}
