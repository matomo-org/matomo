<?php
require_once "ViewDataTable/HtmlTable.php";

class Piwik_ViewDataTable_HtmlTable_AllColumns extends Piwik_ViewDataTable_HtmlTable 
{
	protected function getViewDataTableId()
	{
		return 'tableAllColumns';
	}
	
	public function main()
	{
		$this->viewProperties['show_exclude_low_population'] = true;
		parent::main();
	}
	
	protected function getRequestString()
	{
		$requestString = parent::getRequestString();
		return $requestString . '&filter_add_columns_when_show_all_columns=1';
	}
	
	protected function postDataTableLoadedFromAPI()
	{
		parent::postDataTableLoadedFromAPI();
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
