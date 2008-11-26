<?php
require_once "ViewDataTable/HtmlTable.php";

class Piwik_ViewDataTable_HtmlTable_AllColumns extends Piwik_ViewDataTable_HtmlTable 
{
	const LOW_POPULATION_THRESHOLD_PERCENTAGE_VISIT = 0.005;
	
	protected function getViewDataTableId()
	{
		return 'tableAllColumns';
	}
	
	public function main()
	{
		//TODO should be cached at least statically?
		$this->viewProperties['show_exclude_low_population'] = true;
		$this->handleLowPopulation();
		parent::main();
	}
	
	protected function handleLowPopulation()
	{
		require_once "VisitsSummary/Controller.php";
		$visits = Piwik_VisitsSummary_Controller::getVisits();
		$visitsThreshold = floor( self::LOW_POPULATION_THRESHOLD_PERCENTAGE_VISIT * $visits); 
		if($visitsThreshold > 0)
		{
			$this->setExcludeLowPopulation( $visitsThreshold, Piwik_Archive::INDEX_NB_VISITS );
		}
	}
	
	protected function getRequestString()
	{
		$requestString = parent::getRequestString();
		return $requestString . '&filter_add_columns_when_show_all_columns=1';
	}
	
	protected function postDataTableLoadedFromAPI()
	{
		$this->setColumnsToDisplay(array('label', 
										'nb_visits', 
										'nb_uniq_visitors', 
										'nb_actions_per_visit', 
										'avg_time_on_site', 
										'bounce_rate'));
		$filter = new Piwik_DataTable_Filter_ColumnCallbackReplace($this->dataTable, 'avg_time_on_site', create_function('$averageTimeOnSite', 'return Piwik::getPrettyTimeFromSeconds($averageTimeOnSite);'));
		$filter = new Piwik_DataTable_Filter_ColumnCallbackReplace($this->dataTable, 'bounce_rate', create_function('$bounceRate', 'return $bounceRate."%";'));
		$this->setColumnTranslation('nb_actions_per_visit', Piwik_Translate('General_ColumnActionsPerVisit'));
		$this->setColumnTranslation('avg_time_on_site', Piwik_Translate('General_ColumnAvgTimeOnSite'));
		$this->setColumnTranslation('bounce_rate', Piwik_Translate('General_ColumnBounceRate'));
	}
}
