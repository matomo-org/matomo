<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\DataTable\Filter;

use Exception;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Tracker\GoalManager;

/**
 * Adds the Goal related metrics to a DataTable using metrics that already exist.
 *
 * Metrics added are:
 * - **revenue_per_visit**: total goal and ecommerce revenue / nb_visits
 * - **goal_%idGoal%_conversion_rate**: the conversion rate. There will be one of
 *                                      these columns for each goal that exists
 *                                      for the site.
 * - **goal_%idGoal%_nb_conversions**: the number of conversions. There will be one of
 *                                     these columns for each goal that exists
 *                                     for the site.
 * - **goal_%idGoal%_revenue_per_visit**: goal revenue / nb_visits. There will be one of
 *                                        these columns for each goal that exists
 *                                        for the site.
 * - **goal_%idGoal%_revenue**: goal revenue. There will be one of
 *                              these columns for each goal that exists
 *                              for the site.
 * - **goal_%idGoal%_avg_order_revenue**: goal revenue / number of orders or abandoned
 *                                        carts. Only for ecommerce order and abandoned cart
 *                                        reports.
 * - **goal_%idGoal%_items**: number of items. Only for ecommerce order and abandoned cart
 *                            reports.
 * 
 * Adding the **filter_update_columns_when_show_all_goals** query parameter to
 * an API request will trigger the execution of this Filter.
 * 
 * Note: This filter must be called before [ReplaceColumnNames](#) is called.
 * 
 * **Basic usage example**
 * 
 *     $dataTable->filter('AddColumnsProcessedMetricsGoal',
 *         array($enable = true, $idGoal = Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER));
 * 
 * @package Piwik
 * @subpackage DataTable
 * @api
 */
class AddColumnsProcessedMetricsGoal extends AddColumnsProcessedMetrics
{
    /**
     * Process main goal metrics: conversion rate, revenue per visit
     */
    const GOALS_MINIMAL_REPORT = -2;

    /**
     * Process main goal metrics, and conversion rate per goal
     */
    const GOALS_OVERVIEW = -1;

    /**
     * Process all goal and per-goal metrics
     */
    const GOALS_FULL_TABLE = 0;

    /**
     * Constructor.
     * 
     * @param DataTable $table The table that will eventually filtered.
     * @param bool $enable Always set to true.
     * @param string $processOnlyIdGoal Defines what metrics to add (don't process metrics when you don't display them).
     *                                  If self::GOALS_FULL_TABLE, all Goal metrics (and per goal metrics) will be processed.
     *                                  If self::GOALS_OVERVIEW, only the main goal metrics will be added.
     *                                  If an int > 0, then will process only metrics for this specific Goal.
     */
    public function __construct($table, $enable = true, $processOnlyIdGoal)
    {
        $this->processOnlyIdGoal = $processOnlyIdGoal;
        $this->isEcommerce = $this->processOnlyIdGoal == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER || $this->processOnlyIdGoal == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART;
        parent::__construct($table);
        // Ensure that all rows with no visit but conversions will be displayed
        $this->deleteRowsWithNoVisit = false;
    }

    /**
     * Adds the processed metrics. See [AddColumnsProcessedMetrics](#AddColumnsProcessedMetrics) for
     * more information.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        // Add standard processed metrics
        parent::filter($table);
        $roundingPrecision = GoalManager::REVENUE_PRECISION;
        $expectedColumns = array();
        foreach ($table->getRows() as $key => $row) {
            $currentColumns = $row->getColumns();
            $newColumns = array();

            // visits could be undefined when there is a conversion but no visit
            $nbVisits = (int)$this->getColumn($row, Metrics::INDEX_NB_VISITS);
            $conversions = (int)$this->getColumn($row, Metrics::INDEX_NB_CONVERSIONS);
            $goals = $this->getColumn($currentColumns, Metrics::INDEX_GOALS);
            if ($goals) {
                $revenue = 0;
                foreach ($goals as $goalId => $goalMetrics) {
                    if ($goalId == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART) {
                        continue;
                    }
                    if ($goalId >= GoalManager::IDGOAL_ORDER
                        || $goalId == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER
                    ) {
                        $revenue += (int)$this->getColumn($goalMetrics, Metrics::INDEX_GOAL_REVENUE, Metrics::$mappingFromIdToNameGoal);
                    }
                }

                if ($revenue == 0) {
                    $revenue = (int)$this->getColumn($currentColumns, Metrics::INDEX_REVENUE);
                }
                if (!isset($currentColumns['revenue_per_visit'])) {
                    // If no visit for this metric, but some conversions, we still want to display some kind of "revenue per visit"
                    // even though it will actually be in this edge case "Revenue per conversion"
                    $revenuePerVisit = $this->invalidDivision;
                    if ($nbVisits > 0
                        || $conversions > 0
                    ) {
                        $revenuePerVisit = round($revenue / ($nbVisits == 0 ? $conversions : $nbVisits), $roundingPrecision);
                    }
                    $newColumns['revenue_per_visit'] = $revenuePerVisit;
                }
                if ($this->processOnlyIdGoal == self::GOALS_MINIMAL_REPORT) {
                    $row->addColumns($newColumns);
                    continue;
                }
                // Display per goal metrics
                // - conversion rate
                // - conversions
                // - revenue per visit
                foreach ($goals as $goalId => $goalMetrics) {
                    $goalId = str_replace("idgoal=", "", $goalId);
                    if (($this->processOnlyIdGoal > self::GOALS_FULL_TABLE
                            || $this->isEcommerce)
                        && $this->processOnlyIdGoal != $goalId
                    ) {
                        continue;
                    }
                    $conversions = (int)$this->getColumn($goalMetrics, Metrics::INDEX_GOAL_NB_CONVERSIONS, Metrics::$mappingFromIdToNameGoal);

                    // Goal Conversion rate
                    $name = 'goal_' . $goalId . '_conversion_rate';
                    if ($nbVisits == 0) {
                        $value = $this->invalidDivision;
                    } else {
                        $value = round(100 * $conversions / $nbVisits, $roundingPrecision);
                    }
                    $newColumns[$name] = $value . "%";
                    $expectedColumns[$name] = true;

                    // When the table is displayed by clicking on the flag icon, we only display the columns
                    // Visits, Conversions, Per goal conversion rate, Revenue
                    if ($this->processOnlyIdGoal == self::GOALS_OVERVIEW) {
                        continue;
                    }

                    // Goal Conversions
                    $name = 'goal_' . $goalId . '_nb_conversions';
                    $newColumns[$name] = $conversions;
                    $expectedColumns[$name] = true;

                    // Goal Revenue per visit
                    $name = 'goal_' . $goalId . '_revenue_per_visit';
                    // See comment above for $revenuePerVisit
                    $goalRevenue = (float)$this->getColumn($goalMetrics, Metrics::INDEX_GOAL_REVENUE, Metrics::$mappingFromIdToNameGoal);
                    $revenuePerVisit = round($goalRevenue / ($nbVisits == 0 ? $conversions : $nbVisits), $roundingPrecision);
                    $newColumns[$name] = $revenuePerVisit;
                    $expectedColumns[$name] = true;

                    // Total revenue
                    $name = 'goal_' . $goalId . '_revenue';
                    $newColumns[$name] = $goalRevenue;
                    $expectedColumns[$name] = true;

                    if ($this->isEcommerce) {

                        // AOV Average Order Value
                        $name = 'goal_' . $goalId . '_avg_order_revenue';
                        $newColumns[$name] = $goalRevenue / $conversions;
                        $expectedColumns[$name] = true;

                        // Items qty
                        $name = 'goal_' . $goalId . '_items';
                        $newColumns[$name] = $this->getColumn($goalMetrics, Metrics::INDEX_GOAL_ECOMMERCE_ITEMS, Metrics::$mappingFromIdToNameGoal);
                        $expectedColumns[$name] = true;
                    }
                }
            }

            // conversion_rate can be defined upstream apparently? FIXME
            try {
                $row->addColumns($newColumns);
            } catch (Exception $e) {
            }
        }
        $expectedColumns['revenue_per_visit'] = true;

        // make sure all goals values are set, 0 by default
        // if no value then sorting would put at the end
        $expectedColumns = array_keys($expectedColumns);
        $rows = $table->getRows();
        foreach ($rows as &$row) {
            foreach ($expectedColumns as $name) {
                if (false === $row->getColumn($name)) {
                    $value = 0;
                    if (strpos($name, 'conversion_rate') !== false) {
                        $value = '0%';
                    }
                    $row->addColumn($name, $value);
                }
            }
        }
    }
}