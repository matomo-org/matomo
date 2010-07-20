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
class Piwik_DataTable_Filter_AddColumnsProcessedMetricsGoal extends Piwik_DataTable_Filter_AddColumnsProcessedMetrics
{
	
	/*
	 * Process main goal metrics: conversion rate, revenue per visit
	 */
	const GOALS_MINIMAL_REPORT = -2;
	/*
	 * Process main goal metrics, and conversion rate per goal 
	 */
	const GOALS_OVERVIEW = -1;
	/*
	 * Process all goal and per-goal metrics 
	 */
	const GOALS_FULL_TABLE = 0;
	
	protected $mappingIdToNameGoal;
	
	/**
	 * Adds processed goal metrics to a table: 
	 * - global conversion rate, 
	 * - global revenue per visit.
	 * Can also process per-goal metrics:
	 * - conversion rate
	 * - nb conversions
	 * - revenue per visit
	 * 
	 * @param $table
	 * @param $enable should be true (automatically set to true when filter_update_columns_when_show_all_goals is found in the API request)
	 * @param $processOnlyIdGoal Defines what metrics to add (don't process metrics when you don't display them)
	 * 			If self::GOALS_FULL_TABLE, all Goal metrics (and per goal metrics) will be processed
	 * 			If self::GOALS_OVERVIEW, only the main goal metrics will be added
	 * 			If an int > 0, then will process only metrics for this specific Goal
	 * @return void
	 */
	public function __construct( $table, $enable = true, $processOnlyIdGoal )
	{
		$this->mappingIdToNameGoal = Piwik_Archive::$mappingFromIdToNameGoal;
		$this->processOnlyIdGoal = $processOnlyIdGoal;
		parent::__construct($table);
	}
	
	protected function filter()
	{
		$roundingPrecision = 2;
		$expectedColumns = array();
		foreach($this->table->getRows() as $key => $row)
		{
			$currentColumns = $row->getColumns();
			$newColumns = array();

			// visits could be undefined when there is a conversion but no visit
			$nbVisits = (int)$this->getColumn($row, Piwik_Archive::INDEX_NB_VISITS);

//			$newColumns['nb_visits'] = $nbVisits;
//			$newColumns['label'] = $currentColumns['label'];
			
			$goals = $this->getColumn($currentColumns, Piwik_Archive::INDEX_GOALS); 
			if($goals)
			{
				$revenue = (int)$this->getColumn($currentColumns, Piwik_Archive::INDEX_REVENUE);
				
				if($nbVisits == 0)
				{
					$revenuePerVisit = $this->invalidDivision;
				}
				else
				{
					$revenuePerVisit = round( $revenue / $nbVisits, $roundingPrecision );
				}
				$newColumns['revenue_per_visit'] = $revenuePerVisit;
				
				if($this->processOnlyIdGoal == self::GOALS_MINIMAL_REPORT)
				{
					$row->addColumns($newColumns);
					continue;
				}
				
				foreach($goals as $goalId => $columnValue)
				{
					if($this->processOnlyIdGoal > self::GOALS_FULL_TABLE
						&& $this->processOnlyIdGoal != $goalId)
					{
						continue;
					}
    				$conversions = (int)$this->getColumn($columnValue, Piwik_Archive::INDEX_GOAL_NB_CONVERSIONS);
					
					// Goal Conversion rate
					$name = 'goal_' . $goalId . '_conversion_rate';
					if($nbVisits == 0)
					{
						$value = $this->invalidDivision;
					}
					else
					{
						$value = round(100 * $conversions / $nbVisits, $roundingPrecision) . "%";
					}
					$newColumns[$name] = $value;
					$expectedColumns[$name] = true;
					
					// When the table is displayed by clicking on the flag icon, we only display the columns
					// Visits, Conversions, Per goal conversion rate, Revenue
					if($this->processOnlyIdGoal == self::GOALS_OVERVIEW)
					{
						continue;
					}
					
					// Goal Conversions
					$name = 'goal_' . $goalId . '_nb_conversions';
					$newColumns[$name] = $conversions;
					$expectedColumns[$name] = true;
					
					// Goal Revenue per visit
					$name = 'goal_' . $goalId . '_revenue_per_visit';
					if($nbVisits == 0)
					{
						$value = $this->invalidDivision;
					}
					else
					{
						$revenuePerVisit = round( (float)$this->getColumn($columnValue, Piwik_Archive::INDEX_GOAL_REVENUE) / $nbVisits, $roundingPrecision );
					}
					$newColumns[$name] = $revenuePerVisit;
					$expectedColumns[$name] = true;
					
				}
			}
			
			$row->addColumns($newColumns);
		}
		$expectedColumns['revenue_per_visit'] = true;
		$expectedColumns['conversion_rate'] = true;
		
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
