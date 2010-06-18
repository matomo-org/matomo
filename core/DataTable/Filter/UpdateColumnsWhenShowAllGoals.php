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
class Piwik_DataTable_Filter_UpdateColumnsWhenShowAllGoals extends Piwik_DataTable_Filter
{
	const GOALS_OVERVIEW = -1;
	const GOALS_FULL_TABLE = 0;
	protected $mappingIdToNameGoal;
	
	/**
	 * @param $table
	 * @param $enable Automatically set to true when filter_update_columns_when_show_all_goals is found in the API request
	 * @param $processOnlyIdGoal
	 * @return unknown_type
	 */
	public function __construct( $table, $enable = true, $processOnlyIdGoal )
	{
		parent::__construct($table);
		$this->mappingIdToNameGoal = Piwik_Archive::$mappingFromIdToNameGoal;
		$this->processOnlyIdGoal = $processOnlyIdGoal;
		$this->filter();
	}
	
	protected function filter()
	{
		$invalidDivision = 'N/A';
		$roundingPrecision = 2;
		$expectedColumns = array();
		foreach($this->table->getRows() as $key => $row)
		{
			$currentColumns = $row->getColumns();
			$newColumns = array();
			
			$nbVisits = 0;
			// visits could be undefined when there is a conversion but no visit
			if(isset($currentColumns[Piwik_Archive::INDEX_NB_VISITS]))
			{
				$nbVisits = $currentColumns[Piwik_Archive::INDEX_NB_VISITS];
			}
			$newColumns['nb_visits'] = $nbVisits;
			$newColumns['label'] = $currentColumns['label'];
			
			if(isset($currentColumns[Piwik_Archive::INDEX_GOALS]))
			{
				$nbVisitsConverted = $revenue = 0;
				if(isset($currentColumns[Piwik_Archive::INDEX_NB_VISITS_CONVERTED]))
				{
					$nbVisitsConverted = $currentColumns[Piwik_Archive::INDEX_NB_VISITS_CONVERTED];
					$revenue = $currentColumns[Piwik_Archive::INDEX_REVENUE];
				}
	
				if($nbVisitsConverted == 0)
				{
					$conversionRate = $invalidDivision;
				}
				else
				{
					$conversionRate = round(100 * $nbVisitsConverted / $nbVisits, $roundingPrecision);
				}
				$newColumns['goals_conversion_rate'] = $conversionRate;
				
				if($nbVisits == 0)
				{
					$revenuePerVisit = $invalidDivision;
				}
				else
				{
					$revenuePerVisit = round( $revenue / $nbVisits, $roundingPrecision );
				}
				$newColumns['revenue_per_visit'] = $revenuePerVisit;
				foreach($currentColumns[Piwik_Archive::INDEX_GOALS] as $goalId => $columnValue)
				{
					if($this->processOnlyIdGoal != 0
						&& $this->processOnlyIdGoal != $goalId)
					{
						continue;
					}
					$name = 'goal_' . $goalId . '_conversion_rate';
					if($nbVisits == 0)
					{
						$value = $invalidDivision;
					}
					else
					{
						$value = round(100 * $columnValue[Piwik_Archive::INDEX_GOAL_NB_CONVERSIONS] / $nbVisits, $roundingPrecision);
					}
					$newColumns[$name] = $value;
					$expectedColumns[$name] = true;
					
					$name = 'goal_' . $goalId . '_nb_conversions';
					$newColumns[$name] = $columnValue[Piwik_Archive::INDEX_GOAL_NB_CONVERSIONS];
					$expectedColumns[$name] = true;
					
					$name = 'goal_' . $goalId . '_revenue_per_visit';
					if($nbVisits == 0)
					{
						$value = $invalidDivision;
					}
					else
					{
						$revenuePerVisit = round( $columnValue[Piwik_Archive::INDEX_GOAL_REVENUE] / $nbVisits, $roundingPrecision );
					}
					$newColumns[$name] = $revenuePerVisit;
					$expectedColumns[$name] = true;
				}
			}
			
			$row->setColumns($newColumns);
		}
		$expectedColumns['revenue_per_visit'] = true;
		$expectedColumns['goals_conversion_rate'] = true;
		
		// make sure all goals values are set, 0 by default
		// if no value then sorting would put at the end
		$expectedColumns = array_keys($expectedColumns);
		$rows = $this->table->getRows();
		foreach($rows as &$row)
		{
			foreach($expectedColumns as $name)
			{
				if(false === $row->getColumn($name))
				{
					$row->addColumn($name, 0);
				}
			}
		}
	}
}
