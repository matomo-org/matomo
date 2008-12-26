<?php
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
		foreach($this->table->getRows() as $key => $row)
		{
//		nb_actions / nb_visits => Actions/visit
//		sum_visit_length / nb_visits => Avg. Time on Site 
//		bounce_count=> Bounce Rate
			$nbVisits = $row->getColumn(Piwik_Archive::INDEX_NB_VISITS);
			$actionsPerVisit = round($row->getColumn(Piwik_Archive::INDEX_NB_ACTIONS) / $nbVisits, $this->roundPrecision);
			$averageTimeOnSite = round($row->getColumn(Piwik_Archive::INDEX_SUM_VISIT_LENGTH) / $nbVisits, $this->roundPrecision);
			$bounceRate = round(100 * $row->getColumn(Piwik_Archive::INDEX_BOUNCE_COUNT) / $nbVisits, $this->roundPrecision);
			$row->addColumn('nb_actions_per_visit', $actionsPerVisit);
			$row->addColumn('avg_time_on_site', $averageTimeOnSite);
			$row->addColumn('bounce_rate', $bounceRate);
		}
	}
}

