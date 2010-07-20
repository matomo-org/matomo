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
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_AddColumnsProcessedMetrics extends Piwik_DataTable_Filter
{
	protected $invalidDivision = '0';
	protected $roundPrecision = 1;
	
	/**
	 * @param $table
	 * @param $enable Automatically set to true when filter_add_columns_when_show_all_columns is found in the API request
	 * @return void
	 */
	public function __construct( $table, $enable = true )
	{
		parent::__construct($table);
		$this->filter();
	}
	
	protected function filter()
	{
		$rowsIdToDelete = array();	
		$bounceRateColumnWasSet = false;	
		foreach($this->table->getRows() as $key => $row)
		{
			$nbVisits = $this->getColumn($row, Piwik_Archive::INDEX_NB_VISITS, 'nb_visits');
			if($nbVisits == 0)
			{
				// case of keyword/website/campaign with a conversion for this day, 
				// but no visit, we don't show it  
				$rowsIdToDelete[] = $key;
				continue;
			}
				
			$nbVisitsConverted = (int)$this->getColumn($row, Piwik_Archive::INDEX_NB_VISITS_CONVERTED);
			if($nbVisitsConverted == 0)
			{
				$conversionRate = $this->invalidDivision;
			}
			else
			{
				$conversionRate = round(100 * $nbVisitsConverted / $nbVisits, $this->roundPrecision) . "%";
			}
			$row->addColumn('conversion_rate', $conversionRate);
		
			// nb_actions / nb_visits => Actions/visit
			// sum_visit_length / nb_visits => Avg. Time on Site 
			// bounce_count=> Bounce Rate
			$actionsPerVisit = round($this->getColumn($row, Piwik_Archive::INDEX_NB_ACTIONS) / $nbVisits, $this->roundPrecision);
			$averageTimeOnSite = round($this->getColumn($row, Piwik_Archive::INDEX_SUM_VISIT_LENGTH) / $nbVisits, $rounding = 0);
			$bounceRate = round(100 * $this->getColumn($row, Piwik_Archive::INDEX_BOUNCE_COUNT) / $nbVisits, $this->roundPrecision);
			$row->addColumn('nb_actions_per_visit', $actionsPerVisit);
			$row->addColumn('avg_time_on_site', $averageTimeOnSite);
			try {
				$row->addColumn('bounce_rate', $bounceRate."%");
			} catch(Exception $e) {
				$bounceRateColumnWasSet = true;
			}
		}
		$this->table->deleteRows($rowsIdToDelete);
	}
	
	/**
	 * Returns column from a given row.
	 * Will work with 2 types of datatable
	 * - raw datatables coming from the archive DB, which columns are int indexed
	 * - datatables processed resulting of API calls, which columns have human readable english names
	 * 
	 * @param $row
	 * @param $columnIdRaw see consts in Piwik_Archive::
	 * @return Value of column, false if not found
	 */
	protected function getColumn($row, $columnIdRaw)
	{
		$columnIdReadable = Piwik_Archive::$mappingFromIdToName[$columnIdRaw];
		if($row instanceof Piwik_DataTable_Row)
		{
    		$raw = $row->getColumn($columnIdRaw);
    		if($raw !== false)
    		{
    			return $raw;
    		}
    		return $row->getColumn($columnIdReadable);
		}
		if(isset($row[$columnIdRaw]))
		{
			return $row[$columnIdRaw];
		}
		if(isset($row[$columnIdReadable]))
		{
			return $row[$columnIdReadable];
		}
		return false;
	}
	
}
