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
class Piwik_ViewDataTable_HtmlTable_Goals extends Piwik_ViewDataTable_HtmlTable 
{
	protected function getViewDataTableId()
	{
		return 'tableGoals';
	}
	
	public function main()
	{
		$this->idSite = Piwik_Common::getRequestVar('idSite', null, 'int');
		$this->processOnlyIdGoal = Piwik_Common::getRequestVar('filter_only_display_idgoal', 0, 'int');
		$this->viewProperties['show_exclude_low_population'] = true;
		$this->viewProperties['show_goals'] = true;
		
		$this->setColumnsTranslations( array(
			'goal_%s_conversion_rate' => '%s conversion rate',
			'goal_%s_nb_conversions' => '%s conversions',
			'goal_%s_revenue_per_visit' => '%s revenue per visit',
		
			'nb_conversions' => Piwik_Translate('Goals_ColumnConversions'), 
			'conversion_rate' => Piwik_Translate('Goals_ColumnConversionRate'), 
			'revenue' => Piwik_Translate('Goals_ColumnRevenue'),
    		'revenue_per_visit' => Piwik_Translate('General_ColumnValuePerVisit'),
		));
		
		$this->setColumnsToDisplay( array(	
			'label', 
			'nb_visits', 
			'goal_%s_nb_conversions',
			'goal_%s_conversion_rate',
			'goal_%s_revenue_per_visit',
		
			'conversion_rate',
			'revenue_per_visit',
		));
		
		// We ensure that the 'Sort by' column is actually displayed in the table
		// eg. most daily reports sort by nb_uniq_visitors but this column is not displayed in the Goals table
		$columnsToDisplay = $this->getColumnsToDisplay();
		$columnToSortBy = $this->getSortedColumn();
		if(!in_array($columnToSortBy, $columnsToDisplay))
		{
			$this->setSortedColumn('nb_visits', 'desc');
		}
		parent::main();
	}
	
	public function disableSubTableWhenShowGoals()
	{
		$this->controllerActionCalledWhenRequestSubTable = null;
	}
	
	public function setColumnsToDisplay($columnsNames)
	{
		$newColumnsNames = array();
		$goals = array();
		$idSite = $this->getIdSite();
		if($idSite)
		{
			$goals = Piwik_Goals_API::getInstance()->getGoals( $idSite );
		}
		foreach($columnsNames as $columnName)
		{
			if(in_array($columnName, array('goal_%s_conversion_rate', 'goal_%s_nb_conversions', 'goal_%s_revenue_per_visit')))
			{
				foreach($goals as $goal)
				{
					$idgoal = $goal['idgoal'];
					if($this->processOnlyIdGoal > Piwik_DataTable_Filter_UpdateColumnsWhenShowAllGoals::GOALS_FULL_TABLE
						&& $this->processOnlyIdGoal != $idgoal)
					{
						continue;
					}
					$name = Piwik_Translate($this->getColumnTranslation($columnName), $goal['name']);
					$columnNameGoal = str_replace('%s', $idgoal, $columnName);
					$this->setColumnTranslation($columnNameGoal, $name);
					if(strstr($columnNameGoal, '_rate') !== false)
					{
						$this->columnsToPercentageFilter[] = $columnNameGoal;
					}
					// For the goal table (when the flag icon is clicked), we only display the per Goal Conversion rate
					elseif($this->processOnlyIdGoal == Piwik_DataTable_Filter_UpdateColumnsWhenShowAllGoals::GOALS_OVERVIEW)
					{
						continue;
					}

					if(strstr($columnNameGoal, '_revenue') !== false)
					{
						$this->columnsToRevenueFilter[] = $columnNameGoal;
					}
					else
					{
						$this->columnsToConversionFilter[] = $columnNameGoal;
					}
					$newColumnsNames[] = $columnNameGoal;
				}
			}
			else
			{
				$newColumnsNames[] = $columnName;
			}
		}
		parent::setColumnsToDisplay($newColumnsNames);
	}
	
	protected function getRequestString()
	{
		$requestString = parent::getRequestString();
		if($this->processOnlyIdGoal > Piwik_DataTable_Filter_UpdateColumnsWhenShowAllGoals::GOALS_FULL_TABLE)
		{
			$requestString .= "&filter_only_display_idgoal=".$this->processOnlyIdGoal;
		}
		return $requestString . '&filter_update_columns_when_show_all_goals=1';
	}	
	
	protected $columnsToPercentageFilter = array();
	protected $columnsToRevenueFilter = array();
	protected $columnsToConversionFilter = array();
	protected $idSite = false;
	
	private function getIdSite()
	{
		return $this->idSite;
	}
	
	protected function postDataTableLoadedFromAPI()
	{
		parent::postDataTableLoadedFromAPI();
		$this->columnsToPercentageFilter[] = 'conversion_rate';
		foreach($this->columnsToPercentageFilter as $columnName)
		{
			$this->dataTable->filter('ColumnCallbackReplace', array($columnName, create_function('$rate', 'return sprintf("%.1f",$rate)."%";')));
		}
		$this->columnsToRevenueFilter[] = 'revenue_per_visit';
		foreach($this->columnsToRevenueFilter as $columnName)
		{
    		$this->dataTable->filter('ColumnCallbackReplace', array($columnName, create_function('$value', 'return sprintf("%.1f",$value);')));
    		$this->dataTable->filter('ColumnCallbackReplace', array($columnName, array("Piwik", "getPrettyMoney"), array($this->getIdSite())));
		}
		
		foreach($this->columnsToConversionFilter as $columnName)
		{
			// this ensures that the value is set to zero for all rows where the value was not set (no conversion)
    		$this->dataTable->filter('ColumnCallbackReplace', array($columnName, create_function('$value', 'return $value;')));
		}
	}
}
