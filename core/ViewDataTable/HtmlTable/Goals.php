<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_ViewDataTable
 */

require_once "ViewDataTable/HtmlTable.php";

class Piwik_ViewDataTable_HtmlTable_Goals extends Piwik_ViewDataTable_HtmlTable 
{
	protected function getViewDataTableId()
	{
		return 'tableGoals';
	}
	
	public function main()
	{
		$this->viewProperties['show_exclude_low_population'] = true;
		$this->viewProperties['show_goals'] = true;
		$this->setColumnsToDisplay( array(	'label', 
											'nb_visits', 
											'goals_conversion_rate',
											'goal_%s_conversion_rate',
											'revenue_per_visit',
							));
		parent::main();
	}
	
	public function disableSubTableWhenShowGoals()
	{
		$this->controllerActionCalledWhenRequestSubTable = null;
	}
	
	protected function getRequestString()
	{
		$requestString = parent::getRequestString();
		return $requestString . '&filter_update_columns_when_show_all_goals=1';
	}
	
	protected $columnsToPercentageFilter = array();

	private function getIdSite()
	{
		return Piwik_Common::getRequestVar('idSite', null, 'int');
	}
	
	public function setColumnsToDisplay($columnsNames)
	{
		$newColumnsNames = array();
		foreach($columnsNames as $columnName)
		{
			if($columnName == 'goal_%s_conversion_rate')
			{
				require_once "Tracker/GoalManager.php";
				require_once "Goals/API.php";
				$goals = Piwik_Goals_API::getGoals( $this->getIdSite() );
				foreach($goals as $goal)
				{
					$idgoal = $goal['idgoal'];
					$name = $goal['name'];
					$columnName = 'goal_'.$idgoal.'_conversion_rate';
					$newColumnsNames[] = $columnName;
					$this->setColumnTranslation($columnName, $name);
					$this->columnsToPercentageFilter[] = $columnName;
				}
			}
			else
			{
				$newColumnsNames[] = $columnName;
			}
		}
		parent::setColumnsToDisplay($newColumnsNames);
	}
	
	protected function postDataTableLoadedFromAPI()
	{
		parent::postDataTableLoadedFromAPI();
		$this->columnsToPercentageFilter[] = 'goals_conversion_rate';
		foreach($this->columnsToPercentageFilter as $columnName)
		{
			$this->dataTable->filter('ColumnCallbackReplace', array($columnName, create_function('$rate', 'return $rate."%";')));
		}
		$this->dataTable->filter('ColumnCallbackReplace', array('revenue_per_visit', array("Piwik", "getPrettyMoney")));
	}
}
