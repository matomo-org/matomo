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
class Piwik_DataTable_Filter_AddColumnsWhenShowAllColumns extends Piwik_DataTable_Filter
{
	protected $roundPrecision = 1;
	public function __construct( $table )
	{
		parent::__construct($table);
		$this->filter();
	}
	
	protected function filter()
	{
		$rowsIdToDelete = array();		
		foreach($this->table->getRows() as $key => $row)
		{
			$nbVisits = $row->getColumn(Piwik_Archive::INDEX_NB_VISITS);
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
			$actionsPerVisit = round($row->getColumn(Piwik_Archive::INDEX_NB_ACTIONS) / $nbVisits, $this->roundPrecision);
			$averageTimeOnSite = round($row->getColumn(Piwik_Archive::INDEX_SUM_VISIT_LENGTH) / $nbVisits, $this->roundPrecision);
			$bounceRate = round(100 * $row->getColumn(Piwik_Archive::INDEX_BOUNCE_COUNT) / $nbVisits, $this->roundPrecision);
			$row->addColumn('nb_actions_per_visit', $actionsPerVisit);
			$row->addColumn('avg_time_on_site', $averageTimeOnSite);
			$row->addColumn('bounce_rate', $bounceRate);
		}
		$this->table->deleteRows($rowsIdToDelete);
	}
}
