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
	
	protected function getColumn($row, $columnIdRaw, $columnIdReadable)
	{
		$raw = $row->getColumn($columnIdRaw);
		if($raw !== false)
		{
			return $raw;
		}
		return $row->getColumn($columnIdReadable);
	}
	protected function filter()
	{
		$rowsIdToDelete = array();		
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
			
			// nb_actions / nb_visits => Actions/visit
			// sum_visit_length / nb_visits => Avg. Time on Site 
			// bounce_count=> Bounce Rate
			$actionsPerVisit = round($this->getColumn($row, Piwik_Archive::INDEX_NB_ACTIONS, 'nb_actions') / $nbVisits, $this->roundPrecision);
			$averageTimeOnSite = round($this->getColumn($row, Piwik_Archive::INDEX_SUM_VISIT_LENGTH, 'sum_visit_length') / $nbVisits, $rounding = 0);
			$bounceRate = round(100 * $this->getColumn($row, Piwik_Archive::INDEX_BOUNCE_COUNT, 'bounce_count') / $nbVisits, $this->roundPrecision);
			$row->addColumn('nb_actions_per_visit', $actionsPerVisit);
			$row->addColumn('avg_time_on_site', $averageTimeOnSite);
			$row->addColumn('bounce_rate', $bounceRate);
		}
		$this->table->filter('ColumnCallbackReplace', array('bounce_rate', create_function('$bounceRate', 'return $bounceRate."%";')));
		$this->table->deleteRows($rowsIdToDelete);
	}
}
