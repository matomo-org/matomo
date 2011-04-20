<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
	
	/**
	 * Adds processed goal metrics to a table: 
	 * - global conversion rate, 
	 * - global revenue per visit.
	 * Can also process per-goal metrics:
	 * - conversion rate
	 * - nb conversions
	 * - revenue per visit
	 * 
	 * @param Piwik_DataTable $table
	 * @param bool $enable should be true (automatically set to true when filter_update_columns_when_show_all_goals is found in the API request)
	 * @param bool $processOnlyIdGoal Defines what metrics to add (don't process metrics when you don't display them)
	 * 			If self::GOALS_FULL_TABLE, all Goal metrics (and per goal metrics) will be processed
	 * 			If self::GOALS_OVERVIEW, only the main goal metrics will be added
	 * 			If an int > 0, then will process only metrics for this specific Goal
	 * @return void
	 */
	public function __construct( $table, $enable = true, $processOnlyIdGoal )
	{
		$this->processOnlyIdGoal = $processOnlyIdGoal;
		// Ensure that all rows with no visit but conversions will be displayed
		$this->deleteRowsWithNoVisit = false;
		parent::__construct($table);
	}
	
	public function filter($table)
	{
		// Add standard processed metrics
		parent::filter($table);
		$roundingPrecision = 2;
		$expectedColumns = array();

		foreach($table->getRows() as $key => $row)
		{
			$currentColumns = $row->getColumns();
			$newColumns = array();

			// visits could be undefined when there is a conversion but no visit
			$nbVisits = (int)$this->getColumn($row, Piwik_Archive::INDEX_NB_VISITS);
			$conversions = (int)$this->getColumn($row, Piwik_Archive::INDEX_NB_CONVERSIONS);
			$goals = $this->getColumn($currentColumns, Piwik_Archive::INDEX_GOALS);
			if($goals)
			{
				$revenue = 0;
				foreach($goals as $goalId => $columnValue)
				{
					$revenue += (int)$this->getColumn($columnValue, Piwik_Archive::INDEX_GOAL_REVENUE, Piwik_Archive::$mappingFromIdToNameGoal);
				}

				if($revenue == 0)
				{
					$revenue = (int)$this->getColumn($currentColumns, Piwik_Archive::INDEX_REVENUE);
				}
				// If no visit for this metric, but some conversions, we still want to display some kind of "revenue per visit" 
				// even though it will actually be in this edge case "Revenue per conversion"
				$revenuePerVisit = $this->invalidDivision;
				if($nbVisits > 0 
					|| $conversions > 0)
				{
					$revenuePerVisit = round( $revenue / ($nbVisits == 0 ? $conversions : $nbVisits), $roundingPrecision );
				}
				$newColumns['revenue_per_visit'] = $revenuePerVisit;
				
				if($this->processOnlyIdGoal == self::GOALS_MINIMAL_REPORT)
				{
					$row->addColumns($newColumns);
					continue;
				}
				
				foreach($goals as $goalId => $columnValue)
				{
					$goalId = str_replace("idgoal=", "", $goalId);
					if($this->processOnlyIdGoal > self::GOALS_FULL_TABLE
						&& $this->processOnlyIdGoal != $goalId)
					{
						continue;
					}
    				$conversions = (int)$this->getColumn($columnValue, Piwik_Archive::INDEX_GOAL_NB_CONVERSIONS, Piwik_Archive::$mappingFromIdToNameGoal);
					
					// Goal Conversion rate
					$name = 'goal_' . $goalId . '_conversion_rate';
					if($nbVisits == 0)
					{
						$value = $this->invalidDivision;
					}
					else
					{
						$value = round(100 * $conversions / $nbVisits, $roundingPrecision);
					}
        			$newColumns[$name] = $value. "%";
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
					// See comment above for $revenuePerVisit
					$revenuePerVisit = round( (float)$this->getColumn($columnValue, Piwik_Archive::INDEX_GOAL_REVENUE, Piwik_Archive::$mappingFromIdToNameGoal) / ($nbVisits == 0 ? $conversions : $nbVisits), $roundingPrecision );
					$newColumns[$name] = $revenuePerVisit;
					$expectedColumns[$name] = true;
					
				}
			}
			
			$row->addColumns($newColumns);
		}
		$expectedColumns['revenue_per_visit'] = true;
		
		// make sure all goals values are set, 0 by default
		// if no value then sorting would put at the end
		$expectedColumns = array_keys($expectedColumns);
		$rows = $table->getRows();
		foreach($rows as &$row)
		{
			foreach($expectedColumns as $name)
			{
				if(false === $row->getColumn($name))
				{
					$value = 0;
					if(strpos($name, 'conversion_rate') !== false)
					{
						$value = '0%';
					}
					$row->addColumn($name, $value);
				}
			}
		}
	}
}
