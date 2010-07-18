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
 * @subpackage Piwik_ViewDataTable
 */
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
		Piwik_Controller::setPeriodVariablesView($this);
		$columnUniqueVisitors = false;
		if($this->period == 'day')
		{
			$columnUniqueVisitors = 'nb_uniq_visitors';
		}
		$this->setColumnsToDisplay(array('label', 
										'nb_visits', 
										$columnUniqueVisitors, 
										'nb_actions_per_visit', 
										'avg_time_on_site', 
										'bounce_rate'));
		$this->dataTable->filter('ColumnCallbackReplace', array('avg_time_on_site', create_function('$averageTimeOnSite', 'return Piwik::getPrettyTimeFromSeconds($averageTimeOnSite);')));
	}
}
